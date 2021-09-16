
<p align="center"><a href="https://collect.africa" target="_blank"><img src="https://github.com/shadrachodek/woo-collect/blob/master/assets/images/collect-hero.jpg" width="400"></a></p>


# Collect Payment Gateway Plugin for WooCommerce

**Contributors:** shadrachodek

**Tags:** collect, woocommerce, payment gateway, shadrachodek plugins, opay, barter, nigeria, naira, mastercard, visa

**Requires at least:** 4.7

**Tested up to:** 5.8

**Stable tag:** 1.0.0

**Requires PHP:** 5.6

**License:** GPLv2 or later

**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

Local payments in African markets made easy

## Description

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

## Why Collect?
* Collect offers the best pricing for local payment methods so your customers can enjoy low transaction fees when they pay with Collect.
* All your customer and transaction data in one simple and elegant data so you can understand your customers' behavior and make smart decisions.
* Responsive customer support
* Easy to understand and well-documented APIs to build your custom payment experiences

Sign up for a Collect Merchant account by clicking [here](https://collect.africa).


## Installation


### Automatic Installation
1. Login to your WordPress Admin area
2. Go to __Plugins__ > __Add New__ from the from the top left menu.
3. In the search box type __Collect Payment Gateway for WooCommerce__
4. From the search result you will see __Collect Payment Gateway for WooCommerce__ click on __Install Now__ to install the plugin
5. A popup window will ask you to confirm your wish to install the Plugin.
6. After installation, activate the plugin.
7. Open the settings page for WooCommerce and click the __Payments__ tab.
8. Click on the __Collect_ link from the available Checkout Options
9. Configure your __Collect Payment Gateway__ settings. See below for details.


### Manual Installation
1. Download the plugin zip file
2. Login to your WordPress Admin. Click on __Plugins__ > __Add New__ from the top left menu.
3. Click on the "Upload" option, then click "Choose File" to select the zip file from your computer. Once selected, press "OK" and press the "Install Now" button.
4. Activate the plugin.
5. Open the settings page for WooCommerce and click the "Payments" tab.
6. Click on the __Collect__ link from the available Checkout Options
7. Configure your __Collect Payment Gateway__ settings. See below for details.

### Configure the plugin
To configure the plugin, go to __WooCommerce > Settings__ from the left hand menu, then click __Checkout__ from the top tab. You will see __Collect__ as part of the available Checkout Options. Click on it to configure the payment gateway.
* Go to __WooCommerce  > Settings__ and click on the Payments tab
* You’ll see __Collect__ listed along with your other payment methods.
* Click __Set Up__ to configure the plugin. There is a selection of options on the screen. * * Read what each one does below.

* __Enable/Disable__ - check the box to enable Collect Payment Gateway.
* __Title__ - allows you to determine what your customers will see this payment option as on the checkout page.
* __Description__ - controls the message that appears under the payment fields on the checkout page. Here you can list the types of cards you accept.
* __Test Mode__ - Check to enable test mode. Test mode enables you to test payments before going live. If you ready to start receiving real payment on your site, kindly uncheck this.
* __Test Secret Key__ - Enter your Test Secret Key here. Get your API keys from your Collect account under Settings > Developer/API
* __Test Public Key__ - Enter your Test Public Key here. Get your API keys from your Collect account under Settings > Developer/API
* __Live Secret Key__ - Enter your Live Secret Key here. Get your API keys from your Collect account under Settings > Developer/API
* __Live Public Key__ - Enter your Live Public Key here. Get your API keys from your Collect account under Settings > Developer/API
* Click on __Save Changes__ for the changes you made to be effected.

Some account might be experiencing poor network connections, which can sometimes affect order status updates after a transaction, we strongly recommend that you set a [Webhook URL](https://app.collect.africa/settings/keys) on your Collect dashboard. This way, whenever a transaction is complete on your store, we’ll send a notification to the __Webhook URL__, which will update the order and mark it as paid. You can set this up by using the URL in red at the top of the Settings page. Just copy the URL and save it as your webhook URL on your Collect dashboard under __Settings__ > __API Keys & Webhooks__ tab.

If you do not find __Collect__ on the Payment method options, please go through the settings again and ensure that:

* You’ve checked the “Enable/Disable” checkbox
* You’ve entered your API Keys in the appropriate field
* You’ve clicked on Save Changes during setup


## Frequently Asked Questions


### What Do I Need To Use The Plugin

1.	You need to have WooCommerce plugin installed and activated on your WordPress site.
2.	You need to open a Collect merchant account on [Collect](https://collect.africa)


## Changelog

### 1.0.0 - August 23, 2021
* First release


## Screenshots ##

### 1. Collect WooCommerce Payment Gateway Setting Page
###
![Screenshot 1](https://github.com/shadrachodek/woo-collect/blob/master/assets/images/Screenshot_1.jpg)


### 2. Collect WooCommerce Payment Gateway on the checkout page
###
![Screenshot 2](https://github.com/shadrachodek/woo-collect/blob/master/assets/images/Screenshot_2.jpg)


### 3. Collect inline payment page
###
![Screenshot 3](https://github.com/shadrachodek/woo-collect/blob/master/assets/images/Screenshot_3.jpg)

