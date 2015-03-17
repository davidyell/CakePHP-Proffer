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
use Proffer\Lib\ImageTransform;
use Proffer\Lib\ProfferPath;

class ProfferListener implements EventListenerInterface
{

    /**
     * Returns a list of events this object is implementing. When the class is registered
     * in an event manager, each individual method will be associated with the respective event.
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents()
    {
        return [
            'Proffer.beforeThumbs' => 'beforeThumbs',
            'Proffer.afterThumbs' => 'afterThumbs'
        ];
    }

    /**
     * Event handler for the beforeThumbs event
     *
     * @param Event $event The passed event
     * @param ProfferPath $path Array of path data
     * @param array $dimensions Array of dimensions in pixels
     * @param string $thumbnailMethod The engine to use to make thumbnails
     * @return ImageInterface
     */
    public function beforeThumbs(Event $event, ProfferPath $path, array $dimensions, $thumbnailMethod = 'gd')
    {
        $transform = new ImageTransform();
        return $transform->makeThumbnails($path, $dimensions, $thumbnailMethod);
    }

    /**
     * Event handler for the afterThumbs event
     *
     * @param Event $event The passed event
     * @param ProfferPath $path Array of path data
     * @param ImageInterface $image An Imagine image instance
     * @param string $prefix The thumbnail prefix
     * @return ImageInterface
     */
    public function afterThumbs(Event $event, ProfferPath $path, ImageInterface $image, $prefix)
    {
        $transform = new ImageTransform();
        return $transform->saveThumbs($image, $path, $prefix);
    }
}
