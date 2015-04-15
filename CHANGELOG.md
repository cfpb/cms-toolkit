# Change Log

All notable changes to this project will be documented in this file.
We follow the [Semantic Versioning 2.0.0](http://semver.org/) format.


## 1.5.3 - 2015-04-01

### Changed
- `url` field output (in the meta box) from `tpe="url"` to `type="text"`,
  so that relative URLs will not fail native browser validation.


## 1.5.2 - 2015-03-30

### Fixed
- Version number correction


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
- Meta-data saving of `date`, `time`, `datetime` fields

### Fixed
- Readability of those field's taxonomy tags
- Format of how each field is saved


## 1.3.0 - 2015-01-15

### Added
- Arbitrary formset functionality

### Fixed
- Lots of issues with saving and removing fields
- Some visual issues
