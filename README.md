#CakePHP3-Proffer
An upload plugin for CakePHP 3.

![Proffer definition](http://i.imgur.com/OaAqQ6x.png)

##What is it?
So I needed a way to upload images in CakePHP 3, and as I couldn't find anything that I liked I decided to write my own 
in a similar vein to how [@josegonzalez](https://github.com/josegonzalez) had written his 
[CakePHP-Upload](https://github.com/josegonzalez/cakephp-upload) plugin for CakePHP 2.

##Requirements
* PHP 5.4+
* Database
* CakePHP 3
* [File Info is enabled](http://php.net/manual/en/book.fileinfo.php) for mimetype validation

##Status
This is currently in alpha, but will upload images for you.

[![Build Status](https://travis-ci.org/davidyell/CakePHP3-Proffer.svg?branch=master)](https://travis-ci.org/davidyell/CakePHP3-Proffer)
[![Coverage Status](https://coveralls.io/repos/davidyell/CakePHP3-Proffer/badge.png)](https://coveralls.io/r/davidyell/CakePHP3-Proffer)
[![Dependency Status](https://www.versioneye.com/user/projects/54eee43931e55e12f9000018/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54eee43931e55e12f9000018)
[![Latest Stable Version](https://poser.pugx.org/davidyell/proffer/v/stable.svg)](https://packagist.org/packages/davidyell/proffer) [![Total Downloads](https://poser.pugx.org/davidyell/proffer/downloads.svg)](https://packagist.org/packages/davidyell/proffer) [![Latest Unstable Version](https://poser.pugx.org/davidyell/proffer/v/unstable.svg)](https://packagist.org/packages/davidyell/proffer) [![License](https://poser.pugx.org/davidyell/proffer/license.svg)](https://packagist.org/packages/davidyell/proffer)

##Installation
You can find it on Packagist [https://packagist.org/packages/davidyell/proffer](https://packagist.org/packages/davidyell/proffer)

### Getting the plugin
Add it to your `composer.json` in your require section `"davidyell/proffer": "dev-master"` and then run `composer update`.

### CakePHP
Then you'll need to load the plugin in your `config/bootstrap.php` file. `Plugin::load('Proffer', ['bootstrap' => true]);`.

### Database
Next you need to add the fields to your table. You'll want to add your file upload field, this will store the name of the uploaded file such as `example.jpg` and you also need the dir field to store the directory in which the file has been stored. By default this is `dir`.

##Configuration
You will need to add a few things to your Table class.

```php
<?php
// Configure your upload field to use the file datatype
protected function _initializeSchema(\Cake\Database\Schema\Table $table)
{
    $table->columnType('photo', 'proffer.file');
    return $table;
}

// Add the behaviour and configure any options you want
$this->addBehavior('Proffer.Proffer', [
	'photo' => [	// The name of your upload field
		'root' => WWW_DIR . 'files', // Customise the root upload folder here, or omit to use the default
		'dir' => 'photo_dir',	// The name of the field to store the folder
		'thumbnailSizes' => [
			'square' => ['w' => 200, 'h' => 200],	// Define the size and prefix of your thumbnails
			'portrait' => ['w' => 100, 'h' => 300, 'crop' => true],		// Crop will crop the image as well as resize it
		],
		'thumbnailMethod' => 'imagick'	// Options are Imagick, Gd or Gmagick
	]
]);
```

Each upload field should have an array of settings which control the options for that upload field. In the example 
above my upload field is called `photo` and I pass an array of options, namely the name of the field to store the 
directory in.

By default files will be uploaded to `/webroot/files/<table alias>/<uuid>/<filename>`.

In order to upload a file to your application you will need to add the form fields to your view.
```php
echo $this-Form->create($entity, ['type' => 'file']); // Dont miss this out or no files will upload
echo $this->Form->input('image', ['type' => 'file']);
echo $this->Form->button(__('Submit'));
echo $this->Form->end();
```
This will turn your form into a multipart form and add the relevant fields.

###Configuration options
There are a number of configuration options you can pass into the behaviour when you attach it to your table. These options are passed as an array value of the upload field.

####root
**default:** `WWW_DIR . 'files'`  
Allows you to customise the root folder in which all the file upload folders and files will be created.

####dir
**required** `string`  
The database field which will store the name of the folder in which the files are uploaded.

####thumbnailSizes
**required** `array`  
An array of sizes to create thumbnails of an uploaded image. The format is that the image prefix will be the array key and the sizes are the value as an array.  
Eg, `'square' => ['w' => 200, 'h' => 200]` would create a thumbnail prefixed with `square_` and would be 100px x 100px.

####thumbnailMethod
**default:** `gd`  
Which Imagine engine to use to convert the images. Defaults to PHP's GD library. Can also be `imagick` and `gmagick`.

##Validation
Proffer comes with some basic validation rules which you can use to validate your uploads. In order to use these you 
will need to load the validation rules and apply them to your field.

In your validation function in your table class you'll need to add the validator as a provider and then apply the rules.

```php
<?php
$validator->provider('proffer', 'Proffer\Model\Validation\ProfferRules');

// Check the filesize in bytes
$validator->add('photo', 'proffer', [
	'rule' => ['filesize', 2000000],
	'provider' => 'proffer'
])

// Make sure the extension matches
->add('photo', 'proffer', [
	'rule' => ['extension', ['jpg', 'jpeg', 'png']],
	'message' => 'Invalid extension',
	'provider' => 'proffer'
])

// Ensure that the upload is the correct mime type
->add('photo', 'proffer', [
	'rule' => ['mimetype', ['image/jpeg', 'image/png']],
	'message' => 'Not the correct mime type',
	'provider' => 'proffer'
])

// Set the thumbnail resize dimensions
->add('photo', 'proffer', [
	'rule' => ['dimensions', [
		'min' => ['w' => 100, 'h' => 100],
		'max' => ['w' => 500, 'h' => 500]
	]],
	'message' => 'Image is not correct dimensions.',
	'provider' => 'proffer'
]);
```

You can [read more about custom validation providers in the book](http://book.cakephp.org/3.0/en/core-libraries/validation.html#adding-validation-providers).

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

The example `LogFilenameListener` class used here is [available as a Gist](https://gist.github.com/davidyell/f6ee8013f06414997504). 
This listener listens for the `Proffer.beforeThumbs` and `Proffer.afterThumbs` events and write the filename to the logs instead of 
creating any thumbnails.

##Proffer shell tasks
Proffer comes with a built in shell which can help you achieve certain things when dealing with your uploaded files. To 
find out more about the shell you can use the `-h` flag on the command line.

```bash
$ bin/cake proffer.proffer -h
```

###Regenerate thumbnail task
If you would like to regenerate the thumbnails for files already on your system, or you've changed your configuration. You
can use the built-in shell to regenerate the thumbnails for a table.

```bash
$ bin/cake proffer.proffer generate <table>
```

###Cleanup task
The cleanup task will look at a models uploads folder and match the files there with it's matching entry in the 
database. If a file doesn't have a matching record in the database it **will be deleted**.

```bash
$ bin/cake proffer.proffer cleanup <table>
```

##Contribution
Please open a pull request or submit an issue if there is anything you would like to contribute. Please write a test for 
any new functionality that you add and be sure to run the tests before you commit. Also don't forget to run PHPCS with 
the PSR2 standard to avoid errors in TravisCI.

##License
Please see [LICENSE](LICENSE)
