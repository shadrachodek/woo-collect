<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}


class WC_Gateway_Collect extends WC_Payment_Gateway {

    /**
     * Is test mode active?
     *
     * @var bool
     */
    public $testmode;

    /**
     * Should orders be marked as complete after payment?
     *
     * @var bool
     */
    public $autocomplete_order;

    /**
     * Collect test public key.
     *
     * @var string
     */
    public $test_public_key;

    /**
     * Collect test secret key.
     *
     * @var string
     */
    public $test_secret_key;

    /**
     * Collect live public key.
     *
     * @var string
     */
    public $live_public_key;

    /**
     * Collect live secret key.
     *
     * @var string
     */
    public $live_secret_key;

    /**
     * Should the cancel & remove order button be removed on the pay for order page.
     *
     * @var bool
     */
    public $remove_cancel_order_button;


    /**
     * accept for virtual orders
     *
     * @var bool
     */
    public $enable_for_virtual;


    /**
     * Should custom metadata be enabled?
     *
     * @var bool
     */
    public $custom_metadata;


    /**
     * API public key
     *
     * @var string
     */
    public $public_key;

    /**
     * API secret key
     *
     * @var string
     */
    public $secret_key;

    /**
     * Add Instructions to the thank you page
     *
     * @var string
     */
    public $instructions;

    /**
     * Collect payment page type.
     *
     * @var string
     */
    public $payment_page;

    /**
     * Collect test url.
     *
     * @var string
     */
    public $test_url;

    /**
     * Collect live url.
     *
     * @var string
     */
    public $live_url;

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        // Setup general properties.
        $this->setup_properties();

        $this->supports = array(
            'products',
        );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();



        // Get settings.
        $this->title = $this->get_option('title');

        $this->description  = $this->get_option('description');
        $this->payment_page = $this->get_option( 'payment_page' );
        $this->instructions = $this->get_option('instructions');

        $this->enabled            = $this->get_option( 'enabled' );
        $this->testmode           = $this->get_option( 'testmode' ) === 'yes' ? true : false;
        $this->autocomplete_order = $this->get_option( 'autocomplete_order' ) === 'yes' ? true : false;


        $this->test_public_key = $this->get_option( 'test_public_key' );
        $this->test_secret_key = $this->get_option( 'test_secret_key' );

        $this->live_public_key = $this->get_option( 'live_public_key' );
        $this->live_secret_key = $this->get_option( 'live_secret_key' );

        $this->enable_for_methods = $this->get_option('enable_for_methods', array());
        $this->enable_for_virtual = $this->get_option('enable_for_virtual', 'yes') === 'yes';

        $this->public_key = $this->testmode ? $this->test_public_key : $this->live_public_key;
        $this->secret_key = $this->testmode ? $this->test_secret_key : $this->live_secret_key;

        $this->live_url = "https://api.collect.africa";
        $this->test_url = "https://devapi.collect.africa";

        // Hooks
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
        add_filter('woocommerce_payment_complete_order_status', array($this, 'change_payment_complete_order_status'), 10, 3);

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

