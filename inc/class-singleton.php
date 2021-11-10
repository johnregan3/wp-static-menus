<?php
/**
 * Base singleton class to be extended by all other singletons.
 *
 * @package Mindsize\WPSM
 * @since   0.1.0
 * @author  Mindsize
 */

namespace Mindsize\WPSM;

/**
 * Class Singleton.
 *
 * @since 0.1.0
 */
abstract class Singleton {

	/**
	 * The instance.
	 *
	 * @var array
	 */
	protected static $instance = [];

	/**
	 * Class constructor.
	 *
	 * Prevent direct object creation.
	 *
	 * @see self::init()
	 */
	protected function __construct() {
	}

	/**
	 * Clone.
	 *
	 * Prevent object cloning.
	 */
	final private function __clone() {
	}

	/**
	 * Get the instance.
	 *
	 * @return static Single instance of the extended class.
	 */
	final public static function get_instance() {
		$class = get_called_class();

		if ( ! isset( static::$instance[ $class ] ) ) {
			self::$instance[ $class ] = new $class();

			// Run the Initialization of the class.
			self::$instance[ $class ]->init();
		}

		return self::$instance[ $class ];
	}

	/**
	 * Initialize.
	 *
	 * Initialization function called when object is instantiated.
	 *
	 * Does nothing by default. This class should be overridden in the child class.
	 *
	 * Stuff that you only want to do once, such as hooking into actions and
	 * filters, goes here.
	 */
	protected function init() {
	}
}
