<?php
/**
 * Plugin Name: Mindsize - WP Static Menus
 * Description: Improve page load times by serving static navigation menus.
 * Version:     0.1.0
 * Author:      Mindsize
 * Author URI:  http://mindsize.me/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-static-menus
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   Mindsize/WPStaticMenus
 * @author    Mindsize <info@mindsize.me>
 * @copyright Copyright (c) 2021, Mindsize, LLC.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

use Mindsize\WPStaticMenus\Plugin;

$ms_autoloader = dirname( __FILE__ ) . '/vendor/autoload.php';

if ( file_exists( $ms_autoloader ) ) {
	require_once $ms_autoloader;
}

/**
 * Get an instance of the Menu Cache plugin.
 *
 * @return Plugin
 */
function ms_wp_static_menus() : Plugin {
	static $instance;

	if ( empty( $instance ) ) {
		if ( class_exists( 'Mindsize\WPStaticMenus\Plugin' ) ) {
			$instance = new Plugin();
		}
	}

	return $instance;
}

// Load the plugin.
add_action(
	'plugins_loaded',
	function() {
		try {
			ms_wp_static_menus()->init();
		} catch ( \TypeError $e ) {

			// Class Plugin is not instantiated, most likely because the autoloader hasn't been set up.
			add_action(
				'admin_notices',
				function() {
					if ( 'plugins' !== get_current_screen()->id ) {
						return;
					}
					?>
					<div class="error notice">
						<p><?php echo wp_kses_post( __( 'WP Static Menus plugin isn\'t working. Did you run <code>composer install</code> after it was downloaded?', 'wp-static-menus' ) ); ?></p>
					</div>
					<?php
				}
			);
		}
	}
);
