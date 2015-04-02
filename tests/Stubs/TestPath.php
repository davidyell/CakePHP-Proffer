<?php

/**
 * @category Proffer
 * @package TestPath.php
 *
 * @author David Yell <neon1024@gmail.com>
 * @when 02/04/15
 *
 */

namespace Proffer\Tests\Stubs;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Proffer\Lib\ProfferPath;

class TestPath extends ProfferPath
{
    public function __construct(Table $table, Entity $entity, $field, array $settings)
    {
        $this->setRoot(TMP . 'ProfferTests');

        $this->setTable($table->alias());
        $this->setField($field);
        $this->setSeed('proffer_test');

        if (isset($settings['thumbnailSizes'])) {
            $this->setPrefixes($settings['thumbnailSizes']);
        }

        $this->setFilename($entity->get($field));
    }
}
