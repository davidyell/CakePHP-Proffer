# :fallen_leaf: Archived
I'm very sorry, but I have not worked on this project for a long time and so I have chosen to archive it. The CakePHP 4 version of the plugin never got to a release stage.

I would recommend checking out https://github.com/FriendsOfCake/cakephp-upload which provides similar functionality, except the image manipulation, and is activly maintained.

Thanks to everyone who contributed!


----


# CakePHP3-Proffer
An upload plugin for CakePHP 3. Looking for CakePHP 4? Check out the `cake-4` branch.

![Proffer definition](http://i.imgur.com/OaAqQ6x.png)

## What is it?
So I needed a way to upload images in [CakePHP 3](http://github.com/cakephp/cakephp), and as I couldn't find anything 
that I liked I decided to write my own in a similar vein to how [@josegonzalez](https://github.com/josegonzalez) had 
written his [CakePHP-Upload](https://github.com/josegonzalez/cakephp-upload) plugin for CakePHP 2.

## Requirements
* PHP 5.6+
* Database
* CakePHP 3
* [Composer](http://getcomposer.org/)
* [File Info is enabled](http://php.net/manual/en/book.fileinfo.php) for mimetype validation

For more requirements, please check the `composer.json` file in the repository.

This plugin implements the [Intervention](http://image.intervention.io/) image library.

## Status
[![Build Status](https://travis-ci.org/davidyell/CakePHP3-Proffer.svg?branch=master)](https://travis-ci.org/davidyell/CakePHP3-Proffer)
[![Coverage Status](https://coveralls.io/repos/davidyell/CakePHP3-Proffer/badge.png)](https://coveralls.io/r/davidyell/CakePHP3-Proffer)
[![Dependency Status](https://www.versioneye.com/user/projects/54eee43931e55e12f9000018/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54eee43931e55e12f9000018)
[![Latest Stable Version](https://poser.pugx.org/davidyell/proffer/v/stable.svg)](https://packagist.org/packages/davidyell/proffer) [![Total Downloads](https://poser.pugx.org/davidyell/proffer/downloads.svg)](https://packagist.org/packages/davidyell/proffer) [![Latest Unstable Version](https://poser.pugx.org/davidyell/proffer/v/unstable.svg)](https://packagist.org/packages/davidyell/proffer) [![License](https://poser.pugx.org/davidyell/proffer/license.svg)](https://packagist.org/packages/davidyell/proffer)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/65daa950-3128-44ef-b388-d4370efd853c/mini.png)](https://insight.sensiolabs.com/projects/65daa950-3128-44ef-b388-d4370efd853c)

## Documentation
All the documentation can be found in the [docs](docs) folder.
* [Installation](docs/installation.md)
* [Configuration](docs/configuration.md)
* [Validation](docs/validation.md)
* [Customisation](docs/customisation.md)
* [Shell tasks](docs/shell.md)
* [Examples](docs/examples.md)
* [FAQ](docs/faq.md)
* [Upgrading](docs/upgrading.md)

## Contribution
Please open a pull request or submit an issue if there is anything you would like to contribute. Please write a test for 
any new functionality that you add and be sure to run the tests before you commit. Also don't forget to run PHPCS with 
the PSR2 standard to avoid errors in TravisCI.

:warning: Please target all new PRs at the `develop` branch.

## License
Please see [LICENSE](LICENSE)
