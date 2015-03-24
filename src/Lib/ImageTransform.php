<?php
/**
 * ImageTransform class
 * This class deals with creating thumbnails for image uploads using the Imagine library
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Lib;

use Cake\Event\Event;
use Cake\ORM\Table;
use Imagine\Filter\Transformation;
use Imagine\Gd\Imagine as Gd;
use Imagine\Gmagick\Imagine as Gmagick;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Imagine\Imagick\Imagine as Imagick;

class ImageTransform implements ImageTransformInterface
{

    /**
     * Store our instance of Imagine
     *
     * @var ImagineInterface $Imagine
     */
    private $Imagine;

    /**
     * @var Table $table Instance of the table being used
     */
    protected $Table;

    /**
     * @var ProfferPathInterface $Path Instance of the path class
     */
    protected $Path;

    /**
     * Construct the transformation class
     *
     * @param Table $table The table instance
     * @param ProfferPathInterface $path Instance of the path class
     */
    public function __construct(Table $table, ProfferPathInterface $path)
    {
        $this->Table = $table;
        $this->Path = $path;
    }

    /**
     * Get the specified Imagine engine class
     *
     * @return ImagineInterface
     */
    protected function getImagine()
    {
        return $this->Imagine;
    }

    /**
     * Set the Imagine engine class
     *
     * @param string $engine The name of the image engine to use
     * @return void
     */
    protected function setImagine($engine = 'gd')
    {
        switch ($engine) {
            default:
            case 'gd':
                $this->Imagine = new Gd();
                break;
            case 'gmagick':
                $this->Imagine = new Gmagick();
                break;
            case 'imagick':
                $this->Imagine = new Imagick();
                break;
        }
    }

    /**
     * Take an upload fields configuration and create all the thumbnails
     *
     * @param array $config The upload fields configuration
     * @return void
     */
    public function processThumbnails(array $config)
    {
        foreach ($config['thumbnailSizes'] as $prefix => $dimensions) {

            $method = null;
            if (!empty($config['thumbnailMethod'])) {
                $method = $config['thumbnailMethod'];
            }

            $event = new Event('Proffer.beforeThumb', $this, [
                'path' => $this->Path,
                'prefix' => $prefix,
                'dimensions' => $dimensions,
                'thumbnailMethod' => $method
            ]);
            $this->Table->eventManager()->dispatch($event);

            $image = $this->makeThumbnail($dimensions, $method);
            $this->saveThumbnail($image, $prefix);

            $event = new Event('Proffer.afterThumb', $this, [
                'path' => $this->Path,
                'image' => $image
            ]);
            $this->Table->eventManager()->dispatch($event);
        }
    }

    /**
     * Generate thumbnail
     *
     * @param array $dimensions Array of thumbnail dimensions
     * @param string $thumbnailMethod Which engine to use to make thumbnails
     * @return ImageInterface
     */
    public function makeThumbnail(array $dimensions, $thumbnailMethod = 'gd')
    {
        $this->setImagine($thumbnailMethod);

        $image = $this->getImagine()->open($this->Path->fullPath());

        if (isset($dimensions['crop']) && $dimensions['crop'] === true) {
            $image = $this->thumbnailCropScale($image, $dimensions['w'], $dimensions['h']);
        } else {
            $image = $this->thumbnailScale($image, $dimensions['w'], $dimensions['h']);
        }

        return $image;
    }

    /**
     * Save thumbnail to the file system
     *
     * @param ImageInterface $image The ImageInterface instance from Imagine
     * @param string $prefix The thumbnail size prefix
     * @return ImageInterface
     */
    public function saveThumbnail(ImageInterface $image, $prefix)
    {
        $filePath = $this->Path->fullPath($prefix);
        $image->save($filePath, ['jpeg_quality' => 100, 'png_compression_level' => 9]);

        return $image;
    }

    /**
     * Scale an image to best fit a thumbnail size
     *
     * @param ImageInterface $image The ImageInterface instance from Imagine
     * @param int $width The width in pixels
     * @param int $height The height in pixels
     * @return ImageInterface
     */
    protected function thumbnailScale(ImageInterface $image, $width, $height)
    {
        $transformation = new Transformation();
        $transformation->thumbnail(new Box($width, $height));
        return $transformation->apply($image);
    }

    /**
     * Create a thumbnail by scaling an image and cropping it to fit the exact dimensions
     *
     * @param ImageInterface $image The ImageInterface instance from Imagine
     * @param int $targetWidth The width in pixels
     * @param int $targetHeight The height in pixels
     * @return ImageInterface
     */
    protected function thumbnailCropScale(ImageInterface $image, $targetWidth, $targetHeight)
    {
        $target = new Box($targetWidth, $targetHeight);
        $sourceSize = $image->getSize();
        if ($sourceSize->getWidth() > $sourceSize->getHeight()) {
            $width = $sourceSize->getWidth() * ($target->getHeight() / $sourceSize->getHeight());
            $height = $targetHeight;
            $cropPoint = new Point((int)(max($width - $target->getWidth(), 0) / 2), 0);
        } else {
            $height = $sourceSize->getHeight() * ($target->getWidth() / $sourceSize->getWidth());
            $width = $targetWidth;
            $cropPoint = new Point(0, (int)(max($height - $target->getHeight(), 0) / 2));
        }
        $box = new Box($width, $height);
        return $image->thumbnail($box, ImageInterface::THUMBNAIL_OUTBOUND)
            ->crop($cropPoint, $target);
    }
}
