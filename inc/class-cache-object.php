<?php
/**
 * Object Cache method class
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
 * Class Cache_Object.
 *
 * Based on work by Gabor Javorszky (@javorszky)
 * https://github.com/javorszky/wp-fragment-cache
 *
 * @since 0.1.0
 */
class Cache_Object extends Cache {

	/**
	 * Cache group name.
	 */
	const CACHE_GROUP = 'wms-wpsm-cache';

	/**
	 * Cache group key.
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 * Initialize.
	 *
	 * @param object $menu_args WP Nav Menu args.
	 */
	public function __construct( $menu_args ) {
		$this->set_display_name();

		// Cast as an array for easy handling.
		$this->menu_args = (array) $menu_args;
		$this->key       = $this->get_key();
	}

	/**
	 * Required method to set the display name property for the Caching method.
	 *
	 * This is used to display the name in the settings.
	 */
	public function set_display_name() {
		$this->display_name = __( 'Object Cache', 'ms-wpsm' );
	}

	/**
	 * Set the cache data.
	 *
	 * @param string $html The menu's markup.
	 *
	 * @return bool If the operation was successful.
	 */
	public function set_cached_markup( $html ) {
		return wp_cache_set( $this->key, $html, self::GROUP, Plugin::get_instance()->cache_length );
	}

	/**
	 * Get the cached data.
	 *
	 * @return string The cache.
	 */
	public function get_cached_markup() {
		return wp_cache_get( $this->key, self::GROUP );
	}

	/**
	 * Clear the cache.
	 */
	public function clear_cache( $conditions ) {
		if ( function_exists( 'wp_cache_delete_group' ) ) {
			wp_cache_delete_group( self::GROUP );
		}
	}

	/**
	 * Get the cache key.
	 *
	 * @return string Encoded cache key string.
	 */
	protected function get_key() {

		// Sort array to ensure misordered but otherwise identical conditions aren't saved separately.
		array_multisort( $this->menu_args );
		return md5( wp_json_encode( $this->menu_args ) );
	}
}
