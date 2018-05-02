<?php
/**
 * All-API Streamlabs API Class for TwitchPress.
 *
 * @link https://dev.streamlabs.com/
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Streamlabs Extension
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'TWITCHPRESS_All_API' ) ) { return; }

if( !class_exists( 'TwitchPress_All_API_Streamlabs_Calls' ) ) :

class TwitchPress_All_API_Streamlabs_Calls {
    
    public function refresh_users_token() {
        // Endpoint
        $endpoint = 'https://streamlabs.com/api/v1.0/token';
                    
        // Call Parameters
        $parameters = array(
            'access_token'     => $this->get_users_token(),
            'refresh_token'    => $this->get_users_refresh_token(),
            'token_lifetime'   => $this->get_users_token_lifetime(),
            'token_created_at' => $this->get_users_token_birthtime(),
            'token_expires_at' => $created_at + $lifetime,
            'grant_type'       => 'refresh_token',
            'client_id'        => $this->allapi_app_key,
            'client_secret'    => $this->allapi_app_secret,
            'redirect_uri'     => $this->allapi_app_uri
        );                           
                
        // Ensure required scope is permitted by client and by user during oAuth2.
        $confirm_scope = $this->confirm_scope( 'user_read', 'both', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
                                                                                       
        // Has the users access token expired?
        if ( $parameters['token_expires_at'] > time() ) { return false; }
        
        // Build our cURL query and store the array
        $usersObject = json_decode( $this->cURL_get( $endpoint, $parameters, array(), false, __FUNCTION__ ), true );
        return $usersObject;                   
    }
} 

endif;