<?php
/**
 * Custom validation rules for validating uploads
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Model\Validation;

use Cake\Validation\Validator;

class ProfferRules extends Validator {

	public static function filesize($value, array $context) {
		var_dump(__METHOD__ . ' in ' . __FILE__);
		var_dump(func_get_args());
		exit;
	}

	public static function extension($value, array $context) {
		var_dump(__METHOD__ . ' in ' . __FILE__);
	}
}