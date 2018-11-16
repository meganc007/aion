<?php
/**
 * Plugin Name
 *
 * @package     Aion
 * @author      RADD Creative
 * @copyright   2018 RADD Creative
 *
 * @wordpress-plugin
 * Plugin Name: Aion
 * Plugin URI:  https://raddcreative.com/
 * Description: Auto-updater for RADD Creative software.
 * Version:     0.1.0
 * Author:      RADD Creative
 * Author URI:  https://raddcreative.com/
 * Text Domain: aion
 */

if ( ! defined( 'ABSPATH' ) ) 
{
	die;
}

define( 'AION__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( AION__PLUGIN_DIR . '/classes/aionplugin.php' );
//require_once( AION__PLUGIN_DIR . '/classes/license.php' );
require_once( AION__PLUGIN_DIR . '/classes/updatePlugins.php' );

// wp_register_script( 'setup.js', plugin_dir_url( __FILE__ ) . 'js/setup.js', array('jquery'));
// wp_enqueue_script( 'setup.js' );


//The get_plugin_data() function was "undefined", so this if statement lets us access it
if( !function_exists('get_plugin_data') ){
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( !class_exists('WP_Upgrader') ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
}

if ( !class_exists('Plugin_Upgrader') ) {
	require_once( ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php' );
}

if( !function_exists('request_filesystem_credentials') ) {
    require_once ABSPATH  . 'wp-admin/includes/file.php';
}

if( !function_exists('show_message') ) {
    require_once ABSPATH  . 'wp-admin/includes/misc.php';
}

if ( class_exists('AionPlugin') ) {
	$aion = new AionPlugin();
}

if ( class_exists('updatePlugins') ) {
	$aion_update_plugins = new updatePlugins();
}


//activation 
register_activation_hook( __FILE__, array( $aion, 'activate' ) );

//deactivation 
register_deactivation_hook( __FILE__, array( $aion, 'deactivate' ) );

add_action('init', array( $aion_update_plugins, 'getJSON' ) );
