<?php
/**
 * Settings handler
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
 * Class Settings.
 *
 * @since 0.1.0
 */
class Settings extends Singleton {

	// Our page-specific setting.
	const SETTING = 'ms_wpsm';

	// WP Option name.
	const OPTION_NAME = 'ms-wpsm';

	/**
	 * The Plugin instance.
	 *
	 * @var stdClass
	 */
	public $plugin;

	/**
	 * Holds our saved Option values.
	 *
	 * @var array
	 */
	public $option = [];

	/**
	 * Inititalize.
	 */
	protected function init() {
		$this->plugin = Plugin::get_instance();
		$this->option = $this->get_option();
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );

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
			esc_html__( 'Static Menus', 'ms-wpsm' ),
			esc_html__( 'WP Static Menus', 'ms-wpsm' ),
			'manage_options',
			self::SETTING,
			[ $this, 'render_settings_page' ]
		);

		add_action( 'admin_print_footer_scripts-' . $hook, [ $this, 'footer_script' ] );
	}

	/**
	 * Register our setting, settings & fields.
	 *
	 * @action admin_init
	 */
	public function register_settings() {
		register_setting( self::SETTING, self::OPTION_NAME );

		add_settings_section(
			'menus',
			esc_html__( 'Menus', 'ms-wpsm' ),
			[ $this, 'menus_intro' ],
			self::SETTING
		);

		/**
		 * @todo Add custom validator for checkboxes to set/get the value more clearly.
		 */
		add_settings_field(
			'theme_locations',
			__( 'Cached Menu Locations', 'lucado' ),
			[ $this, 'locations_render' ],
			self::SETTING,
			'menus'
		);


		add_settings_section(
			'cache_config',
			esc_html__( 'Cache Configuration', 'ms-wpsm' ),
			[ $this, 'cache_config_intro' ],
			self::SETTING
		);

		add_settings_field(
			'caching_method',
			__( 'Caching Method', 'lucado' ),
			[ $this, 'caching_method_render' ],
			self::SETTING,
			'cache_config'
		);

		$html_field_classes = 'html-settings';
		if ( Plugin::CACHE_METHOD_HTML !== $this->get_value( 'caching_method' ) ) {
			$html_field_classes .= ' hidden'; // Add "hidden" class to hide.
		}

		add_settings_field(
			'html_settings',
			__( 'HTML Cache Settings', 'lucado' ) . '<p class="description" style="font-weight: normal">' . __( 'The location where HTML files will be stored', 'ms-wpsm' ) . '</p>',
			[ $this, 'html_settings_render' ],
			self::SETTING,
			'cache_config',
			[ 'class' => $html_field_classes ]
		);

		add_settings_field(
			'cache_length',
			__( 'Cache Length', 'lucado' ) . '<p class="description" style="font-weight: normal">' . __( 'Maximum Cache Length, in Minutes', 'ms-wpsm' ) . '</p>',
			[ $this, 'cache_length_render' ],
			self::SETTING,
			'cache_config'
		);

		add_settings_field(
			'exceptions',
			__( 'Exceptions', 'lucado' ),
			[ $this, 'exceptions_render' ],
			self::SETTING,
			'cache_config'
		);

		add_settings_section(
			'cache_tools',
			esc_html__( 'Cache Tools', 'ms-wpsm' ),
			[ $this, 'cache_tools_intro' ],
			self::SETTING
		);

		add_settings_field(
			'disable_caching',
			__( 'Disable All Caching', 'lucado' ),
			[ $this, 'disable_caching_render' ],
			self::SETTING,
			'cache_tools'
		);

		add_settings_field(
			'empty_all_caches',
			__( 'Empty All Caches', 'lucado' ),
			[ $this, 'empty_all_caches_render' ],
			self::SETTING,
			'cache_tools'
		);
	}

	/**
	 * Intro text to the Menus settings section.
	 */
	public function menus_intro() {
		echo wp_kses_post( '<hr>' );
		echo wp_kses_post( 'Menus are cached by their displayed location.<br>Menu locations are determined by each theme, and user-created menus are assigned to these locations.', 'ms-wpsm' );

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

		foreach ( $menus as $location => $menu ) :
			$item_name  = $field_name . '[' . $location . ']';
			$item_value = isset( $value[ $location ] ) ? 1 : 0;
			?>
			<input type="checkbox" name="<?php echo esc_attr( $item_name ); ?>" value="1" <?php checked( $item_value, 1 ); ?>><?php echo esc_html( ucwords( $location ) ); ?><br>
			<?php
		endforeach;
	}
	/**
	 * Intro text to the Cache Config settings section.
	 */
	public function cache_config_intro() {
		echo wp_kses_post( '<hr>' );
	}

	/**
	 * Render the Caching method field.
	 */
	public function caching_method_render() {
		$value      = $this->get_value( 'caching_method' );
		$field_name = self::OPTION_NAME . '[caching_method]';
		?>
		<select id="caching-method-select" name="<?php echo esc_attr( $field_name ); ?>">
			<?php foreach ( $this->plugin->cache_methods as $label => $class_name ) : ?>
				<option value="<?php echo esc_attr( $class_name ); ?>" <?php selected( $class_name, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<br>
		<p class="description">
			<?php esc_html_e( 'Default is "Object Cache."', 'ms-wpsm' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the settings fields for HTML Caching.
	 */
	public function html_settings_render() {
		$value      = $this->get_value( 'html_settings' );
		$value      = ( is_array( $value ) ) ? $value : [];
		$file_path  = ( ! empty( $value['file_path'] ) ) ? $value['file_path'] : '';
		$dir_name   = ( ! empty( $value['dir_name'] ) ) ? $value['dir_name'] : '';
		$field_name = self::OPTION_NAME . '[html_settings]';

		?>
		<p>
			<?php esc_html_e( 'Cache File Path', 'ms-wpsm' ); ?><br>
			<input class="widefat" type="text" name="<?php echo esc_attr( $field_name . '[file_path]' ); ?>" placeholder="<?php echo esc_html( WP_CONTENT_DIR ); ?>" value="<?php echo esc_html( $file_path ); ?>"/>
		</p>
		<p>
			<?php esc_html_e( 'Cache Directory Name', 'ms-wpsm' ); ?><br>
			<input type="text" name="<?php echo esc_attr( $field_name . '[dir_name]' ); ?>" placeholder="cache" value="<?php echo esc_html( $dir_name ); ?>"/>
		</p>
		<?php
	}

	/**
	 * Render the cache length settings field.
	 */
	public function cache_length_render() {
		$value      = $this->get_value( 'cache_length' );
		$field_name = self::OPTION_NAME . '[cache_length]';

		// Max is one week.
		?>
		<input name="<?php echo esc_attr( $field_name ); ?>" type="number" min="1" max="10080" value="<?php echo esc_attr( $value ); ?>" placeholder="60">
		<br>
		<p class="description">
			<?php esc_html_e( 'Defaults to 60 minutes.', 'ms-wpsm' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the display exceptions field.
	 */
	public function exceptions_render() {
		$value      = $this->get_value( 'exceptions' );
		$value      = ( ! empty( $value ) ) ? $value : 0;
		$field_name = self::OPTION_NAME . '[exceptions]';
		?>
		<p><?php esc_html_e( 'Do not display cached menus to:', 'ms-wpsm' ); ?></p>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="logged_in" <?php checked( $value, 'logged_in' ); ?>><?php esc_html_e( 'Logged-in Users', 'ms-wpsm' ); ?><br>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="admins" <?php checked( $value, 'admins' ); ?>><?php esc_html_e( 'Administrators & Editors', 'ms-wpsm' ); ?><br><br>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="0" <?php checked( $value, 0 ); ?>><?php esc_html_e( 'Display cached menus to everyone', 'ms-wpsm' ); ?><br>
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
		$text       = ( ! empty( $value ) ) ? __( 'Caching is disabled.', 'ms-wpsm' ) : __( 'Caching is enabled.', 'ms-wpsm' );
		$text_color = ( ! empty( $value ) ) ? '#b32d2e' : '#00a32a';

		?>
		<p id="disable-caching" class="description" style="color: <?php echo esc_attr( $text_color ); ?>">
			<strong><?php echo esc_html( $text ); ?></strong>
		</p>
		<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $value, 1 ); ?>><?php esc_html_e( 'Disable Caching?', 'ms-wpsm' ); ?><br>

		<?php
	}

	/**
	 * Render the Empty All Caches button field.
	 */
	public function empty_all_caches_render() {
		$field_name = self::OPTION_NAME . '[empty_all_caches]';
		?>
		<button type="button" class="button button-secondary" name="<?php echo esc_attr( $field_name ); ?>"><?php esc_html_e( 'Empty All Caches Now', 'ms-wpsm' ); ?></button>
		<?php
	}

	/**
	 * Render the Settings Page.
	 */
	public function render_settings_page() {
		?>
			<div class="wrap">
				<h1><?php esc_html_e( 'WP Static Menus', 'ms-wpsm' ); ?></h1>
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
	 * Render the footer scripts.
	 *
	 * @action admin_print_footer_scripts-{$hook}
	 */
	public function footer_script() {
		?>
		<script>
			(function($) {
				$( document ).on( 'change', '#caching-method-select', function() {
					if ( $(this).val() === '<?php echo esc_js( wp_slash( Plugin::CACHE_METHOD_HTML ) ); ?>' ) {
						$( '.html-settings' ).removeClass( 'hidden' );
					} else {
						$( '.html-settings' ).addClass( 'hidden' );
					}
				});
			})(jQuery);
		</script>
		<?php
	}

	public function admin_notices() {
		$screen = get_current_screen();

		if ( 'tools_page_ms_wpsm' !== $screen->id ) {
			return;
		}

		if ( empty( $this->get_value( 'disable_caching' ) ) ) {
			return;
		}

		?>
			<div class="notice notice-error">
				<?php // translators: link to disable-caching ID on the page. ?>
				<p><?php echo wp_kses_post( sprintf( __( 'Caching is currently <a href="%s">Disabled</a>.', 'ms-wpsm' ), '#disable-caching' ) ); ?></p>
			</div>
		<?php
	}


}
