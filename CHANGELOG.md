# Change Log

All notable changes to this project will be documented in this file.
We follow the [Semantic Versioning 2.0.0](http://semver.org/) format.


## 2.2.4 - 2016-01-05

### Changed
- Fix build failure by pinning php-coveralls version at 1.0.0

## 2.2.3 - 2015-06-23

### Changed
- Post type `menu-position` changed to 100 to move them down to the bottom

## 2.2.2 - 2015-06-30

### Changed
- `display_tags()` now displays readable timestamps

### Fixed
- `placeholder` attribute for select fields is now checked so a Fatal Error is
    is not thrown.

## 2.2.1 - 2015-06-25

### Changed
- `time`/`datetime` fields saved under a taxonomy now save the timezone in the
    term description

## 2.2.0 - 2015-06-23

### Changed
- `date`/`time`/`datetime` fields can be repeated but only single date fields
    are saved into a taxonomy (plans to remove date types from a taxonomy are
    being considered)

## 2.1.0 - 2015-06-22

### Changed
- Uploading a file through the custom file upload field creates an attachment

## 2.0.2 - 2015-06-09

### Changed
- Fix bug that caused empty links to interrupt saving of fields that followed

## 2.0.1 - 2015-06-02

### Changed
- Fix bug that caused numeric indexes to be off

## 2.0.0 - 2015-05-04

### Changed
- Serialize all custom meta box meta data
- Remove `formset` type
- Added `'repeated'` parameter for repeating a field of any type
- Fixed some in-code documentation and README
- Added backwards compatibility that helps phase out old data (temporary)
- Changed most ineffective WP_Error's to call wp_die() instead

## 1.5.6 - 2015-05-06

### Changed
- Fix menu position parameter for custom post types

## 1.5.5 - 2015-04-21

### Changed
- Added a checkbox to Post to turn featured image on/off within a the blog post.


## 1.5.4 - 2015-04-15

### Changed
- Names of our custom post types in the sidebar to be plural, matching the
  standard for default WordPress post types.


## 1.5.3 - 2015-04-01

### Changed
- `url` field output (in the meta box) from `type="url"` to `type="text"`,
  so that relative URLs will not fail native browser validation.


## 1.5.2 - 2015-03-30

### Changed
- Version number due to confusion about misplaced tags.

**This version provides no functional changes over 1.5.1.**
It is only being included here so it doesn't appear that we skipped a version.


## 1.5.1 - 2015-03-26

### Added
- labeling to link and "how to" to formsets

### Changed
- Refactored some code

### Fixed
- A bug that ignored validation for some field types under certain circumstances


## 1.5.0 - 2015-03-24

### Changed
- Where and how date/time/datetime is saved
- Refactored tests

### Removed
- Obsolete tests


## 1.4.0 - 2015-03-20

### Added
- Meta-data saving of `wysiwyg`, `date`, `time`, `datetime` fields

### Fixed
- Readability of the date/time fields' taxonomy tags
- Format of how each field is saved


## 1.3.0 - 2015-01-15

### Added
- Arbitrary formset functionality

### Fixed
- Lots of issues with saving and removing fields
- Some visual issues
