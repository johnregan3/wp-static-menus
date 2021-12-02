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
	 * The slug of this cache class.
	 *
	 * @var string
	 */
	protected $slug = 'wp-static-menus';

	/**
	 * Get the path to the cache file.
	 *
	 * Defaults to 'wp-content/cache/wp-static-menus/{MENU LOCATION}.html'.
	 *
	 * @todo need a better default name. Could end up serving the wrong static file to the menu location.
	 *
	 * @param array $conditions Array of conditions.
	 *
	 * @return string The file path, else empty string.
	 */
	public function get_cache_file_path( $conditions ) {

		// Conditions can possibly be wrapped in an array.
		if ( is_array( $conditions ) ) {
			$conditions = array_shift( $conditions );
		}

		$file_name = ( ! empty( $conditions->theme_location ) ) ? $conditions->theme_location : 'menu';

		/**
		 * Filter the menu's cache file name.
		 *
		 * @param string   $file name  The given file name.
		 * @param stdClass $conditions The nav menu object.
		 *
		 * @return string The filtered file name.
		 */
		$filtered_file_name = apply_filters( 'wp_static_menus_cache_file_name', $file_name, $conditions );

		$file_name = ( ! empty( $filtered_file_name ) && is_string( $file_name ) ) ? $filtered_file_name : $file_name;
		$path      = $this->get_cache_path();

		return trailingslashit( $path ) . sanitize_title( $file_name ) . '.html';
	}

	/**
	 * Get the path to the directory where cache files are stored.
	 *
	 * Defaults to 'wp-content/cache/wp-static-menus/'.
	 *
	 * @param string $append Optional. String to append to the path.
	 *
	 * @return string The cache path.
	 */
	public function get_cache_path( $append = null ) {
		$path = trailingslashit( WP_CONTENT_DIR . '/cache/' . $this->slug );

		/*
		 * Get override path from settings.
		 *
		 * This still uses the WP_CONTENT_DIR to prevent unintended paths by less-advanced users.
		 *
		 * This path can be completely overriden using the wp_static_menus_cache_path filter below.
		 */
		$settings_path = Settings::get_instance()->get_value( 'cache_path' );
		if ( ! empty( $settings_path ) && is_string( $settings_path ) ) {

			// Normalize the settings path.
			$settings_path = wp_normalize_path( $settings_path );

			// Wp_normalize_path allows slashes at the start of the string.
			$settings_path = ltrim( $settings_path, '/' );
			$path          = trailingslashit( WP_CONTENT_DIR ) . trailingslashit( $settings_path );
		}

		/**
		 * Filter the cache directory path.
		 *
		 * Defaults to 'wp-content/cache/wp-static-menus/'.
		 *
		 * @todo Add more params.
		 *
		 * @param string $path The default path.
		 *
		 * @return string Custom cache directory path.
		 */
		$filtered_path = apply_filters( 'wp_static_menus_cache_path', $path );

		$path = ( ! empty( $filtered_path ) && is_string( $filtered_path ) ) ? $filtered_path : $path;

		// Add any extra path string that was passed.
		$path .= ( is_string( $append ) ) ? $append : '';

		return trailingslashit( $path );
	}

	/**
	 * Return the start debug comment.
	 *
	 * @return string
	 */
	public function get_cache_start_comment() {
		if ( ! $this->is_debug() ) {
			return;
		}
		return __( 'Start WP Static Menus', 'wp-static-menus' );
	}

	/**
	 * Return the end debug comment.
	 *
	 * @return string
	 */
	public function get_cache_close_comment() {
		if ( ! $this->is_debug() ) {
			return;
		}
		return __( 'End WP Static Menus', 'wp-static-menus' );
	}

	/**
	 * Remove the current cache files and directory.
	 *
	 * This is necessary as $this->clear_cache() only clears files
	 * within a directory. This method is used to delete both the directory and files.
	 *
	 * Example:
	 * If the existing cache path is
	 * /cache/wp-static-menus/
	 * then $this->clear_cache() will only delete *files* from within the
	 * /cache/wp-static-menus/ directory.
	 *
	 * However, if the new path is
	 * /cache/my-menus/
	 * we need to delete everything, including the former directory (/cache/wp-static-menus/).
	 *
	 * Note that (in this example) we must be sure to preserve the /cache/ directory.
	 *
	 * @param string $new_path The path to be updated.
	 */
	public function delete_directory_on_update( $new_path ) {
		// The existing (former) path.
		$existing_path = $this->get_cache_path();

		/*
		 * Find the parts that the two paths have in common
		 * so we don't delete a common (grand)parent cache directory.
		 * This directory could possibly be containing other files cached
		 * by the user.
		 *
		 * wp_normalize_path() allows for double-slashes at the start of the path.
		 * These are important here, so don't try to strip them out.
		 */
		$existing_directories = explode( '/', wp_normalize_path( $existing_path ) );
		$new_directores       = explode( '/', wp_normalize_path( $new_path ) );

		/*
		 * Returns common directory, only if they match in a specific order.
		 *
		 * We don't want to just delete the last directory in the existing path.
		 * If the existing path was /caches/, and the new one is /caches/menus,
		 *
		 * This means that if the existing path is
		 * /cache/menus/
		 * and the new path is
		 * /cache/mysite/menus/
		 * we will delete /cache/menus/, but not the entire /cache/ directory.
		 */
		$common_directories = array_intersect_assoc( $existing_path_pieces, $new_path_pieces );

		if ( ! empty( $common_directories ) ) {
			$delete_path = implode( '/', $common_directories );
		}

		return $delete_path;

		// $this->ensure_directory_exists( $delete_path );
		// $this->delete_directory_contents( $delete_path );
	}
}
