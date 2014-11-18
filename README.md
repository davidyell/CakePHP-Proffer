#CakePHP3-Proffer
An upload plugin for CakePHP 3.

##What is it?
So I needed a way to upload images in CakePHP 3, and as I couldn't find anything that I liked I decided to write my own in a similar vein to how [@josegonzalez](https://github.com/josegonzalez) had written his [CakePHP-Upload](https://github.com/josegonzalez/cakephp-upload) plugin for CakePHP 2.

##Requirements
* PHP 5.4+
* Database
* CakePHP 3

##Status
This is currently in alpha, but will upload images for you.

##Installation
You can find it on Packagist [https://packagist.org/packages/davidyell/proffer](https://packagist.org/packages/davidyell/proffer)

Add it to your `composer.json` in your require section `"davidyell/proffer": "dev-master"`

##Configuration
You will need to add the behaviour to your Table class.

```php
<?php
$this->addBehavior('Proffer.Proffer', [
	'photo' => [	// The name of your upload field
		'dir' => 'photo_dir',	// The name of the field to store the folder
		'thumbnailSizes' => [
			'square' => ['w' => 200, 'h' => 200],	// Define the sizes of your thumbnails
			'portrait' => ['w' => 100, 'h' => 300, 'crop' => true],		// Crop will crop the image as well as resize it
		],
		'thumbnailMethod' => 'imagick'	// Options are Imagick, Gd or Gmagick
	]
]);
```

Each upload field should have an array of settings which control the options for that upload field. In the example above my upload field is called `photo` and I pass an array of options, namely the name of the field to store the directory in.

By default files will be uploaded to `/webroot/files/<table alias>/<uuid>/<filename>`.

##Contribution
Please open a pull request or submit an issue is there is anything you would like to contribute.