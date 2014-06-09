=======
WordPress CMS Toolkit
==============

[![Build Status](https://travis-ci.org/cfpb/cms-toolkit.svg?branch=master)](https://travis-ci.org/cfpb/cms-toolkit)

This plugin provides tools for extending WordPress for use as a Content Management System (CMS). Tools include 
things like the function `build_post_type()`, a helper function for WordPress 
core's `register_post_type` function. The goal of the Toolkit is to promote DRY coding 
practices while simplifying the process of creating admin meta boxes. While CMS Toolkit is 
currently integrated with WordPress as a plugin, it may be more helpful to 
think of it as a library - a collection of methods which, when installed, are 
available throughout the application and make building complex functionality 
in WordPress a little easier.

## Table of Contents

1. [Install](#install)
1. [Activate](#activate)
1. [Develop](#develop)
1. [Extend](#extend)
1. [Technical details](#technical-details)
    1. [Simplified taxonomy registration](#simplified-taxonomy-registration)
    1. [Simplified post type registration](#simplified-post-type-registration)
    1. [MetaBox\Models: Register a self-validating meta box](#metaboxmodels-register-a-self-validating-meta-box)
    1. [Capabilities](#capabilities)
1. [Unit Tests](#unit-tests)

## Install

This plugin can be installed as a normal WordPress plugin. In the future it may also
be installed with composer.

__Warning:__ This plugin uses features introduced in PHP 5.3, since WordPress supports
back to 5.2.7, make sure your host is running the correct version. See [namespacing below.](#whats-a-namespace)

## Activate

To activate the plugin follow the steps below:

1. Login to WordPress account.
2. Go to Plugins screen and find __"CFPB_Utilities"__ in the list
3. Click __Activate Plugin__ to activate it.

## Develop

See Contributing.md

## Extend

Out of the box this plugin does nothing. The activation script merely makes the
namespaces, classes, and methods available to WordPress. Developers should consider
writing 'child' plugins that import classes and functionality from this one.
Importing with `use...as` in PHP is kind of like using `import <module> as` in python.

To check if this plugin is active, we've included a useful constant called
`DEPENDENCIES_READY` set on activation. Check if it is defined as a means of making
sure this plugin is active before running anything that depends on it.

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

A namespace is an isolated place where classes and methods can live without trampling
all over other methods in your system. Classes in this plugin exist in the CFPB\Utils
namespace and can be imported with `use`. To get the post type class, include a line
like: `use \CFPB\Utils\PostTypes;` at the beginning. You can also rename the class
something else by `use \CFPB\Utils\PostTypes as Foo;`. [PHP namespacing is really
cool, read about it.](http://www.php.net/manual/en/language.namespaces.php) [Some
people don't like
`use`](http://jason.pureconcepts.net/2013/04/php-namespaces-avoid-use/), to avoid it
you'll have to write out the fully qualified namespace whenever you call methods or
instantiate classes out of this plugin.

#### Example

There are many examples of how to use these methods in the unit tests, but here's a
full example of child plugin:

```
<?php
/*
*
* Add the normal Plugin front matter here
*
*/

namespace YourVendorName\YourPluginName;

use \CFPB\Utils\PostType;

class Base {
    public $util;

    function __construct() {
        $this->util = new PostType();
    }

    static function build() {
        add_action( 'init', array($this, 'post_types') );
    }
    static function post_types() {
        $this->util->build_post_type(
            'Regulation',
            'Regulations',
            'regulation',
            $prefix = 'cfpb_',
            $args = array(
                'has_archive' => false,
                'rewrite' => array( 'slug' => 'regulations', 'with_front' => false ),
                'supports' => array( 'title', 'editor', 'revisions', 'page-attributes', 'custom-fields' )
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

You might optionally wrap the add_action call in a conditional to check if the parent 
plugin is installed.

Without `use` you would have to type `\CFPB\Utils\PostType\build_post_type()` in the 
constructor.

##Technical details

This plugin extends WordPress by adding self-validating objects for creating meta box
forms (for post screens only, for now), post types, and taxonomies. It also modifies
WordPress permissions to inhibit certain behaviors among under-privileged users.

This plugin is highly extensible and 'child plugins' must be created in order to
actually do anything (except for permissions, for now). Each file in `/inc/` contains
a single class that, if extended, will simplify an otherwise difficult workflow.

Below are technical details for how this plugin works and how to extend it organized by 
class. Each class is in the CFPB\Utils namespace and can be called through a well 
qualified namespace. There are also unit tests which offer code-based examples of how to 
use these things.

### Simplified taxonomy registration

Namespace `\CFPB\Utils\`
Class: Taxonomy
Filename: inc/taxonmies.php

The function `build_taxonomies` is a helper function that reduces the amount of
repeating yourself required to register multiple taxonomies. It takes several
parameters and calls `register_taxonomy`. To actually register taxonomies, you should
hook your `build_taxonomies` call into `init` and flush rewrite rules if necessary.
Example:

```
<?php

$T = new \CFPB\Utils\Taxonomy();
$T->build_taxonomy(
    'Sub Category',
    'Sub Categories',
    'sub_category',
    'custom_post_type'
);

?>
```

In this example we create a taxonomy for the 'custom_post_type's called Sub Category.

Also contained here is `remove_post_term` which can be used to remove a term by ID or 
slug from a post.

### Simplified post type registration

Namespace: `\CFPB\Utils\`
Class: `PostType`
Filename: inc/post-types.php

Post-types.php contains a single class for post type registration with two methods: 
`build_post_type` and `maybe_flush_rewrite_rules`. To create a post type, simply 
instantiate the class with a singlular ($name) and plural ($plural) version of the 
name, a slug, and an optional prefix string and arguments array then pass these to 
`build_post_type`. Inline documentation elaborates this more clearly.

Finally, this class includes a method for flushing the rewrite rules only if needed. 
Rewrite rules are stored in a single database table and are cached by WordPress to 
help map URLs to the proper resource. Flushing them, expecially on a website with 
many custom rules, is expensive, but must be done in order for custom post type 
archives and permalinks to match the defined permalink structure. This method flushes 
the rules only after it checks for a specific string in the cached rewrite object. It 
is currently written only to support custom post types.

### MetaBox\Models: Register a self-validating meta box

Namespace: `\CFPB\Utils\MetaBox`
Class: Models
Filename: inc/meta-box-models.php

WordPress supports the adding of custom meta boxes on post editing screens, and for
now is limited only to those screens. The meta-box-models.php file contains a class
called `Models` which can be extended to create new MetaBoxes and have them register
automatically. This shortcuts the traditional route to meta box construction an
reduces the amount of repeating yourself required to make multiple boxes on the same
site. Creating a new meta box is as simple as:

```php
<?php
class TestMetaBox extends \CFPB\Utils\MetaBox\Models {
    public $title = 'Meta Box';
    public $slug = 'meta_box';
    public $post_type = 'post';
    public $context = 'side';
    public $fields = array(
        'field_one' => array(
            'title' => 'This is a field',
            'slug' => 'field_one',
            'type' => 'text_area',
            'params' => array(
                'cols' => 27,
            ),
            'placeholder' => 'Enter text',
            'howto' => 'Type some text',
            'meta_key' => 'field_one',
        ),
        'field_two' => array(
            'slug' => 'field_two',
            'title' => 'This is another field',
            'type' => 'number',
            'params' => array(),
            'placeholder' => '0-100',
            'howto' => 'Type a number',
            'meta_key' => 'category',
        ),
    );

    function __construct() {
        parent::__construct();
    }
}

$T = new TestMetaBox();

add_action( 'add_meta_boxes', array($T, 'generate') );
add_action( 'save_post', array($T, 'validate_and_save') );
?>
```
The class has a few key parts, the public variables $title, $slug, $post_type, and
$context and the public variable $fields, an array containing arrays for each html
elements of the box you want to generate. In the example above we make one
`<textarea>` field 27 columns wide targeted at the '`field_one`' meta key and one
`<input type="number">` field in a meta box on the side of 'post' editing screens.

Once you have the class, you need to hook it's `generate` method into
`add_meta_boxes` in order for it to show in WordPress. Something like this should
work: `add_action('add_meta_boxes', array( $TestMetaBox, 'generate') );`. When the
`generate` method is triggered by WordPress, it builds a meta box with built-in
callbacks. By default those come from `MetaBox\Views` but this is replaceable!

If you want to replace the callback that prints the HTML, create your own class and
pass it as a parameter to `set_view`. This will inject your views as into the class.
See the unit tests for this class for examples of how to do this. Your new Views
class must have a `ready_and_print_html` method. Examples of how to do this can be
seen in the unit tests where dependency injection is used to replace the Views class
with a mock.

This class also contains methods for validating and saving this form, too. Lines
106-148 handle form data. To use these validators, just hook `validate_and_save` into
`save_post` just as you would if you rolled your own meta box.

#### Fields

A meta box class can accept many different field types that correspond to valid HTML
elements. Each field array should contain the following keys: 'slug', 'title',
'type', 'params', 'placeholder', 'howto', and 'meta_key'. It can also contain 'class',
which assigns a class to a div wrapping the header and fields. With the exception of
'params' these are all strings. A field array like the following:

```php
<?php
'checkbox' => array(
    'title' => 'Checkbox',
    'slug' => 'checkbox',
    'label' => 'A Checkbox',
    'class' => 'some-class',
    'type' => 'boolean',
    'params' => array(),
    'placeholder' => '',
    'howto' => 'Check the box',
    'meta_key' => 'boolean_one',
),
?>
```

Will generate the following HTML in a meta box:

```HTML
<div class="some-class">
    <h4>Checkbox</h4>
    <p>
        <p>
            <label for="checkbox">A Checkbox</label>
            <input id="checkbox" name="boolean_one" value="" type="checkbox">Checkbox</input>
        </p>
        <p class="howto">Check the box</p>
    </p>
</div>
```
The IDs and classes correspond to IDs and classes used in the WordPress admin.
Changing the value of 'type' will modify the type of form field generated. Possible
values are listed below. Check the unit tests for examples of how to use each type.

* `text_area` generates a text area meta box.
* `number` generates an input field with the number type, optionally add a 'max_num' key to the params array to limit the length of input. For example: `'param' => array( 'max_length' => 2),`
* `text` generates a standard input field
* `boolean` an input field with the 'checkbox' type
* `radio` two input fields with values 'true' and 'false' (this may change in the future)
* `email` an input with the 'email' type
* `url` an input with the 'url' type
* `date` generates a trio of fields: a dropdown for months and two input fields for day and year
* `select` generates a `<select>` field with options specified in the 'params' array. For example `'param' => array( 'one', 'two', 'three',),`
* `mutliselect` is identical to `select` except that it passes the 'multiple' attribute allowing users to select multiple values
* `taxonomyselect` generates a `<select>` field with options pulled from the terms attached to the taxonomy specified in `meta_key`
* `nonce` generates a WordPress Nonce field using 'slug' for the ID
* `hidden` generates a hidden field with a value you can pass in 'params'
* `post_select` generates a drop down menu of all posts. The array passed to 'params' will be passed to `get_posts` and [you can use all the keys](http://codex.wordpress.org/Template_Tags/get_posts).

__Note:__ invalid 'type' values will always generate an `<hr />` and invalid values for $post_type or $context will generate `WP_Error`s

### Capabilities

The least developed feature of this plugin is the capability management functions
defined in capabilities.php. By 'least developed' we mean that it could be more
useful. This class removes the ability to edit the administrator role and the ability
for editors to promote users beyond their current level. That is, _if_ an editor can
modify user permissions and promote users (which would need to be done separately),
they can only do so for non-administrators and cannot promote anyone to
administrator.

If the [Review And Move to Production (RAMP) plugin](http://crowdfavorite.com/ramp/)
is active, this class will also make that plugin available to editors but not
authors. By default RAMP is made available to any user with the ability to edit
posts.

In the future we may see a world where this class can be used by feature plugins to
create meta capabilities for individual post types but we are not there yet.

## Unit Tests

[WP_Mock and
PHPunit](http://greg.harmsboone.org/blog/2014/01/01/writing-unit-tests-for-wordpress)
are used to write unit tests for each method. Any core WordPress methods called are
mocked. WP_Mock may be installed through composer and [PHPunit installation is well
documented](http://phpunit.de/).

Many of these tests end without assertions. In these tests, the verification is that
a core WordPress method is called properly and the correct number of times. The best
example of this can be seen in the tests for `maybe_flush_rewrite_rules` where in
some cases we want the method to call once and in others we want it not to call.
There is no assertion, but if it is called unexpectedly, the test will fail. Other
good examples of this are in the Meta Box classes' tests.
