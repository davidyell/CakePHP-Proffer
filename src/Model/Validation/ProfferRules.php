<?php
/**
 * Custom validation rules for validating uploads
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Model\Validation;

use Cake\Validation\Validation;

class ProfferRules extends Validation
{

    /**
     * Validate the dimensions of an image. If the file isn't an image then validation will fail
     *
     * @param array $value An array of the name and value of the field
     * @param array $dimensions Array of rule dimensions for example
     * ['dimensions', [
     *        'min' => ['w' => 100, 'h' => 100],
     *        'max' => ['w' => 500, 'h' => 500]
     * ]]
     * would validate a minimum size of 100x100 pixels and a maximum of 500x500 pixels
     * @return bool
     */
    public static function dimensions($value, array $dimensions)
    {
        $fileDimensions = getimagesize($value['tmp_name']);

        if ($fileDimensions === false) {
            return false;
        }

        $sourceWidth = $fileDimensions[0];
        $sourceHeight = $fileDimensions[1];

        foreach ($dimensions as $rule => $sizes) {
            if ($rule === 'min') {
                if (isset($sizes['w']) && $sourceWidth < $sizes['w']) {
                    return false;
                }
                if (isset($sizes['h']) && $sourceHeight < $sizes['h']) {
                    return false;
                }
            } elseif ($rule === 'max') {
                if (isset($sizes['w']) && $sourceWidth > $sizes['w']) {
                    return false;
                }
                if (isset($sizes['h']) && $sourceHeight > $sizes['h']) {
                    return false;
                }
            }
        }

        return true;
    }
}
