#Installation
This manual page deals with the installation of the Proffer plugin. Where you can get the code and where should it be in your project.

## Packagist
You can find it on Packagist [https://packagist.org/packages/davidyell/proffer](https://packagist.org/packages/davidyell/proffer)

## Getting the plugin
In your terminal you can use

```bash
$ composer require 'davidyell/proffer:~0.4'
```

If you would rather edit your `composer.json` you can add it to your `composer.json` in your require section `"davidyell/proffer": "dev-master"` and then run `composer update`.

It's always advised to lock your dependencies to a specific version number. You can [check the releases](https://github.com/davidyell/CakePHP3-Proffer/releases),
 or [read more about versions on Composer.org](https://getcomposer.org/doc/01-basic-usage.md#package-versions). For more information about [installing plugins with CakePHP](http://book.cakephp.org/3.0/en/plugins.html#installing-a-plugin-with-composer), check the book.

:warning: Installing the plugin without the use of Composer is unsupported, you do so at your own risk.

## CakePHP
Then you'll need to load the plugin in your `config/bootstrap.php` file.

```php
Plugin::load('Proffer');
```

## Database
Next you need to add the fields to your table. You'll want to add your file upload field, this will store the name of the
uploaded file such as `example.jpg` and you also need the dir field to store the directory in which the file has been
stored. By default this is `dir`.

An example query to add columns might look like this for MySQL.

```sql
ALTER TABLE `teams`
ADD COLUMN `photo` VARCHAR(255),
ADD COLUMN `photo_dir` VARCHAR(255)
```

Don't forget to ensure that the fields are present in your entities `$_accessible` array.

[< Readme](../README.md) | [Configuration >](configuration.md)
