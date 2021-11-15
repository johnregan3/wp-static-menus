<?php
/**
 * HTML file Cache method class
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
 * Class Cache_HTML.
 *
 * @since 0.1.0
 */
class Cache_HTML extends Cache {


	/**
	 * Initialize.
	 */
	public function __construct() {
		$this->set_display_name();
	}

	/**
	 * Required method to set the display name property for the Caching method.
	 *
	 * This is used to display the name in the settings.
	 */
	public function set_display_name() {
		$this->display_name = __( 'HTML File', 'ms-wpsm' );
	}

	/**
	 * Abstracted method for classes to override and store their data.
	 *
	 * @param string $output Output string.
	 */
	protected function set_cached_markup( $output ) {
	}

	/**
	 * Abstracted method for classes to override and get their data.
	 */
	public function get_cached_markup() {
	}

	/**
	 * Abstracted method for classes to override and clear their cache.
	 */
	public function clear_cache( $conditions ) {
	}

	/**
	 * Check if the string is an enabled theme location.
	 *
	 * @return bool If the input location is registered for caching.
	 */
	public function is_enabled_location() {
		return false;
	}
}
