#Proffer shell tasks
This manual page deals with the command line tools which are included with the Proffer plugin.

##Getting available tasks
Proffer comes with a built in shell which can help you achieve certain things when dealing with your uploaded files. To
find out more about the shell you can use the following command to output the help and options.

```bash
$ bin/cake proffer
```

##Regenerate thumbnail task
If you would like to regenerate the thumbnails for files already on your system, or you've changed your configuration. You
can use the built-in shell to regenerate the thumbnails for a table.

```bash
$ bin/cake proffer generate <table>
```

If you have used a custom ImageTransform or Path class in your uploads, these can be passed as params.
This example shows regenerating thumbnails for the `UserImages` table class, using a custom path class. 
**Note** the fully namespaced class name and escaped double backslash.

```bash
$ bin/cake proffer generate -p \\App\\Lib\\Proffer\\UserImagePath UserImages
```

##Cleanup task
The cleanup task will look at a models uploads folder and match the files there with it's matching entry in the
database. If a file doesn't have a matching record in the database it **will be deleted**.

:warning: This shell only works with the default behaviour settings.

```bash
$ bin/cake proffer cleanup -vd <table>
```

Using the `-vd` options will perform a verbose dry-run, this is recommended before running the shell for real.

[< Customisation](customisation.md) | [Examples >](examples.md)
