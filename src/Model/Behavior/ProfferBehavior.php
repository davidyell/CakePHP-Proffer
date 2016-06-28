<?php
/**
 * Proffer
 * An upload behavior plugin for CakePHP 3
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Model\Behavior;

use ArrayObject;
use Cake\Database\Type;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Exception;
use Proffer\Lib\ImageTransform;
use Proffer\Lib\ProfferPath;
use Proffer\Lib\ProfferPathInterface;

/**
 * Proffer behavior
 */
class ProfferBehavior extends Behavior
{
    /**
     * Build the behaviour
     *
     * @param array $config Passed configuration
     *
     * @return void
     */
    public function initialize(array $config)
    {
        Type::map('proffer.file', '\Proffer\Database\Type\FileType');
        $schema = $this->_table->schema();
        foreach (array_keys($this->config()) as $field) {
            $schema->columnType($field, 'proffer.file');
        }
        $this->_table->schema($schema);
    }

    /**
     * beforeMarshal event
     *
     * If a field is allowed to be empty as defined in the validation it should be unset to prevent processing
     *
     * @param \Cake\Event\Event $event Event instance
     * @param ArrayObject $data Data to process
     * @param ArrayObject $options Array of options for event
     *
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        foreach ($this->config() as $field => $settings) {
            if ($this->_table->validator()->isEmptyAllowed($field, false) &&
                isset($data[$field]['error']) && $data[$field]['error'] === UPLOAD_ERR_NO_FILE
            ) {
                unset($data[$field]);
            }
        }
    }

    /**
     * beforeSave method
     *
     * Process any uploaded files, generate paths, move the files and kick of thumbnail generation if it's an image
     *
     * @param \Cake\Event\Event $event The event
     * @param \Cake\Datasource\EntityInterface $entity The entity
     * @param ArrayObject $options Array of options
     * @param \Proffer\Lib\ProfferPathInterface $path Inject an instance of ProfferPath
     *
     * @return true
     *
     * @throws \Exception
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options, ProfferPathInterface $path = null)
    {
        foreach ($this->config() as $field => $settings) {
            if ($entity->has($field) && is_array($entity->get($field)) &&
                $entity->get($field)['error'] === UPLOAD_ERR_OK) {
                // Allow path to be injected or set in config
                if (!empty($settings['pathClass'])) {
                    $path = new $settings['pathClass']($this->_table, $entity, $field, $settings);
                } elseif (!isset($path)) {
                    $path = new ProfferPath($this->_table, $entity, $field, $settings);
                }

                $event = new Event('Proffer.afterPath', $entity, ['path' => $path]);
                $this->_table->eventManager()->dispatch($event);
                if (!empty($event->result)) {
                    $path = $event->result;
                }

                $path->createPathFolder();

                if ($this->moveUploadedFile($entity->get($field)['tmp_name'], $path->fullPath())) {
                    $imagePaths = [$path->fullPath()];

                    $entity->set($field, $path->getFilename());
                    $entity->set($settings['dir'], $path->getSeed());

                    // Only generate thumbnails for image uploads
                    if (getimagesize($path->fullPath()) !== false && isset($settings['thumbnailSizes'])) {
                        // Allow the transformation class to be injected
                        if (!empty($settings['transformClass'])) {
                            $imageTransform = new $settings['transformClass']($this->_table, $path);
                        } else {
                            $imageTransform = new ImageTransform($this->_table, $path);
                        }

                        $thumbnailPaths = $imageTransform->processThumbnails($settings);
                        $imagePaths = array_merge($imagePaths, $thumbnailPaths);

                        $eventData = ['path' => $path, 'images' => $imagePaths];
                        $event = new Event('Proffer.afterCreateImage', $entity, $eventData);
                        $this->_table->eventManager()->dispatch($event);
                    }
                } else {
                    throw new Exception('Cannot upload file');
                }
            }
            unset($path);
        }

        return true;
    }

    /**
     * afterDelete method
     *
     * Remove images from records which have been deleted, if they exist
     *
     * @param \Cake\Event\Event $event The passed event
     * @param \Cake\Datasource\EntityInterface $entity The entity
     * @param ArrayObject $options Array of options
     * @param \Proffer\Lib\ProfferPathInterface $path Inject an instance of ProfferPath
     *
     * @return true
     */
    public function afterDelete(Event $event, EntityInterface $entity, ArrayObject $options, ProfferPathInterface $path = null)
    {
        foreach ($this->config() as $field => $settings) {
            $dir = $entity->get($settings['dir']);

            if (!empty($entity) && !empty($dir)) {
                if (!empty($settings['pathClass'])) {
                    $path = new $settings['pathClass']($this->_table, $entity, $field, $settings);
                } elseif (!isset($path)) {
                    $path = new ProfferPath($this->_table, $entity, $field, $settings);
                }

                $event = new Event('Proffer.beforeDeleteFolder', $entity, ['path' => $path]);
                $this->_table->eventManager()->dispatch($event);
                $path->deleteFiles($path->getFolder(), true);
            }

            $path = null;
        }

        return true;
    }

    /**
     * Wrapper method for move_uploaded_file
     * This will check if the file has been uploaded or not before picking the correct method to move the file
     *
     * @param string $file Path to the uploaded file
     * @param string $destination The destination file name
     *
     * @return bool
     */
    protected function moveUploadedFile($file, $destination)
    {
        if (is_uploaded_file($file)) {
            return move_uploaded_file($file, $destination);
        }

        return rename($file, $destination);
    }
}
