=== Collect Payment Gateway Plugin for WooCommerce ===
Contributors: shadrachodek
Tags: collect, woocommerce, payment gateway, shadrachodek plugins, opay, barter, nigeria, naira, mastercard, visa, collect africa
Requires at least: 4.7
Tested up to: 5.8
Stable tag: 1.0.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Local payments in African markets made easy


== Description ==

Collect makes it easy for African businesses to accept payments from their customers through local payment methods. Integrate Collect today and let your customers pay you with their preferred payment method.

With the Collect WooCommerce Payment Gateway plugin, you will be able to accept the following payment methods in your shop:

* __Mastercard__
* __Visa__
* __Verve__
* __USSD__
* __Mobile Money__
* __Bank Transfer__
* __Opay__
* __Online Banking__
* __Barter__

== Why Collect? ==
* Collect offers the best pricing for local payment methods so your customers can enjoy low transaction fees when they pay with Collect.
* All your customer and transaction data in one simple and elegant data so you can understand your customers' behavior and make smart decisions.
* Responsive customer support
* Easy to understand and well-documented APIs to build your custom payment experiences

Sign up for a Collect Merchant account by clicking [here](https://collect.africa).

== Installation ==
1. Go to **WordPress Admin** > **Plugins** > **Add New** from the top left menu
2. In the search box type **Collect Payment Gateway for WooCommerce**
3. Install the plugin through the *Plugins* menu in WordPress.
3. Activate the plugin through the *Plugins* menu in WordPress.


= Configure the plugin =
* Go to **WooCommerce** > **Settings** and click on the **Payments** tab
* You’ll see **Collect** listed along with your other payment methods.
* Click **Set Up** to configure the plugin. There is a selection of options on the screen.
* Read what each one does below.


1. **Enable/Disable** – Check this checkbox to Enable Collect on your store’s checkout

2. **Title** – This will represent **Collect** on your list of Payment options during checkout. It guides users to know which option to select to pay with Collect. **Title** is set to “Collect” by default, but you can change it to suit your needs.

3. **Description** – This controls the message that appears under the payment
fields on the checkout page. Use this space to give more details to customers about what **Collect** is and what payment methods they can use with it.

4. **Test Mode** – Check this to enable test mode. When selected, the fields in step six will say “Test” instead of “Live.” Test mode enables you to test payments before going live. The orders process with test payment methods, no money is involved so there is no risk. You can uncheck this when your store is ready to accept real payments.

5. **Payment Option** – Select how Collect Checkout displays to your customers. A popup displays Collect Checkout on the same page, while Redirect will redirect your customer to make payment.

6. **API Keys** – The next two text boxes are for your Collect API keys, which you can get from your Collect Dashboard. If you enabled Test Mode in step four, then you’ll need to use your test API keys here. Otherwise, you can enter your live keys.

7. Click on **Save Changes** to update the settings.


Some account might be experiencing poor network connections, which can sometimes affect order status updates after a transaction, we strongly recommend that you set a [Webhook URL](https://app.collect.africa/settings/keys) on your Collect dashboard. This way, whenever a transaction is complete on your store, we’ll send a notification to the **Webhook URL**, which will update the order and mark it as paid. You can set this up by using the URL in red at the top of the Settings page. Just copy the URL and save it as your webhook URL on your Collect dashboard under **Settings** > **API Keys & Webhooks** tab.

If you do not find **Collect** on the Payment method options, please go through the settings again and ensure that:

* You’ve checked the “Enable/Disable” checkbox
* You’ve entered your API Keys in the appropriate field
* You’ve clicked on Save Changes during setup


== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

1.	You need to have WooCommerce plugin installed and activated on your WordPress site.
2.	You need to open a Collect merchant account on [Collect](https://collect.africa)


== Changelog ==

= 1.0.0 - August 23, 2021 =
*   First release

= 1.0.1 - September 29, 2021 =
*   Fix: Live URL not auto switch for live mode on payment redirect


== Screenshots ==

1. Collect WooCommerce Payment Gateway Setting Page

2. Collect WooCommerce Payment Gateway on the checkout page

3. Collect popup payment page