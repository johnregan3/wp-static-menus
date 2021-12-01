# WP Static Menus Plugin


**Author:** [Mindsize](https://mindsize.me)<br>
**Version**: 0.1.0

WordPress plugin for caching menus and serving them statically. Based upon [WP Static Menus by LiquidWeb](https://github.com/liquidweb/wp-static-menus).  It requires the [WP Fragment Cache plugin](https://github.com/Mindsize/wp-fragment-cache) to run.

This plugin caches menus by menu Theme Location (the ones registered in the theme), not the individual user-created nav venus.

It also uses HTML files located in `wp-content/cache/wp-static-menus/` to store each menu's markup.

## Usage

Upload the plugin, then from within its directory run `composer install`.

After activating the plugin, navigate to `Tools > WP Static Menus` in the WP Admin Menu. Then select the menu location(s) to be cached.

From here, the plugin can get to work with no other required changes to the configuration.

### Plugin Tools

On this same settings page you can also select to whom static menus should _not_ be served (e.g., logged-in users, Adminstrators & Editors).  This is useful if your menus appear differently for different users (like containing a user profile link, for example).  Also, disabling cached menus for Admins allows them to see edits immediately.

The Tools section includes a checkbox to quickly disable all caching, as well as a button to empty all caches.

Finally, the plugin can display a handy button in the WP Admin Bar for flushing the cache files.

## Development

### Unit Testing

For the sake of simplicity, this plugin does not use the full WP Unit Testing suite of tools. When `composer install` is initially run, a standalone version of PHPUnit is added to the plugin.

To run the unit tests, simply run `vendor/bin/phpunit` from inside the plugin's directory and PHPUnit will go to work.
