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

## 2.0.2 - Correct GitHub reference and rename plugin

- Correct GitHub reference (Skyrocket)
- Rename plugin (Skyrocket)

## 2.0.3 - Remove year tag

- Remove year, already included in publication

## 2.0.4 - Mutiple small enhancements

- **Conditional Style Enqueueing**: Deferred wp_enqueue_style('clinical-studies-style') from the wp_enqueue_scripts hook into render_shortcode() so inline styles are only loaded on pages that actually render the shortcode.

- **Priority Filtering**: Added support for the priority shortcode attribute to filter records where priority is less than or equal to the specified value.

- **Shortcode & Style Defaults**: Enabled default style registration/enqueueing.

- **Fetching & Caching Hardening**: Removed custom browser user-agent spoofing and disabled SSL verification overrides in wp_remote_get(), and prevented empty decoded JSON arrays from being cached.

- **Author Formatting & Cleanup**: Added string type checks when pairing author surnames with initials in format_authors(), removed temporary debug error_log() calls, and deduplicated the priority sort branch.

## 2.0.5 - Allow display and bulleted or numbered list

- Allow display and bulleted or numbered list