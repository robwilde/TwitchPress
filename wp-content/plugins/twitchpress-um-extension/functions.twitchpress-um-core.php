<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
* Get an array of the roles that have been paired with Twitch subscription plans. 
* 
* @version 1.0
*/
function twitchpress_um_get_subscription_plan_roles() {
    $array = array();
    $array[] = get_option( 'twitchpress_um_subtorole_none' );
    $array[] = get_option( 'twitchpress_um_subtorole_prime' );
    $array[] = get_option( 'twitchpress_um_subtorole_1000' );
    $array[] = get_option( 'twitchpress_um_subtorole_2000' );
    $array[] = get_option( 'twitchpress_um_subtorole_3000' );
    return $array;
}
        
if( !function_exists( 'twitchpress_is_request' ) ) {
    /**
     * What type of request is this?
     *
     * Functions and constants are WordPress core. This function will allow
     * you to avoid large operations or output at the wrong time.
     * 
     * @param  string $type admin, ajax, cron or frontend.
     * @return bool
     */
    function twitchpress_is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    } 
}