<?php
/**
 * TwitchPress $_POST processing using admin-post.php the proper way!
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {    
    exit;
}

add_action( 'admin_post_twitchpress_api_version_switch', 'twitchpress_api_version_switch' );
  
function twitchpress_api_version_switch() {

    // Only users with the twitchpress_developer capability will be allowed to do this...
    if( !current_user_can( 'twitchpressdevelopertoolbar' ) ) 
    {      
        TwitchPress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_twitchapiswitch_notice',
            'warning',
            false,
            __( 'No Permission', 'twitchpress' ),
            __( 'You do not have the TwitchPress Developer capability for this action. That permission must be added to your WordPress account first.', 'twitchpress' ) 
        );

        wp_redirect();
        exit;                      
    }
    
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        update_option( 'twitchpress_apiversion', 6 );
        $version = 6;
        $name = 'Helix';        
    }
    elseif( TWITCHPRESS_API_NAME == 'helix' )
    {
        update_option( 'twitchpress_apiversion', 5 );
        $version = 5;
        $name = 'Kraken';    
    }

    TwitchPress_Admin_Notices::add_wordpress_notice(
        'devtoolbar_twitchapiswitch_notice',
        'success',
        false,
        __( 'Twitch API Version Changed', 'twitchpress' ),
        sprintf( __( 'You changed the Twitch API version to %d (%s)', 'twitchpress' ), $version, $name ) 
    );
        
    wp_redirect( wp_get_referer() );
    exit;    
}
