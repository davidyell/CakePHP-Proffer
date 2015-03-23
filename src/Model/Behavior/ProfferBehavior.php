<?php
/**
 * Proffer
 * An upload behavior plugin for CakePHP 3
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Exception;
use Proffer\Event\ProfferListener;
use Proffer\Lib\ProfferPath;

/**
 * Proffer behavior
 */
class ProfferBehavior extends Behavior
{
    /**
     * Initialize the behavior
     *
     * @param array $config Array of pass configuration
     * @return void
     */
    public function initialize(array $config)
    {
        $listener = new ProfferListener();
        $this->_table->eventManager()->on($listener);
    }

    /**
     * beforeMarshal event
     *
     * @param Event $event
     * @param ArrayObject $data
     * @param ArrayObject $options
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
     * @param Event $event The event
     * @param Entity $entity The entity
     * @param ArrayObject $options Array of options
     * @param ProfferPath $path Inject an instance of ProfferPath
     * @return true
     * @throws Exception
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options, ProfferPath $path = null)
    {
        foreach ($this->config() as $field => $settings) {
            if ($entity->has($field) && is_array($entity->get($field)) &&
                $entity->get($field)['error'] === UPLOAD_ERR_OK) {
                if (!$path) {
                    $path = new ProfferPath($this->_table, $entity, $field, $settings);
                }

                $event = new Event('Proffer.afterPath', $entity, ['path' => $path]);
                $this->_table->eventManager()->dispatch($event);
                if (!empty($event->result)) {
                    $path = $event->result;
                }

                $path->createPathFolder();

                if ($this->moveUploadedFile($entity->get($field)['tmp_name'], $path->fullPath())) {
                    $entity->set($field, $entity->get($field)['name']);
                    $entity->set($settings['dir'], $path->getSeed());

                    // Don't generate thumbnails for non-images
                    if (getimagesize($path->fullPath()) !== false && isset($settings['thumbnailSizes'])) {
                        $this->makeThumbs($field, $path);
                    }
                } else {
                    throw new Exception('Cannot move file');
                }
            }
        }

        return true;
    }

    /**
     * afterDelete method
     *
     * Remove images from records which have been deleted, if they exist
     *
     * @param Event $event The passed event
     * @param Entity $entity The entity
     * @param ArrayObject $options Array of options
     * @param ProfferPath $path Inject and instance of ProfferPath
     * @return bool
     */
    public function afterDelete(Event $event, Entity $entity, ArrayObject $options, ProfferPath $path = null)
    {
        foreach ($this->config() as $field => $settings) {
            $dir = $entity->get($settings['dir']);

            if (!empty($entity) && !empty($dir)) {
                if (!$path) {
                    $path = new ProfferPath($this->_table, $entity, $field, $settings);
                }

                $path->deleteFiles($path->getFolder(), true);
            }
        }

        return true;
    }

    /**
     * Dispatch events to allow generation of thumbnails
     *
     * @param string $field The name of the upload field
     * @param ProfferPath $path The path array
     * @return void
     */
    protected function makeThumbs($field, ProfferPath $path)
    {
        foreach ($this->config($field)['thumbnailSizes'] as $prefix => $dimensions) {
            $eventParams = ['path' => $path, 'dimensions' => $dimensions, 'thumbnailMethod' => null];

            if (isset($this->config($field)['thumbnailMethod'])) {
                $eventParams['thumbnailMethod'] = $this->config($field)['thumbnailMethod'];
            }

            // Event listener handles generation
            $event = new Event('Proffer.beforeThumbs', $this->_table, $eventParams);

            $this->_table->eventManager()->dispatch($event);
            if (!empty($event->result)) {
                $image = $event->result;

                $event = new Event('Proffer.afterThumbs', $this->_table, [
                    'path' => $path,
                    'image' => $image,
                    'prefix' => $prefix
                ]);
            }

            $this->_table->eventManager()->dispatch($event);
            if (!empty($event->result)) {
                $image = $event->result;
            }
        }
    }

    /**
     * Wrapper method for move_uploaded_file
     * This will check if the file has been uploaded or not before picking the correct method to move the file
     *
     * @param string $file Path to the uploaded file
     * @param string $destination The destination file name
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
