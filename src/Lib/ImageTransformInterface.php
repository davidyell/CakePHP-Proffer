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
use Imagine\Image\ImageInterface;

interface ImageTransformInterface {

    /**
     * @param Table $table Instance of the current table
     * @param ProfferPathInterface $path Instance of the current path class
     */
    function __construct(Table $table, ProfferPathInterface $path);

    /**
     * Take an upload fields configuration and create all the thumbnails
     *
     * @param array $config The upload fields configuration
     * @return void
     */
    function processThumbnails(array $config);

    /**
     * Create a thumbnail from a file
     *
     * @param array $dimensions The thumbnail dimensions
     * @param string $thumbnailMethod Which method to use to create the thumbnail
     * @return \Imagine\Image\ImageInterface
     */
    function makeThumbnail(array $dimensions, $thumbnailMethod = 'gd');

    /**
     * Save the thumbnail to the file system
     *
     * @param ImageInterface $image An instance of the Imagine image
     * @param $prefix The prefix to use when saving
     * @return \Imagine\Image\ImageInterface
     */
    function saveThumbnail(ImageInterface $image, $prefix);

}