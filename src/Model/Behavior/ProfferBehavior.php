<?php
declare(strict_types=1);

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
use Proffer\Exception\InvalidClassException;
use Proffer\Lib\ImageTransform;
use Proffer\Lib\ImageTransformInterface;
use Proffer\Lib\ProfferPath;
use Proffer\Lib\ProfferPathInterface;
use Psr\Http\Message\UploadedFileInterface;

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
    public function initialize(array $config): void
    {
        Type::map('proffer.file', '\Proffer\Database\Type\FileType');
        $schema = $this->_table->getSchema();
        foreach (array_keys($this->getConfig()) as $field) {
            if (is_string($field)) {
                $schema->setColumnType($field, 'proffer.file');
            }
        }
        $this->_table->setSchema($schema);
    }

    /**
     * beforeMarshal event
     *
     * If a field is allowed to be empty as defined in the validation it should be unset to prevent processing
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \ArrayObject $data Data to process
     * @param \ArrayObject $options Array of options for event
     *
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        foreach ($this->getConfig() as $field => $settings) {
            if (!isset($data[$field])) {
                continue;
            }
            /** @var \Laminas\Diactoros\UploadedFile $upload */
            $upload = $data[$field];
            if (
                $this->_table->getValidator()->isEmptyAllowed($field, false) &&
                $upload instanceof UploadedFileInterface &&
                $upload->getError() === UPLOAD_ERR_NO_FILE
            ) {
                unset($data[$field]);
            }
        }
    }

    /**
     * beforeSave method
     *
     * Hook the beforeSave to process the request data
     *
     * @param \Cake\Event\Event $event The event
     * @param \Cake\Datasource\EntityInterface $entity The entity
     * @param \ArrayObject $options Array of options
     * @param \Proffer\Lib\ProfferPathInterface|null $path Inject an instance of ProfferPath
     *
     * @return true
     * @throws \Exception
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options, ?ProfferPathInterface $path = null)
    {
        foreach ($this->getConfig() as $field => $settings) {
            if ($entity->has($field) && $entity->get($field) instanceof UploadedFileInterface) {
                if ($entity->get($field)->getError() === UPLOAD_ERR_OK) {
                    $this->process($field, $settings, $entity, $path);
                } else {
                    throw new \Exception("Cannot find anything to process for the field `$field`");
                }
            }
        }

        return true;
    }

    /**
     * Process any uploaded files, generate paths, move the files and kick off thumbnail generation if it's an image
     *
     * @param string $field The upload field name
     * @param array $settings Array of upload settings for the field
     * @param \Cake\Datasource\EntityInterface $entity The current entity to process
     * @param \Proffer\Lib\ProfferPathInterface|null $path Inject an instance of ProfferPath
     *
     * @return void
     * @throws \Exception If the file cannot be renamed / moved to the correct path
     */
    protected function process($field, array $settings, EntityInterface $entity, ?ProfferPathInterface $path = null)
    {
        $path = $this->createPath($entity, $field, $settings, $path);

        if ($entity->get($field) instanceof UploadedFileInterface && !\is_array($entity->get($field))) {
            $uploadList = [$entity->get($field)];
        } else {
            $uploadList = $entity->get($field);
        }

        foreach ($uploadList as $upload) {
            /** @var \Laminas\Diactoros\UploadedFile $upload */
            try {
                $upload->moveTo($path->fullPath());

                $entity->set($field, $path->getFilename());
                $entity->set($settings['dir'], $path->getSeed());

                $this->createThumbnails($entity, $settings, $path);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        unset($path);
    }

    /**
     * Load a path class instance and create the path for the uploads to be moved into
     *
     * @param \Cake\Datasource\EntityInterface $entity Instance of the entity
     * @param string $field The upload field name
     * @param array $settings Array of upload settings for the field
     * @param \Proffer\Lib\ProfferPathInterface|null $path Inject an instance of ProfferPath
     *
     * @return \Proffer\Lib\ProfferPathInterface
     * @throws \Proffer\Exception\InvalidClassException If the custom class doesn't implement the interface
     */
    protected function createPath(EntityInterface $entity, $field, array $settings, ?ProfferPathInterface $path = null)
    {
        if (!empty($settings['pathClass'])) {
            $path = new $settings['pathClass']($this->_table, $entity, $field, $settings);
            if (!$path instanceof ProfferPathInterface) {
                throw new InvalidClassException("Class {$settings['pathClass']} does not implement the ProfferPathInterface.");
            }
        } elseif (!isset($path)) {
            $path = new ProfferPath($this->_table, $entity, $field, $settings);
        }

        $event = new Event('Proffer.afterPath', $entity, ['path' => $path]);
        $this->_table->getEventManager()->dispatch($event);
        if (!empty($event->getResult())) {
            $path = $event->getResult();
        }

        $path->createPathFolder();

        return $path;
    }

    /**
     * Create a new image transform instance, and create any configured thumbnails; if the upload is an image and there
     * are thumbnails configured.
     *
     * @param \Cake\Datasource\EntityInterface $entity Instance of the entity
     * @param array $settings Array of upload field settings
     * @param \Proffer\Lib\ProfferPathInterface $path Instance of the path class
     *
     * @return void
     * @throws \Proffer\Exception\InvalidClassException If the transform class doesn't implement the interface
     */
    protected function createThumbnails(EntityInterface $entity, array $settings, ProfferPathInterface $path)
    {
        if (!isset($settings['thumbnailSizes']) || getimagesize($path->fullPath()) === false) {
            return;
        }

        $imagePaths = [$path->fullPath()];

        if (!empty($settings['transformClass'])) {
            $imageTransform = new $settings['transformClass']($this->_table, $path);
            if (!$imageTransform instanceof ImageTransformInterface) {
                throw new InvalidClassException("Class {$settings['pathClass']} does not implement the ImageTransformInterface.");
            }
        } else {
            $imageTransform = new ImageTransform($this->_table, $path);
        }

        $thumbnailPaths = $imageTransform->processThumbnails($settings);
        $imagePaths = array_merge($imagePaths, $thumbnailPaths);

        $eventData = ['path' => $path, 'images' => $imagePaths];
        $event = new Event('Proffer.afterCreateImage', $entity, $eventData);
        $this->_table->getEventManager()->dispatch($event);
    }

    /**
     * afterDelete method
     *
     * Remove images from records which have been deleted, if they exist
     *
     * @param \Cake\Event\Event $event The passed event
     * @param \Cake\Datasource\EntityInterface $entity The entity
     * @param \ArrayObject $options Array of options
     * @param \Proffer\Lib\ProfferPathInterface $path Inject an instance of ProfferPath
     *
     * @return true
     */
    public function afterDelete(Event $event, EntityInterface $entity, ArrayObject $options, ?ProfferPathInterface $path = null)
    {
        foreach ($this->getConfig() as $field => $settings) {
            $dir = $entity->get($settings['dir']);

            if (!empty($entity) && !empty($dir)) {
                if (!empty($settings['pathClass'])) {
                    $path = new $settings['pathClass']($this->_table, $entity, $field, $settings);
                } elseif (!isset($path)) {
                    $path = new ProfferPath($this->_table, $entity, $field, $settings);
                }

                $event = new Event('Proffer.beforeDeleteFolder', $entity, ['path' => $path]);
                $this->_table->getEventManager()->dispatch($event);
                $path->deleteFiles($path->getFolder(), true);
            }

            $path = null;
        }

        return true;
    }
}
