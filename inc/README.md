# Taxonomies, Post Types, Meta Boxes, and Capabilities

## Table Of Contents

1. [Simplified taxonomy registration](#simplified-taxonomy-registration)   
1. [Simplified post type registration](#simplified-post-type-registration)   
1. [MetaBox\Models: Register a self-validating meta box](#metaboxmodels-register-a-self-validating-meta-box)
1. [Capabilities](#capabilities)

## Simplified taxonomy registration

_Namespace `\CFPB\Utils\` Class: Taxonomy Filename: inc/taxonmies.php_

The function `build_taxonomies` is a helper function that reduces the amount of
repeating yourself required to register multiple taxonomies. It takes several
parameters and calls `register_taxonomy`. To actually register taxonomies, you
should hook your `build_taxonomies` call into `init` and flush rewrite rules if
necessary. Example:

```php
<?php

$T = new \CFPB\Utils\Taxonomy(); 
function register_my_taxonomy() {
  $T->build_taxonomy(
    'Sub Category',
    'Sub Categories',
    'sub_category',
    'custom_post_type' 
  );
}
?> 
```

In this example we create a taxonomy for the 'custom_post_type's called Sub
Category. You still need to hook `register_my_taxonomy` into `init`.

Also contained here is `remove_post_term` which can be used to remove a term by
ID or slug from a post.

## Simplified post type registration

_Namespace: `\CFPB\Utils\` Class: `PostType` Filename: inc/post-types.php_

Post-types.php contains a single class for post type registration with two
methods: `build_post_type` and `maybe_flush_rewrite_rules`. To create a post
type, simply instantiate the class with a singlular ($name) and plural
($plural) version of the name, a slug, an optional prefix string, and
arguments array then pass these to `build_post_type`. Inline documentation
elaborates this more clearly.

Finally, this class includes a method for flushing the rewrite rules only if
needed. Rewrite rules are stored in a single database table and are cached by
WordPress to help map URLs to the proper resource. Flushing them, especially on
a website with many custom rules, is expensive, but must be done in order for
custom post type archives and permalinks to begin working. This method flushes 
checks the cached rewrite object and only flushes if the custom post type's url 
string is absent. It is currently written only to support custom post types.

## MetaBox\Models: Register a self-validating meta box

_Namespace: `\CFPB\Utils\MetaBox` Class: Models Filename: inc/meta-box-models.php_

WordPress supports the adding of custom meta boxes on post editing screens, and
for now is limited only to those screens. The meta-box-models.php file contains
a class called `Models` which can be extended to create new MetaBoxes and have
them register automatically. This shortcuts the traditional route to meta box
construction an reduces the amount of repeating yourself required to make
multiple boxes on the same site. Creating a new meta box is as simple as:

```php 
<?php 
// metabox.php 
namespace testNamespace; 
class TestMetaBox extends \CFPB\Utils\MetaBox\Models {
    public $title = 'Meta Box';
    public $slug = 'meta_box';
    public $post_type = 'post';
    public $context = 'side';
    public $fields = array(
        'field_one' => array(
            'title' => 'This is a field',
            'slug' => 'field_one',
            'type' =>'text_area',
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

    function __construct() {     parent::__construct();   } }
?> 
```

Then hook your functions into your plugin activation. We recommend using three
functions within a class to do this: one function to call `generate` out of your
meta box class, another to call `validate_and_save`, and a third to add those
functions to their appropriate actions. Finally, hook the third function into
plugins_loaded.

```php
<?php 
// plugin.php 
namespace testNamespace; 
class Base {
    function hook_the_things() {
        require_once( 'metabox.php');
        
        add_action( 'save_post', array( '\testNamespace\TestMetaBox', 'do_the_saves' ) );
        add_action( 'add_meta_boxes', array('\testNamespace', 'add_the_box' ) );   
    }

    function add_the_box() {
        $TestMetaBox = new \testNamespace\TestMetaBox();
        $TestMetaBox->generate();   
    }

    function do_the_saves( $post_id ) {
        $post = get_post( $post_id );
        $TestMetaBox = new \testNamespace\TestMetaBox();     
        if ( in_array( $post->post_type, $TestMetaBox->post_type ) ) {
            $TestMetaBox->validate_and_save($post_id);
        }   
    } 
} 
add_action( 'plugins_loaded', array( '\testNamespace\Base', 'hook_the_things' ) );
?> 
``` 

The class has a few key parts, the public variables $title, $slug,
$post_type, $context and $fields. The last is an array containing arrays for 
each html element of the box you want to generate. In the example
above we make one `<textarea>` field 27 columns wide targeted at the
'`field_one`' meta key and one `<input type="number">` field. Both of these will
go into a meta box on the side of 'post' editing screens with the title Meta Box.

Once you have the class, you need to hook it's `generate` method into
`add_meta_boxes` in order for it to show in WordPress. See the example above for
how to do this.

This class also contains methods for validating and saving this form, too. Lines
106-148 handle form data. To use these validators, just hook `validate_and_save`
into `save_post` as illustrated above.

Because all of these functions are contained in classes you are extending, you 
can overwrite them if needed. Just declare a function with the same name as the 
one in the parent class and WordPress will use yours instead of ours. If you 
want to still run the parent's version, you can always call `parent::
overwritten_function_name()`. In certain cases you can also fully replace a 
class from the cms-toolkit by injecting a new dependency. See the unit tests for
an example of how to do this.

### Fields

A meta box class can accept many different field types that correspond to valid
HTML elements. Each field array should contain the following keys: 'slug',
'title', 'type', 'params', 'placeholder', 'howto', and 'meta_key'. It can also
contain 'class', which assigns a class to a div wrapping the header and fields.
With the exception of 'params' these are all strings. A field array like the
following:

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

The IDs and classes
correspond to IDs and classes used in the WordPress admin. Changing the value of
'type' will modify the type of form field generated. Possible values are listed
below. Check the unit tests for examples of how to use each type.

* `text_area` generates a text area meta box.
* `wysiwyg` calls `wp_editor` to generate a text editor. Defaults to TinyMCE with
Quicktags enabled. A `params` array is directly related to the `settings` array
seen [here](http://codex.wordpress.org/Function_Reference/wp_editor) so use it
the same way.
that is used as the settings for the 
* `number` generates an input field with the number type, optionally add a 
'max_num' key to the params array to limit the length of input. For example:
`'param' => array( 'max_length' => 2),` 
* `text` generates a standard input field * `boolean` an input field with
the 'checkbox' type * `radio` two input fields with values 'true' and 'false'
(this may change in the future) 
* `email` an input with the 'email' type 
* `url` an input with the 'url' type 
* `link` two inputs, one with the `url` type and another with `text`, validates 
as an array like `array(0 => 'url', 1 => 'text')`; 
* `date` generates a trio of fields: a dropdown for months and two
input fields for day and year
* `time` generates a similar trio of fields for hour, minute, and am/pm selection.
* `datetime` calls both `date` and `time` to generate a datetime set of fields.
* `select` generates a `<select>` field with options specified in the 'params' 
array. For example `'param' => array( 'one', 'two', 'three',),` 
* `mutliselect` is identical to `select` except that it
passes the 'multiple' attribute, generating a multiselect box styled with
[multiselect.js](http://loudev.com) 
* `taxonomyselect` generates a `<select>`
field with options pulled from the terms attached to the taxonomy specified in
`meta_key` 
* `nonce` generates a WordPress Nonce field using 'slug' for the ID 
* `hidden` generates a hidden field with a value you can pass in 'params' 
* `post_select` generates a drop down menu of all posts. The array passed to
'params' will be passed to `get_posts` and [you can use all the
keys](http://codex.wordpress.org/Template_Tags/get_posts). 
* `fieldset` to make a set of fields that affect the same meta key ([see  below](#fieldsets))
* `formset` create multiple sets of fields (not necessarily `fieldset`s)([see  below](#formsets))

__Note:__ invalid 'type' values will generate nothing and cause validation errors and invalid values for `$post_type` or `$context` will generate `WP_Error`s. The `date`, `time`, and `datetime` fields also generate a tag field, similar to Wordpress tags, that show the data saved from that field and can be removed to delete that data.

### Fieldsets

Fieldsets are groups of fields that display together and save with similar ( 
though not identical) meta keys. As an example, say you are making an address 
book and want a way to save a phone number and a description to the `phone` key
. You'll need two fields, one text field limited to 10 characters and another 
text_area field limited to 40 characters. The example below will save the 
number and description to meta keys `phone_num` and `phone_desc`.

```php
<?php 
$this->fields = array(
    'phone' => array(
        'title' => 'Phone number',
        'slug' => 'phone',
        'type' => 'fieldset',
        'fields' => array(
            array(
                'type' => 'text',
                'max_length' => 11,
                'label' => 'Number',
                'meta_key' => 'number',
            ),
            array(
                'type' => 'text',
                'max_length' => 40,
                'label' => 'Description',
                'meta_key' => 'desc',
            ),
        ),
        'meta_key' => 'phone',
        'howto' => '',
    ),
); 
?>
```

That form data will be saved to the `phone_number` and `phone_desc` custom-field keys like this:
```php
<?php 
$phone_number = array( '5555555', ); 
$phone_desc = array( 'Description of the phone number', ); 
?>
```

### Formsets

Formset is a feature that allows you to repeat a set of fields which can be repeated up to the maximum value. Each of the fields are saved individually but have a strict naming convention to maintain uniquity. This is discussed further below.

As an example of how to use them, think about this user story: As a content editor, I want to create a page that has a main header and a listing of at least one, but no more than six, articles, each of which has its own headline and description.

To create the listing of articles, we could either create 6 text and text area fields within a single meta box's `fields` array with `meta_key`'s like `header_1`, `description_1`, `header_2`, `description_2`, and so on, or we could make a formset of the fields with parameters to set how many we want so we don't have to create all those fields seperately. That code looks like this:
```php
<?php
    $fields =  array(
    	'main_header' => array(
        	'title' => 'Main Page Header',
            'label' => 'Main Header',
            'slug => 'main_header',
            'type' => 'text',
            'meta_key' => 'main_header',
        ),
        'articles' => array(
        	'title' => 'Article',
            'slug' => 'articles',
            'type' => 'formset',
            'fields' => array (
                array (
                    'type' => 'text',
                    'label' => 'Headline',
                    'meta_key' => 'headline',
                ),
                array (
                    'type' => 'text_area',
                    'label' => 'Description',
                    'params' => array(
                        'rows' => 5,
                        'cols' => 100,
                    ),
                    'meta_key' => 'desc',
                ),
            ),
            'params' => array(
                'init_num_forms' => 1,
                'max_num_forms' => 6,
            ),
            'meta_key' => 'articles',
        ),
    );
?>
```
As you can see, we have two fields in the metabox's `fields` array; one `text` field for the main page's header and another `formset` field for each article's header and description. The formset typed field has a `fields` array with more fields in it. Those fields are repeated according to the parameter's set in the `params` array. The `init_num_forms` is what controls how many of the formsets are displayed by default and `max_num_forms` is how many formsets there are total.

Every field type is supported for this use. That means that you could declare a _formset within a formset_. Think of each of those articles from the example before. If we wanted to have at least 2 but at most 3 links associated with each article, we could declare a formset as one of the fields of the article formset.

The **naming convention** for formset fields are how each of the fields are saved and retrieved uniquely from the database. The `meta_key` is key that the data is associated with. The name of each field of a formset is as such: `<formset meta_key>_<formset iteration integer>_<field meta_key`. So the `meta_key` of each field of the formset in the above example would be `articles_0_headline`, `articles_0_desc`, `articles_1_headline`, `articles_1_desc`, etc until the max number of formsets. This means that _without the `meta_key`, formsets will not work at all_.

**Note**: It is important to note that in the example the fields of the metabox's `fields` array are associated with a string. (In the formset example, there were two fields; one field was called `main_header` and the other was called `articles`.) This is not supported in the formset fields array. As above, each field in the `fields` array in the formset is an unnamed array.

## Capabilities

The least developed feature of this plugin is the capability management
functions defined in capabilities.php. By 'least developed' we mean that it
could be more useful. This class removes the ability to edit the administrator
role and the ability for editors to promote users beyond their current level.
That is, _if_ an editor can modify user permissions and promote users (which
would need to be done separately), they can only do so for non-administrators
and cannot promote anyone to administrator.

If the [Review And Move to Production (RAMP)
plugin](http://crowdfavorite.com/ramp/) is active, this class will also make
that plugin available to editors but not authors. By default RAMP is made
available to any user with the ability to edit posts.

In the future we may see a world where this class can be used by feature plugins
to create meta capabilities for individual post types but we are not there yet.
