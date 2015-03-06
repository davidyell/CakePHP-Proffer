#Configuration
This manual page relates to how to configure the Proffer behaviour, what the configuration options do, their defaults and how to change them.

## Configuring the behaviour in your table
You will need to add a few things to your Table class.

Below is an example setup, which also includes some of the defaults so you can see what they look like. You can check the options below to
see which ones you must define and which ones can be ignored to use the defaults.

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

##Configuration options
There are a number of configuration options you can pass into the behaviour when you attach it to your table. These options are passed as an array value of the upload field.

###dir
**required** `string`  
The database field which will store the name of the folder in which the files are uploaded.

###thumbnailSizes
**required** `array`  
An array of sizes to create thumbnails of an uploaded image. The format is that the image prefix will be the array key and the sizes are the value as an array.  
Eg, `'square' => ['w' => 200, 'h' => 200]` would create a thumbnail prefixed with `square_` and would be 100px x 100px.

###root
**default:** `WWW_DIR . 'files'`  
Allows you to customise the root folder in which all the file upload folders and files will be created.

###thumbnailMethod
**default:** `gd`  
Which Imagine engine to use to convert the images. Defaults to PHP's GD library. Can also be `imagick` and `gmagick`.