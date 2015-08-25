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

use Cake\Database\Type;

class FileType extends Type
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
