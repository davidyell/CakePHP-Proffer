<?php
/**
 * @category Proffer
 * @package ImageTransformInterface.php
 *
 * @author David Yell <neon1024@gmail.com>
 * @when 06/03/15
 *
 */

namespace Proffer\Lib;

use Imagine\Image\ImageInterface;

interface ImageTransformInterface
{
    public function makeThumbnails(ProfferPath $path, array $dimensions, $thumbnailMethod = 'gd');

    public function saveThumbs(ImageInterface $image, ProfferPath $path, $prefix);
}
