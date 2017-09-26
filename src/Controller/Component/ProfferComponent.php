<?php

namespace Proffer\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;

/**
 * ProfferComponent
 * A component for the Proffer plugin, making the process of retrieving uploaded files easier
 *
 * @author Gus Antoniassi (scripts.gus@gmail.com)
 */
class ProfferComponent extends Component
{
    /**
     * Returns the URL path for the specified resource
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity filled with data
     * @param string $field Name of the image field
     * @param array $options Array of options
     *
     * @return string The URL path
     */
    public function getUploadUrl(\Cake\Datasource\EntityInterface $entity, $field, array $options = [])
    {
        $options += [
            'folder' => 'files',
            'dir' => $field . '_dir',
            'thumb' => '',
            'fullUrl' => true,
        ];

        if (!empty($options['thumb'])) {
            $options['thumb'] .= '_';
        }

        $table = strtolower($entity->getSource());
        $dir = $entity->get($options['dir']);

        if (empty($dir)) {
            return '';
        }

        return Router::url("/{$options['folder']}/{$table}/{$field}/{$dir}/{$options['thumb']}{$entity->get($field)}", $options['fullUrl']);
    }
}
