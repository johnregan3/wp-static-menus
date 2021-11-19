# WP Static Menus Plugin


**Author:** [Mindsize](https://mindsize.me)<br>
**Version**: 0.1.0

WordPress plugin for caching menus and serving them statically. Based upon [WP Static Menus by Liquid Web](https://github.com/liquidweb/wp-static-menus).

Important note: this plugin caches menus by Menu Location registered in the theme, not individual user-created Nav Menus.

## Usage

After installing and activating the plugin, navigate to Tools > WP Static Menus in the WP Admin Menu. Here, select Menu Location(s) to be cached.

From here, the plugin can get to work with no other required changes to the configuration.

### Configuration Options

On this page you can also select how the cache is to be stored.  The defaults are Object Cache, HTML File(s), or Transients.

Along with selecting the cache storage method, you can choose the length to hold the cache.

Finally, you can also select to whom static menus should _not_ be served (e.g., logged-in users, Adminstrators & Editors).

The Tools section on the Settings Page includes a quick checkbox to disable all caching, as well as a button to immediately empty all caches.

## Filters

## Adding Custom Cache Storage Methods
