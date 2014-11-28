<?php
namespace Proffer\Tests\Stubs;

use Proffer\Model\Behavior\ProfferBehavior;

/**
 * Class ProfferTestBehavior
 * Test stub class to allow overloading of certain methods
 *
 * @package Proffer\Tests\Stubs
 */
class ProfferTestPathBehavior extends ProfferBehavior {

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
		if (!file_exists(TMP . 'ProfferTests' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' . DS)) {
			mkdir(TMP . 'ProfferTests' . DS . 'proffertest' . DS . 'photo' . DS . 'proffer_test' . DS, 0777, true);
		}

		return copy($file, $destination);
	}

}