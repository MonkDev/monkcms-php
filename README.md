MonkCMS PHP
===========

[![Latest Stable Version](https://img.shields.io/packagist/v/monkdev/monkcms.svg?style=flat)](https://packagist.org/packages/monkdev/monkcms)
[![Build Status](https://img.shields.io/travis/MonkDev/monkcms-php/dev.svg?style=flat)](https://travis-ci.org/MonkDev/monkcms-php)
[![codecov](https://codecov.io/gh/MonkDev/monkcms-php/branch/dev/graph/badge.svg)](https://codecov.io/gh/MonkDev/monkcms-php)
[![Dependency Status](https://img.shields.io/gemnasium/MonkDev/monkcms-php.svg?style=flat)](https://gemnasium.com/MonkDev/monkcms-php)

A PHP client for accessing the MonkCMS API in non-website environments.

While `monkcms.php` is great for building websites, it includes many features
that simply aren't necessary — and make it hard to use — in other environments:
sessions, caching, Easy Edit, etc. This library strips all of that away to
provide only what's absolutely necessary to access content through the MonkCMS
API. It's ideal for use in web apps, command line scripts, APIs, and more.

*   [Documentation](https://monkdev.github.io/monkcms-php/classes/Monk.Cms.html)

Overview
--------

### Install

Using [Composer](http://getcomposer.org), add `monkdev/monkcms` to your
`composer.json`:

```json
{
  "require": {
    "monkdev/monkcms": "~0.6"
  }
}
```

```bash
$ composer update
```

Or:

```bash
$ composer require monkdev/monkcms:~0.6
```

### Configure

Configuration can be done by passing an array to the constructor:

```php
$cms = new Monk\Cms(array(
    'siteId'     => 12345,
    'siteSecret' => 'secret'
));
```

Or after instantiation by calling `setConfig`:

```php
$cms->setConfig(array(
    'siteId'     => 12345,
    'siteSecret' => 'secret'
));
```

When a configuration value isn't set, it falls back to a sensible default in
many cases. These defaults can be changed to help alleviate repeating the same
configuration in multiple places:

```php
Monk\Cms::setDefaultConfig(array(
    'siteId'     => 12345,
    'siteSecret' => 'secret'
));
```

While only the `siteId` and the `siteSecret` are required, the following configuration values are avaialble for use

```php
$defaultConfig = array(
    'request'    => null, // Override the default Http Request library used in the package
    'siteId'     => null, // Required
    'siteSecret' => null, // Required
    'cmsCode'    => 'EKK', // Override the default CMS Code
    'cmsType'    => 'CMS', // Override the default CMS Type (Sermon Cloud/Church Cloud vs CMS Content)
    'url'        => 'http://api.monkcms.com' // Override the default API Endpoint
);
```

### Request

Requesting content is simple:

```php
$content = $cms->get('sermon/detail/sermon-slug');
```

If you're familiar with `getContent` from `monkcms.php`,

*   `sermon` is the module,
*   `detail` is the `display` value, and
*   `sermon-slug` is the `find` value (optional).

Additional parameters can be passed in an array as the second argument:

```php
$content = $cms->get('sermon/list', array(
    'nonfeatures' => true,
    'howmany'     => 5
));
```

If you'd prefer to forgo the slash-separated string format, you can instead pass
a single array argument with all of the values:

```php
$content = $cms->get(array(
    'module'  => 'sermon',
    'display' => 'list',
    'howmany' => 5
));
```

`get` returns [JSON as described by the API docs](http://developers.monkcms.com/article/json/)
in associative array form. So, for example, a sermon's title can be accessed at
`$content['show']['title']`.

If a failure occurs, `get` throws a `Monk\Cms\Exception`.

### Multiple shows

If you want to use `show` key to format API output, there are 2 ways

#### 1. Using inline string

For example:

```php
$cms->get(array(
  'module'  => 'smallgroup',
  'display' => 'list',
  'order' => 'recent',
  'emailencode' => 'no',
  'howmany' => 1,
  'page' => 1,
  'show' => "___starttime format='g:ia'__ __endtime format='g:ia'__",
));
```

#### 2. Using an array

For example:

```php
$cms->get(array(
  'module'  => 'smallgroup',
  'display' => 'list',
  'order' => 'recent',
  'emailencode' => 'no',
  'howmany' => 1,
  'page' => 1,
  'show' => [
    "__starttime format='g:ia'__",
    "__endtime format='g:ia'__"
  ]
));
```

Development
-----------

[Composer](http://getcomposer.org) is used for dependency management and task
running. Start by installing the dependencies:

```bash
$ composer install
```

### Tests

Testing is done with [PHPUnit](http://phpunit.de). To run the tests:

```bash
$ composer test
```

Continuous integration is setup through [Travis CI](https://travis-ci.org/MonkDev/monkcms-php)
to run the tests against PHP v5.6, v7.0, and v7.1. ([Circle CI](https://circleci.com/gh/MonkDev/monkcms-php)
is also setup to run the tests against PHP v5.6, but is backup for now until
multiple versions can easily be specified.) The code coverage results are sent
to [Codecov](https://codecov.io/gh/MonkDev/monkcms-php) during CI for tracking
over time. Badges for both are dispayed at the top of this README.

### Documentation

[phpDocumentor](http://phpdoc.org) is used for code documentation. To build:

```bash
$ composer phpdoc
```

This creates a `doc` directory (that is ignored by git).

### Quality

A number of code quality tools are configured to aid in development. To run them
all at once:

```bash
$ composer quality
```

Each tool can also be run individually:

*   [php -l](http://www.php.net/manual/en/function.php-check-syntax.php):
    `$ composer phplint`
*   [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer):
    `$ composer phpcs`
*   [PHP Copy/Paste Detector](https://github.com/sebastianbergmann/phpcpd):
    `$ composer phpcpd`
*   [PHPLOC](https://github.com/sebastianbergmann/phploc): `$ composer phploc`
*   [PHP Mess Detector](http://phpmd.org): `$ composer phpmd`
*   [SensioLabs Security Checker](https://github.com/sensiolabs/security-checker):
    `$ composer security-checker`

Deployment
----------

Publishing a release to [Packagist](https://packagist.org) simply requires
creating a git tag:

```bash
$ git tag -a vMAJOR.MINOR.PATCH -m "Version MAJOR.MINOR.PATCH"
$ git push origin vMAJOR.MINOR.PATCH
```

Be sure to choose the correct version by following [Semantic Versioning](http://semver.org).

### Publish Documentation

After releasing a new version, the documentation must be manually built and
published to the `gh-pages` branch.
