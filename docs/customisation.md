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

You would attach this listener in the same way as above, but there is no need to remove the existing listeners first.

The advantages of customisation using a listener is that you can encapsulate the file naming functionality into a single 
file and also attach this listener to multiple tables, if you wanted the same naming convention in multiple places.

##Advanced customisation
If you do not want to create an event listener or just want more control over how the plugin is handling paths or creating thumbnails
you can replace these components with your own by creating a class using the provided interfaces and injecting them into the plugin.

In your table classes configuration you can add the `pathClass` and `transformClass` configuration options and specify a 
fully namespaced class name as a string. When the plugin runs it will look for and instantiate these classes.

An example might look like this.

```php
// src/Model/Table/ExamplesTable.php
    $this->addBehavior('Proffer.Proffer', [
        'image' => [
            'dir' => 'image_dir',
            'thumbnailSizes' => [
                'square' => ['w' => 100, 'h' => 100]
            ],
            'pathClass' => '\App\Lib\Proffer\UserProfilePath',
            'transformClass' => '\App\Lib\Proffer\UserProfileAvatar'
        ]
    ]);
```
 
### Using the interfaces
Using the configuration above, you can completely change the implementation of these core classes if you want to, by 
creating your own. Make sure that they implement the correct interface so that the plugin will still work.

```php
// src/Lib/Proffer/UserProfilePath.php
class UserProfilePath implements Proffer\Lib\ProfferPathInterface
{
    // Create the stub methods and implement your code here
}

// src/Lib/Proffer/UserProfileAvatar.php
class UserProfileAvatar implements Proffer\Lib\ImageTransformInterface
{
    // Create the stub methods and implement your code here
}
```

### Extending the plugin classes
Using the configuration above you can also customise specific methods by extending the plugin classes and overriding 
their methods.

```php
// src/Lib/Proffer/UserProfilePath.php
class UserProfilePath extends Proffer\Lib\ProfferPath
{
    public function generateSeed($seed)
    {
        if ($seed) {
            return $seed;
        }
        
        return date('Y-m-d-H-i-s');
    }
}
```