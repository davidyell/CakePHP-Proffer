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
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Utility\String;
use Proffer\Event\ImageTransform;

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
 * @param array $config
 * @return void
 */
	public function initialize(array $config) {
		$imageTransform = new ImageTransform();
		$this->_table->eventManager()->attach($imageTransform);
	}

/**
 * beforeValidate method
 *
 * @param Event $event
 * @param Entity $entity
 * @param ArrayObject $options
 */
	public function beforeValidate(Event $event, Entity $entity, ArrayObject $options) {
		foreach ($this->config() as $field => $settings) {
			if ($this->_table->validator()->isEmptyAllowed($field, false) && $entity->get($field)['error'] === UPLOAD_ERR_NO_FILE) {
				$entity->__unset($field);
			}
		}
	}

/**
 * beforeSave method
 *
 * @param Event $event
 * @param Entity $entity
 * @param ArrayObject $options
 * @return bool
 */
	public function beforeSave(Event $event, Entity $entity, ArrayObject $options) {
		foreach ($this->config() as $field => $settings) {
			if ($entity->has($field) && is_array($entity->get($field)) && $entity->get($field)['error'] === UPLOAD_ERR_OK) {

				if (!is_uploaded_file($entity->get($field)['tmp_name'])) {
					throw new BadRequestException('File must be uploaded using HTTP post.');
				}

				$path = $this->buildPath($this->_table, $entity, $field);

				if (move_uploaded_file($entity->get($field)['tmp_name'], $path['full'])) {
					$entity->set($field, $entity->get($field)['name']);
					$entity->set($settings['dir'], $path['parts']['seed']);

					// Don't generate thumbnails for non-images
					if (getimagesize($path['full']) !== false) {
						$this->makeThumbs($field, $path);
					}
				}
			}
		}

		return true;
	}

/**
 * Generate the defined thumbnails
 *
 * @param string $field The name of the upload field
 * @param string $path The path array
 * @return void
 */
	protected function makeThumbs($field, $path) {
		foreach ($this->config($field)['thumbnailSizes'] as $prefix => $dimensions) {

			// Event listener handles generation
			$event = new Event('Proffer.beforeThumbs', $this->_table, [
				'path' => $path,
				'thumbnailMethod' => $this->config($field)['thumbnailMethod'],
				'dimensions' => $dimensions
			]);

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
 * @param Table $table
 * @param Entity $entity
 * @param $field
 * @return array
 */
	protected function buildPath(Table $table, Entity $entity, $field) {
		$path['root'] = WWW_ROOT . 'files';
		$path['table'] = strtolower($table->alias());

		if (!empty($entity->get($this->config($field)['dir']))) {
			$path['seed'] = $entity->get($this->config($field)['dir']);
		} else {
			$path['seed'] = String::uuid();
		}

		$path['name'] = $entity->get($field)['name'];

		$fullPath = implode(DS, $path);

		if (!file_exists($path['root'] . DS . $path['table'] . DS . $path['seed'] . DS)) {
			mkdir($path['root'] . DS . $path['table'] . DS . $path['seed'] . DS, 0777, true);
		}

		return ['full' => $fullPath, 'parts' => $path];
	}

}
