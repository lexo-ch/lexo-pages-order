# LEXO Pages Order
Order the subpages.

---
## Versioning
Release tags are created with Semantic versioning in mind. Commit messages were following convention of [Conventional Commits](https://www.conventionalcommits.org/).

---
## Compatibility
- WordPress version `>=4.7`. Tested and works fine up to `6.8`.
- PHP version `>=7.4.1`. Tested and works fine up to `8.3.11`.

---
## Installation
1. Go to the [latest release](https://github.com/lexo-ch/lexo-pages-order/releases/latest/).
2. Under Assets, click on the link named `Version x.y.z`. It's a compiled build.
3. Extract zip file and copy the folder into your `wp-content/plugins` folder and activate LEXO Pages Order in plugins admin page. Alternatively, you can use downloaded zip file to install it directly from your plugin admin page.

---
## Filters
#### - `po/admin_localized_script`
*Parameters*
`apply_filters('po/admin_localized_script', $args);`
- $args (array) The array which will be used for localizing `poAdminLocalized` variable in the admin.

#### - `po/enqueue/admin-po.js`
*Parameters*
`apply_filters('po/enqueue/admin-po.js', $args);`
- $args (bool) Printing of the file `admin-po.js` (script id is `po/admin-po.js-js`). It also affects printing of the localized `poAdminLocalized` variable.

#### - `po/enqueue/admin-po.css`
*Parameters*
`apply_filters('po/enqueue/admin-po.css', $args);`
- $args (bool) Printing of the file `admin-po.css` (stylesheet id is `po/admin-po.css-css`).

#### - `po//message/column_excluded`
*Parameters*
`apply_filters('po//message/column_excluded', $args);`
- $args (string) Content of the message in the pages list on dashboard when page is excluded.

#### - `po//message/column_not_excluded`
*Parameters*
`apply_filters('po//message/column_not_excluded', $args);`
- $args (string) Content of the message in the pages list on dashboard when page is not excluded.

---
## Actions
#### - `po/init`
- Fires on LEXO Pages Order init.

#### - `po/localize/admin-po.js`
- Fires right before LEXO Pages Order admin script has been enqueued.

---
## Changelog
Changelog can be seen on [latest release](https://github.com/lexo-ch/lexo-pages-order/releases/latest/).
