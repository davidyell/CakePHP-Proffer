#Validation
This manual page deals with how to use the included ProfferRules validation provider to add upload related validation rules to
your application.

##Built-in validation provider
Proffer comes an extra validation rule to check the dimensions of an uploaded image. Other rules are provided by the core and are listed below.

In your validation function in your table class you'll need to add the validator as a provider and then apply the rules.

```php
<?php
$validator->provider('proffer', 'Proffer\Model\Validation\ProfferRules');

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

If you need to validate other aspects of the uploaded file, there are a number of core validation methods you might find helpful.
* [Extension](http://api.cakephp.org/3.0/class-Cake.Validation.Validation.html#_extension)
* [File size](http://api.cakephp.org/3.0/class-Cake.Validation.Validation.html#_fileSize)
* [Mime type](http://api.cakephp.org/3.0/class-Cake.Validation.Validation.html#_mimeType) 

## Basic validation rules
If you want your users to submit a file when creating a record, but not when updating it, you can configure this using the basic Cake rules.

```php
$validator
    ->requirePresence('image', 'create')
    ->allowEmpty('image', 'update');
```

So now your users do not need to upload a file every time they update a record.

[< Configuration](configuration.md) | [Customisation >](customisation.md)
