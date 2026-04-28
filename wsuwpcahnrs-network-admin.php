<?php 
/**
 * Plugin Name:     WSUWP Multisite Info
 * Description:     Displays information about CAHRNS websites in a multisite installation.
 * Version:         1.4.0
 * Author:          CAHNRS Communications
 * Author URI:      https://cahnrs.wsu.edu/
 * Text Domain:     wsuwp-multisite-info
 */

 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//Define the version of this WSUWP Multisite Info plugin
define( 'WSUWPMULTISITEINFOVERSION', '1.4.0' );

//Load other files of this plugin
function wsuwp_multisite_info_init(){
	require_once __DIR__ . '/includes/plugin.php';
}

add_action( 'plugins_loaded', 'wsuwp_multisite_info_init' );