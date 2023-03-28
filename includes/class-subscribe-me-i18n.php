<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://nikhil.wisdmlabs.net
 * @since      1.0.0
 *
 * @package    Subscribe_Me
 * @subpackage Subscribe_Me/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Subscribe_Me
 * @subpackage Subscribe_Me/includes
 * @author     Nikhil <nikhil.mhaske@wisdmlabs.com>
 */
class Subscribe_Me_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'subscribe-me',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
