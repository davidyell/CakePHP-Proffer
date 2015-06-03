#Customisation
This manual page deals with customising the behaviour of the Proffer plugin. How to change the upload location and changing
file names. It also cover how you can use the Proffer events to change the way the plugin behaves.

##Customising upload file names and paths using an event listener
Using the `Proffer.afterPath` event you can hook into all the details about the file upload before it is processed. Using
this event you can change the name of the file and the upload path to match whatever convention you want. I have created
an example listener which is [available as an example](examples/UploadFilenameListener.md).

```php
// In your Table classes 'initialize()' method

// Add your new custom listener
$listener = new App\Event\LogFilenameListener();
$this->eventManager()->on($listener);
```

The advantages of customisation using a listener is that you can encapsulate the file naming functionality into a single
file and also attach this listener to multiple tables, if you wanted the same naming convention in multiple places.

[You can read more about Event Listeners in the book.](http://book.cakephp.org/3.0/en/core-libraries/events.html)

:warning: The listener will overwrite any settings that are configured in the path class. This includes if you are using
your own path class.

##Advanced customisation
If you want more control over how the plugin is handling paths or creating thumbnails you can replace these components
with your own by creating a class using the provided interfaces and injecting them into the plugin.

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

The configuration options are covered in the [configuration documentation](configuration.md).

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

[< Validation](validation.md) | [Shell tasks >](shell.md)
