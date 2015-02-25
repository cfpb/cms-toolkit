======= 
WordPress CMS Toolkit 
==============

This plugin provides tools for extending WordPress for use as a Content
Management System (CMS). Tools include things like the function
`build_post_type()`, a helper function for WordPress core's
`register_post_type` function. The goal of the Toolkit is to promote DRY coding
practices while simplifying the process of creating admin meta boxes. While CMS
Toolkit is currently integrated with WordPress as a plugin, it may be more
helpful to think of it as a library - a collection of methods which, when
installed, are available throughout the application and make building complex
functionality in WordPress a little easier.

[![Build Status](https://travis-ci.org/cfpb/cms-toolkit.svg)](https://travis-ci.org/cfpb/cms-toolkit)

## Table of Contents

1. [Install](#install) 
2. [Activate](#activate) 
3. [Develop](#develop) 
4. [Extend](#extend) 
5. [Technical details](#technical-details)
6. [Unit Tests](#unit-tests) 
7. [FAQ](faq.md)

## Install

This plugin can be installed as a normal WordPress plugin.

__Warning:__ __This plugin requires PHP 5.3+.__

## Activate

To activate the plugin follow the steps below:

1. Login to WordPress account. 
2. Go to Plugins screen and find __"WordPress CMS Toolkit"__ in the list 
3. Click __Activate Plugin__ to activate it.

## Develop

See [CONTRIBUTING.md](CONTRIBUTING.md)

## How to Use

Out of the box this plugin makes namespaces, classes, and methods available to
WordPress. Developers should write 'child' plugins that import
classes and functionality from this one. Importing with `use...as` in PHP is
kind of like using `import <module> as` in python.

To check if this plugin is active, check for the existence of the
DEPENDENCIES_READY constant in your child plugin.

Example:

```php
<?php 
if ( defined('DEPENDENCIES_READY') ) {
   // do stuff... 
} else {
// do other stuff... 
} 
?> 
```

### What's a namespace?

A namespace is an isolated place where classes and methods can live without
trampling all over other methods in your system. Classes in this plugin exist in
the CFPB\Utils namespace and can be imported with `use`. To get the post type
class, include a line like: `use \CFPB\Utils\PostTypes;` at the beginning. You
can also rename the class something else by `use \CFPB\Utils\PostTypes as Foo;`.
[PHP namespacing is really cool, read about it.](http://www.php.net/manual/en/language.namespaces.php) 
[Some people don't like `use`](http://jason.pureconcepts.net/2013/04/php-namespaces-avoid-use/), to
avoid it you'll have to write out the fully qualified namespace whenever you
call methods or instantiate classes out of this plugin.

#### Example

There are many examples of how to use these methods in the unit tests, but
here's a full example of child plugin:

```php
<?php 
/* * 
* Add the normal Plugin front matter here 
* */

namespace YourVendorName\YourPluginName;

use \CFPB\Utils\PostType;

class Base {
   public $util;

  function __construct() { 
    $this->util = new PostType(); 
  }

  static function build() {
     add_action( 'init', array($this,'post_types') );
  }
  static function post_types() {
    $this->util->build_post_type(
      'Regulation',
      'Regulations',
      'regulation',
      $prefix = 'cfpb_',
      $args = array(
        'has_archive' => false,
        'rewrite' => array( 
          'slug' => 'regulations',
          'with_front' => false
        ),
      'supports' => array( 
        'title',
        'editor',
        'revisions',
        'page-attributes',
        'custom-fields'
        )
      )
    );
  $this->util->maybe_flush_rewrite_rules('cfpb_regulation');
  } 
}

$p = new \Vendor\Plugin\Base(); 
if ('DEPENDENCIES_READY') {
  add_action('plugins_loaded', array($p, 'build')); 
} 
?>
```

##Technical details

This plugin extends WordPress by adding objects for creating self-validating
meta box forms (for post screens only, for now), post types, and taxonomies. It
also modifies WordPress permissions to inhibit certain behaviors among under-
privileged users.

This plugin is highly extensible and 'child plugins' should be created in order to
actually do anything (except for permissions, for now).

See [`/inc/README.md`](inc/README.md) for how this plugin works and examples of how to extend it
organized by class.

## Unit Tests

[WP_Mock and PHPunit](http://greg.harmsboone.org/blog/2014/01/01/writing-unit-
tests-for-wordpress) are used to write unit tests for each method. Any core
WordPress methods called are mocked. WP_Mock may be installed through composer
and [PHPunit installation is well documented](http://phpunit.de/).

Many of these tests end without assertions. In these tests, the verification is
that a core WordPress method is called properly and the correct number of times.
