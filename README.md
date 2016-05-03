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

##Status
This is currently in alpha, but will upload images for you.

[![Build Status](https://travis-ci.org/davidyell/CakePHP3-Proffer.svg?branch=master)](https://travis-ci.org/davidyell/CakePHP3-Proffer)
[![Coverage Status](https://coveralls.io/repos/davidyell/CakePHP3-Proffer/badge.png)](https://coveralls.io/r/davidyell/CakePHP3-Proffer)

##Installation
You can find it on Packagist [https://packagist.org/packages/davidyell/proffer](https://packagist.org/packages/davidyell/proffer)

Add it to your `composer.json` in your require section `"davidyell/proffer": "dev-master"`

##Configuration
You will need to add the behaviour to your Table class.

```php
<?php
$this->addBehavior('Proffer.Proffer', [
	'photo' => [	// The name of your upload field
		'root' => WWW_DIR . 'files', // Customise the root upload folder here, or leave blank to use the default
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
echo $this->Form->input('image_dir', ['type' => 'hidden']); // Import for edit forms
echo $this->Form->button(__('Submit'));
echo $this->Form->end();
```
This will turn your form into a multipart form and add the relevant fields.

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

##Customisation
Proffer uses an event listener to generate thumbnails. If you want to customise your thumbnail generation in any way 
you can either create your own listener and listen for the `Proffer.beforeThumbs` and `Proffer.afterThumbs` methods, or
just extend and overload the methods in the default listener located in `src/Event/ProfferListener.php`.

The listener is separated from the thumbnail generation allowing you to hook to your own class which allows you to use
your own image library if you don't want to use Imagine.

The thumbnails are generated using the [Imagine library](http://imagine.readthedocs.org/en/latest/index.html). So you can
use the documentation there to build your own thumbnail generating listeners.

By default generated thumbnail images will be set to the highest image quality in the `ImageTransform` class.

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
$ bin/cake proffer.proffer generate <PluginName>.<table>
```

###Cleanup task
The cleanup task will look at a models uploads folder and match the files there with it's matching entry in the 
database. If a file doesn't have a matching record in the database it will be deleted.

```bash
$ bin/cake proffer.proffer cleanup <table>
$ bin/cake proffer.proffer cleanup <PluginName>.<table>
```

##Contribution
Please open a pull request or submit an issue is there is anything you would like to contribute. Please write a test for 
new functionality that you add and be sure to run the tests.

##License
Please see [LICENSE](LICENSE)
