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

	const SETTING = 'ms_wpsm';

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

	protected function init() {
		$this->plugin = Plugin::get_instance();
		$this->option = $this->get_option();
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	// Load up our option property.
	public function get_option() {
		return get_option( self::OPTION_NAME );
	}

	// Return a value from the saved settings.
	public function get_value( $key ) {
		return ( isset( $this->option[ $key ] ) ) ? $this->option[ $key ] : false;
	}

	public function add_settings_page() {
		add_submenu_page(
			'tools.php',
			esc_html__( 'Static Menus', 'ms-wpsm' ),
			esc_html__( 'WP Static Menus', 'ms-wpsm' ),
			'manage_options',
			self::SETTING,
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings() {

		register_setting( self::SETTING, self::OPTION_NAME );


		add_settings_section(
			'plugin_config',
			esc_html__( 'Plugin Configuration', 'ms-wpsm' ),
			[ $this, 'plugin_config_intro' ],
			self::SETTING
		);

		add_settings_field(
			'caching_method',
			__( 'Caching Method', 'lucado' ),
			[ $this, 'caching_method_render' ],
			self::SETTING,
			'plugin_config'
		);

		add_settings_field(
			'exceptions',
			__( 'Exceptions', 'lucado' ),
			[ $this, 'exceptions_render' ],
			self::SETTING,
			'plugin_config'
		);

		add_settings_section(
			'menus',
			esc_html__( 'Menus', 'ms-wpsm' ),
			[ $this, 'menus_intro' ],
			self::SETTING
		);

		add_settings_field(
			'theme_locations',
			__( 'Cached Menu Locations', 'lucado' ),
			[ $this, 'locations_render' ],
			self::SETTING,
			'menus'
		);
	}

	public function plugin_config_intro() {}

	// section content cb
	public function caching_method_render() {
		$value = $this->get_value( 'caching_method' );
		$field_name = self::OPTION_NAME . '[caching_method]';
		?>
		<select id="<?php echo esc_attr( $field_name ); ?>" name="<?php echo esc_attr( $field_name ); ?>">
			<?php foreach ( $this->plugin->cache_methods as $method ) : ?>
				<option value="<?php echo esc_attr( $method ); ?>" <?php selected( $method, $value ); ?>><?php echo esc_html( $method ); ?></option>
			<?php endforeach; ?>
		</select>
		<br>
		<p class="description">
			<?php esc_html_e( 'Default is "Object Cache."', 'ms-wpsm' ); ?>
		</p>
		<?php
	}

	public function exceptions_render() {
		$value = $this->get_value( 'exceptions' );
		$value = ( ! empty( $value ) ) ? $value : 0;
		$field_name = self::OPTION_NAME . '[exceptions]';
		?>
		<p><?php esc_html_e( 'Do not display cached menus to:', 'ms-wpsm' ); ?></p>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="logged_in" <?php checked( $value, 'logged_in' ); ?>><?php esc_html_e( 'Logged-in Users', 'ms-wpsm' ); ?><br>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="admins" <?php checked( $value, 'admins' ); ?>><?php esc_html_e( 'Administrators & Editors', 'ms-wpsm' ); ?><br><br>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="0" <?php checked( $value, 0 ); ?>><?php esc_html_e( 'Display cached menus to everyone', 'ms-wpsm' ); ?><br>
		<?php
	}

	public function menus_intro() {
		echo wp_kses_post( 'Menus are cached by their displayed location.<br>Menu locations are determined by each theme, and user-created menus are assigned to these locations.', 'ms-wpsm' );
	}

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
	 * Render the Test Manager Screen.
	 */
	public function render_settings_page() {
		?>

			<div class="wrap">
				<h1><?php esc_html_e( 'WP Static Menus', 'ethree' ); ?></h1>
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

	public function get_enabled_locations() {
		$locations = $this->get_value( 'theme_locations' );
		$locations = ( ! empty( $locations ) && is_array( $locations ) ) ? $value : [];
		return array_keys( $locations );
	}


}
