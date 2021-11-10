<?php
/**
 * Plugin Name: WP Static Menus
 * Description: Improve page load times by serving static nav menus.
 * Version:     0.1.0
 * Author:      Mindsize
 * Author URI:  http://mindsize.me/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ms-wpsm
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
 * @package   Mindsize/WPSM
 * @author    Mindsize
 * @copyright Copyright (c) 2021, Mindsize, LLC.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

/**
 * Autoload Plugin classes.
 *
 * Loads classes in the inc/ directory whose
 * file names are prefixed with "class-" and also contain the
 * "Mindsize\WPSM" namespace.
 *
 * @since 0.1.0
 *
 * @param string $class A Class name.
 */
function ms_wpsm_autoload( $class ) {
	$class = strtolower( $class );
	if ( false === strpos( $class, 'mindsize\wpsm' ) ) {
		return;
	}

	$class = str_replace( 'mindsize\\wpsm\\', '', $class );
	$class = str_replace( '\\', '/', $class );
	$class = str_replace( '_', '-', $class );
	$path  = get_stylesheet_directory() . '/inc/class-' . $class . '.php';

	if ( file_exists( $path ) && is_readable( $path ) ) {
		include $path;
	}
}

spl_autoload_register( 'ms_wpsm_autoload' );

/**
 * Kick things off.
 *
 * @since 0.1.0
 *
 * @action plugins_loaded
 */
function ms_wpsm_load() {
	Mindsize\WPSM\Plugin::get_instance();
}
add_action( 'plugins_loaded', 'ms_wpsm_load' );
