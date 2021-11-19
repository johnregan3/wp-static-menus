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
	 * The cache path.
	 *
	 * @var string
	 */
	protected $cache_path = '';

	/**
	 * Initialize.
	 *
	 * @param object $menu_args WP Nav Menu args.
	 */
	public function __construct( $menu_args ) {
		$this->set_display_name();
		$this->plugin = Plugin::get_instance();

		// Cast as an array for easy handling.
		$this->menu_args = (array) $menu_args;

		$this->cache_path = $this->get_cache_path();
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
	 * @param string $html wp_nav_menu markup.
	 */
	public function set_cached_markup( $html ) {
		$bytes  = 0;
		$closed = false;

		$file = $this->get_cache_file_path();

		$this->ensure_directory_exists( dirname( $file ) );

		// phpcs:disable WordPress.WP.AlternativeFunctions
		$cache_file = fopen( $file, 'w' );
		if ( false !== $cache_file ) {
			$bytes  = fwrite( $cache_file, $output );
			$closed = fclose( $cache_file );
		}
		// phpcs:enable

		return ( 0 < $bytes ) && ( true === $closed );
	}

	/**
	 * Abstracted method for classes to override and get their data.
	 */
	public function get_cached_markup() {
		$file = $this->file_path;

		if ( file_exists( $file ) ) {
			$data = wp_remote_get( $file );
			if ( ! is_wp_error( $data ) ) {
				return $data;
			}
		}
		return false;
	}

	/**
	 * Abstracted method for classes to override and clear their cache.
	 *
	 * @param string $append Optional. String to append to the cache path.
	 */
	public function clear_cache( $append = null ) {
		$path = $this->get_cache_path( $append );
		$this->ensure_directory_exists( $path );
		$this->delete_directory_contents( $path );
	}

	/**
	 * Fetch the path of the cache file.
	 *
	 * @return string The file path, else empty string.
	 */
	public function get_cache_file_path() {
		/**
		 * HTML file name will be generated based on passed conditions. Allow for customizing these conditions further.
		 * This will create a new args array which will create a separate version of the cached fragment.
		 *
		 * @todo add this to other cache classes.
		 */
		$file_conditions = apply_filters( 'ms_wpsm_html_cache_file_conditions', $this->menu_args, $this );

		/**
		 * Sort the args in the array so that if two arrays have identical values but were just out of order
		 * we don't need to store separate caches. This reduces the total size of the cache dir.
		 */
		array_multisort( $file_conditions );

		/**
		 * Filter the file base.
		 *
		 * @param string  The cache path.
		 * @param array   The menu args.
		 * @param stdClass This.
		 *
		 * @return string The file base.
		 */
		$file_base = apply_filters( 'ms_wpsm_html_cache_file_base', trailingslashit( $this->cache_path ), $this->menu_args, $this );

		/**
		 * Filter the file name.
		 *
		 * @param array  The file conditions.
		 * @param array   The menu args.
		 * @param stdClass This.
		 *
		 * @return string The file name.
		 */
		$file_name = apply_filters( 'ms_wpsm_html_cache_file_name', md5( wp_json_encode( $file_conditions ) ), $this->menu_args, $this );

		/**
		 * Filter the file name.
		 *
		 * @param array  The file conditions.
		 * @param array   The menu args.
		 * @param stdClass This.
		 *
		 * @return string The file name.
		 */
		$file_path = apply_filters(
			'ms_wpsm_html_cache_file_path',
			trailingslashit( $file_base ) . $file_name . '.html',
			$file_base,
			$file_name,
			$file_conditions,
			$this
		);

		if ( empty( $file_path ) || ! is_string( $file_path ) ) {
			return '';
		}

		return $file_path;
	}

	/**
	 * Get the name of the directory for the cache.
	 *
	 * This string will be sanitized by sanitize_title.
	 *
	 * @todo sort this out, along with get_cache_file_path.
	 *
	 * @return string
	 */
	public function get_cache_dir() {
		return sanitize_title( apply_filters( 'ms_wpsm_html_cache_dir', 'ADD_SLUG' ) );
	}

	/**
	 * Get the path to the cache directory, plus any potentially added URI.
	 *
	 * @todo sort this out, along with get_cache_file_path.
	 *
	 * @param string $append Optional. String to append to the cache path.
	 *
	 * @return string
	 */
	public function get_cache_path( $append = null ) {

		$cache_path = WP_CONTENT_DIR . '/cache/';
		$setting = $this->plugin->settings->get_value( 'theme_locations' );
		/**
		 * Filter the cache file path.
		 *
		 * @param string The default path.
		 *
		 * @return string The filtered file path.
		 */
		$path = trailingslashit( apply_filters( 'ms_wpsm_html_cache_path', WP_CONTENT_DIR . '/cache/' . $this->get_cache_dir() ) );

		// Add any extra path that was passed.
		if ( ! empty( $append ) ) {
			$path .= $append;
		}

		return trailingslashit( $path );
	}

	/**
	 * Ensure that the HTML cache directory exists. If not, create it.
	 *
	 * @param string $path Optional. The cache path.
	 */
	protected function ensure_directory_exists( $path = null ) {
		$path = ! empty( $path ) ? $path : $this->get_cache_path();

		if ( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
		}
	}
}
