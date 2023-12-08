<?php
/**
 * Plugin Name: ShortPixel Critical CSS
 * Plugin URI: https://shortpixel.com/
 * Description: Use ShortPixel's Critical CSS web service to automatically generate the required CSS for the "above the fold" area and improve your website performance
 * Version: 1.0.1
 * Author: ShortPixel
 * GitHub Plugin URI: https://github.com/short-pixel-optimizer/shortpixel-critical-css
 * Primary Branch: main
 * Author URI: https://shortpixel.com
 * License: GPL3
 */

/**
 * Activation hooks
 */

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

use Dice\Dice;
use \ShortPixel\CriticalCSS\FileLog;
use ShortPixel\CriticalCSS\Settings\ApiKeyTools;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @return \ShortPixel\CriticalCSS
 * @alias SPCCSS()
 */
function shortpixel_critical_css() {
	return shortpixel_critical_css_container()->create( 'ShortPixel\CriticalCSS' );
}

function shortpixel_critical_css_container($env = 'prod' ) {
	static $container;
	if ( empty( $container ) ) {
		$container = new Dice();
		include __DIR__ . "/config_{$env}.php";
	}

	return $container;
}

/**
 * Init function shortcut
 */
function shortpixel_critical_css_init() {
	shortpixel_critical_css()->init();

	$settings = shortpixel_critical_css()->settings_manager->get_settings();
	if( empty($settings['ccss_spio_apikey_found_dismissed']) && !ApiKeyTools::getApiKey() && ApiKeyTools::getSPIOApiKey() ) {
		add_action( 'admin_notices', 'shortpixel_critical_css_spio_apikey_found' );
    }

    if(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && empty($settings['ccss_cron_disabled_notice_dismissed'])) {
		add_action( 'admin_notices', 'shortpixel_critical_css_cron_disabled_notice' );
	}
}

/**
 * API run function shortcut
 */
function shortpixel_critical_css_api_run() {
    return shortpixel_critical_css()->api_run(intval($_POST['queue_id']));
}
/**
 * API remove function shortcut
 */
function shortpixel_critical_css_api_remove() {
	return shortpixel_critical_css()->api_queue_remove(intval($_POST['queue_id']));
}

function shortpixel_critical_css_web_run() {
	return shortpixel_critical_css()->web_queue_run(intval($_POST['queue_id']));
}

function shortpixel_critical_css_web_remove() {
	return shortpixel_critical_css()->web_queue_remove(intval($_POST['queue_id']));
}

/**
 * Get CSS function shortcut
 */
function shortpixel_critical_css_get() {
    return shortpixel_critical_css()->get_ccss();
}

/**
 * Contact function shortcut
 */
function shortpixel_critical_css_contact() {
	return shortpixel_critical_css()->contact();
}

/**
 * Contact function shortcut
 */

function shortpixel_critical_css_usekey() {
	return shortpixel_critical_css()->use_spio_key();
}
function shortpixel_critical_css_getapikey() {
	return shortpixel_critical_css()->get_apikey();
}
function shortpixel_critical_css_updateapikey() {
	return shortpixel_critical_css()->update_apikey();
}
function shortpixel_critical_css_dismiss() {
	return shortpixel_critical_css()->dismiss_notification();
}

function shortpixel_critical_css_switch_theme() {
    return shortpixel_critical_css()->switch_theme();
}

function shortpixel_critical_css_force_web_check() {
	return shortpixel_critical_css()->force_web_check();
}
/**
 * Activate function shortcut
 */
function shortpixel_critical_css_activate() {
	shortpixel_critical_css()->init();
	shortpixel_critical_css()->activate();
}

/**
 * Deactivate function shortcut
 */
function shortpixel_critical_css_deactivate() {
	shortpixel_critical_css()->deactivate();
}

/**
 * Error for older php
 */
function shortpixel_critical_css_php_upgrade_notice() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Oops! %s requires a minimum PHP version of 5.4.0. Your current version is: %s. Please contact your host to upgrade.</p>
	</div>', $info['Name'], PHP_VERSION
		)
	);
}

/**
 * Error for WP CRON disabled
 */
function shortpixel_critical_css_cron_disabled_notice() {
    $info = get_plugin_data( __FILE__ );
    _e(
        sprintf(
            '
	<div class="spccss_notice error notice is-dismissible" data-dismissed-causer="ccss_cron_disabled_notice">
	    <div class="body-wrap">
            <div class="message-wrap" style="padding-bottom: 10px;">
	            <button style="float:right;margin: 2px 5px 0 20px;" class="button button-primary dismiss-button">Dismiss</button>
                <p>%s requires the WP Cron to be active, please check your wp-config.php file and remove the "DISABLE_WP_CRON" define. If you\'re using a server-side scheduled job to run the WP Cron, then you can safely ignore this message.</p>
	        </div>
        </div>
    </div>', $info['Name']
        )
    );
}

