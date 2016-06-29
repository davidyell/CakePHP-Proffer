# Upgrading
If you are upgrading between versions this documentation page will give you some insights into the changes which you
will need to make and potential pitfalls.

For more release information [please see the releases](https://github.com/davidyell/CakePHP3-Proffer/releases).

## 0.7.0
[Release 0.7.0](https://github.com/davidyell/CakePHP3-Proffer/releases/tag/0.7.0)

You should only encounter problems if you have a Transform class which depends upon the Imagine image library, which has been removed in this release.

## 0.6.0
[Release 0.6.0](https://github.com/davidyell/CakePHP3-Proffer/releases/tag/0.6.0)

When migrating to `0.6.0` you might encounter problems with validation, specifically the `filesize()` method. You will
need to change the param order to match, `fileSize($check, $operator = null, $size = null)`. This is documented in the
[api validation docs](http://api.cakephp.org/3.0/class-Cake.Validation.Validation.html#_fileSize).

The `operator` can be either a word or operand is greater >, is less <, greater or equal >= less or equal <=, is less <,
 equal to ==, not equal !=.

## 0.5.0
[Release 0.5.0](https://github.com/davidyell/CakePHP3-Proffer/tree/0.5.0)

When upgrading to `0.5.0` you no longer need to bootstrap the plugin, as the data type class will be loaded
automatically.

So the only change required is to change your `config/bootstrap.php` to be `Plugin::load('Proffer')`.

## 0.4.0
[Release 0.4.0](https://github.com/davidyell/CakePHP3-Proffer/releases/tag/v0.4.0)

This version removes some of the events in the plugin, so any code which hooks the events will need to be updated.
Instead of hooking these events you can inject your own transform class in the plugin in which you can implement your
changes.

## 0.3.0
[Release 0.3.0](https://github.com/davidyell/CakePHP3-Proffer/releases/tag/v0.3.0)

If you need to make the generation of thumbnails optional, this is now possible by updating the configuration.
