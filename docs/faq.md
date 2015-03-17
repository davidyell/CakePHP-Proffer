#Frequently asked questions
This manual page collects together all the frequent questions about the plugin, it's functionality and some of the more 
common errors people might experience.

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

## Still having trouble?
If you're still having trouble, head to `#cakephp` on Freenode.net and ask for help. A web chat client is available 
on [the Freenode website](http://webchat.freenode.net/).