function shortpixel_critical_css_spio_apikey_found() {
	$info = get_plugin_data( __FILE__ );
	_e(
			'
	<div class="spccss_notice notice notice-warning is-dismissible" data-dismissed-causer="ccss_spio_apikey_found">
		<p>You already have a ShortPixel account for this website. Do you want to use ShortPixel Critical CSS with this account?</p>
		<p><button class="button button-primary" id="spccss_usekey">Use this account</button> <button class="button button-primary dismiss-button">Dismiss</button></p>
	</div>'
	);
}

/**
 * Error if vendors autoload is missing
 */
function shortpixel_critical_css_php_vendor_missing() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s is corrupted it seems, please re-install the plugin.</p>
	</div>', $info['Name']
		)
	);
}

function shortpixel_critical_css_generate_plugin_links($links)
{
    $in = '<a href="options-general.php?page=' . \ShortPixel\CriticalCSS::LANG_DOMAIN . '">' . __( 'Settings' ) . '</a>';
    array_unshift($links, $in);
    return $links;
}

function shortpixel_critical_css_admin_init() {
    shortpixel_critical_css()->first_install();
}

function shortpixel_critical_css_rewrite_disabled()
{
	?>
	<div class="error notice">
		<p><?php _e( 'The URL Rewrites are disabled. The ShortPixel CriticalCSS plugin may not work as intended. Please go to <a href="options-permalink.php">Permalinks Settings</a> and choose any option except Plain. If the Permalinks are not set to plain, you might need to check with your hosting or admin why the rewrites are not working.'  ); ?></p>
	</div>
	<?php
}

if ( empty($GLOBALS['wp_rewrite']) ) {
	$GLOBALS['wp_rewrite'] = new WP_Rewrite();
}
if ( !$GLOBALS['wp_rewrite']->using_mod_rewrite_permalinks() ) {
	add_action( 'admin_notices', 'shortpixel_critical_css_rewrite_disabled' );
}


if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
	add_action( 'admin_notices', 'shortpixel_critical_css_php_upgrade_notice' );
} else {
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		include_once __DIR__ . '/vendor/autoload.php';
        add_action( 'admin_init', 'shortpixel_critical_css_admin_init' );
		add_action( 'plugins_loaded', 'shortpixel_critical_css_init', 11 );
		add_action( 'wp_ajax_shortpixel_critical_css_web_run', 'shortpixel_critical_css_web_run' );
		add_action( 'wp_ajax_shortpixel_critical_css_web_remove', 'shortpixel_critical_css_web_remove' );
		add_action( 'wp_ajax_shortpixel_critical_css_api_run', 'shortpixel_critical_css_api_run' );
        add_action( 'wp_ajax_shortpixel_critical_css_api_remove', 'shortpixel_critical_css_api_remove' );
        add_action( 'wp_ajax_shortpixel_critical_css_get', 'shortpixel_critical_css_get' );
		add_action( 'wp_ajax_shortpixel_critical_css_contact', 'shortpixel_critical_css_contact' );
		add_action( 'wp_ajax_shortpixel_critical_css_usekey', 'shortpixel_critical_css_usekey' );
		add_action( 'wp_ajax_shortpixel_critical_css_getapikey', 'shortpixel_critical_css_getapikey' );
		add_action( 'wp_ajax_shortpixel_critical_css_updateapikey', 'shortpixel_critical_css_updateapikey' );
		add_action( 'wp_ajax_shortpixel_critical_css_dismiss', 'shortpixel_critical_css_dismiss' );
        add_action( 'switch_theme', 'shortpixel_critical_css_switch_theme');
		add_action( 'wp_ajax_shortpixel_critical_css_force_web_check', 'shortpixel_critical_css_force_web_check' );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'shortpixel_critical_css_generate_plugin_links' ); //for plugin settings page
        register_activation_hook( __FILE__, 'shortpixel_critical_css_activate' );
		register_deactivation_hook( __FILE__, 'shortpixel_critical_css_deactivate' );
	} else {
		add_action( 'admin_notices', 'shortpixel_critical_css_php_vendor_missing' );
	}

}

if ( !defined( 'SPCCSS_DEBUG' ) ) {
    define( 'SPCCSS_DEBUG', isset( $_GET[ 'SPCCSS_DEBUG' ] ) ? intval($_GET[ 'SPCCSS_DEBUG' ])
          : false);
        //: FileLog::DEBUG_AREA_ALL);
}
