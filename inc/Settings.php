<?php
/**
 * Settings handler
 *
 * Renders the settings page and saves settings into an option.
 *
 * @package Mindsize\WPStaticMenus
 * @since   0.1.0
 * @author  Mindsize <info@mindsize.me>
 */

namespace Mindsize\WPStaticMenus;

/**
 * Class Settings.
 *
 * @since 0.1.0
 */
class Settings {

	// Our page-specific setting.
	const SETTING = 'wp_static_menus';

	// WP Option name.
	const OPTION_NAME = 'wp-static-menus';

	// Flush Cache nonce action.
	const NONCE_ACTION_FLUSH = 'flush_cache';

	/**
	 * The Class instance.
	 *
	 * @var stdClass
	 */
	protected static $instance;

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected static $plugin;


	/**
	 * Holds our saved Option values.
	 *
	 * @var array
	 */
	public $option = [];

	/**
	 * Get the instance.
	 *
	 * @return static Single instance of this class.
	 */
	public static function get_instance() {
		$class = get_called_class();

		if ( ! isset( static::$instance ) ) {
			self::$instance = new $class();

			// Run the initialization of the class.
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Inititalize.
	 *
	 * Note that the cache is flushed any time our option is updated.
	 *
	 * @see Plugin::init()
	 */
	public function init() {
		$this->plugin = ms_wp_static_menus();
		$this->option = $this->get_option();
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_notices', [ $this, 'admin_notice_disabled' ] );

		// These must always be available for the Settings page form button.
		add_action( 'wp_ajax_js_flush_cache', [ $this, 'js_flush_cache' ] );
		add_action( 'admin_footer', [ $this, 'admin_bar_js' ] );

		// Admin bar cache-flushing functionality.
		if ( $this->get_value( 'show_admin_bar' ) ) {
			wp_enqueue_script( 'jquery' );
			add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 99 );
			add_action( 'wp_footer', [ $this, 'admin_bar_js' ], 99999 );
		}
	}

	/**
	 * Print direct link to the Settings Page from the WP Plugins screen.
	 *
	 * @filter plugin_action_links_
	 *
	 * @param  array $links Array of links for the Plugins screen.
	 *
	 * @return array        Updated array of links.
	 */
	public function settings_link( $links ) {
		return array_merge(
			[
				'settings' => '<a href="' . self::settings_url() . '">' . __( 'Settings', 'wp-static-menus' ) . '</a>',
			],
			$links
		);
	}

	/**
	 * Return the Settings Page URL.
	 *
	 * @return string The Settings Page URL.
	 */
	private static function settings_url() {
		return admin_url( 'tools.php?page=' . self::SETTING );
	}

	/**
	 * Create the Empty Cache URL.
	 *
	 * @todo remove this.
	 *
	 * @return string The Nonce URL.
	 */
	private static function empty_cache_url() {
		return wp_nonce_url( add_query_arg( 'flush_cache', '1', self::settings_url() ), self::NONCE_ACTION_FLUSH, self::NONCE_NAME );
	}

	/**
	 * Load our option property.
	 *
	 * @return array Array from the option.
	 */
	public function get_option() {
		return get_option( self::OPTION_NAME );
	}

	/**
	 * Return a value from the saved settings.
	 *
	 * @param string $key A setting name.
	 *
	 * @return mixed The value of the setting, else false.
	 */
	public function get_value( $key ) {
		return ( isset( $this->option[ $key ] ) ) ? $this->option[ $key ] : false;
	}

	/**
	 * Register the Settings Page.
	 *
	 * Also fires our footer scripts hook.
	 *
	 * @action admin_menu
	 */
	public function add_settings_page() {
		$hook = add_submenu_page(
			'tools.php',
			esc_html__( 'WP Static Menus', 'wp-static-menus' ),
			esc_html__( 'WP Static Menus', 'wp-static-menus' ),
			'manage_options',
			self::SETTING,
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register our setting, settings & fields.
	 *
	 * @action admin_init
	 */
	public function register_settings() {
		register_setting(
			self::SETTING,
			self::OPTION_NAME
		);

		add_settings_section(
			'config',
			esc_html__( 'Settings', 'wp-static-menus' ),
			[ $this, 'plugin_settings_intro' ],
			self::SETTING
		);

		add_settings_field(
			'theme_locations',
			__( 'Static Menu Locations', 'wp-static-menus' ),
			[ $this, 'render_locations' ],
			self::SETTING,
			'config'
		);

		add_settings_field(
			'disable_user_caching',
			__( 'Disable User Menu Caching', 'wp-static-menus' ),
			[ $this, 'render_disable_user_caching' ],
			self::SETTING,
			'config'
		);

		add_settings_field(
			'cache_length',
			__( 'Cache Length', 'wp-static-menus' ),
			[ $this, 'render_cache_length' ],
			self::SETTING,
			'config'
		);

		add_settings_field(
			'exceptions',
			__( 'Exceptions', 'wp-static-menus' ),
			[ $this, 'render_exceptions' ],
			self::SETTING,
			'config'
		);


		add_settings_section(
			'cache_tools',
			esc_html__( 'Tools', 'wp-static-menus' ),
			[ $this, 'cache_tools_intro' ],
			self::SETTING
		);

		add_settings_field(
			'disable_caching',
			__( 'Disable All Caching', 'wp-static-menus' ),
			[ $this, 'render_disable_caching' ],
			self::SETTING,
			'cache_tools'
		);

		add_settings_field(
			'empty_all_caches',
			__( 'Flush Menu Cache', 'wp-static-menus' ),
			[ $this, 'render_flush_menu_cache' ],
			self::SETTING,
			'cache_tools'
		);

		add_settings_field(
			'show_admin_bar',
			__( 'Admin Bar', 'wp-static-menus' ),
			[ $this, 'render_show_admin_bar' ],
			self::SETTING,
			'cache_tools'
		);
	}

	/**
	 * Intro text to the Menus settings section.
	 */
	public function plugin_settings_intro() {
		echo wp_kses_post( '<hr>' );
	}

	/**
	 * Render the Menu Locations field.
	 */
	public function render_locations() {
		$value      = $this->get_value( 'theme_locations' );
		$value      = ( ! empty( $value ) && is_array( $value ) ) ? $value : [];
		$field_name = self::OPTION_NAME . '[theme_locations]';
		$menus      = get_registered_nav_menus();

		// Sanity check.
		if ( empty( $menus ) || ! is_array( $menus ) ) {
			return;
		}
		?>
		<fieldset>
			<?php
			foreach ( $menus as $location => $description ) :
				$item_name  = $field_name . '[' . $location . ']';
				$item_value = isset( $value[ $location ] ) ? 1 : 0;
				?>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $item_name ); ?>" value="1" <?php checked( $item_value, 1 ); ?>><?php echo esc_html( $description ); ?><br>
				</label>
				<br>
				<?php
			endforeach;
			?>
		</fieldset>
		<?php
	}

	/**
	 * Render the Disable User-level Caching field.
	 */
	public function render_disable_user_caching() {
		$value      = (bool) $this->get_value( 'disable_user_caching' );
		$field_name = self::OPTION_NAME . '[disable_user_caching]';

		?>
		<fieldset>
			<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $value, 1 ); ?>><?php esc_html_e( 'Disable Menu caching for individual users?', 'wp-static-menus' ); ?>
			<p class="description"><?php esc_html_e( 'Check this box if your menus do not show user-specific information, like "Edit Profile" links.', 'wp-static-menus' ); ?></p>
		</fieldset>

		<?php
	}

	/**
	 * Intro text to the Cache Config settings section.
	 */
	public function cache_config_intro() {
		echo wp_kses_post( '<hr>' );
	}

	/**
	 * Render the cache length field.
	 *
	 * Note that the cache is flushed any time our option is updated.
	 * This will automatically set up our new cache length.
	 */
	public function render_cache_length() {
		$value      = intval( $this->get_value( 'cache_length' ) );
		$value      = ( ! empty( $value ) ) ? $value : 60;
		$field_name = self::OPTION_NAME . '[cache_length]';
		?>
		<fieldset>
			<label>
				<input type="number" class="widefat" name="<?php echo esc_attr( $field_name ); ?>" placeholder="60" value="<?php echo esc_attr( $value ); ?>" min="1">
			</label>
			<p class="description"><?php esc_html_e( 'Maximum number of minutes to hold cache files.', 'wp-static-menus' ); ?></p>
		</fieldset>
		<?php
	}

	/**
	 * Render the display exceptions field.
	 */
	public function render_exceptions() {
		$value      = $this->get_value( 'exceptions' );
		$value      = ( ! empty( $value ) ) ? $value : 0;
		$field_name = self::OPTION_NAME . '[exceptions]';
		?>
		<p><?php echo wp_kses_post( __( 'Do <span style="text-decoration: underline">not</span> display cached menus to:', 'wp-static-menus' ) ); ?></p>
		<fieldset>
			<label>
				<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="logged_in" <?php checked( $value, 'logged_in' ); ?>><?php esc_html_e( 'Any Logged-in User', 'wp-static-menus' ); ?><br>
			</label><br>
			<label>
				<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="admins" <?php checked( $value, 'admins' ); ?>><?php esc_html_e( 'Super Administrators, Administrators, and Editors', 'wp-static-menus' ); ?>
			</label><br><hr style="width: 300px; margin-left: 0;">
			<label>
				<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="0" <?php checked( $value, 0 ); ?>><?php esc_html_e( 'Display cached menus to everyone', 'wp-static-menus' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Intro text to the Tools section.
	 */
	public function cache_tools_intro() {
		echo wp_kses_post( '<hr>' );
	}

	/**
	 * Render the Disable Caching field.
	 */
	public function render_disable_caching() {
		$value      = (bool) $this->get_value( 'disable_caching' );
		$field_name = self::OPTION_NAME . '[disable_caching]';

		?>
		<fieldset id="disable-caching">
			<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $value, 1 ); ?>><?php esc_html_e( 'Disable Caching?', 'wp-static-menus' ); ?><br>
		</fieldset>
		<?php
	}

	/**
	 * Render the Flush Menu Cache button field.
	 */
	public function render_flush_menu_cache() {
		$field_name = self::OPTION_NAME . '[empty_all_caches]';
		?>
		<a id="flush-menu-cache-button" type="button" class="button button-secondary" name="<?php echo esc_attr( $field_name ); ?>" href="#" onclick="flushStaticMenus();return false;"><?php esc_html_e( 'Flush Menu Cache', 'wp-static-menus' ); ?></a>
		<?php
	}

	/**
	 * Render the Admin Bar field.
	 */
	public function render_show_admin_bar() {
		$value      = (bool) $this->get_value( 'show_admin_bar' );
		$field_name = self::OPTION_NAME . '[show_admin_bar]';
		?>
		<fieldset>
			<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $value, 1 ); ?>><?php esc_html_e( 'Display a "Flush Static Menus" button in the Admin Bar?', 'wp-static-menus' ); ?><br>
		</fieldset>
		<?php
	}

	/**
	 * Render the Settings Page.
	 */
	public function render_settings_page() {
		?>
			<div class="wrap">
				<h1><?php esc_html_e( 'WP Static Menus', 'wp-static-menus' ); ?></h1>
				<form method="post" action="options.php">
					<?php
						settings_fields( self::SETTING );
						do_settings_sections( self::SETTING );
						submit_button();
					?>
				</form>
		</div>
		<?php
	}

	/**
	 * Get the Menu Locations with caching enabled.
	 *
	 * Note that this is different than Plugin::get_enabled_locations(),
	 * as this is a setting, which does not use the filter hook.
	 */
	public function get_enabled_locations() {
		$locations = $this->get_value( 'theme_locations' );
		$locations = ( ! empty( $locations ) && is_array( $locations ) ) ? $value : [];
		return array_keys( $locations );
	}

	/**
	 * Add Flush Cache link to Admin Bar.
	 *
	 * @filter admin_bar_menu
	 *
	 * @param obj $wp_admin_bar The WP Admin Bar.
	 */
	public static function admin_bar_menu( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$args = [
			'id'    => 'wp-static-menus-flush',
			'title' => __( 'Flush Static Menus', 'wp-static-menus' ),
			'href'  => '#',
			'meta'  => [
				'onclick' => 'flushStaticMenus(); return false',
			],
		];

		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Caching Disabled admin notice.
	 *
	 * Appears on both the Plugin Settings page and the Appearance > Menus Admin Screen.
	 *
	 * @action admin_notices
	 */
	public function admin_notice_disabled() {
		$screen = get_current_screen()->id;
		if ( 'nav-menus' !== $screen && 'tools_page_' . self::SETTING !== $screen ) {
			return;
		}

		if ( empty( $this->get_value( 'disable_caching' ) ) ) {
			return;
		}
		?>
			<div class="notice notice-error">
				<?php // translators: link to "Diable Caching" checkbox on the Settings page. ?>
				<p><?php echo wp_kses_post( sprintf( __( 'Menu Caching is currently <a href="%s">Disabled</a>.', 'wp-static-menus' ), self::settings_url() . '#disable-caching' ) ); ?></p>
			</div>
		<?php
	}

	/**
	 * Empty the Cache via AJAX.
	 *
	 * This is used when the "Flush Static Menus" admin bar button is clicked,
	 * as well as if the button is clicked on the Settings page form.
	 *
	 * @action wp_ajax_js_flush_cache
	 */
	public function js_flush_cache() {
		check_ajax_referer( self::NONCE_ACTION_FLUSH, 'security' );

		$this->plugin->flush_cache();

		echo wp_json_encode( 'Cache flushed successfully' );
		wp_die();
	}

	/**
	 * Javascript to handle flushing the cache.
	 *
	 * Don't check if admin_bar link is enabled, as this
	 * is also used by the "flush cachce" button on the Settings page.
	 *
	 * @action wp_footer
	 * @action admin_footer
	 */
	public function admin_bar_js() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			function flushStaticMenus() {
				var ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
				data = {
					'action': 'js_flush_cache',
					'security': '<?php echo esc_js( wp_create_nonce( self::NONCE_ACTION_FLUSH ) ); ?>',
				};

				jQuery.post( ajaxUrl, data, function(response) {
					jQuery( '#wp-admin-bar-wp-static-menus-flush' ).addClass( 'success' );
					jQuery( '#flush-menu-cache-button' ).attr( 'disabled', 'disabled' );
					setTimeout( function() {
						jQuery( '#flush-menu-cache-button' ).removeAttr( 'disabled' );
						jQuery( '#wp-admin-bar-wp-static-menus-flush' ).removeClass( 'success' );
					}, 1000 );
				});
			}
		</script>
		<style type="text/css">
			#wp-admin-bar-wp-static-menus-flush,
			#wp-admin-bar-wp-static-menus-flush a {
				transition: all 500ms;
			}
			#wp-admin-bar-wp-static-menus-flush.success,
			#wp-admin-bar-wp-static-menus-flush.success a {
				color: #fff !important;
				background: #00a32a !important;
			}
		</style>
		<?php
	}
}
