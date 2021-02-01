<?php
/**
 * Plugin Name:     Currency Converter
 * Version:         0.1.0
 *
 * @package         currency_converter
 */


/**
 * shortcode for currency exchanger: [cryptoCurrencyConverter]
 */

define('CURRENCY_EXCHANGE_PLUGIN_DIR', dirname(__FILE__) . '/');
define('CURRENCY_EXCHANGE_PLUGIN_URL', plugins_url("",__FILE__) . '/');

include "admin/AdminSettings.php";
include "currency_server/CurrencyServerData.php";
include "logger/Logger.php";

if (!defined("ABSPATH")) {
	exit; // Exit if accessed directly
}

// Load plugin settings within the WP admin dashboard.
if (is_admin()) {
	$adminSettings = new AdminSettings();
	$adminSettings->load();
}

// create storage for logger on plugin activate
register_activation_hook(__FILE__, 'onPluginActivate');
function onPluginActivate()
{
	$logger = new Logger();
	$logger->create_storage();
}

// register JS & CSS
function register_script()
{
	// JS
	wp_enqueue_script('jquery');

	wp_register_script('currency_converter_popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js');
	wp_enqueue_script('currency_converter_popper');
	wp_register_script('currency_converter_bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js');
	wp_enqueue_script('currency_converter_bootstrap');
	wp_register_script('currency_converter_bootstrap-select', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js');
	wp_enqueue_script('currency_converter_bootstrap-select');

	// CSS
	wp_register_style('currency_converter_bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css');
	wp_enqueue_style('currency_converter_bootstrap');
	wp_register_style('currency_converter_bootstrap-select', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css');
	wp_enqueue_style('currency_converter_bootstrap-select');
	wp_register_style('currency_style',  plugins_url('/css/currency-style.css', __FILE__ ));
	wp_enqueue_style('currency_style');
}
add_action( 'wp_enqueue_scripts', 'register_script' );


/**
 * shortcode for currency exchanger. Usage: [cryptoCurrencyConverter]
 * @return false|string
 */
function currencyExchangeView_shortcode()
{
	ob_start();
	include(CURRENCY_EXCHANGE_PLUGIN_DIR . "/render/currencyConverterView.php");
	return ob_get_clean();
}
add_shortcode('cryptoCurrencyConverter', 'currencyExchangeView_shortcode');


/**
 * AJAX server side
 * returns exchange rate for AJAX call for AJAX parameters: "currency_symbol_from" "currency_symbol_to"
 */
function getRate()
{
	$currency_symbol_from = $_POST["currency_symbol_from"];
	$currency_symbol_to = $_POST["currency_symbol_to"];
	$log_it = $_POST["log_it"];
	if (empty($currency_symbol_from) || empty($currency_symbol_to)) {
		echo 0;
		wp_die();
	}

	$currencyServerData = new CurrencyServerData();
	$rate = $currencyServerData->getRate($currency_symbol_from, $currency_symbol_to);
	if ($log_it!="false") {
		Logger::log("convert from $currency_symbol_from to $currency_symbol_to");
	}

	echo $rate;
	wp_die();
}
add_action('wp_ajax_getRate', 'getRate');
add_action('wp_ajax_nopriv_getRate', 'getRate');


///**
// * returns JSON with currencies (not used in this version)"
// */
//function getCurrenciesList() {
//	$currencyServerData = new CurrencyServerData();
//	$currencyServerData->setCacheTime(9999);
//	$currencyList = $currencyServerData->getCurrenciesList();
//	echo json_encode($currencyList);
//	wp_die();
//}
//add_action( 'wp_ajax_getCurrenciesListt', 'getCurrenciesList' );
//add_action( 'wp_ajax_nopriv_getCurrenciesList', 'getCurrenciesList' );
