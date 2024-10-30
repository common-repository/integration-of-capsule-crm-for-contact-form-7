<?php

/**
 * Plugin Name: Contact Form 7 - Capsule CRM - Integration Pro
 * Plugin URI: https://wisersteps.com/plugin/contact-form-7-capsule-crm-integration/
 * Description: Contact Form 7 - Capsule CRM Integration allows you to connect your Contact Form 7 to Capsule CRM, Add/Update Person/Organization, Opportunity and Cases and connect them to each other.
 * Version: 1.0.5
 * Author: WiserSteps
 * Author URI: https://www.wisersteps.com
 * Developer: Omar Kasem
 * Developer URI: https://www.wisersteps.com
 * Text Domain: contact-form-7-capsule-crm
 * Domain Path: /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
// Require Contact form 7
add_action( 'admin_init', 'cfcc_require_cf7' );
function cfcc_require_cf7()
{
    
    if ( !in_array( 'contact-form-7/wp-contact-form-7.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        add_action( 'admin_notices', function () {
            echo  '<div class="error"><p>Sorry, This Addon Requires Contact form 7 to be installed and activated.</p></div>' ;
        } );
        deactivate_plugins( plugin_basename( __FILE__ ) );
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }

}

// Define Name & Version
define( 'CFCC_CAPSULE_CRM_DOMAIN', 'contact-form-7-capsule-crm' );
define( 'CFCC_CAPSULE_CRM_VERSION', '1.0.4' );
define( 'CFCC_CAPSULE_CRM_LOG_FILE', __DIR__ . '/logs/cfcc_capsule_crm.log' );
define( 'CFCC_CAPSULE_CRM_PATH_DIR', __DIR__ );
// Require Main Files
require plugin_dir_path( __FILE__ ) . 'app/class-app.php';
require plugin_dir_path( __FILE__ ) . 'app/class-helper.php';
new CFCC_CAPSULE_CRM\App( CFCC_CAPSULE_CRM_DOMAIN, CFCC_CAPSULE_CRM_VERSION );