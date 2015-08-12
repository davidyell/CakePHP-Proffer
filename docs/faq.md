#Frequently asked questions
This manual page collects together all the frequent questions about the plugin, it's functionality and some of the more
common errors people might experience.

## Proffers scope
The scope of the plugin is the limit of the functionality it will provide.

First and foremost it is an upload plugin. This means it's core responsibility is to copy files from once place to
another. Which, in most cases, will be from a client machine to a server.

Additional functionality to this is the generation of various sizes of thumbnail and some associated tools. In this
capacity there are some events which process images and create the thumbnails. There are also some related shell tasks
to make thumbnail generation easier.

Some things which the plugin does not do are provide methods for linking images in the front-end of your website, such
as a helper. It's up to the developer to place the uploaded content in the front-end of the website. Nor will the plugin
interact with your admin to display uploaded images or anything like that.

Proffer will also not manage your file system for you. It can only upload images, and doesn't version them or anything
similar. This kind of functionality would need to be developed by the developer.

The provided thumbnail generation is basic. If you want to expand upon this, such as creating new types of thumbnail or
creating watermarked images you are encouraged to hook the events in the plugin and create your own code for generating
your customised thumbnails.

## Errors
If you are experiencing any of these errors, here are your solutions.

### Unknown type "proffer.file"
This has two primary causes.

The first is that in your `config/boostrap.php` you might have forgotten to include the
`'bootstrap' => true` when loading the plugin, which means the datatype isn't loaded.

```php
// config/bootstrap.php
Plugin::load('Proffer', ['bootstrap' => true]);
```

The second thing is that you might have forgotten to include the `_initializeSchema` method in your table class. This
method bind the data type class to the field.

```php
// src/Model/Table/Examples.php
protected function _initializeSchema(\Cake\Database\Schema\Table $table) {
    $table->columnType('file','proffer.file');
    return $table;
}
```

### File name is written to the database as "Array"
The thing to check is your form is using the file type, and your input is also a file type.

```php
echo $this->Form->input($entity, ['type' => 'file']);
echo $this->Form->input('file_upload', ['type' => 'file']);
// etc
```
### No database changes and no file system changes
If the form is submitting without issue, yet no file upload is tacking place, ensure that your form is multipart. In your template, make sure your form is type file. `$this->Form->create($example, ['type' => 'file'])`.

## Still having trouble?
If you're still having trouble, head to `#cakephp` on Freenode.net and ask for help. A web chat client is available
on [the Freenode website](http://webchat.freenode.net/).


[< Examples](examples.md) | [Readme >](../README.md)
