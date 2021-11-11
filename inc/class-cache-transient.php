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
	 * Cache group name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Default expiration time, in seconds.
	 *
	 * @var int
	 */
	protected $expires = MONTH_IN_SECONDS;

	/**
	 * Initialize.
	 *
	 * @todo filter the cache expiry.  Maybe move this to class Cache.
	 */
	public function __construct() {
		$this->expires = Plugin::get_instance()->cache_expiry;
	}

	/**
	 * Set the cache data.
	 *
	 * @param string $output The output.
	 * @param array  $conditions The conditions array.
	 *
	 * @return bool True if the value was set, false otherwise.
	 */
	protected function set_cached_markup( $output, $conditions ) {
		$name = $this->get_name( $conditions );

		$expires = isset( $conditions['expires'] ) && ! empty( $conditions['expires'] ) ? absint( $conditions['expires'] ) : $this->expires;

		return set_transient( $name, $output, $expires );
	}

	/**
	 * Get the cached data.
	 *
	 * @param array $conditions Array of Conditions.
	 *
	 * @return string The cache.
	 */
	protected function get_cached_markup( $conditions ) {
		$name   = $this->get_name( $conditions );
		$output = get_transient( $name );
		if ( false === $output ) {

			// Refresh the markup.
			$output = '';

			$this->set_cached_markup( $output );
		}

		return $output;
	}

	/**
	 * Clear the cache.
	 *
	 * @todo find good method for deleting transients from both DB and Object Cache.
	 *
	 * @param array $conditions The conditions array.
	 */
	public function clear_cache( $conditions ) {
		if ( function_exists( 'delete_transient' ) ) {
			return;
		}
		$name = $this->get_name( $conditions );
		delete_transient( $name );
	}

	/**
	 * Get the transient name.
	 *
	 * @param array $conditions Array of conditions.
	 *
	 * @return string Encoded transient name string.
	 */
	protected function get_name( $conditions ) {
		// Sort array to ensure misordered but otherwise identical conditions aren't saved separately.
		array_multisort( $conditions );

		$name = self::LABEL . md5( wp_json_encode( $conditions ) );

		// Trim to max transient name length.
		return substr( $name, 0, 172 );

	}

}
