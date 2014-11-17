<?php
namespace Proffer\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Utility\String;

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
	$path = WWW_ROOT . 'files' . DS;
	$file = $this->request->data['photo'];
	if ($file['error'] == UPLOAD_ERR_OK) {
		if (is_uploaded_file($file['tmp_name'])) {
			$tmp = $file['tmp_name'];
			$name = $file['name'];
			move_uploaded_file($tmp, $path . $name);

			$player->set('photo', $name);
		}
	}
	 */

/**
 * beforeSave method
 *
 * @param Event $event
 * @param Entity $entity
 * @param ArrayObject $options
 * @return bool
 */
	public function beforeSave(Event $event, Entity $entity, ArrayObject $options) {
		/* @var \Cake\ORM\Table $table */
		$table = $event->subject();

		foreach ($this->config() as $field => $settings) {
			if ($entity->has($field) && is_array($entity->get($field)) && $entity->get($field)['error'] === UPLOAD_ERR_OK) {

				if (!is_uploaded_file($entity->get($field)['tmp_name'])) {
					throw new BadRequestException('File must be uploaded using HTTP post.');
				}

				$path = $this->buildPath($table, $entity, $field);

				if (move_uploaded_file($entity->get($field)['tmp_name'], $path['full'])) {
					$entity->set($field, $entity->get($field)['name']);
					$entity->set($settings['dir'], $path['parts']['seed']);
				}
			}
		}

		return true;
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
		$path['seed'] = String::uuid();
		$path['name'] = $entity->get($field)['name'];

		$fullPath = implode(DS, $path);
		if (file_exists($fullPath)) {
			$this->buildPath($table, $entity, $field);
		} else {
			mkdir($path['root'] . DS . $path['table'] . DS . $path['seed'] . DS, 0777, true);
		}

		return ['full' => $fullPath, 'parts' => $path];
	}

}
