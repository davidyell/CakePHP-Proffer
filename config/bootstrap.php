<?php

/**
 * @category Proffer
 * @package bootstrap.php
 * 
 * @author David Yell <neon1024@gmail.com>
 * @when 03/03/15
 *
 */

namespace Proffer\config;

use Cake\Database\Type;

Type::map('file', 'Proffer\Database\Type\FileType');