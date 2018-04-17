<?php

/**
 * @category Proffer
 * @package FileType.php
 *
 * @author David Yell <neon1024@gmail.com>
 * @when 03/03/15
 *
 */

namespace Proffer\Database\Type;

use Cake\Database\Type\StringType;

class FileType extends StringType
{
    /**
     * Prevent the marhsaller changing the upload array into a string
     *
     * @param mixed $value Passed upload array
     * @return mixed
     */
    public function marshal($value)
    {
        return $value;
    }
}
