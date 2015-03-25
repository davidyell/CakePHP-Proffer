<?php

/**
 * @category Proffer
 * @package ImageTransformInterface.php
 *
 * @author David Yell <neon1024@gmail.com>
 * @when 23/03/15
 *
 */

namespace Proffer\Lib;

use Cake\ORM\Table;

interface ImageTransformInterface
{
    /**
     * Take an upload fields configuration and process each configured thumbnail
     *
     * @param array $config The upload fields configuration
     * @return void
     */
    public function processThumbnails(array $config);

    /**
     * Create a thumbnail from a source file
     *
     * @param string $prefix The prefix name for the thumbnail
     * @param array $dimensions The thumbnail dimensions
     * @param string $thumbnailMethod Which method to use to create the thumbnail
     * @return void
     */
    public function makeThumbnail($prefix, array $dimensions, $thumbnailMethod = 'gd');
}