        // Customer Emails.
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);

        // Payment callback handler API hook.
        add_action( 'woocommerce_api_wc_gateway_collect', array( $this, 'verify_collect_payment_transaction' ) );

        // Webhook listener/API hook.
        add_action( 'woocommerce_api_wc_collect_webhook', array( $this, 'process_webhooks' ) );

        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

    }

    /**
     * Setup general properties for the gateway.
     */
    protected function setup_properties()
    {
        $this->id = 'collect';
        $this->method_title = __('Collect', 'woo-collect');
        $this->method_description = sprintf( __( 'Collect platform is built to simplify and accelerate African payments. <a href="%1$s" target="_blank">Sign up</a> for a Collect account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'woo-collect' ), 'https://collect.africa', 'https://app.collect.africa/settings/keys' );
        $this->has_fields = false;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woo-collect'),
                'label' => __('Enable Collect', 'woo-collect'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', 'woo-collect'),
                'type' => 'text',
                'description' => __('Payment method description that the customer will see on your checkout.', 'woo-collect'),
                'default' => __('Collect', 'woo-collect'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'woo-collect'),
                'type' => 'textarea',
                'description' => __('Payment method description that the customer will see on your website.', 'woo-collect'),
                'default' => __('Local payments in African markets made easy.', 'woo-collect'),
                'desc_tip' => true,
            ),
            'instructions' => array(
                'title' => __('Instructions', 'woo-collect'),
                'type' => 'textarea',
                'description' => __('Instructions that will be added to the thank you page.', 'woo-collect'),
                'default' => __('Thank you for choosing Collect', 'woo-collect'),
                'desc_tip' => true,
            ),
            'enable_for_methods' => array(
                'title' => __('Enable for shipping methods', 'woo-collect'),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'default' => '',
                'description' => __('If Collect is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woo-collect'),
                'options' => $this->load_shipping_method_options(),
                'desc_tip' => true,
                'custom_attributes' => array(
                    'data-placeholder' => __('Select shipping methods', 'woo-collect'),
                ),
            ),
            'enable_for_virtual' => array(
                'title' => __('Accept for virtual orders', 'woo-collect'),
                'label' => __('Accept Collect if the order is virtual', 'woo-collect'),
                'type' => 'checkbox',
                'default' => 'yes',
            ),
            'testmode' => array(
                'title' => __('Test mode', 'woo-collect'),
                'label' => __('Enable Test Mode', 'woo-collect'),
                'type' => 'checkbox',
                'description' => __('Test mode enables you to test payments before going live. <br />
Once the LIVE MODE is enabled on your Collect account uncheck this.', 'woo-collect'),
                'default' => 'yes',
                'desc_tip' => true,
            ),
            'payment_page'    => array(
                'title'       => __( 'Payment Option', 'woo-collect' ),
                'type'        => 'select',
                'description' => __( 'Popup shows the payment popup on the page while Redirect will redirect the customer to Collect to make payment.', 'woo-collect' ),
                'default'     => '',
                'desc_tip'    => false,
                'options'     => array(
                    ''          => __( 'Select One', 'woo-collect' ),
                    'inline'    => __( 'Popup', 'woo-collect' ),
                    'redirect'  => __( 'Redirect', 'woo-collect' ),
                ),
            ),
            'test_secret_key' => array(
                'title' => __('Test Secret Key', 'woo-collect'),
                'type' => 'text',
                'description' => __('Enter your Test Secret Key here', 'woo-collect'),
                'default' => '',
            ),
            'test_public_key' => array(
                'title' => __('Test Public Key', 'woo-collect'),
                'type' => 'text',
                'description' => __('Enter your Test Public Key here.', 'woo-collect'),
                'default' => '',
            ),
            'live_secret_key' => array(
                'title' => __('Live Secret Key', 'woo-collect'),
                'type' => 'text',
                'description' => __('Enter your Live Secret Key here.', 'woo-collect'),
                'default' => '',
            ),
            'live_public_key' => array(
                'title' => __('Live Public Key', 'woo-collect'),
                'type' => 'text',
                'description' => __('Enter your Live Public Key here.', 'woo-collect'),
                'default' => '',
            ),
            'autocomplete_order'               => array(
                'title'       => __( 'Autocomplete Order After Payment', 'woo-collect' ),
                'label'       => __( 'Autocomplete Order', 'woo-collect' ),
                'type'        => 'checkbox',
                'class'       => 'wc-collect-autocomplete-order',
                'description' => __( 'If enabled, the order will be marked as complete after successful payment', 'woo-collect' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Display collect payment icon.
     */
    public function get_icon() {

        $icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/collect-payment.png', COLLECT_MAIN_FILE ) ) . '" alt="Collect Payment" />';

        return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

    }

    /**
     * Check If The Gateway Is Available For Use.
     *
     * @return bool
     */
    public function is_available()
    {
        if ( 'yes' !== $this->enabled ) {
            return false;
        }

        if ( ! ( $this->public_key && $this->secret_key ) ) {

            return false;

        }

        $order = null;
        $needs_shipping = false;

        // Test if shipping is needed first.
        if (WC()->cart && WC()->cart->needs_shipping()) {
            $needs_shipping = true;
        } elseif (is_page(wc_get_page_id('checkout')) && 0 < get_query_var('order-pay')) {
            $order_id = absint(get_query_var('order-pay'));
            $order = wc_get_order($order_id);

            // Test if order needs shipping.
            if ($order && 0 < count($order->get_items())) {
                foreach ($order->get_items() as $item) {
                    $_product = $item->get_product();
                    if ($_product && $_product->needs_shipping()) {
                        $needs_shipping = true;
                        break;
                    }
                }
            }
        }

        $needs_shipping = apply_filters('woocommerce_cart_needs_shipping', $needs_shipping);

        // Virtual order, with virtual disabled.
        if (!$this->enable_for_virtual && !$needs_shipping) {
            return false;
        }

        // Only apply if all packages are being shipped via chosen method, or order is virtual.
        if (!empty($this->enable_for_methods) && $needs_shipping) {
            $order_shipping_items = is_object($order) ? $order->get_shipping_methods() : false;
            $chosen_shipping_methods_session = WC()->session->get('chosen_shipping_methods');

            if ($order_shipping_items) {
                $canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids($order_shipping_items);
            } else {
                $canonical_rate_ids = $this->get_canonical_package_rate_ids($chosen_shipping_methods_session);
            }

            if (! count( $this->get_matching_rates( $canonical_rate_ids ) )) {
                return false;
            }
        }

        return parent::is_available();
    }

    /**
     * Checks to see whether or not the admin settings are being accessed by the current request.
     *
     * @return bool
     */
    private function is_accessing_settings()
    {
        if (is_admin()) {
            // phpcs:disable WordPress.Security.NonceVerification
            if (!isset($_REQUEST['page']) || 'wc-settings' !== $_REQUEST['page']) {
                return false;
            }
            if (!isset($_REQUEST['tab']) || 'checkout' !== $_REQUEST['tab']) {
                return false;
            }
            if (!isset($_REQUEST['section']) || 'collect' !== $_REQUEST['section']) {
                return false;
            }
            // phpcs:enable WordPress.Security.NonceVerification

            return true;
        }

        return false;
    }

    /**
     * Payment form on checkout page
     */
    public function payment_fields() {

        if ( $this->description ) {
            echo wpautop( wptexturize( $this->description ) );
        }

        if ( ! is_ssl() ) {
            return;
        }

    }

    /**
     * Load admin scripts.
     */
    public function admin_scripts() {

        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        if ( 'woocommerce_page_wc-settings' !== $screen_id ) {
            return;
        }

        wp_enqueue_script( 'wc_collect_admin', plugins_url('/assets/js/collect-admin.js', COLLECT_MAIN_FILE), array(), COLLECT_VERSION, true );


    }

    public function admin_options() {
        ?>

        <h2><?php _e( 'Collect', 'woo-collect' ); ?>
            <?php
            if ( function_exists( 'wc_back_link' ) ) {
                wc_back_link( __( 'Return to payments', 'woo-collect' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
            }
            ?>
        </h2>

        <h4>
            <strong><?php printf( __( 'Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>', 'woo-collect' ), 'https://app.collect.africa/settings/keys', WC()->api_request_url( 'WC_Collect_Webhook' ) ); ?></strong>
        </h4>

        <?php

        echo '<table class="form-table">';
        $this->generate_settings_html( $this->get_form_fields() );
        echo '</table>';

    }

    /**
     * Outputs scripts used for collect payment.
     */
    public function payment_scripts() {

        if ( ! is_checkout_pay_page() ) {
            return;
        }

        if ( $this->enabled === 'no' ) {
            return;
        }

        $order_key = sanitize_key( $_GET['key'] );

        $order_id  = absint( get_query_var( 'order-pay' ) );

        $order = wc_get_order( $order_id );

        $payment_method = method_exists( $order, 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;

        if ( $this->id !== $payment_method ) {
            return;
        }


        wp_enqueue_script( 'jquery' );

        wp_enqueue_script( 'collect', 'https://cdn.collect.africa/collect-widget.js', '', COLLECT_VERSION, true );
        wp_enqueue_script( 'wc_collect', plugins_url('/assets/js/collect.js', COLLECT_MAIN_FILE), array('jquery', 'collect'), COLLECT_VERSION, true );

        $params = array(
            'key' => $this->public_key,
        );

        if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

            $email         = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
            $first_name         = method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
            $last_name       = method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;
            $amount        = $order->get_total() * 100;
            $txnref        = $order_id . '_' . time();
            $callback_url = WC()->api_request_url( 'WC_Gateway_Collect' );
            $the_order_id  = method_exists( $order, 'get_id' ) ?: $order->get_id();
            $the_order_key = method_exists( $order, 'get_order_key' ) ? $order->get_order_key() : $order->order_key;
            $currency      = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->order_currency;


            if ( $the_order_id == $order_id && strtolower($the_order_key) == $order_key ) {

                $params['email']      = $email;
                $params['first_name'] = $first_name;
                $params['last_name']  = $last_name;
                $params['amount']     = $amount;
                $params['txnref']     = $txnref;
                $params['currency']   = $currency;
                $params['callback']   = $callback_url;

            }



            update_post_meta( $order_id, '_collect_txn_ref', $txnref );

        }

        wp_localize_script( 'wc_collect', 'wc_collect_params', $params );

    }

    /**
     * Check if Collect merchant details is filled.
     */
    public function admin_notices() {

        if ( $this->enabled == 'no' ) {
            return;
        }

        // Check required fields.
        if ( ! ( $this->public_key && $this->secret_key ) ) {
            echo '<div class="error"><p>' . sprintf( __( 'Please enter your Collect merchant details <a href="%s">here</a> to be able to use the Collect WooCommerce plugin.', 'woo-collect' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=collect' ) ) . '</p></div>';
            return;
        }

    }


    /**
     * Loads all of the shipping method options for the enable_for_methods field.
     *
     * @return array
     */
    private function load_shipping_method_options()
    {
        // Since this is expensive, we only want to do it if we're actually on the settings page.
        if (!$this->is_accessing_settings()) {
            return array();
        }

        $data_store = WC_Data_Store::load('shipping-zone');
        $raw_zones = $data_store->get_zones();

        foreach ($raw_zones as $raw_zone) {
            $zones[] = new WC_Shipping_Zone($raw_zone);
        }

        $zones[] = new WC_Shipping_Zone(0);

        $options = array();
        foreach (WC()->shipping()->load_shipping_methods() as $method) {

            $options[$method->get_method_title()] = array();

            // Translators: %1$s shipping method name.
            $options[$method->get_method_title()][$method->id] = sprintf(__('Any &quot;%1$s&quot; method', 'woocommerce'), $method->get_method_title());

            foreach ($zones as $zone) {

                $shipping_method_instances = $zone->get_shipping_methods();

                foreach ($shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance) {

                    if ($shipping_method_instance->id !== $method->id) {
                        continue;
                    }

                    $option_id = $shipping_method_instance->get_rate_id();

                    // Translators: %1$s shipping method title, %2$s shipping method id.
                    $option_instance_title = sprintf(__('%1$s (#%2$s)', 'woocommerce'), $shipping_method_instance->get_title(), $shipping_method_instance_id);

                    // Translators: %1$s zone name, %2$s shipping method instance name.
                    $option_title = sprintf(__('%1$s &ndash; %2$s', 'woocommerce'), $zone->get_id() ? $zone->get_zone_name() : __('Other locations', 'woocommerce'), $option_instance_title);

                    $options[$method->get_method_title()][$option_id] = $option_title;
                }
            }
        }

        return $options;
    }

    /**
     * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
     *
     * @param array $order_shipping_items Array of WC_Order_Item_Shipping objects.
     * @return array $canonical_rate_ids    Rate IDs in a canonical format.
     * @since  3.4.0
     *
     */
    private function get_canonical_order_shipping_item_rate_ids($order_shipping_items)
    {

        $canonical_rate_ids = array();

        foreach ($order_shipping_items as $order_shipping_item) {
            $canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
        }

        return $canonical_rate_ids;
    }

    /**
     * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
     *
     * @param array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
     * @return array $canonical_rate_ids  Rate IDs in a canonical format.
     * @since  3.4.0
     *
     */
    private function get_canonical_package_rate_ids($chosen_package_rate_ids)
    {

        $shipping_packages = WC()->shipping()->get_packages();
        $canonical_rate_ids = array();

        if (!empty($chosen_package_rate_ids) && is_array($chosen_package_rate_ids)) {
            foreach ($chosen_package_rate_ids as $package_key => $chosen_package_rate_id) {
                if (!empty($shipping_packages[$package_key]['rates'][$chosen_package_rate_id])) {
                    $chosen_rate = $shipping_packages[$package_key]['rates'][$chosen_package_rate_id];
                    $canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
                }
            }
        }

        return $canonical_rate_ids;
    }

    /**
     * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
     *
     * @param array $rate_ids Rate ids to check.
     * @return boolean
     * @since  3.4.0
     *
     */
    private function get_matching_rates($rate_ids)
    {
        // First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
        return array_unique(array_merge(array_intersect($this->enable_for_methods, $rate_ids), array_intersect($this->enable_for_methods, array_unique(array_map('wc_get_string_before_colon', $rate_ids)))));
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment($order_id)
    {

        if ( 'redirect' === $this->payment_page ) {
            return $this->collect_payment_processing($order_id);
        }

        $order = wc_get_order( $order_id );

        return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url( true ),
        );
    }

    /**
     * Process a redirect payment option payment.
     *
     * @param int $order_id
     * @return array|void
     * @since 5.7
     */
    public function collect_payment_processing($order_id) {

        $order        = wc_get_order( $order_id );
        $email        = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
        $amount       = $order->get_total() * 100;
        $txnref       = $order_id . '_' . time();
        $callback_url = WC()->api_request_url( 'WC_Gateway_Collect' );

        update_post_meta( $order_id, '_collect_txn_ref', $txnref );
        $headers = array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->secret_key,
        );

        $body = array(
            'email' => $email,
            'amount' => $amount,
            'reference' => $txnref,
            'callback_url' => $callback_url
        );

        $args = array(
            'body'    => json_encode($body),
            'headers' => $headers,
            'timeout' => 60,
        );

        // get payment initialize url
        $collect_url = $this->testmode ? $this->test_url : $this->live_url;
        $collect_url = $collect_url . '/payments/initialize';

        $request = wp_remote_post($collect_url, $args);
        error_log(print_r(wp_remote_retrieve_response_code( $request ) , true));
        if ( !is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
            $response = json_decode( wp_remote_retrieve_body( $request ) );
            return array(
                'result'   => 'success',
                'redirect' => $response->data->checkout_url,
            );

        } else {
            wc_add_notice(__('Unable to process payment try again', 'woo-collect'), 'error');

            return;
        }


    }

    /**
     * Displays the payment page.
     * @param $order_id
     */
    public function receipt_page( $order_id ) {

        $order = wc_get_order( $order_id );

        echo '<div id="wc-collect-form">';

        echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Collect.', 'woo-collect' ) . '</p>';

        echo '<div id="collect_form">';
        echo '<form method="post">';
        echo '<button type="submit" id="collect-payment-button">' . __( 'Pay Now', 'woo-collect' ) . '</button>';
        if ( ! $this->remove_cancel_order_button ) {
            echo '  <a class="button cancel" id="collect-cancel-payment-button" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woo-collect' ) . '</a>';
        }
        echo '</form>';



        echo '</div>';

    }


    /**
     * Output for the order received page.
     */
    public function thankyou_page()
    {
        if ($this->instructions) {
            echo wp_kses_post(wpautop(wptexturize($this->instructions)));
        }
    }

    /**
     * Change payment complete order status to completed for COD orders.
     *
     * @param string $status Current order status.
     * @param int $order_id Order ID.
     * @param WC_Order|false $order Order object.
     * @return string
     * @since  3.1.0
     */
    public function change_payment_complete_order_status($status, $order_id = 0, $order = false)
    {
        if ($order && 'collect' === $order->get_payment_method()) {
            $status = 'completed';
        }
        return $status;
    }

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order Order object.
     * @param bool $sent_to_admin Sent to admin.
     * @param bool $plain_text Email format: plain text or HTML.
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && !$sent_to_admin && $this->id === $order->get_payment_method()) {
            echo wp_kses_post(wpautop(wptexturize($this->instructions)) . PHP_EOL);
        }
    }

    public function verify_collect_payment_transaction()
    {
        if ( isset( $_REQUEST['reference'] ) ) {
            $collect_reference = sanitize_text_field( $_REQUEST['reference'] );
        } else {
            $collect_reference = false;
        }

        @ob_clean();

        if ($collect_reference) {
            
            $collect_url = $this->testmode ? $this->test_url : $this->live_url;
            $collect_get_payment_url = $collect_url . '/payments/' . $collect_reference;

            $headers = array(
                'Authorization' => 'Bearer ' . $this->secret_key,
            );

            $args = array(
                'headers' => $headers,
                'timeout' => 60,
            );

            $request = wp_remote_get( $collect_get_payment_url, $args );
            if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

                $response = json_decode( wp_remote_retrieve_body( $request ) );

                if ( 'success' == $response->data->status ) {

                    $order_reference_detail = explode( '_', $response->data->reference );
                    $order_id      = (int) $order_reference_detail[0];
                    $order         = wc_get_order( $order_id );

                    if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

                        wp_redirect( $this->get_return_url( $order ) );

                        exit;

                    }

                    $order_total      = $order->get_total();
                    $order_currency   = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
                    $currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
                    $amount_paid      = $response->data->amount / 100;
                    $payment_currency = strtoupper( $response->data->currency );
                    $gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );
                    $collect_reference     = $response->data->reference;

                    // check if the amount paid is equal to the order amount.
                    if ( $amount_paid < $order_total ) {

                        $order->update_status( 'on-hold', '' );

                        add_post_meta( $order_id, '_transaction_id', $collect_reference, true );

                        $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-collect' ), '<br />', '<br />', '<br />' );
                        $notice_type = 'notice';

                        // Add Customer Order Note
                        $order->add_order_note( $notice, 1 );

                        // Add Admin Order Note
                        $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Collect Transaction Reference:</strong> %9$s', 'woo-collect' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $collect_reference );
                        $order->add_order_note( $admin_order_note );

                        function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                        wc_add_notice( $notice, $notice_type );

                    } else {

                        if ( $payment_currency !== $order_currency ) {

                            $order->update_status( 'on-hold', '' );

                            update_post_meta( $order_id, '_transaction_id', $collect_reference );

                            $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-collect' ), '<br />', '<br />', '<br />' );
                            $notice_type = 'notice';

                            // Add Customer Order Note
                            $order->add_order_note( $notice, 1 );

                            // Add Admin Order Note
                            $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Collect Transaction Reference:</strong> %9$s', 'woo-collect' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $collect_reference );
                            $order->add_order_note( $admin_order_note );

                            function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                            wc_add_notice( $notice, $notice_type );

                        } else {

                            $order->payment_complete( $collect_reference );
                            $order->add_order_note( sprintf( __( 'Payment via Collect successful (Transaction Reference: %s)', 'woo-collect' ), $collect_reference ) );

                            if ( $this->is_autocomplete_order_enabled( $order ) ) {
                                $order->update_status( 'completed' );
                            }
                        }
                    }

                    WC()->cart->empty_cart();

                } else {

                    $order_details = explode( '_', sanitize_text_field( $_REQUEST['reference'] ) );

                    $order_id = (int) $order_details[0];

                    $order = wc_get_order( $order_id );

                    $order->update_status( 'failed', __( 'Payment was declined by Collect.', 'woo-collect' ) );

                }
            }

            wp_redirect( $this->get_return_url( $order ) );

            exit;
        }

        wp_redirect( wc_get_page_permalink( 'cart' ) );

        exit;

    }

    public function process_webhooks()
    {
        if ( ( strtoupper( $_SERVER['REQUEST_METHOD'] ) != 'POST' ) || ! array_key_exists( 'HTTP_X_COLLECT_SIGNATURE', $_SERVER ) ) {
            exit;
        }

        $json = file_get_contents('php://input');

        // validate event do all at once to avoid timing attack.
        if ( $_SERVER['HTTP_X_COLLECT_SIGNATURE'] !== hash_hmac( 'sha512', $json, $this->secret_key ) ) {
            exit;
        }

        $event = json_decode($json);

        if ('payment.success' == $event->event) {

            sleep(10);

            $order_reference_detail = explode('_', $event->payment->reference);

            $order_id = (int) $order_reference_detail[0];

            $order = wc_get_order( $order_id );

            $collect_txn_ref = get_post_meta( $order_id, '_collect_txn_ref', true );


            if ($event->payment->reference != $collect_txn_ref) {
                exit;
            }

            http_response_code(200);

            if (in_array($order->get_status(), array('processing', 'completed', 'on-hold'))) {
                exit;
            }

            $order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : $order->get_order_currency();

            $currency_symbol = get_woocommerce_currency_symbol($order_currency);

            $order_total = $order->get_total();

            $amount_paid = $event->payment->amount / 100;

            $collect_reference = $event->payment->reference;

            $payment_currency = strtoupper($event->payment->currency);

            $gateway_symbol = get_woocommerce_currency_symbol($payment_currency);

            // check if the amount paid is equal to the order amount.
            if ($amount_paid < $order_total) {

                $order->update_status('on-hold', '');

                add_post_meta($order_id, '_transaction_id', $collect_reference, true);

                $notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-collect'), '<br />', '<br />', '<br />');
                $notice_type = 'notice';

                // Add Customer Order Note.
                $order->add_order_note($notice, 1);

                // Add Admin Order Note.
                $admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Collect Transaction Reference:</strong> %9$s', 'woo-collect'), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $collect_reference);
                $order->add_order_note($admin_order_note);

                function_exists('wc_reduce_stock_levels') ? wc_reduce_stock_levels($order_id) : $order->reduce_order_stock();

                wc_add_notice($notice, $notice_type);

                WC()->cart->empty_cart();
            } else {

                if ($payment_currency !== $order_currency) {

                    $order->update_status('on-hold', '');

                    update_post_meta($order_id, '_transaction_id', $collect_reference);

                    $notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-collect'), '<br />', '<br />', '<br />');
                    $notice_type = 'notice';

                    // Add Customer Order Note.
                    $order->add_order_note($notice, 1);

                    // Add Admin Order Note.
                    $admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Collect Transaction Reference:</strong> %9$s', 'woo-collect'), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $collect_reference);
                    $order->add_order_note($admin_order_note);

                    function_exists('wc_reduce_stock_levels') ? wc_reduce_stock_levels($order_id) : $order->reduce_order_stock();

                    wc_add_notice($notice, $notice_type);
                } else {

                    $order->payment_complete($collect_reference);

                    $order->add_order_note(sprintf(__('Payment via Collect successful (Transaction Reference: %s)', 'woo-collect'), $collect_reference));

                    WC()->cart->empty_cart();

                    if ($this->is_autocomplete_order_enabled($order)) {
                        $order->update_status('completed');
                    }
                }
            }

            exit;
        }

        exit;
    }


    /**
     * Checks if autocomplete order is enabled for the payment method.
     *
     * @since 5.7
     * @param WC_Order $order Order object.
     * @return bool
     */
    protected function is_autocomplete_order_enabled( $order ) {
        $autocomplete_order = false;

        $payment_method = $order->get_payment_method();

        $settings = get_option('woocommerce_' . $payment_method . '_settings');

        if ( isset( $settings['autocomplete_order'] ) && 'yes' === $settings['autocomplete_order'] ) {
            $autocomplete_order = true;
        }

        return $autocomplete_order;
    }









}

