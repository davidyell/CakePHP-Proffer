<?php

/**
 * @category Proffer
 * @package bootstrap.php
 *
 * @author David Yell <neon1024@gmail.com>
 *
 */

namespace Proffer\config;

use Cake\Database\Type;

Type::map('proffer.file', 'Proffer\Database\Type\FileType');
