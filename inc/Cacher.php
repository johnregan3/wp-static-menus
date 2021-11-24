<?php
/**
 * Cache handler
 *
 * Requires WP_Fragment_Cache to operate.
 *
 * Comments only appear in markup if WP_DEBUG is true.
 *
 * @package Mindsize\WPStaticMenus
 * @since   0.1.0
 * @author  Mindsize <info@mindsize.me>
 */

namespace Mindsize\WPStaticMenus;

/**
 * Class Cacher
 *
 * @since 0.1.0
 */
class Cacher extends \WP_Fragment_HTML_Cache {

	/**
	 * The slug of the fragment cache prefixes all hooks.
	 *
	 * @var string
	 */
	protected $slug = 'wp-static-menus';

	/**
	 * Fetch the path of the cache file.
	 *
	 * @param array $conditions Array of conditions.
	 *
	 * @return string The file path, else empty string.
	 */
	public function get_cache_file_path( $conditions ) {
		if ( is_array( $conditions ) ) {
			$conditions = array_shift( $conditions );
		}

		// file name should be menu location.
		$path      = $this->get_cache_path();
		$file_name = 'menu';
		if ( ! empty( $conditions->theme_location ) ) {
			$file_name = $conditions->theme_location;
		}

		return trailingslashit( $path ) . $file_name . '.html';
	}

	public function get_cache_path( $append = null ) {
		return trailingslashit( WP_CONTENT_DIR . '/cache/' . $this->slug );
	}

	public function get_cache_start_comment() {
		return __( 'Start WP Menu Cache', 'wp-static-menus' );
	}

	public function get_cache_close_comment() {
		return __( 'End WP Menu Cache', 'wp-static-menus' );
	}
}
