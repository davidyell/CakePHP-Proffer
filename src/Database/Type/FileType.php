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

use Cake\Database\Driver;
use Cake\Database\Type;
use PDO;

class FileType extends Type
{
    public function toDatabase($value, Driver $driver)
    {
        return $value;
    }

    public function toPHP($value, Driver $driver)
    {
        return $value;
    }

    public function toStatement($value, Driver $driver)
    {
        return PDO::PARAM_STR;
    }
}
