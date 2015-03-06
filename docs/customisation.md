#Customisation
This manual page deals with customising the behaviour of the Proffer plugin. How to change the upload location and changing
file names. It also cover how you can use the Proffer events to change the way the plugin behaves.

##Events system
The primary way of customising the Proffer plugin is through the use of creating Event Listener classes, which can listen for
specific events and change the data on the way. This technique allows powerful customisation without the need for excessive
configuration. It also means that if you are happy with the defaults, you don't have to do anything.

[You can read more about Event Listeners in the book.](http://book.cakephp.org/3.0/en/core-libraries/events.html)

##Thumbnail customisation
Proffer uses an [event listener](http://book.cakephp.org/3.0/en/core-libraries/events.html) to generate thumbnails. If you 
want to customise your thumbnail generation in any way you can either create your own listener and listen for 
the `Proffer.beforeThumbs` and `Proffer.afterThumbs` methods, or just extend and overload the methods in the default 
listener located in `src/Event/ProfferListener.php`.

The listener is separated from the thumbnail generation allowing you to hook to your own class which allows you to use
your own image library if you don't want to use Imagine.

The thumbnails are generated using the [Imagine library](http://imagine.readthedocs.org/en/latest/index.html). So you can
use the documentation there to build your own thumbnail generating listeners.

By default generated thumbnail images will be set to the highest image quality in the `ImageTransform` class.

##How to replace the event listener
If you want to replace the event listener with your own custom class you can do that by using the `Table::eventManager()`.

```php
// In your Table class

// Remove ALL the listeners attached to these events
$this->eventManager()->off('Proffer.beforeThumbs');
$this->eventManager()->off('Proffer.afterThumbs');

// Add your new custom listener
$listener = new App\Event\LogFilenameListener();
$this->eventManager()->on($listener);
```

The example `LogFilenameListener` class used here is [available as an example](examples/LogFilenameListener.md). 
This listener listens for the `Proffer.beforeThumbs` and `Proffer.afterThumbs` events and write the filename to the logs instead of 
creating any thumbnails.

##Customising upload file names and paths
Using the `Proffer.afterPath` event you can hook into all the details about the file upload before it is processed. Using 
this event you can change the name of the file and the upload path to match whatever convention you want. I have created 
an example listener which is [available as an example](examples/UploadFilenameListener.md).