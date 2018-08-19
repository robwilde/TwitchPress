<?php
/**
 * TwitchPress - Twitch API application credentials are set here.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Set_App' ) ) :

class TwitchPress_Set_App {
    
    // Twitch API application credentials 
    public $app_id = null; 
    public $app_secret = null;// Possibly change to protected?
    public $app_redirect = null;
    public $app_token = null;
    public $app_token_scopes = null;

    function __construct() {
        $this->set();
    }
    
    function set() {
        $this->app_id           = get_option( 'twitchpress_app_id', 0 ); 
        $this->app_secret       = get_option( 'twitchpress_app_secret', 0 );
        $this->app_redirect     = get_option( 'twitchpress_app_redirect', 0 );
        $this->app_token        = get_option( 'twitchpress_app_token', 0 );
        $this->app_token_scopes = get_option( 'twitchpress_app_token_scopes', 0 );         
    }
    
    function get( $value = null ) {
        if( $value ) {
            return eval( '$this->main_app_$value' );
        }   
        return array(
            'id'       => $this->app_id,
            'secret'   => $this->app_secret,
            'redirect' => $this->app_redirect,
            'token'    => $this->app_token,
            'scopes'   => $this->app_token_scopes
        );
    }

}

endif;

TwitchPress_Object_Registry::add( 'twitchapp', new TwitchPress_Set_App() );