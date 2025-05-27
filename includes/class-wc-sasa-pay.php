<?php
session_start();
require_once "Lib/SasaPay/C2B.php";
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
class WC_SasaPay extends WC_Payment_Gateway
{
    public string $sign;
    public string $client_id;

    public string $client_secret;
    public bool $debug           = false;
    public bool $enable_c2b      = false;
    public bool $enable_reversal = false;

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {

        $this->id           = 'sasapay';
        $this->icon         = apply_filters('woocommerce_gateway_icon', plugins_url('assets/icon.png', __FILE__));
        $this->method_title = __('SasaPay', 'woocommerce');
        $this->has_fields   = true;

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title              = $this->get_option('title');
        $this->description        = $this->get_option('description');
        $this->instructions       = $this->get_option('instructions');
        $this->enable_for_methods = $this->get_option('enable_for_methods', array());
        $this->enable_for_virtual = $this->get_option('enable_for_virtual', 'yes') === 'yes';
        $this->sign               = $this->get_option('signature', md5(rand(12, 999)));
        $this->enable_reversal    = $this->get_option('enable_reversal', 'no') === 'yes';
        $this->enable_c2b         = $this->get_option('enable_c2b', 'no') === 'yes';
        $this->enable_bonga       = $this->get_option('enable_bonga', 'no') === 'yes';
        $this->debug              = $this->get_option('debug', 'no') === 'yes';
        $this->shortcode          = $this->get_option('shortcode');
        $this->type               = $this->get_option('type', 4);
        $this->env                = $this->get_option('env', 'sandbox');
        $this->client_id          = $this->get_option('client_id', '');
        $this->client_secret      = $this->get_option('client_secret', '');
        //actions
//        add_action( 'admin_notices', array( $this, 'admin_notices' ));
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ));
        add_action( 'wp_enqueue_scripts', array($this,'theme_enqueue_scripts'));
        //callback handler
        add_action( 'woocommerce_api_wc_sasapay', array( $this, 'verify_payment' ) );
        add_filter('wc_sasapay_settings', array($this, 'set_default_options'), 1, 1);
    }

    /**
     * @return void
     */
    public function theme_enqueue_scripts(): void
    {
        // all styles
        wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css', array(), 20141119 );
        // all scripts
        wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.6.1.js', array(), null, true );
    }

    /**
     * @return array
     */
    public function set_default_options(): array
    {
        return array(
            'env'        => $this->get_option('env', 'sandbox'),
        );
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $shipping_methods = array();
        foreach (WC()->shipping()->load_shipping_methods() as $method) {
            $shipping_methods[$method->id] = $method->get_method_title();
        }
        $this->debug       = $this->get_option('debug', 'no') === 'yes';
        $this->enable_c2b  = $this->get_option('enable_c2b', 'no') === 'yes';
        $this->form_fields = array(
            'enabled'            => array(
                'title'       => __('Enable/Disable', 'woocommerce'),
                'label'       => __('Enable ' . $this->method_title, 'woocommerce'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'yes',
            ),
            'title'              => array(
                'title'       => __('Method Title', 'woocommerce'),
                'type'        => 'text',
                'description' => __('Payment method name that the customer will see on your checkout.', 'woocommerce'),
                'default'     => __('SasaPay', 'woocommerce'),
                'desc_tip'    => true,
            ),
            'env'                => array(
                'title'       => __('Environment', 'woocommerce'),
                'type'        => 'select',
                'options'     => array(
                    'sandbox' => __('Sandbox', 'woocommerce'),
                    'live'    => __('Live', 'woocommerce'),
                ),
                'description' => __('M-Pesa Environment', 'woocommerce'),
                'desc_tip'    => true,
                'class'       => 'select2 wc-enhanced-select',
            ),
            'instructions'       => array(
                'title'       => __('Instructions', 'woocommerce'),
                'type'        => 'textarea',
                'description' => __('Instructions that will be added to the thank you page.', 'woocommerce'),
                'default'     => __('Thank you for shopping with us.', 'woocommerce'),
                'desc_tip'    => true,
            ),
            'completion'         => array(
                'title'       => __('Order Status on Payment', 'woocommerce'),
                'type'        => 'select',
                'options'     => array(
                    'completed'  => __('Mark order as completed', 'woocommerce'),
                    'on-hold'    => __('Mark order as on hold', 'woocommerce'),
                    'processing' => __('Mark order as processing', 'woocommerce'),
                ),
                'description' => __('What status to set the order after Mpesa payment has been received', 'woocommerce'),
                'desc_tip'    => true,
                'class'       => 'select2 wc-enhanced-select',
            ),
            'enable_for_methods' => array(
                'title'             => __('Enable for shipping methods', 'woocommerce'),
                'type'              => 'multiselect',
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 400px;',
                'default'           => '',
                'description'       => __('If M-Pesa is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woocommerce'),
                'options'           => $shipping_methods,
                'desc_tip'          => true,
                'custom_attributes' => array(
                    'data-placeholder' => __('Select shipping methods', 'woocommerce'),
                ),
            ),
            'enable_for_virtual' => array(
                'title'   => __('Accept for virtual orders', 'woocommerce'),
                'label'   => __('Accept M-Pesa if the order is virtual', 'woocommerce'),
                'type'    => 'checkbox',
                'default' => 'yes',
            ),
        );
    }

    /**
     *
     */
    public function payment_fields()
    {
        if ($description = $this->get_description()) {
            echo wpautop(wptexturize($description));
        }
        woocommerce_form_field(
            'billing_sasapay_phone',
            array(
                'type'        => 'tel',
                'class'       => array('form-row-wide', 'wc-sasapay-phone-field'),
                'label'       => 'Enter your SasaPay Phone Number',
                'label_class' => 'wc-sasapay-label',
                'placeholder' => 'Start typing...',
                'required'    => true,
            )
        );

    }

    /**
     *
     */
    public function validate_fields(): bool
    {
        if (empty($_POST['billing_sasapay_phone'])) {
            wc_add_notice('SasaPay phone number is required!', 'error');
            return false;
        }
        return true;
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id): array
    {
        try{
            //order
            $order = new WC_Order($order_id);
            //save order ID i session
            $_SESSION['order_id']=$order_id;
            //phone
            $phone = sanitize_text_field($_POST['billing_sasapay_phone'] ?? $order->get_billing_phone());
            $c2b   = new C2B();
            //register callbacks
            $c2b->register();
            //pay
            $res=$c2b->pay(
                1,
                $phone,
                $phone,
                'Buy online'
            );
            if ($res['status']) {
                $_SESSION['checkout_request_id']=$res['CheckoutRequestID'];
                $_SESSION['payment_gateway']=$res['PaymentGateway'];
                if ($res['PaymentGateway']=='SasaPay'){
                    return [
                        'result'   => 'success',
                        'redirect' => $order->get_checkout_payment_url( true )
                    ];
                }else{
                    $redirect_url = $this->get_return_url( $order );
                    return [
                        'result'   => 'success',
                        'redirect' => $redirect_url
                    ];
                }
            } else {
                wc_add_notice(__($res['message'], 'woocommerce'), 'error');
                return [
                    'result'=>'fail',
                    'redirect' => '',
                ];
            }
        } catch (Exception $exception) {
            return [
                'result'=>'fail',
                'redirect' => '',
            ];
        }
    }

    /**
     * @param $transaction
     * @return void
     */
    public function record_transaction($transaction): void
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $order_id=$_SESSION['order_id'];

        $table = $wpdb->prefix.'sasapay_transactions';
        $create_ddl="CREATE TABLE $table  (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            OrderID varchar(150) DEFAULT '' NULL,
            CustomerMobile varchar(150) DEFAULT '' NULL,
            TransactionDate datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            MerchantRequestID varchar(150) DEFAULT '' NULL,
            CheckoutRequestID varchar(150) DEFAULT '' NULL,
            ResultCode varchar(150) DEFAULT '' NULL,
            ResultDesc varchar(150) DEFAULT '' NULL,
            TransAmount varchar(100) NULL,
            BillRefNumber varchar(100) NULL,
            PRIMARY KEY  (id)
	    ) $charset_collate;";

        $data=[
            "OrderID"           =>$order_id,
            "MerchantRequestID" =>$transaction['MerchantRequestID'],
            "CheckoutRequestID" =>$transaction['CheckoutRequestID'],
            "ResultCode"        => $transaction['ResultCode'],
            "ResultDesc"        =>$transaction['ResultDesc'],
            "TransAmount"       =>$transaction['TransAmount'],
            "BillRefNumber"     =>$transaction['BillRefNumber'] ,
            "TransactionDate"   =>$transaction['TransactionDate'],
            "CustomerMobile"    =>$transaction['CustomerMobile']
        ];

        maybe_create_table( $table, $create_ddl );
        $format = array('%s','%d');
        $wpdb->insert($table,$data,$format);
        //complete order
        $order = wc_get_order( $order_id );
        $order->update_status( 'completed' );
        $order->reduce_order_stock();
        WC()->cart->empty_cart();
    }
    /**
     * @return void
     */
    public function verify_payment(): void
    {
        header( 'HTTP/1.1 201 OK' );
        try{
            $order_id=$_SESSION['order_id'];
            $request=json_decode(file_get_contents('php://input'), true);
            //check for response from the server
            if (array_key_exists('CheckoutRequestID',$request)){
                $this->record_transaction($request);
                exit();
            }
            $verification_code=$request['code'];
            //complete payment
            $c2b   = new C2B();
            $checkout_request_id=(string)$_SESSION['checkout_request_id'];
            //process
            $res=$c2b->complete_payment_process($checkout_request_id,$verification_code);

            $order = wc_get_order( $order_id );
            $redirect_url = $this->get_return_url( $order );
            //complete order request
            if ($res['status']) {
                //redirect
                header("Location: ".$redirect_url);
            }
            else{
                wp_die( "SasaPay IPN Request Failure. ".$res['detail'] );
            }
            die();
        } catch (Exception $exception) {
            wp_die( "SasaPay IPN Request Failure" );
        }
    }
    /**
     * Displays the payment page
     */
    public function receipt_page( $order ) {
        $order = wc_get_order( $order );
       ?>
            <div>

            </div>

        <div class="container height-100 d-flex justify-content-center align-items-center">
            <div class="position-relative">
                <div class="card p-2 text-center">
                    <h6>Please enter the one time verification code <br> to complete order payment</h6>
                    <div> <span>A code has been sent to</span> <small>********956</small> </div>
                    <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2">
                        <input class="m-2 text-center form-control rounded" type="text" id="first" maxlength="1" />
                        <input class="m-2 text-center form-control rounded" type="text" id="second" maxlength="1" />
                        <input class="m-2 text-center form-control rounded" type="text" id="third" maxlength="1" />
                        <input class="m-2 text-center form-control rounded" type="text" id="fourth" maxlength="1" />
                        <input class="m-2 text-center form-control rounded" type="text" id="fifth" maxlength="1" />
                        <input class="m-2 text-center form-control rounded" type="text" id="sixth" maxlength="1" /> </div>
                    <div class="mt-4"> <button id="validate-otp" class="btn btn-danger px-4 validate">Validate</button> </div>
                    <form id="otpForm" action="" method="post">
                        <input type="hidden" name="otp_confirmation" id="otp_confirmation" value="verification_code">
                    </form>
                </div>
            </div>
        </div>
        <style>
            .height-100 {
                height: 50vh
            }

            .card {
                width: 600px;
                border: none;
                height: 300px;
                box-shadow: 0px 5px 20px 0px #d2dae3;
                z-index: 1;
                display: flex;
                justify-content: center;
                align-items: center
            }

            .card h6 {
                color: #003860;
                font-size: 20px
            }

            .inputs input {
                width: 40px;
                height: 40px
            }

            input[type=number]::-webkit-inner-spin-button,
            input[type=number]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                margin: 0
            }

            .card-2 {
                background-color: #fff;
                padding: 10px;
                width: 350px;
                height: 100px;
                bottom: -50px;
                left: 20px;
                position: absolute;
                border-radius: 5px
            }

            .card-2 .content {
                margin-top: 50px
            }

            .card-2 .content a {
                color: #003860
            }

            .form-control:focus {
                box-shadow: none;
                border: 2px solid #003860
            }

            .validate {
                border-radius: 20px;
                height: 40px;
                background-color: #003860;
                border: 1px solid #003860;
                width: 140px
            }
        </style>
        <script>
            (function() {
                'use strict';
                let verification_code="";
                jQuery(document).ready(function() {
                    jQuery('#validate-otp').click(getVerificationCode);
                });
                function getVerificationCode()
                {
                    const inputs = document.querySelectorAll('#otp > *[id]');
                    for (let i = 0; i < inputs.length; i++) {
                        if (inputs[i].nodeType === 1) {
                            verification_code = verification_code + inputs[i].value;
                        }
                    }
                    if (inputs.length > 5) {
                        const code = document.getElementById("otp_confirmation").value = verification_code;
                        const url="<?php echo WC()->api_request_url('WC_SasaPay');?>";
                        (async () => {
                            const rawResponse = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({code})
                            });
                            const content = await rawResponse.json();

                            console.log(content);
                        })();
                    }
                }
            })();
        </script>
        <?php
    }
    /**
     * Handles admin notices
     *
     * @return void
     */
    public function admin_notices(): void
    {

        if ( 'no' == $this->enabled ) {
            return;
        }

        /**
         * Check if public key is provided
         */
        if ( ! $this->client_id || ! $this->client_secret ) {

            echo '<div class="error"><p>';
            echo sprintf(
                'Provide your SasaPay "Pay Button" client secret and client id <a href="%s">here</a> to be able to use the WooCommerce SasaPay Payment Gateway plugin.',
                admin_url( 'admin.php?page=wc-settings&tab=checkout&section=sasapay' )
            );
            echo '</p></div>';
            return;
        }

    }
}
