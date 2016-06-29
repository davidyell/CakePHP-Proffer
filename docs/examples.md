#Examples
This manual page shows some examples of how to customise the behaviour of the plugin,
as well as event listeners and image display.

##Displaying uploaded images
You can use the `HtmlHelper` to link the images. Just make sure that you have both upload fields in the data set to the view.
This is what it would look like if you're using the defaults, if you've implemented your own path class, you will need
to update the paths accordingly.
```php
echo $this->Html->image('../files/<table>/<field>/' . $data->get('image_dir') . '/<prefix>_' . $data->get('image'));
```

## Example event listener
Here are some basic event listener example classes
* [Customize the upload folder and filename](examples/UploadFilenameListener.md)
* [Customize behavior of file creation/deletion](examples/UploadAndDeleteImageListener.md)

##Uploading multiple related images
This example will show you how to upload many images which are related to your
current table class. An example setup might be that you have a `Users` table class
and a `UserImages` table class. The example below is just [baked code](http://book.cakephp.org/3.0/en/bake/usage.html).

###Tables
The relationships are setup as follows. Be sure to attach the behavior to the
table class which is receiving the uploads.

```php
// src/Model/Table/UsersTable.php
$this->hasMany('UserImages', ['foreignKey' => 'user_id'])

// src/Model/Table/UserImagesTable.php
$this->addBehavior('Proffer.Proffer', [
    'image' => [
        'dir' => 'image_dir',
        'thumbnailSizes' => [
            'square' => ['w' => 100, 'h' => 100],
            'large' => ['w' => 250, 'h' => 250]
        ]
    ]
]);

$this->belongsTo('Users', ['foreignKey' => 'user_id', 'joinType' => 'INNER']);
```

###Entities
Your entity must allow the associated field in it's `$_accessible` array. So in our
example we need to check that the `'user_images' => true` is included in our `User` entity.

###Controller
No changes need to be made to standard controller code as Cake will automatically save any
first level associated data by default. As our `Users` table is directly associated with
our `UserImages` table, we don't need to change anything.

If you were working with a related models data, you would need to specify the associations
to populate when [merging the entity data](http://book.cakephp.org/3.0/en/orm/saving-data.html#converting-request-data-into-entities)
using the `'associated'` key.

###Templates
You will need to include the related fields in your templates using the correct
field names, so that your request data is formatted correctly.

```php
// Don't forget that you need to include ['type' => 'file'] in your ->create() call
<fieldset>
    <legend>User images</legend>
    <?php
    echo $this->Form->input('user_images.0.image', ['type' => 'file']);
    echo $this->Form->input('user_images.1.image', ['type' => 'file']);
    echo $this->Form->input('user_images.2.image', ['type' => 'file']);
    ?>
</fieldset>
```

How you deal with the display of existing images, deletion of existing images,
and adding of new upload fields is up to you, and outside the scope of this example.

###Deleting images but preserving data
If you need to delete an upload and remove it's associated data from your data store, you can achieve this in your controller.

The easiest way is to add a checkbox to your form and then look for it when processing your post data.

An example form might look like. It's important to note that I've disabled the `hiddenField` option here.

```php
echo $this->Form->input('cover', ['type' => 'file']);
if (!empty($league->cover)) {
    echo $this->Form->input('delete_cover', ['type' => 'checkbox', 'hiddenField' => false, 'label' => 'Remove my cover photo']);
}
```

Then in your controller, check for the field before using `patchEntity`

```php
// Deleting the upload?
if (isset($this->request->data['delete_cover'])) {
    $this->request->data['image_dir'] = null;
    $this->request->data['cover'] = null;

    $path = new \Proffer\Lib\ProfferPath($this->Leagues, $league, 'cover', $this->Leagues->behaviors()->Proffer->config('cover'));
    $path->deleteFiles($path->getFolder(), true);
}

// patchEntity etc
```

[< Shell tasks](shell.md) | [FAQ >](faq.md)
