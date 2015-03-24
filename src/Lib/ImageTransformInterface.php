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

interface ImageTransformInterface {

    /**
     * @param Table $table Instance of the current table
     * @param ProfferPathInterface $path Instance of the current path class
     */
    function __construct(Table $table, ProfferPathInterface $path);

    /**
     * Take an upload fields configuration and process each configured thumbnail
     *
     * @param array $config The upload fields configuration
     * @return void
     */
    function processThumbnails(array $config);

    /**
     * Create a thumbnail from a source file
     *
     * @param string $prefix The prefix name for the thumbnail
     * @param array $dimensions The thumbnail dimensions
     * @param string $thumbnailMethod Which method to use to create the thumbnail
     * @return void
     */
    function makeThumbnail($prefix, array $dimensions, $thumbnailMethod = 'gd');
}
