<?php
/**
 * Settings handler
 *
 * Renders the settings page and saves settings into an option.
 *
 * @todo Testing: Consider ways to set/get option value when this is a singleton.
 *
 * @package Mindsize\WPStaticMenus
 * @since   0.1.0
 * @author  Mindsize <info@mindsize.me>
 */

namespace Mindsize\WPStaticMenus;

use Mindsize\WPStaticMenus\Cacher;

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

	// Nonce Name.
	const NONCE_NAME = 'wp_static_menus';

	// Flush Cache nonce action.
	const NONCE_ACTION_FLUSH = 'flush_cache';

	/**
	 * The Class instance.
	 *
	 * @var stdClass
	 */
	protected static $instance;

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
	 */
	public function init() {
		$this->option = $this->get_option();
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_notices', [ $this, 'admin_notice_disabled' ] );
		add_action( 'admin_head-tools_page_' . self::SETTING, [ $this, 'empty_cache' ] );

		if ( $this->get_value( 'show_admin_bar' ) ) {
			add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 99 );
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
			self::OPTION_NAME,
			[
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
			]
		);

		add_settings_section(
			'config',
			esc_html__( 'Settings', 'wp-static-menus' ),
			[ $this, 'plugin_settings_intro' ],
			self::SETTING
		);

		add_settings_field(
			'theme_locations',
			__( 'Cached Menu Locations', 'wp-static-menus' ),
			[ $this, 'locations_render' ],
			self::SETTING,
			'config'
		);

		add_settings_field(
			'exceptions',
			__( 'Exceptions', 'wp-static-menus' ),
			[ $this, 'exceptions_render' ],
			self::SETTING,
			'config'
		);

		add_settings_section(
			'overrides',
			esc_html__( 'Overrides', 'wp-static-menus' ),
			[ $this, 'overrides_intro' ],
			self::SETTING
		);

		add_settings_field(
			'cache_path',
			__( 'Cache Directory', 'wp-static-menus' ) . '<p style="font-weight: normal;">Where the cache files are stored within /wp-content/</p>',
			[ $this, 'cache_path_render' ],
			self::SETTING,
			'overrides'
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
			[ $this, 'disable_caching_render' ],
			self::SETTING,
			'cache_tools'
		);

		add_settings_field(
			'empty_all_caches',
			__( 'Empty All Caches', 'wp-static-menus' ),
			[ $this, 'empty_all_caches_render' ],
			self::SETTING,
			'cache_tools'
		);

		add_settings_field(
			'show_admin_bar',
			__( 'Admin Bar', 'wp-static-menus' ),
			[ $this, 'show_admin_bar_render' ],
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
	public function locations_render() {
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
					<input type="checkbox" name="<?php echo esc_attr( $item_name ); ?>" value="1" <?php checked( $item_value, 1 ); ?>><?php echo esc_html( $description ); ?>
				</label>
				<br>
				<?php
			endforeach;
			?>
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
	 * Render the display exceptions field.
	 */
	public function exceptions_render() {
		$value      = $this->get_value( 'exceptions' );
		$value      = ( ! empty( $value ) ) ? $value : 0;
		$field_name = self::OPTION_NAME . '[exceptions]';
		?>
		<p><?php esc_html_e( 'Do NOT display cached menus to:', 'wp-static-menus' ); ?></p>
		<fieldset>
			<label>
				<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="logged_in" <?php checked( $value, 'logged_in' ); ?>><?php esc_html_e( 'Any Logged-in User', 'wp-static-menus' ); ?>
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
	 * Intro text to the Overrides section.
	 */
	public function overrides_intro() {
		echo wp_kses_post( '<hr>' );
	}

	/**
	 * Render the Cache Path field.
	 */
	public function cache_path_render() {
		$value      = $this->get_value( 'cache_path' );
		$field_name = self::OPTION_NAME . '[cache_path]';

		?>
		<fieldset id="cache-path">
			<input class="widefat" style="width: 500px; max-width: 100%;" type="text" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_html( $value ); ?>" placeholder="cache/wp-static-menus/" /><br>
		</fieldset>
		<p class="description">
			<?php echo wp_kses_post( __( 'Default: cache/wp-static-menus/', 'wp-static-menus' ) ); ?>
		</p>
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
	public function disable_caching_render() {
		$value      = (bool) $this->get_value( 'disable_caching' );
		$field_name = self::OPTION_NAME . '[disable_caching]';

		?>
		<fieldset id="disable-caching">
			<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $value, 1 ); ?>><?php esc_html_e( 'Disable Caching?', 'wp-static-menus' ); ?><br>
		</fieldset>
		<?php
	}

	/**
	 * Render the Empty All Caches button field.
	 */
	public function empty_all_caches_render() {
		$field_name = self::OPTION_NAME . '[empty_all_caches]';
		?>
		<a type="button" class="button button-secondary" name="<?php echo esc_attr( $field_name ); ?>" href="<?php echo esc_url( self::empty_cache_url() ); ?>"><?php esc_html_e( 'Empty All Caches', 'wp-static-menus' ); ?></a>
		<?php
	}

	/**
	 * Render the Admin Bar field.
	 */
	public function show_admin_bar_render() {
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
	 * Sanitize our settings option.
	 *
	 * The real purpose of this is to clear the cache_path if it is changed.
	 *
	 * @param array $input The option to be saved.
	 *
	 * @return array The sanitized option.
	 */
	public function sanitize_settings( $input ) {

		// If cache_path has changed, empty the current cache_path directory.
		$existing_cache_path = $this->get_value( 'cache_path' );
		$new_cache_path      = ( isset( $input['cache_path'] ) ) ? $input['cache_path'] : false;
		if ( $new_cache_path !== $existing_cache_path ) {
			$cacher = new Cacher();
			$cacher->clear_cache();
		}

		/*
		 * Normalize the path.
		 *
		 * wp_normalize_path() allows slashes at the start of the string.
		 * We remove those as this setting will only be applied to
		 * the WP_CONTNENT_DIR, and we don't want to unintentionally
		 * end up with double slashes in the middle of that path.
		 *
		 * Those requiring the double slashes will need to use the
		 * wp_static_menus_cache_path filter.
		 */
		if ( isset( $input['cache_path'] ) ) {
			$input['cache_path'] = trailingslashit( ltrim( wp_normalize_path( $input['cache_path'] ), '/' ) );
		}

		return $input;
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
	 * Empty the Cache from the settings page.
	 *
	 * @todo hook this in a better place so we can remove query_args.
	 *
	 * @action admin_head-tools_page_wp_static_menus
	 */
	public function empty_cache() {
		if ( ! isset( $_REQUEST[ self::NONCE_NAME ] )
			||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION_FLUSH )
		) {
			return;
		}

		// A little more verification of this action.
		if ( empty( $_REQUEST['flush_cache'] ) ) {
			return;
		}

		$cacher  = new Cacher();
		$flushed = $cacher->clear_cache();

		add_action( 'admin_notices', [ $this, 'admin_notice_flushed' ] );
	}

	/**
	 * Add link to Admin Bar.
	 *
	 * @filter admin_bar_menu
	 *
	 * @param obj $wp_admin_bar The WP Admin Bar.
	 */
	public static function admin_bar_menu( $wp_admin_bar ) {
		$args = [
			'id'    => 'wp-static-menus-empty',
			'title' => __( 'Flush Static Menus', 'wp-static-menus' ),
			'href'  => self::empty_cache_url(),
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
				<?php // translators: link to "disable-caching" ID on the settings page. ?>
				<p><?php echo wp_kses_post( sprintf( __( 'Menu Caching is currently <a href="%s">Disabled</a>.', 'wp-static-menus' ), self::settings_url() . '#disable-caching' ) ); ?></p>
			</div>
		<?php
	}

	/**
	 * Admin notices.
	 *
	 * @action admin_notices
	 */
	public function admin_notice_flushed() {
		if ( 'tools_page_' . self::SETTING !== get_current_screen()->id ) {
			return;
		}
		?>
			<div class="notice notice-success">
				<p><?php esc_html_e( 'Static Menus cache emptied.', 'wp-static-menus' ); ?></p>
			</div>
		<?php
	}
}
