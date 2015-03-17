#CakePHP3-Proffer
An upload plugin for CakePHP 3.

![Proffer definition](http://i.imgur.com/OaAqQ6x.png)

##What is it?
So I needed a way to upload images in CakePHP 3, and as I couldn't find anything that I liked I decided to write my own 
in a similar vein to how [@josegonzalez](https://github.com/josegonzalez) had written his 
[CakePHP-Upload](https://github.com/josegonzalez/cakephp-upload) plugin for CakePHP 2.

##Requirements
* PHP 5.4+
* Database
* CakePHP 3
* [File Info is enabled](http://php.net/manual/en/book.fileinfo.php) for mimetype validation

##Status
This is currently in alpha, but will upload images for you.

[![Build Status](https://travis-ci.org/davidyell/CakePHP3-Proffer.svg?branch=master)](https://travis-ci.org/davidyell/CakePHP3-Proffer)
[![Coverage Status](https://coveralls.io/repos/davidyell/CakePHP3-Proffer/badge.png)](https://coveralls.io/r/davidyell/CakePHP3-Proffer)
[![Dependency Status](https://www.versioneye.com/user/projects/54eee43931e55e12f9000018/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54eee43931e55e12f9000018)
[![Latest Stable Version](https://poser.pugx.org/davidyell/proffer/v/stable.svg)](https://packagist.org/packages/davidyell/proffer) [![Total Downloads](https://poser.pugx.org/davidyell/proffer/downloads.svg)](https://packagist.org/packages/davidyell/proffer) [![Latest Unstable Version](https://poser.pugx.org/davidyell/proffer/v/unstable.svg)](https://packagist.org/packages/davidyell/proffer) [![License](https://poser.pugx.org/davidyell/proffer/license.svg)](https://packagist.org/packages/davidyell/proffer)

##Documentation
All the documentation can be found in the [docs](docs) folder.
* [Installation](docs/installation.md)
* [Configuration](docs/configuration.md)
* [Validation](docs/validation.md)
* [Customisation](docs/customisation.md)
* [Shell tasks](docs/shell.md)
* [Examples](docs/examples.md)

##Contribution
Please open a pull request or submit an issue if there is anything you would like to contribute. Please write a test for 
any new functionality that you add and be sure to run the tests before you commit. Also don't forget to run PHPCS with 
the PSR2 standard to avoid errors in TravisCI.

NB, All new PRs should be targeted at the `develop` branch.

##License
Please see [LICENSE](LICENSE)
