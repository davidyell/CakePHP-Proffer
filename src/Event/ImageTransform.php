<?php
/**
 * Event listener to perform transformations on an image
 *
 * @author David Yell <neon1024@gmail.com>
 */

namespace Proffer\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Imagine\Image\Box;
use Imagine\Filter\Transformation;

class ImageTransform implements EventListenerInterface {

/**
 * Returns a list of events this object is implementing. When the class is registered
 * in an event manager, each individual method will be associated with the respective event.
 *
 * @return array associative array or event key names pointing to the function
 * that should be called in the object when the respective event is fired
 */
	public function implementedEvents() {
		return [
			'Proffer.beforeThumbs' => 'makeThumbnails'
		];
	}

/**
 * Generate thumbnails
 *
 * @param Event $event
 * @param ImageInterface $image
 * @param array $dimensions
 * @return ImageInterface
 */
	public function makeThumbnails(Event $event, ImageInterface $image, array $dimensions) {
		if (isset($thumbSize['crop']) && $thumbSize['crop'] === false) {
			$image = $this->thumbnailCropScale($image, $dimensions['w'], $dimensions['h']);
		} else {
			$image = $this->thumbnailScale($image, $dimensions['w'], $dimensions['h']);
		}

		return $image;
	}

/**
 * Scale an image to best fit a thumbnail size
 *
 * @param \Imagine\Image\ImageInterface $image
 * @param int $width The width in pixels
 * @param int $height The height in pixels
 * @return \Imagine\Image\ImageInterface
 */
	protected function thumbnailScale($image, $width, $height) {
		$transformation = new Transformation();
		$transformation->thumbnail(new Box($width, $height));
		return $transformation->apply($image);
	}

/**
 * Create a thumbnail by scaling an image and cropping it to fit the exact dimensions
 *
 * @param \Imagine\Image\ImageInterface $image
 * @param int $targetWidth The width in pixels
 * @param int $targetHeight The height in pixels
 * @return \Imagine\Image\ImageInterface
 */
	protected function thumbnailCropScale($image, $targetWidth, $targetHeight) {
		$target = new Box($targetWidth, $targetHeight);
		$sourceSize = $image->getSize();

		if ($sourceSize->getWidth() > $sourceSize->getHeight()) {
			$width = $sourceSize->getWidth() * ($target->getHeight() / $sourceSize->getHeight());
			$cropPoint = new Point((int)(max($width - $target->getWidth(), 0) / 2), 0);
		} else {
			$height = $sourceSize->getHeight() * ($target->getWidth() / $sourceSize->getWidth());
			$cropPoint = new Point(0, (int)(max($height - $target->getHeight(), 0) / 2));
		}

		$box = new Box($targetWidth, $targetHeight);

		return $image->thumbnail($box, ImageInterface::THUMBNAIL_OUTBOUND)
			->crop($cropPoint, $target);
	}
}