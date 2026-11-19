# Clinical Studies Viewer

Fetches clinical studies JSON data from GitHub and renders formatted study lists on WordPress pages via the `[skyrocket_clinical_studies]` shortcode. Supports multi-dataset scopes, filtering by year and priority, custom sorting, record limits, 12-hour transient caching, and on-demand inline styling.

## Installation

1. Upload the `skyrocket-clinical-studies` directory to `/wp-content/plugins/` (containing `skyrocket-clinical-studies.php` and `includes/class-clinical-studies.php`).
2. Activate **Clinical Studies Viewer** in the WordPress **Plugins** screen.
3. Add `[skyrocket_clinical_studies]` to any post, page, or template.

## Shortcode Usage: `[skyrocket_clinical_studies]`

Insert `[skyrocket_clinical_studies]` with no attributes to display all `neuropad` studies sorted by priority.

```text
[skyrocket_clinical_studies]
```

### Supported Attributes

| Attribute | Options / Values | Default | Behaviour |
| :--- | :--- | :--- | :--- |
| `scope` | `neuropad`, `monofilament` | `neuropad` | Selects which GitHub JSON dataset to load. Any unrecognized value falls back to `neuropad`. |
| `from` | Four-digit year (e.g. `2014`) | *(empty)* | Excludes records whose `year` is earlier than the specified value (`year >= from`). Left blank to show all years. |
| `priority` | Integer (e.g. `1`, `2`) | *(empty)* | Shows only records where `priority <= priority`. Records without a `priority` field default to `999`. Left blank to show all. |
| `max-count` | Positive integer (e.g. `5`) | *(empty)* | Limits the number of displayed studies after filtering and sorting. Left blank for no limit. |
| `sort-by` | `priority`, `year`, `title` | `priority` | Controls sort order and tie-breakers (see **Sorting Rules** below). |

### Sorting Rules

* **`priority` (default)**: Priority ascending (`1` before `2`, missing priority treated as `999`), then `year` descending, then `title` ascending (case-insensitive).
* **`year`**: `year` descending (newest first), then `priority` ascending, then `title` ascending (case-insensitive).
* **`title`**: `title` ascending (alphabetical, case-insensitive).

### Practical Examples

**Monofilament studies from 2015 onwards:**
```text
[skyrocket_clinical_studies scope="monofilament" from="2015"]
```

**Top-priority Neuropad studies (priority 1 or 2), capped at 5 items:**
```text
[skyrocket_clinical_studies scope="neuropad" priority="2" max-count="5"]
```

**Most recent Neuropad studies sorted chronologically:**
```text
[skyrocket_clinical_studies scope="neuropad" sort-by="year" max-count="10"]
```

**Alphabetical listing of all Monofilament studies:**
```text
[skyrocket_clinical_studies scope="monofilament" sort-by="title"]
```

## Caching & Flushing Local Cache

Fetched JSON payloads are cached in WordPress transients for **12 hours** under:
* `skyrocket_clinical_studies_neuropad`
* `skyrocket_clinical_studies_monofilament`

Empty JSON arrays are not cached. To pull fresh JSON changes from GitHub immediately:

* **WP-CLI:**
  ```bash
  wp transient delete skyrocket_clinical_studies_neuropad skyrocket_clinical_studies_monofilament
  ```
* **PHP:**
  ```php
  delete_transient('skyrocket_clinical_studies_neuropad');
  delete_transient('skyrocket_clinical_studies_monofilament');
  ```

## Styling

The plugin registers a `clinical-studies-style` inline stylesheet and only enqueues it on pages where `[skyrocket_clinical_studies]` actually renders. You can override any of these classes in your active theme or child theme stylesheet:

* `.clinical-studies-container`
* `.clinical-studies-list`
* `.clinical-study-item`
* `.clinical-study-title`
* `.clinical-study-authors`
* `.clinical-study-meta`
* `.clinical-study-tag`
* `.clinical-study-publication`
* `.clinical-studies-error`
* `.clinical-studies-empty`


## Building a release

The plugin uses [Skyrocket Plugin Updater](https://github.com/lavamitts/skyrocket-plugin-updater) as a Composer dependency.

The updater dependency also provides the distribution build script, which prepares the plugin files for release.

After installing the Composer dependencies, run the following command from the plugin root directory:

```bash
./vendor/lavamitts/skyrocket-plugin-updater/tools/build-distribution.sh
```

This builds the release candidate, preparing a distributable version of the plugin without requiring the development dependency structure to be included in the release.

The resulting distribution package is intended for installation through the standard WordPress plugin installation process.

## Licence

See the repository for licensing information.