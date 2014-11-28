<?php
namespace Proffer\Tests\Stubs;

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