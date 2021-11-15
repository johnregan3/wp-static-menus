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
	 * @param array $menu_args Menu Location args.
	 */
	public function __construct( $menu_args = [] ) {
		$this->set_display_name();
		$this->conditions = $menu_args;
		$this->label      = $this->get_label();
		$this->expires    = Plugin::get_instance()->cache_length;
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
	 * @param string $output The output.
	 *
	 * @return bool True if the value was set, false otherwise.
	 */
	protected function set_cached_markup( $output ) {
		return set_transient( $this->label, $output, Plugin::get_instance()->cache_length );
	}

	/**
	 * Get the cached data.
	 *
	 * @return string The cache.
	 */
	public function get_cached_markup() {
		$output = get_transient( $this->label );

		if ( false === $output ) {

			// Refresh the markup.
			ob_start();
			wp_nav_menu( $this->menu_args );
			$output = ob_get_clean();

			$this->set_cached_markup( $output );
		}

		return $output;
	}

	/**
	 * Clear the cache.
	 *
	 * @todo find good method for deleting transients from both DB and Object Cache.
	 */
	public function clear_cache( $conditions = [] ) {
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
