<?php  
/**
 * TwitchPress - Include shortcode files here (added April 2018)
 *
 * There are some shortcodes that do not have their own file and are loaded in
 * another file pre-2018.
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Basic approach with multiple shortcodes in a single file.                                                                         
include_once( 'includes/functions.twitchpress-shortcodes.php' );

// Expanded approach with a file per shortcode. 
include_once( 'includes/shortcodes/shortcode-sync-buttons-public.php' );        

// Advanced approach using a class to make logging, debugging and UI output easier.
# TODO