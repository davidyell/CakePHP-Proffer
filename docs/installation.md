#Installation
This manual page deals with the installation of the Proffer plugin. Where you can get the code and where should it be in your project.

## Packagist
You can find it on Packagist [https://packagist.org/packages/davidyell/proffer](https://packagist.org/packages/davidyell/proffer)

## Getting the plugin
Add it to your `composer.json` in your require section `"davidyell/proffer": "dev-master"` and then run `composer update`.

## CakePHP
Then you'll need to load the plugin in your `config/bootstrap.php` file. `Plugin::load('Proffer', ['bootstrap' => true]);`.

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