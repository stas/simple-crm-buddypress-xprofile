<?php
/*
Plugin Name: Simple CRM BuddyPress Addon
Plugin URI: http://wordpress.org/extend/plugins/simple-crm-buddypress-users
Description: Import/Export BuddyPress Users to Simple CRM
Author: Stas Sușcov
Version: 0.1
Author URI: http://stas.nerd.ro/
*/

define( 'SCRM_BPU_ROOT', dirname( __FILE__ ) );
define( 'SCRM_BPU_WEB_ROOT', WP_PLUGIN_URL . '/' . basename( SCRM_BPU_ROOT ) );

require_once SCRM_BPU_ROOT . '/includes/crm_bp.class.php';

/**
 * i18n
 */
function scrm_bp_textdomain() {
    load_plugin_textdomain( 'scrm_bp', false, basename( SCRM_BPU_ROOT ) . '/languages' );
}
add_action( 'init', 'scrm_bp_textdomain' );

SCRM_BP::init();

?>
