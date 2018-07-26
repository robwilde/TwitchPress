<?php
/**
* Redirect during shortcode processing, with parameters for displaying
* a notice with a message that applies to the result. 
* 
* @param mixed $message_source is the plugin name i.e. "core" or "subscribermanagement" or "loginextension" etc
* @param mixed $message_key
* 
* @version 1.0
*/
function twitchpress_shortcode_procedure_redirect( $message_key, $title_values_array = array(), $info_values_array = array(), $message_source = 'twitchpress' ) {
    
    // Store values array in shortlife transient and use when generating output.
    set_transient( 'twitchpress_shortcode_' . $message_source . $message_key, 
        array( 'title_values' => $title_values_array, 'info_values' => $info_values_array ), 120 );
    
    wp_redirect( add_query_arg( array(
        'twitchpress_notice' => time(),
        'key'                => $message_key,        
        'source'             => $message_source,
    ), wp_get_referer() ) );
    exit;    
}