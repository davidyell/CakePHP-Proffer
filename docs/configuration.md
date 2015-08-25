#Configuration
This manual page relates to how to configure the Proffer behaviour, what the configuration options do, their defaults and how to change them.

## Configuring the behaviour in your table
You will need to add a few things to your Table class.

Below is an example setup, which also includes some of the defaults so you can see what they look like. You can check the options below to
see which ones you must define and which ones can be ignored to use the defaults.

```php
<?php
// Add the behaviour and configure any options you want
$this->addBehavior('Proffer.Proffer', [
	'photo' => [	// The name of your upload field
		'root' => WWW_ROOT . 'files', // Customise the root upload folder here, or omit to use the default
		'dir' => 'photo_dir',	// The name of the field to store the folder
		'thumbnailSizes' => [ // Declare your thumbnails
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

* By default generated thumbnail images will be set to the highest image quality in the `ImageTransform` class.
* By default files will be uploaded to `/webroot/files/<table alias>/<uuid>/<filename>`.

In order to upload a file to your application you will need to add the form fields to your view.
```php
echo $this->Form->create($entity, ['type' => 'file']); // Dont miss this out or no files will upload
echo $this->Form->input('image', ['type' => 'file']);
echo $this->Form->button(__('Submit'));
echo $this->Form->end();
```
This will turn your form into a multipart form and add the relevant fields.

##Configuration options
There are a number of configuration options you can pass into the behaviour when you attach it to your table. These options are passed as an array value of the upload field.

###dir
**required** `string`
The database field which will store the name of the folder in which the files are uploaded.

###thumbnailSizes
**optional** `array`
An array of sizes to create thumbnails of an uploaded image. The format is that the image prefix will be the array key and the sizes are the value as an array.
Eg, `'square' => ['w' => 200, 'h' => 200]` would create a thumbnail prefixed with `square_` and would be 100px x 100px.
If you do not specify the `thumbnailSizes` configuration option, no thumbnails will be created.

###root
**optional:** defaults to, `WWW_DIR . 'files'`
Allows you to customise the root folder in which all the file upload folders and files will be created.

###thumbnailMethod
**optional:** defaults to, `gd`
Which Imagine engine to use to convert the images. Defaults to PHP's GD library. Can also be `imagick` and `gmagick`.

###pathClass
**optional**
If you want to inject your own class for dealing with paths you can specify it here as a fully qualified namespace.
Eg, `'App\Lib\Proffer\AvatarPath'`.

###transformClass
**optional**
If you want to replace the creation of thumbnails you can specify your own class here, it must be a fully qualified namespace.
EG, `'App\Lib\Proffer\WatermarkThumbnail'`.

## Configuring your templates
You will need to make sure that your forms are using the file type so that the files can be uploaded.

```php
echo $this->Form->create($entity, ['type' => 'file']);
echo $this->Form->input('photo', ['type' => 'file']);
// etc
```

[< Installation](installation.md) | [Validation >](validation.md)
