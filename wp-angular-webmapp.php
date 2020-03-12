<?php
/**
 * @wordpress-plugin
 * Plugin Name: Webmapp - Embedding Angular In Wordpress
 * Author:  Marco Baroncini
 * Version: 0.0.2
 * Plugin URI: https://cyber-way.com 
 * */

defined( 'ABSPATH' ) or die( 'Direct script access diallowed.' );

define( 'WpAngular_APPS_PATH', plugin_dir_path( __FILE__ ) . 'apps' );
define( 'WpAngular_INCLUDES', plugin_dir_path( __FILE__ ) . '/includes' );
define( 'WpAngular_URL', plugin_dir_url( __FILE__ ) );
define( 'WpAngular_APPS_URL', plugin_dir_url( __FILE__ ) .'apps');

define( 'WpAngular_SCRIPTS_HANDLER_KEY', 'wp-angular' );

require_once( WpAngular_INCLUDES . '/loader.php' );


new WpAngularAppLoader();
