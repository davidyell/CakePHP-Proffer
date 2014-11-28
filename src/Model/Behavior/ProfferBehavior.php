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
use Cake\ORM\Table;
use Cake\Utility\String;
use Exception;
use Proffer\Event\ProfferListener;
use Proffer\Lib\ProfferPath;

/**
 * Proffer behavior
 */
class ProfferBehavior extends Behavior {

/**
 * Default configuration.
 *
 * @var array
 */
	protected $_defaultConfig = [];

/**
 * Initialize the behavior
 *
 * @param array $config Array of pass configuration
 * @return void
 */
	public function initialize(array $config) {
		$listener = new ProfferListener();
		$this->_table->eventManager()->attach($listener);
	}

/**
 * beforeValidate method
 *
 * @param Event $event The event
 * @param Entity $entity The current entity
 * @param ArrayObject $options Array of options
 * @return true
 */
	public function beforeValidate(Event $event, Entity $entity, ArrayObject $options) {
		foreach ($this->config() as $field => $settings) {
			if ($this->_table->validator()->isEmptyAllowed($field, false) && $entity->get($field)['error'] === UPLOAD_ERR_NO_FILE) {
				$entity->__unset($field);
			}
		}

		return true;
	}

/**
 * beforeSave method
 *
 * @param Event $event The event
 * @param Entity $entity The entity
 * @param ArrayObject $options Array of options
 * @return true
 * @throws Exception
 */
	public function beforeSave(Event $event, Entity $entity, ArrayObject $options) {
		foreach ($this->config() as $field => $settings) {
			if ($entity->has($field) && is_array($entity->get($field)) && $entity->get($field)['error'] === UPLOAD_ERR_OK) {

				if (!$this->_isUploadedFile($entity->get($field)['tmp_name'])) {
					throw new Exception('File must be uploaded using HTTP post.');
				}

				$path = new ProfferPath($this->_table, $entity, $field, $settings);
				$path->createPathFolder();

				if ($this->_moveUploadedFile($entity->get($field)['tmp_name'], $path->fullPath())) {
					$entity->set($field, $entity->get($field)['name']);
					$entity->set($settings['dir'], $path->getSeed());

					// Don't generate thumbnails for non-images
					if (getimagesize($path->fullPath()) !== false) {
						$this->_makeThumbs($field, $path);
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
 * @return bool
 */
	public function afterDelete(Event $event, Entity $entity, ArrayObject $options) {
		foreach ($this->config() as $field => $settings) {

			$filename = $entity->get($field);
			$dir = $entity->get($settings['dir']);

			if (!empty($entity) && !empty($dir)) {
				$path = $this->_buildPath($this->_table, $entity, $field, $filename);

				foreach ($settings['thumbnailSizes'] as $prefix => $dimensions) {
					$filename = $path['parts']['root'] . DS . $path['parts']['table'] . DS . $path['parts']['seed'] . DS . $prefix . '_' . $entity->get($field);
					unlink($filename);
				}

				$filename = $path['parts']['root'] . DS . $path['parts']['table'] . DS . $path['parts']['seed'] . DS . $entity->get($field);
				unlink($filename);

				rmdir($path['parts']['root'] . DS . $path['parts']['table'] . DS . $path['parts']['seed'] . DS);
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
	protected function _makeThumbs($field, ProfferPath $path) {
		foreach ($this->config($field)['thumbnailSizes'] as $prefix => $dimensions) {

			$eventParams = ['path' => $path, 'dimensions' => $dimensions, 'thumbnailMethod' => null];

			if (isset($this->config($field)['thumbnailMethod'])) {
				$params['thumbnailMethod'] = $this->config($field)['thumbnailMethod'];
			}

			// Event listener handles generation
			$event = new Event('Proffer.beforeThumbs', $this->_table, $eventParams);

			$this->_table->eventManager()->dispatch($event);
			if (!empty($event->result)) {
				$image = $event->result;
			}

			$event = new Event('Proffer.afterThumbs', $this->_table, [
				'image' => $image,
				'path' => $path,
				'prefix' => $prefix
			]);

			$this->_table->eventManager()->dispatch($event);
			if (!empty($event->result)) {
				$image = $event->result;
			}
		}
	}

/**
 * Build a path to upload a file to. Both parts and full path
 *
 * @deprecated
 *
 * @param Table $table The table
 * @param Entity $entity The entity
 * @param string $field The upload field name
 * @param string $filename The name of the file
 * @return array
 */
	protected function _buildPath(Table $table, Entity $entity, $field, $filename) {
		var_dump(debug_backtrace());exit;
	}

/**
 * Wrapper method for the shell
 *
 * @deprecated
 *
 * @param Table $table The table
 * @param Entity $entity The entity
 * @param string $field The upload field name
 * @param string $filename The name of the file
 * @return array
 */
	public function getPath(Table $table, Entity $entity, $field, $filename) {
		var_dump(debug_backtrace());exit;
	}

/**
 * Wrapper method for is_uploaded_file so that we can test
 *
 * @param string $file The tmp_name path to the uploaded file
 * @return bool
 */
	protected function _isUploadedFile($file) {
		return is_uploaded_file($file);
	}

/**
 * Wrapper method for move_uploaded_file so that we can test
 *
 * @param string $file Path to the uploaded file
 * @param string $destination The destination file name
 * @return bool
 */
	protected function _moveUploadedFile($file, $destination) {
		return move_uploaded_file($file, $destination);
	}
}
