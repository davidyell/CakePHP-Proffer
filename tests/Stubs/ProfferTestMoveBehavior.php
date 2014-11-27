<?php
namespace Proffer\Tests\Stubs;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Proffer\Model\Behavior\ProfferBehavior;

/**
 * Class ProfferTestBehavior
 * Test stub class to allow overloading of certain methods
 *
 * @package Proffer\Tests\Stubs
 */
class ProfferTestMoveBehavior extends ProfferBehavior {

/**
 * Check is uploaded file
 *
 * @param string $file The filename
 * @return bool
 */
	protected function _isUploadedFile($file) {
		return true;
	}

/**
 * Make a path
 *
 * @param Table $table The table instance
 * @param Entity $entity The entity instance
 * @param string $field The field
 * @param string $filename The filename
 * @return array
 */
	protected function _buildPath(Table $table, Entity $entity, $field, $filename) {
		return [
			'full' => TMP . 'Tests' . DS . 'proffer_test' . DS . $filename,
			'parts' => [
				'root' => TMP,
				'table' => 'Tests',
				'seed' => 'proffer_test',
				'name' => $filename
			]
		];
	}

/**
 * Move uploaded file
 *
 * @param string $file The filename
 * @param string $destination The file path destination
 * @return bool
 */
	protected function _moveUploadedFile($file, $destination) {
		return false;
	}

}