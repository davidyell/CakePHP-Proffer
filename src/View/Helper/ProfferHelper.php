<?php

namespace Proffer\View\Helper;

use Cake\View\Helper;
use Cake\Routing\Router;

/**
 * ProfferHelper
 * A helper for the Proffer plugin, making the process of retrieving uploaded files easier
 *
 * @author Gus Antoniassi (scripts.gus@gmail.com)
 */
class ProfferHelper extends Helper
{
    public $helpers = ['Url', 'Html'];

    public function __construct(\Cake\View\View $View, array $config = [])
    {
        parent::__construct($View, $config);
    }

    /**
     * Returns the URL path for the specified resource
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity filled with data
     * @param string $field Name of the image field
     * @param array $options Array of options
     *
     * @return string The URL path
     *
     * @throws \Exception
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

    /**
     * Returns an anchor tag linking for the specified resource
     *
     * @param string $title Text to be wrapped inside anchor tag
     * @param \Cake\Datasource\EntityInterface $entity The entity filled with data
     * @param string $field Name of the image field
     * @param array $options Array of options
     * @param array $htmlOptions Array of options for the HtmlHelper
     *
     * @return string The anchor tag
     */
    public function getUploadLink($title, \Cake\Datasource\EntityInterface $entity, $field, array $options = [], array $htmlOptions = [])
    {
        return $this->Html->link($title, $this->getUploadUrl($entity, $field, $options), $htmlOptions);
    }

    /**
     * Returns an image tag sourced at the specified resource
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity filled with data
     * @param string $field Name of the image field
     * @param array $options Array of options
     * @param array $htmlOptions Array of options for the HtmlHelper
     *
     * @return string The image tag
     */
    public function getUploadImage(\Cake\Datasource\EntityInterface $entity, $field, $options = [], $htmlOptions = [])
    {
        return $this->Html->image($this->getUploadUrl($entity, $field, $options), $htmlOptions);
    }
}
