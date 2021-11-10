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
	 * Abstracted method for classes to override and store their data.
	 *
	 * @param string $output     Output string.
	 * @param array  $conditions Array of conditions.
	 */
	abstract protected function set_cached_markup( $output, $conditions );

	/**
	 * Abstracted method for classes to override and get their data.
	 *
	 * @param array $conditions Array of conditions.
	 */
	abstract protected function get_cached_markup( $conditions );

	/**
	 * Abstracted method for classes to override and clear their cache.
	 */
	abstract public function clear_cache();

}

