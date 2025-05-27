<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://sasapay.co.ke
 * @since      1.0.1
 *
 * @package    Sasa_Pay
 * @subpackage Sasa_Pay/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.1
 * @package    Sasa_Pay
 * @subpackage Sasa_Pay/includes
 * @author     SasaPay <care@sasapay.co.ke>
 */
class Sasa_Pay_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.1
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'sasa-pay',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
