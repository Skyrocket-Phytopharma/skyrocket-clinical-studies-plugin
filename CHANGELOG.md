# Changelog

## 1.0.0 - Initial release

- Initial release

## 2.0.0 - Improvements to shortcode

* **Architecture Refactoring**: Split the monolithic single-file plugin into a clean, object-oriented two-file structure, placing the main plugin bootstrap in `skyrocket-clinical-studies.php` and the core class logic into `includes/class-clinical-studies.php`.


* **GitHub JSON Integration & Multi-Scope Support**: Migrated remote data fetching from the legacy upload URL to direct GitHub raw endpoints, adding support for `neuropad` and `monofilament` scopes.


* **Shortcode Enhancements & Filtering**:
* Added an optional `scope` parameter defaulting to `neuropad`.
* Added an optional `from` parameter to filter out records older than a specified year.
* Added an optional `max-count` parameter to limit the number of displayed records.

* **Flexible Sorting Logic**: Implemented advanced sorting rules via the `sort-by` parameter (`year`, `priority`, and `title`), establishing custom tie-breaking hierarchies for descending years, ascending priorities, and alphabetical titles.

## 2.0.1 - Add in priority field

- Add in priority field
- Display year of publication