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

interface ImageTransformInterface
{
    /**
     * Take an upload fields configuration and process each configured thumbnail
     *
     * @param array $config The upload fields configuration
     * @return array
     */
    public function processThumbnails(array $config);

    /**
     * Create a thumbnail from a source file
     *
     * @param string $prefix The prefix name for the thumbnail
     * @param array $dimensions The thumbnail dimensions
     * @return string
     */
    public function makeThumbnail($prefix, array $dimensions);
}
