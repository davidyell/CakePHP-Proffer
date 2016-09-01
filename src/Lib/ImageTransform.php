<?php
/**
 * ImageTransform class
 * This class deals with creating thumbnails for image uploads using the a library
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Lib;

use Cake\ORM\Table;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageTransform implements ImageTransformInterface
{

    /**
     * @var \Cake\ORM\Table $table Instance of the table being used
     */
    protected $Table;

    /**
     * @var \Proffer\Lib\ProfferPathInterface $Path Instance of the path class
     */
    protected $Path;

    /**
     * @var \Intervention\Image\ImageManager Intervention image manager instance
     */
    protected $ImageManager;

    /**
     * Construct the transformation class
     *
     * @param \Cake\ORM\Table $table The table instance
     * @param \Proffer\Lib\ProfferPathInterface $path Instance of the path class
     */
    public function __construct(Table $table, ProfferPathInterface $path)
    {
        $this->Table = $table;
        $this->Path = $path;
    }

    /**
     * Take an upload fields configuration and create all the thumbnails
     *
     * @param array $config The upload fields configuration
     * @return array
     */
    public function processThumbnails(array $config)
    {
        $thumbnailPaths = [];
        if (!isset($config['thumbnailSizes'])) {
            return $thumbnailPaths;
        }

        foreach ($config['thumbnailSizes'] as $prefix => $thumbnailConfig) {
            $method = 'gd';
            if (!empty($config['thumbnailMethod'])) {
                $method = $config['thumbnailMethod'];
            }

            $this->ImageManager = new ImageManager(['driver' => $method]);

            $thumbnailPaths[] = $this->makeThumbnail($prefix, $thumbnailConfig);
        }

        return $thumbnailPaths;
    }

    /**
     * Generate and save the thumbnail
     *
     * @param string $prefix The thumbnail prefix
     * @param array $config Array of thumbnail config
     * @return string
     */
    public function makeThumbnail($prefix, array $config)
    {
        $image = $this->ImageManager->make($this->Path->fullPath());

        $defaultConfig = [
            'jpeg_quality' => 100,
            'w' => $image->width(),
            'h' => $image->height()
        ];
        $config = array_merge($defaultConfig, $config);

        $width = $config['w'];
        $height = $config['h'];

        if (!empty($config['crop'])) {
            $image = $this->thumbnailCrop($image, $width, $height);
        } elseif (!empty($config['fit'])) {
            $image = $this->thumbnailFit($image, $width, $height);
        } elseif (!empty($config['widen'])) {
            $image = $this->thumbnailWiden($image, $width);
        } elseif (!empty($config['heighten'])) {
            $image = $this->thumbnailHeighten($image, $height);
        } else {
            $image = $this->thumbnailResize($image, $width, $height);
        }

        unset($config['crop'], $config['fit'], $config['widen'], $config['heighten'], $config['w'], $config['h']);

        $image->save($this->Path->fullPath($prefix), $config['jpeg_quality']);

        return $this->Path->fullPath($prefix);
    }

    /**
     * Crop an image to a certain size from the centre of the image
     *
     * @see http://image.intervention.io/api/crop
     *
     * @param \Intervention\Image\Image $image Image instance
     * @param int $width Desired width in pixels
     * @param int $height Desired height in pixels
     *
     * @return \Intervention\Image\Image
     */
    protected function thumbnailCrop(Image $image, $width, $height)
    {
        return $image->crop($width, $height);
    }

    /**
     * Resize and crop to find the best fitting aspect ratio
     *
     * @see http://image.intervention.io/api/fit
     *
     * @param \Intervention\Image\Image $image Image instance
     * @param int $width Desired width in pixels
     * @param int $height Desired height in pixels
     *
     * @return \Intervention\Image\Image
     */
    protected function thumbnailFit(Image $image, $width, $height)
    {
        return $image->fit($width, $height);
    }

    /**
     * Widen current image
     *
     * @see http://image.intervention.io/api/widen
     *
     * @param \Intervention\Image\Image $image Image instance
     * @param int $width Desired width in pixels
     *
     * @return \Intervention\Image\Image
     */
    protected function thumbnailWiden(Image $image, $width)
    {
        return $image->widen($width);
    }

    /**
     * Heighten current image
     *
     * @see http://image.intervention.io/api/heighten
     *
     * @param \Intervention\Image\Image $image Image instance
     * @param int $height Desired height in pixels
     *
     * @return \Intervention\Image\Image
     */
    protected function thumbnailHeighten(Image $image, $height)
    {
        return $image->heighten($height);
    }

    /**
     * Resize current image
     *
     * @see http://image.intervention.io/api/resize
     *
     * @param \Intervention\Image\Image $image Image instance
     * @param int $width Desired width in pixels
     * @param int $height Desired height in pixels
     *
     * @return \Intervention\Image\Image
     */
    protected function thumbnailResize(Image $image, $width, $height)
    {
        return $image->resize($width, $height);
    }
}