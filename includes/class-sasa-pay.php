<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sasapay.co.ke
 * @since      1.0.1
 *
 * @package    Sasa_Pay
 * @subpackage Sasa_Pay/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.1
 * @package    Sasa_Pay
 * @subpackage Sasa_Pay/includes
 * @author     SasaPay <care@sasapay.co.ke>
 */
class Sasa_Pay {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.1
	 * @access   protected
	 * @var      Sasa_Pay_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.1
	 */
	public function __construct() {
		if ( defined( 'SASA_PAY_VERSION' ) ) {
			$this->version = SASA_PAY_VERSION;
		} else {
			$this->version = '1.0.1';
		}
        add_filter('woocommerce_payment_gateways', function ($gateways) {
            $gateways[] = 'WC_SasaPay';
            return $gateways;
        }, 9);

        add_action('plugins_loaded','Sasa_Pay::sasapay_wp_init');

        $this->plugin_name = 'sasa-pay';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}
    public static function sasapay_wp_init()
    {
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
            return;
        }
        require_once 'class-wc-sasa-pay.php';

        add_filter( 'woocommerce_payment_gateways', 'Sasa_Pay::wc_add_sasapay_gateway' );
    }
    public static function wc_add_sasapay_gateway( $methods ) {

        if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {
            $methods[] = 'WC_SasaPay';
        } else {
            $methods[] = 'WC_SasaPay';
        }

        return $methods;

    }

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sasa_Pay_Loader. Orchestrates the hooks of the plugin.
	 * - Sasa_Pay_i18n. Defines internationalization functionality.
	 * - Sasa_Pay_Admin. Defines all hooks for the admin area.
	 * - Sasa_Pay_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sasa-pay-loader.php';
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sasa-pay-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sasa-pay-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sasa-pay-public.php';

		$this->loader = new Sasa_Pay_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sasa_Pay_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sasa_Pay_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Sasa_Pay_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Sasa_Pay_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
	 * @since    1.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.1
	 * @return    Sasa_Pay_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
