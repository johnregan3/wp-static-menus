<?php
/**
 * Transient Cache method class
 *
 * Note that transients have a max expiration time, but can be emptied before that time
 * due to external object caches or DB upgrades.
 *
 * @todo Maybe only use Menu name and/or location for the transient name for easier deletion.
 * @todo Maybe a "clear all transients" method.
 *
 * @link https://core.trac.wordpress.org/ticket/20316#comment:47
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
 * Class Cache_Transient.
 *
 * @since 0.1.0
 */
class Cache_Transient extends Cache {

	/**
	 * Cache group name.
	 */
	const LABEL = 'ms-wpsm';

	/**
	 * Transient name.
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Menu Args passed in from the Menu Location.
	 *
	 * @var array
	 */
	protected $menu_args = [];

	/**
	 * Initialize.
	 *
	 * @param object $menu_args WP Nav Menu args.
	 */
	public function __construct( $menu_args ) {
		$this->set_display_name();

		// Cast as an array for easy handling.
		$this->menu_args = (array) $menu_args;
		$this->label     = $this->get_label();
	}

	/**
	 * Required method to set the display name property for the Caching method.
	 *
	 * This is used to display the name in the settings.
	 */
	public function set_display_name() {
		$this->display_name = __( 'Transient', 'ms-wpsm' );
	}

	/**
	 * Set the cache data.
	 *
	 * @param string $html The data to be stored.
	 *
	 * @return bool True if the value was set, false otherwise.
	 */
	public function set_cached_markup( $html ) {
		return set_transient( $this->label, $html, Plugin::get_instance()->cache_length );
	}

	/**
	 * Get the cached data.
	 *
	 * @return string The cache.
	 */
	public function get_cached_markup() {
		return get_transient( $this->label );
	}

	/**
	 * Clear the cache.
	 *
	 * @todo find good method for deleting transients from both DB and Object Cache.
	 */
	public function clear_cache() {
		if ( function_exists( 'delete_transient' ) ) {
			return;
		}
		delete_transient( $this->label );
	}

	/**
	 * Get the transient label.
	 *
	 * @return string Encoded transient name string.
	 */
	protected function get_label() {
		// Sort array to ensure misordered but otherwise identical conditions aren't saved separately.
		array_multisort( $this->menu_args );

		$label = self::LABEL . md5( wp_json_encode( $this->menu_args ) );

		// Trim to max transient name length.
		return substr( $label, 0, 172 );
	}

}
