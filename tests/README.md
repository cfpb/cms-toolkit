# Unit Tests for CMS Toolkit

## Table of Contents

1. [PHPunit settings](#phpunit-settings)
2. [CI integration](#ci-integration)
3. [Annotations](#annotations)
3. [Bootstrap.php](#bootstrap)
4. [Mocking](#mocking)
4. [Post types](#post-types)
5. [Meta box validation](#meta-box-validation)
6. [Meta box save](#meta-box-save)
6. [Meta box generation](#meta-box-generation)
7. [Meta box defaults](#meta-box-defaults)
8. [Dependency injection](#dependency-injection)

## PHPunit Settings

The phpunit test runner reads out settings from the `phpunit.xml` file. Settings here are used to control what happens when you run `phpunit` from your command line. For CMS-Toolkit we use it to ignore any tests prefixed with `@wip`, tell `phpunit` where the tests directory is, and whitelist certain directories for code execution.

We execute tests out of files this directory that being with `test-` and end with `.php`. We also ignore from the project any files in the `vendor/`directory and this one, this allows us to run accurate coverage reports.

## CI Integration

This project is ready for integration with Travis-CI for executing automated test on every push to a GitHub remote. Anyone with a fork can enable Travis from travis-ci.org as well as the master project. It is also ready for Coveralls integration with code coverage information being sent after each Travis Build.

## Annotations

Annotations can be used in PHP to direct behavior, organize tests, or describe the test suite. The `@group` is used most frequently in this application. [Other annotations](http://phpunit.de/manual/4.1/en/appendixes.annotations.html) can be used to allow finer management of tests out in the test runner including `@covers`, that enhances code coverage reports by showing which methods cover more or less than expected, and `@depends`, which allows you to declare "explicit dependencies between test methods" so that test B will only run if test A succeeds.

## Bootstrap

The `bootstrap.php` sets up the environment for testing. All files being tested should be required in this file (use `require_once`) along with any globals or contstants—currently there are none.

## Mocking

The tests here verify only the units of code written for the plugin. It does not actually execute any code from WordPress core and for that reason they can be run without a database or a working installation of WordPress. Rather than execute those functions directly we choose to mock the WordPress API and check only that we call them with the correct parameters. This is useful in the event that some part of the plugin stops working to determine if it's because of something that changed in the plugin or the WordPress code base.

Mocking WordPress utilizes a library called WP_Mock which uses Mockery to replace those functions and classes with our own. @gboone has written [a 
blog post about using WP_Mock to test WordPress plugins.](http://greg.harmsboone.org/blog/2014/01/01/writing-unit-tests-for-wordpress)

## Groups

PHPunit's grouping feature allows you to assemble collections of tests by annotating the docblock with `@group <group-name>`. Listed below are groups of tests that can be used during debugging and troubleshooting. Not all tests are grouped at this point and this is not an exhaustive list of all available groups.

### Works in progress

Works in progress, tests that you are still developing but maybe don't pass reliably, can be tagged with `@wip` and are automatically _excluded_ from the test runner by default. Override with the `--group=wip` flag.

### Post Types

A group of tests targeting all functions related to post type handling is available by running `phpunit --group=post_types`. This group covers post type registration and `maybe_flush_rewrite_rules`.

### Taxonomies

A group of tests targeting all functions related to taxonomy handling is available by running `phpunit --group=taxonomies`. This group covers taxonomy registration and `remove_post_terms`.

### Meta Boxes

The meta box generation piece of this plugin is complicated enough that it is split into three classes: generation and validation (`MetaBox\Models`), view preparation (`MetaBox\View`), and display (`MetaBox\HTML`).

#### Meta Box Validation

A group of tests targeting all functions to validation (but not necessarily saving) of data is available by running `phpunit --group=validation`. This group covers methods in `MetaBox\Models\` including `validate`, `validate_taxonomyselect`, `validate_link` and others.

#### Meta Box Save

A group of tests covering the `save` and `validate_and_save` methods in `MetaBox\Models` is available by runnint `phpunit --group=save`. These verify that meta boxes, when properly hooked into `save_post` will call the WordPress methods that save custom-fields to posts.

#### Meta Box Generation

A group of tests covering the `generate` and other methods related to creating a meta box can be run with `phpunit --group=generate`. These verify that `add_meta_box` is called properly.

#### Meta Box Defaults

Before we generate any HTML we inspect the fields passed by the user and fill in any blanks. In this step we also figure out whether the form is "bound" or not and pass the bound values if they exist. The functions that do all this are located in `MetaBox\View` and can be tested with `phpunit --group=defaults`.

### Dependency injection

Each class has methods available for replacing dependent classes with either a mock or a substitute. This is handy for testing functions in isolation but also if you decide you'd rather roll your own validators or HTML templates rather than use ours. You can verify dependency injection works properly with `phpunit --group=dependency_injection`.

### Complete list of groups

A complete list of groups can be found by running `phpunit --list-groups`. As of version 1.2 these are the groups available:

```
Available test group(s):
 - __nogroup__
 - date
 - defaults
 - dependency_injection
 - doing_it_wrong
 - empty_data
 - generate
 - incomplete
 - isolated
 - link_validate
 - maybe_delete
 - meta_boxes
 - negative
 - number
 - process_defaults
 - remove_term
 - returns_null
 - save
 - select
 - set_view
 - stable
 - strings
 - taxonomy_save
 - taxonomy_select
 - taxonomyselect
 - text
 - textarea
 - urls
 - user_input
 - validate_link
 - validation
 - wip
 - wp_error
```