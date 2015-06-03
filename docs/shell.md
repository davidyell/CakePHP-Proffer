#Proffer shell tasks
This manual page deals with the command line tools which are included with the Proffer plugin.

##Getting available tasks
Proffer comes with a built in shell which can help you achieve certain things when dealing with your uploaded files. To
find out more about the shell you can use the `-h` flag on the command line.

```bash
$ bin/cake proffer.proffer -h
```

##Regenerate thumbnail task
If you would like to regenerate the thumbnails for files already on your system, or you've changed your configuration. You
can use the built-in shell to regenerate the thumbnails for a table.

```bash
$ bin/cake proffer.proffer generate <table>
```

##Cleanup task
The cleanup task will look at a models uploads folder and match the files there with it's matching entry in the
database. If a file doesn't have a matching record in the database it **will be deleted**.

```bash
$ bin/cake proffer.proffer cleanup <table>
```


[< Customisation](customisation.md) | [FAQ >](faq.md)
