<?php
/*
Plugin Name: WordPress CMS Toolkit
Plugin URI: https://github.com/cfpb/cms-toolkit/
Description: This plugin contains classes that help developers turn WordPress into a full CMS.

This plugin provides tools for extending WordPress for as a CMS. This includes 
things like the function `build_post_type()` a helper function for WordPress 
core's `register_post_type` function. The goal was the promote DRY coding 
practices and a simplified process for creating admin meta boxes. While it is 
currently integrated with WordPress as a plugin, it may be more helpful to 
think of it as a library. A collection of methods which, when installed, are 
available throughout the application and make building complex functionality 
in WordPress a little easier.

Version: 2.2.3
Author: Greg Boone, Aman Kaur, Matthew Duran, Scott Cranfill, Kurt Wall
Author URI: https://github.com/cfpb/
License: Public Domain work of the Federal Government

*/
function cfpb_build_plugin() {
	define( 'CFPB_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
	define( 'CFPB_INC', CFPB_DIR . trailingslashit( 'inc' ) );
	require_once( CFPB_INC . 'meta-box-models.php');
	require_once( CFPB_INC . 'meta-box-callbacks.php');
	require_once( CFPB_INC . 'meta-box-view.php');
	require_once( CFPB_INC . 'meta-box-html.php');
	require_once( CFPB_INC . 'capabilities.php');
	require_once( CFPB_INC . 'post-types.php');
	require_once( CFPB_INC . 'taxonomies.php');
	define( 'DEPENDENCIES_READY', true);
	add_action('admin_enqueue_scripts', 'cfpb_cms_toolkit_scripts');
	add_action( 'post_edit_form_tag' , 'post_edit_form_tag' );
}
$general_error = new \WP_Error(
	'_general_toolkit_error',
	'For more information about this error please refer to the README available on <a href="https://github.com/cfpb/cms-toolkit">GitHub</a>'
);
function cfpb_cms_toolkit_scripts() {
	wp_enqueue_script('cms_tookit', plugins_url('/js/functions.js', __FILE__), 'jquery', '1.0', true );
	wp_enqueue_script( 'multi-select_js', plugins_url( '/js/jquery.multi-select.js', __FILE__ ), 'jquery', '1.0', $in_footer = true );
	wp_register_style( 'cms_toolkit_styles',  plugins_url( '/css/styles.css', __FILE__ ), null, '1.0');
	wp_enqueue_style( 'cms_toolkit_styles' );
}
function post_edit_form_tag( ) {
   echo ' enctype="multipart/form-data"';
}

if (PHP_VERSION_ID >= 50300) {
	add_action( 'plugins_loaded', 'cfpb_build_plugin', $priority = 1 );
} else {
	$error = new \WP_Error('_upgrade_required', 'The cms-toolkit plugin requires PHP version 5.3 or higher, you are currently running ' . PHP_VERSION . ' please upgrade or ask your system admin to do this for you.');
	echo "<pre>{$error->get_error_message('_upgrade_required')}</pre>";
	echo "<pre>{$general_error->get_error_message('_general_toolkit_error')}</pre>";
}
