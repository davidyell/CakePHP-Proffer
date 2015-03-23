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

use Imagine\Image\ImageInterface;

interface ImageTransformInterface {

    /**
     * Create a thumbnail from a file
     *
     * @param ProfferPathInterface $path Instance of the path class
     * @param array $dimensions The thumbnail dimensions
     * @param string $thumbnailMethod Which method to use to create the thumbnail
     * @return \Imagine\Image\ImageInterface
     */
    function makeThumbnail(ProfferPathInterface $path, array $dimensions, $thumbnailMethod = 'gd');

    /**
     * Save the thumbnail to the file system
     *
     * @param ImageInterface $image An instance of the Imagine image
     * @param ProfferPathInterface $path Instance of the path class
     * @param $prefix The prefix to use when saving
     * @return \Imagine\Image\ImageInterface
     */
    function saveThumbnail(ImageInterface $image, ProfferPathInterface $path, $prefix);

}