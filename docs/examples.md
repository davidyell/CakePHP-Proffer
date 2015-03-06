#Examples
This manual page shows some examples of how to customise the behaviour of the plugin.

##Displaying uploaded images
You can use the `HtmlHelper` to link the images. Just make sure that you have both upload fields in your data.  
```php
echo $this->Html->image('../files/<table>/<field>/' . $data->get('image_dir') . '/<prefix>_' . $data->get('image'));
```

## Example event listeners
Here are some basic event listner example classes
* [Customise the upload folder and filename](examples/UploadFilenameListener.md)
* [Log the upload filename](examples/LogFilenameListener.md)
