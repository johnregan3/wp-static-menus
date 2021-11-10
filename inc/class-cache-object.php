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
	 *
	 * @var string
	 */
	protected $group = 'wms-wpsm-cache';

	/**
	 * Cache group key.
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 * Default expiration time, in seconds.
	 *
	 * @var int
	 */
	protected $default_expires = MONTH_IN_SECONDS;

	/**
	 * Set the cache data.
	 *
	 * @param string $output The output.
	 * @param array  $conditions The conditions array.
	 *
	 * @return bool If the operation was successful.
	 */
	protected function set_cached_markup( $output, $conditions ) {
		$key = $this->get_key( $conditions );

		$expires = isset( $conditions['expires'] ) && ! empty( $conditions['expires'] ) ? absint( $conditions['expires'] ) : $this->default_expires;

		return wp_cache_set( $key, $output, $this->group, $expires );
	}

	/**
	 * Get the cached data.
	 *
	 * @param array $conditions Array of Conditions.
	 *
	 * @return string The cache.
	 */
	protected function get_cached_markup( $conditions ) {
		$key = $this->get_key( $conditions );
		return wp_cache_get( $key, $this->group );
	}

	/**
	 * Clear the cache.
	 */
	public function clear_cache() {
		if ( function_exists( 'wp_cache_delete_group' ) ) {
			wp_cache_delete_group( $this->group );
		}
	}

	/**
	 * Get the cache key.
	 *
	 * @param array $conditions Array of conditions.
	 *
	 * @return string Encoded cache key string.
	 */
	protected function get_key( $conditions ) {
		// Sort array to ensure misordered but otherwise identical conditions aren't saved separately.
		array_multisort( $conditions );
		return md5( wp_json_encode( $conditions ) );
	}
}
