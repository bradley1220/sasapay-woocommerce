<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sasapay.co.ke
 * @since      1.0.1
 *
 * @package    Sasa_Pay
 * @subpackage Sasa_Pay/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sasa_Pay
 * @subpackage Sasa_Pay/admin
 * @author     SasaPay <care@sasapay.co.ke>
 */
class Sasa_Pay_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        //check if admin
        if (is_admin())
        {
            add_action('admin_menu', array(&$this, 'add_options_sasapay_panel'));
        }

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sasa_Pay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sasa_Pay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sasa-pay-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sasa_Pay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sasa_Pay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sasa-pay-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Show the admin menu for the plugin
     */
    function add_options_sasapay_panel(): void
    {
        if (function_exists('add_menu_page') && current_user_can('manage_options'))
        {
            add_menu_page( 'SasaPay WooCommerce', 'SasaPay', 'manage_options', 'sasapay_settings', array($this, 'settings'),'');
        }
    }
    function settings(): void
    {
        //must check that the user has the required capability
        if (!current_user_can('manage_options'))
        {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        //credentials keys
        $go_live_name='live';
        $merchant_name='merchant_name';
        $merchant_code_name='merchant_code';
        $network_code_name='network_code';
        $currency_code_name='currency_code';
        $callback_url_name='callback_url';
        $validation_url_name='validation_url';
        $api_base_url_name='api_base_url';
        $client_id_name='client_id';
        $client_key_name='client_secret';
        $application_url_name='application_url';
        //credentials values
        $merchant_name_value=get_option($merchant_name);
        $network_code_value=get_option($network_code_name);
        $currency_code_value=get_option($currency_code_name);
        $merchant_code_value=get_option($merchant_code_name);
        $callback_url_name_value=get_option($callback_url_name);
        $validation_url_name_value=get_option($validation_url_name);
        $api_base_url_name_value=get_option($api_base_url_name);
        $application_url_value=get_option($application_url_name);
        $client_id_name_value=get_option($client_id_name);
        $client_key_name_value=get_option($client_key_name);

        // See if the user has posted us some information
        if( isset($_POST['submitted']))
        {
            $callback=WC()->api_request_url('WC_SasaPay');
            $url_info = parse_url($callback);
            if ( class_exists( 'WooCommerce' ) ) {
                $new_url=str_replace( $url_info['host'],$_POST[$callback_url_name],$callback);
            } else {
                $new_url=$callback;
                // you don't appear to have WooCommerce activated
            }
            // Read their posted value
            $go_live_value=$_POST[$go_live_name];
            $merchant_name_value=$_POST[$merchant_name];
            $merchant_code_value=$_POST[$merchant_code_name];
            $network_code_value=$_POST[$network_code_name];
            $currency_code_value=$_POST[$currency_code_name];
            $callback_url_name_value=$new_url;
            $validation_url_name_value=$_POST[$validation_url_name];
            $api_base_url_name_value=$_POST[$api_base_url_name];
            $client_id_name_value=$_POST[$client_id_name];
            $client_key_name_value=$_POST[$client_key_name];
            $application_url_value=$_POST[$application_url_name];

            update_option($go_live_name, $go_live_value);
            update_option($merchant_name, $merchant_name_value);
            update_option($merchant_code_name, $merchant_code_value);
            update_option($network_code_name, $network_code_value);
            update_option($currency_code_name, $currency_code_value);
            update_option($callback_url_name, $callback_url_name_value);
            update_option($validation_url_name, $validation_url_name_value);
            update_option($api_base_url_name, $api_base_url_name_value);
            update_option($client_id_name, $client_id_name_value);
            update_option($client_key_name, $client_key_name_value);
            update_option($client_key_name, $client_key_name_value);
            update_option($application_url_name, $application_url_value);

            ?>
            <div class="updated"><p><strong><?php _e('Settings saved.', 'menu-test' ); ?></strong></p></div>
            <?php
        }

        // Now display the settings editing screen
        echo '<div class="wrap">';
        // header

        echo "<h2>" . __( 'SasaPay WooCommerce Plugin Settings', 'menu-test' ) . "</h2>";

        // settings form
        ?>
        <form name="form1" method="post" action="">

            <hr />

            <h3>SasaPay WooCommerce - General Settings</h3>
            <table cellspacing="2">
                <tr>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo $go_live_name; ?>" <?php checked($go_live_name, get_option($go_live_name));?>  value="<?php echo $go_live_name; ?>" />
                        </label>
                        <?php _e("Go Live", 'menu-test' ); ?>
                    </td>
                </tr>
            </table>

            <hr />

            <h3>SasaPay API Settings</h3>
            <h5>Visit or contact <a href="https://developers.sasapay.app/" style="text-decoration: none">SasaPay Developer Portal</a> to provide these credentials</h5>
            <table cellspacing="2">
                <tr>
                    <td><?php _e("Merchant Name:", 'menu-test' ); ?></td>
                    <td>
                        <label>
                            <input type="text" name="<?php echo $merchant_name; ?>" value="<?php echo $merchant_name_value; ?>" size="40" />
                        </label>
                    </td>
                </tr>

                <tr>
                    <td><?php _e("Merchant Code:", 'menu-test' ); ?></td>
                    <td>
                        <label>
                            <input type="password" name="<?php echo $merchant_code_name; ?>" value="<?php echo $merchant_code_value; ?>" size="40" />
                        </label></td>
                </tr>

                <tr>
                    <td><?php _e("Callback URL:", 'menu-test' ); ?></td>
                    <td>
                        <label>
                            <input type="text" name="<?php echo $callback_url_name; ?>" placeholder="Enter hostname eg sasapay.co.ke" value="<?php echo $callback_url_name_value; ?>" size="40" />
                        </label></td>
                </tr>
                <tr>
                    <td><?php _e("Validation URL:", 'menu-test' ); ?></td>
                    <td>
                        <label>
                            <input type="text" name="<?php echo $validation_url_name; ?>" value="<?php echo $validation_url_name_value; ?>" size="40" />
                        </label></td>
                </tr>
                <tr>
                    <td><?php _e("Sandbox/Production URL:", 'menu-test' ); ?></td>
                    <td>
                        <label>
                            <input type="text" name="<?php echo $api_base_url_name; ?>" value="<?php echo $api_base_url_name_value; ?>" size="40" />
                        </label></td>
                </tr>

            </table>

            <hr />

            <h3>SasaPay Credentials</h3>
            <h5>Go to <a style="text-decoration:none" href="https://developers.sasapay.app/dashboard/applications"> SasaPay Developer portal</a> to get these credentials.</h5>
            <table cellspacing="2">
                <tr>
                    <td><?php _e("Application URL:", 'menu-test' ); ?></td>
                    <td>
                        <label>
                            <input type="text" name="<?php echo $application_url_name; ?>" value="<?php echo $application_url_value; ?>" size="40" />
                        </label>
                    </td>
                </tr>

                <tr>
                    <td><?php _e("Client Id:", 'menu-test' ); ?></td>
                    <td><label>
                            <input type="password" name="<?php echo $client_id_name; ?>" value="<?php echo $client_id_name_value; ?>" size="40" />
                        </label></td>
                </tr>

                <tr>
                    <td><?php _e("Client Secret:", 'menu-test' ); ?></td>
                    <td><label>
                            <input type="password" name="<?php echo $client_key_name; ?>" value="<?php echo $client_key_name_value; ?>" size="40" />
                        </label></td>
                </tr>

            </table>

            <hr/>

            <h3>SasaPay Default Configurations</h3>
            <h5>Go to <a style="text-decoration:none" href="https://developers.sasapay.app/dashboard/applications"> SasaPay Developer portal</a> to get these credentials.</h5>
            <table cellspacing="2">
               <tbody>
               <tr>
                   <td><?php _e("Default Network Code:", 'menu-test' ); ?></td>
                   <td>
                       <label>
                           <input type="text" name="<?php echo $network_code_name; ?>" value="<?php echo $network_code_value; ?>" size="40" />
                       </label>
                   </td>
               </tr>

               <tr>
                   <td><?php _e("Default Currency Code:", 'menu-test' ); ?></td>
                   <td><label>
                           <input type="text" name="<?php echo $currency_code_name; ?>" value="<?php echo $currency_code_value; ?>" size="40" />
                       </label></td>
               </tr>
               </tbody>

            </table>

            <p class="submit">
                <input type="hidden" name="submitted" value="1" />
                <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save') ?>" />
            </p>

        </form>
        </div>

        <?php
    }

}
