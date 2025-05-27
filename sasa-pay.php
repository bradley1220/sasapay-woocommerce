<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sasapay.co.ke
 * @since             1.0.1
 * @package           Sasa_Pay
 *
 * @wordpress-plugin
 * Plugin Name:       SasaPay Woocommerce Plugin
 * Plugin URI:        https://sasapay.co.ke
 * Description:       Accept payments on woocommerce using SasaPay
 * Version:           1.0.1
 * Author:            SasaPay
 * Author URI:        https://sasapay.co.ke
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sasa-pay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SASA_PAY_VERSION', '1.0.1' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sasa-pay-activator.php
 */
function activate_sasa_pay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sasa-pay-activator.php';
    Sasa_Pay_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sasa-pay-deactivator.php
 */
function deactivate_sasa_pay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sasa-pay-deactivator.php';
	Sasa_Pay_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sasa_pay' );
register_deactivation_hook( __FILE__, 'deactivate_sasa_pay' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sasa-pay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.1
 */
function run_sasa_pay(): void
{

	$plugin = new Sasa_Pay();
	$plugin->run();

}
run_sasa_pay();
