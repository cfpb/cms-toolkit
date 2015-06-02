# Change Log

All notable changes to this project will be documented in this file.
We follow the [Semantic Versioning 2.0.0](http://semver.org/) format.


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
