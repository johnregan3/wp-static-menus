<?php
/**
 * Cache Method Abstract class
 *
 * @package Mindsize\WPSM
 * @since   0.1.0
 * @author  Mindsize
 */

namespace Mindsize\WPSM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cache
 *
 * @since 1.0.0
 */
abstract class Cache {

	/**
	 * Display name used for Plugin Settings.
	 *
	 * @var string
	 */
	public $display_name = '';

	/**
	 * Required method to set the display name property for the Caching method.
	 *
	 * This is used to display the name in the settings.
	 */
	abstract public function set_display_name();

	/**
	 * Abstracted method for classes to override and store their data.
	 *
	 * @param string $output     Output string.
	 * @param array  $conditions Array of conditions.
	 */
	abstract protected function set_cached_markup( $output );

	/**
	 * Abstracted method for classes to override and get their data.
	 *
	 * @param array $conditions Array of conditions.
	 */
	abstract public function get_cached_markup();

	/**
	 * Abstracted method for classes to override and clear their cache.
	 */
	abstract public function clear_cache( $conditions );

	/**
	 * Check if the string is an enabled theme location.
	 *
	 * @return bool If the input location is registered for caching.
	 */
	public function is_enabled_location() {
		return false;
	}
}

