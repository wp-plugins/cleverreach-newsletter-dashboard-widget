<?php
/*
Plugin Name: CleverReach Report Dashboard Widget
Plugin URI: http://etzelstorfer.com/
Description: 
Version: 0.1
Author: haet webdevelopment
Author URI: http://etzelstorfer.com
License: GPLv2 or later
*/

/*  Copyright 2015 Hannes Etzelstorfer (email : hannes@etzelstorfer.com) */

define( 'HAET_CRD_PATH', plugin_dir_path(__FILE__) );
define( 'HAET_CRD_URL', plugin_dir_url(__FILE__) );
define( 'HAET_CRD_API_URL', 'http://api.cleverreach.com/soap/interface_v5.1.php?wsdl' );


require HAET_CRD_PATH . 'includes/class-haet-cleverreach-dashboard.php';

load_plugin_textdomain('haet_cleverreach_dashboard', false, dirname( plugin_basename( __FILE__ ) ) . '/translations' );



if (class_exists("haet_cleverreach_dashboard")) {
	$haet_cleverreach_dashboard = new haet_cleverreach_dashboard();
}

//Actions and Filters	
if (isset($haet_cleverreach_dashboard)) {
    add_action( 'wp_dashboard_setup', array(&$haet_cleverreach_dashboard, 'setup_widget'));
    add_action( 'admin_enqueue_scripts', array(&$haet_cleverreach_dashboard, 'admin_page_scripts_and_styles'));
    add_action( 'wp_ajax_cleverreach_dashboard_save_settings', array(&$haet_cleverreach_dashboard, 'save_settings') );
    add_action( 'wp_ajax_cleverreach_dashboard_get_chart_data', array(&$haet_cleverreach_dashboard, 'get_chart_data') );
}



function haet_cleverreach_dashboard_init(){
    if(!isset($haet_cleverreach_dashboard)) 
        $haet_cleverreach_dashboard = new haet_cleverreach_dashboard();
}
register_activation_hook( __FILE__, 'haet_cleverreach_dashboard_init');


function haet_cleverreach_dashboard_deactivate(){
    delete_option('haet_crd_api_key');
}
register_deactivation_hook( __FILE__, 'haet_cleverreach_dashboard_deactivate');



	

