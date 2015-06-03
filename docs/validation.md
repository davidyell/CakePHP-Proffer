#Validation
This manual page deals with how to use the included ProfferRules validation provider to add upload related validation rules to
your application.

##Built-in validation provider
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


[< Configuration](configuration.md) | [Customisation >](customisation.md)
