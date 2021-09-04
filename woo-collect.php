<?php
/**
 * Plugin Name: Collect Payment Gateway for WooCommerce
 * Plugin URI: https://www.collect.africa
 * Author: Shadrach Odekhiran
 * Author URI: https://shadrachodek.com
 * Description: WooCommerce payment gateway for Collect
 * Version: 1.0.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: woo-collect
 * WC requires at least: 3.0.0
 * WC tested up to: 5.1
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!defined('COLLECT_MAIN_FILE')) {
    define('COLLECT_MAIN_FILE', __FILE__);
}

if (!defined('COLLECT_VERSION')) {
    define('COLLECT_VERSION', '1.0.0');
}


if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

add_action('plugins_loaded', 'collect_payment_init', 11);


function collect_payment_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        add_action('admin_notices', 'collect_missing_notice');
        return;
    }

    add_action('admin_notices', 'collect_testmode_notice');

    require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-collect.php';

    add_filter('woocommerce_payment_gateways', 'add_collect_payment_gateway', 99);

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'collect_plugin_action_links');
}


function add_collect_payment_gateway( $gateway )
{
    $gateway[] = 'WC_Gateway_Collect';

    return $gateway;
}

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function collect_plugin_action_links($links)
{

    $settings_link = array(
        'settings' => '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=collect') . '" title="' . __('View Collect WooCommerce Settings', 'woo-collect') . '">' . __('Settings', 'woo-collect') . '</a>',
    );

    return array_merge($settings_link, $links);
}


/**
 * Display a notice if WooCommerce is not installed
 */
function collect_missing_notice()
{
    echo '<div class="error"><p><strong>' . sprintf(__('Collect requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'woo-collect'), '<a href="' . admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539') . '" class="thickbox open-plugin-details-modal">here</a>') . '</strong></p></div>';
}

/**
 * Display the test mode notice.
 **/
function collect_testmode_notice()
{

    if (!current_user_can('manage_options')) {
        return;
    }

    $collect_settings = get_option('woocommerce_collect_settings');
    $test_mode         = isset($collect_settings['testmode']) ? $collect_settings['testmode'] : '';

    if ('yes' === $test_mode) {
        /* translators: 1. Collect settings page URL link. */
        echo '<div class="error"><p>' . sprintf(__('Collect test-mode is still enabled, Click <strong><a href="%s">here</a></strong> to disable it when you want to start accepting live payment on your site.', 'woo-collect'), esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=collect'))) . '</p></div>';
    }
}
