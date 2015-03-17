<?php
/**
 * Custom validation rules for validating uploads
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Model\Validation;

use Cake\Core\Exception\Exception;
use Cake\Validation\Validator;
use finfo;
use Proffer\Error\DisabledExtension;

class ProfferRules extends Validator
{

    /**
     * Check the size of the image
     *
     * @param array $value An array of the name and value of the field
     * @param int $size Filesize in bytes
     * @return bool
     */
    public static function filesize($value, $size)
    {
        if ($value['size'] <= $size) {
            return true;
        }

        return false;
    }

    /**
     * Make sure the extension matches the allowed
     *
     * @param array $value An array of the name and value of the field
     * @param array $extensions Array of file extensions to allow
     * @return bool
     */
    public static function extension($value, array $extensions)
    {
        $extension = pathinfo($value['name'], PATHINFO_EXTENSION);

        if (in_array($extension, $extensions)) {
            return true;
        }

        return false;
    }

    /**
     * Check the mimetype of the file
     *
     * For a full list of mime types
     * http://www.sitepoint.com/web-foundations/mime-types-complete-list/
     *
     * @param array $value An array of the name and value of the field
     * @param array $types An array of mime type strings to match
     * @return bool
     * @throws DisabledExtension
     * @see http://php.net/manual/en/fileinfo.installation.php
     */
    public static function mimetype($value, array $types)
    {
        if (!class_exists('finfo')) {
            throw new DisabledExtension('Please enable the File Info extension in your php.ini');
        }

        $finfo = new finfo();
        $type = $finfo->file($value['tmp_name'], FILEINFO_MIME_TYPE);

        return in_array($type, $types);
    }

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
