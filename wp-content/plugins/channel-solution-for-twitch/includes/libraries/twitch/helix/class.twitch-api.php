<?php
/**
 * Twitch API Helix for WordPress
 * 
 * Do not use this class unless you accept the Twitch Developer Services Agreement
 * @link https://www.twitch.tv/p/developer-agreement
 * 
 * @author   Ryan Bayne
 * @version 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not currently installed on your server, please install cURL if your wish to use Twitch services in TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitch services in TwitchPress.');

if( !class_exists( 'TwitchPress_Twitch_API' ) ) :

class TwitchPress_Twitch_API {
    
    /**
    * Post-request boolean value for tracking the calls purpose
    * and ability to meet requirements. 
    * 
    * @var mixed
    */
    public $call_result = false; 
    
    // Debugging variables.
    public $twitch_call_name = 'Unknown';
    public $twitch_sandbox_mode = false;
    
    // Public notice assistance (built outside of this class)...
    public $public_notice_title = null;
    public $public_notice_actions = array();
    
    // Administrator notice creation (built within this class)...
    public $admin_notice_title = null;      // Usually a string of text
    public $admin_notice_body = null;       // Usually just a string of text
    public $admin_notice_actions = array(); // Multiple actions may be offered 
    public $admin_user_request = false;     // true triggers output for the current admin user
    
    /**
    * Twitch API Version 6 Scopes
    * 
    * @var mixed
    */
    public $twitch_scopes = array( 
            'channel_check_subscription',
            'channel_commercial',
            'channel_editor',
            'channel_feed_edit',
            'channel_feed_read',
            'channel_read',
            'channel_stream',
            'channel_subscriptions',
            'chat_login',
            'collections_edit',
            'communities_edit',
            'communities_moderate',
            'user_blocks_edit',
            'user_blocks_read',
            'user_follows_edit',
            'user_read',
            'user_subscriptions',
            'viewing_activity_read',
            'openid',
            'analytics:read:extensions', // View analytics data for your extensions.
            'analytics:read:games',      // View analytics data for your games.
            'bits:read',                 // View Bits information for your channel.
            'clips:edit',                // Manage a clip object.
            'user:edit',                 // Manage a user object.
            'user:edit:broadcast',       // Edit your channel’s broadcast configuration, including extension configuration. (This scope implies user:read:broadcast capability.)
            'user:read:broadcast',       // View your broadcasting configuration, including extension configurations.
            'user:read:email',           // Read authorized user’s email address.
                        
    );
  
    /**
    * Array of endorsed channels, only partnered or official channels will be 
    * added here to reduce the risk of unwanted/nsfw sample content. 
    * 
    * @var mixed
    * 
    * @version 1.0
    */
    public $twitchchannels_endorsed = array(
        'zypherevolved'        => array( 'display_name' => 'ZypheREvolved' ),
        'nookyyy'              => array( 'display_name' => 'nookyyy' ),
        'starcitizengiveaways' => array( 'display_name' => 'StarCitizenGiveaways' ),        
        'starcitizen'          => array( 'display_name' => 'StarCitizen' ),
    );

    public function __construct(){
        $this->bugnet = new BugNet();
        
        if( get_option( 'twitchress_sandbox_mode_switch' ) == 'yes' ) { 
            $this->twitch_sandbox_mode = true; 
        }
    } 
    
    /**
     * Generate an App Access Token as part of OAuth Client Credentials Flow. 
     * 
     * This token is meant for authorizing the application and making API calls that are not channel-auth specific. 
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return object entire TwitchPress_Curl() object for handling any way required.
     * 
     * @version 2.0
     */
    public function request_app_access_token( $requesting_function = null ){

        // Create our Curl object which uses WP_Http_Curl()
        $this->curl_object = new TwitchPress_Curl();
        $this->curl_object->originating_function = __FUNCTION__;
        $this->curl_object->originating_line = __LINE__;
        $this->curl_object->type = 'POST';
        $this->curl_object->endpoint = 'https://api.twitch.tv/kraken/oauth2/token?client_id=' . twitchpress_get_app_id();
                
        // Set none API related parameters i.e. cache and rate controls...
        $this->curl_object->call_params( 
            false, 
            0, 
            false, 
            null, 
            false, 
            false 
        );
        
        // Use app data from registry...
        $twitch_app = TwitchPress_Object_Registry::get( 'twitchapp' );
        $this->curl_object->set_curl_body( array(
            'client_id'        => $twitch_app->app_id,
            'client_secret'    => $twitch_app->app_secret,
            'redirect_uri'     => $twitch_app->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );

        // Start + make the request in one line... 
        $this->curl_object->do_call( 'twitch' );
        
        // This method returns $call_twitch->curl_response_body;
        return $this->curl_object;
    }
    
    /**
    * Processes the object created by class TwitchPress_Curl(). 
    * 
    * Function request_app_access_token() is called first, it returns $call_object
    * so we can perform required validation and then we call this method.
    * 
    * @param mixed $call_object
    * 
    * @version 2.0
    */
    public function app_access_token_process_call_reply( $call_object ) {
        $options = array();

        if( !isset( $call_object->curl_reply_body->access_token ) ) {
            $this->bugnet->log( __FUNCTION__, __( 'Request for an application access_token was rejected or failed!', 'twitchpress' ), array(), true, false );            
            return false;
        }
        
        if( !isset( $call_object->curl_reply_body->expires_in ) ) {
            $this->bugnet->log( __FUNCTION__, __( 'Requested application token did not come with an expiry time!', 'twitchpress' ), array(), true, false );            
            return false;
        }
        
        // Update option record and object registry...            
        twitchpress_update_app_token( $call_object->curl_reply_body->access_token );
        twitchpress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 
 
        return $call_object->curl_reply_body->access_token; 
    }
    
    /**
     * Generate a visitor/user access token. This also applies to the administrator who
     * sets the main account because they are also a user.  
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return array $token - The generated token and the array of all scopes returned with the token, keyed.
     * 
     * @version 5.2
     */
    public function request_user_access_token( $code = null, $requesting_function = null ){

        if( !$code ) {
            $code = $this->twitch_client_code;
        }
        
        $endpoint = 'https://api.twitch.tv/kraken/oauth2/token';

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'POST', $endpoint );
 
        $this->call_object->grant_type = 'authorization_code';
 
        $this->call_object->auth_code = $code;
        
        $this->call_object->state = twitchpress_get_app_token();
        
        $this->call();
        
        $result = $this->call_object->curl_reply_response;
 
        if ( is_array( $result ) && array_key_exists( 'access_token', $result ) )
        {
            $appending = '';
            if( $requesting_function == null ) { $appending = $token; }
            else{ $appending = sprintf( __( 'Requesting function was %s() and the token is %s.', 'twitchpress' ), $requesting_function, $result['access_token'] ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'Access token returned. %s', 'twitchpress' ), $appending ), array(), true, false );

            return $result;
        } 
        else 
        {
            $request_string = '';
            if( $requesting_function == null ) { $request_string = __( 'Requesting function is not known!', 'twitchpress' ); }
            else{ $request_string = __( 'Requesting function is ', 'twitchpress' ) . $requesting_function; }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'No access token returned: %s()', 'twitchpress' ), $request_string ), array(), true, false );
        
            return false;
        }
    }
                       
    /**
     * Checks a token for validity and access grants available.
     * 
     * @return array $result if token is still valid, else false.  
     * 
     * @version 5.2
     * 
     * @deprecated this has not been a great approach, new approach coming October 2018
     */    
    public function check_application_token(){                    

        $url = 'https://api.twitch.tv/kraken';
        $post = array( 
            'oauth_token' => $this->twitch_client_token, 
            'client_id'   => $this->twitch_client_id,          
        );

        $result = json_decode( $this->cURL_get( $url, $post, array(), false, __FUNCTION__ ), true );                   
        
        if ( isset( $result['token']['valid'] ) && $result['token']['valid'] )
        {      
            return $result;
        } 
        else 
        {
            $this->bugnet->log( __FUNCTION__, __( 'Invalid app token', 'twitchpress' ), array(), true, true );
            return false;
        }
        
        return false;     
    }        
                   
    /**
     * Checks a user oAuth2 token for validity.
     * 
     * @param $authToken - [string] The token that you want to check
     * 
     * @return $authToken - [array] Either the provided token and the array of scopes if it was valid or false as the token and an empty array of scopes
     * 
     * @version 6.0
     */    
    public function check_user_token( $wp_user_id ){
        
        // Get the giving users token. 
        $user_token = twitchpress_get_user_token( $wp_user_id );
        if( !$user_token ){ return false;}
        
        $endpoint = 'https://api.twitch.tv/kraken';

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint );
        
        $this->call();
        
        $result = $this->call_result;
        
        $token = array();
        
        if ( isset( $result['token'] ) && isset( $result['token']['valid'] ) && $result['token']['valid'] !== false )
        {      
            $token['token'] = $user_token;
            $token['scopes'] = $result['token']['authorization']['scopes'];
            $token['name'] = $result['token']['user_name'];
        } 
        else 
        {
            $this->bugnet->log( __FUNCTION__, __( 'Users token has expired', 'twitchpress' ), array(), true, true );
            $token['token'] = false;
            $token['scopes'] = array();
            $token['name'] = '';
        }
        
        return $token;     
    }

    /**
    * Establish an application token.
    * 
    * This method will check the existing token.
    * Existing token invalid, it will request a new one. 
    * Various values can be replaced during this procedure to help
    * generate debugging information for users.  
    * 
    * @param mixed $old_token
    * 
    * @returns array $result if token valid, else returns the return from request_app_access_token(). 
    * 
    * @version 5.0
    * 
    * @deprecated a new approach that relies on the access token expiry and call responses is WIP
    */
    public function establish_application_token( $function ) {     
        $result = $this->check_application_token();  

        // check_application_token() makes a call and if token invalid the following values will not be returned by the API
        if ( !isset( $result['token']['valid'] ) || !$result['token']['valid'] ){
            return $this->request_app_access_token( $function . ' + ' . __FUNCTION__ );
        }
                                  
        return $result;
    }
    
    /**
    * Establish current user token or token on behalf of a user who has
    * giving permission for extended sessions.
    * 
    * @returns array $result if token valid, else returns the return from request_app_access_token(). 
    * 
    * @version 5.2
    */
    public function establish_user_token( $function, $user_id ) { 
        // Maybe use an existing token? 
        $result = $this->check_user_token( $user_id );  

        if( isset( $result['token'] ) && $result['token'] !== false )
        {      
            return $result['token'];// Old token is still in session.    
        }
        elseif ( !isset( $result['token']['valid'] ) || !$result['token']['valid'] )
        {    
            // Attempt to refresh the users token, else request a new one.
            // This method updates user meta. 
            $new_token = $this->refresh_token_by_userid( $user_id );
                      
            if( is_string( $new_token ) ) 
            {            
                return $new_token;
            }
            elseif( !$new_token )
            {
                // Refresh failed - attempt to request a new token.
                $code = twitchpress_get_user_code( $user_id ); 

                // This method does not update user meta because $user_id is not always available where it is used.
                $user_access_token_array = $this->request_user_access_token( $code, __FUNCTION__ );
                
                #   Example for $user_access_token_array             
                #      
                #  'access_token' => string 'psv9jaiqgimari17zb1ekeg9emlw38' (length=30)
                #  'refresh_token' => string 'lmgdjnlik871s4qzxe94scu4x8ou0rxzacvgfni95bbob0crxv' (length=50)
                #  'scope' => 
                #      array (size=19)
                #         0 => string 'channel_check_subscription' (length=26)
                #         1 => string 'channel_commercial' (length=18)
                #     'expires_in' => int 15384         
                
                twitchpress_update_user_token( $user_id, $user_access_token_array['access_token'] );
                twitchpress_update_user_token_refresh( $user_id, $user_access_token_array['refresh_token'] );
                       
                return $user_access_token_array['access_token'];
            }
        }
    }
    
    /**
    * Refreshes an existing token to extend a session. 
    * 
    * @link https://dev.twitch.tv/docs/authentication#refreshing-access-tokens
    * 
    * @version 1.0
    * 
    * @param integer $user_id
    */
    public function refresh_token_by_userid( $user_id ) {
        $token_refresh = twitchpress_get_user_token_refresh( $user_id );
        if( !$token_refresh ) { return false; }
        
        $endpoint = 'https://api.twitch.tv/kraken/oauth2/token';

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'POST', $endpoint );
        
        $this->call_object->grant_type = 'refresh_token';
        
        $this->call_object->refresh_token = $token_refresh;
        
        $this->call_object->scope = twitchpress_prepare_scopes( twitchpress_get_visitor_scopes() );
        
        $this->call();
        
        $result = $this->call_result;
        //$result = json_decode( $this->cURL_post( $url, $post, $options, false ), true );
        
        
        
            # Success Example $result
            #
            # "access_token": "asdfasdf",
            # "refresh_token": "eyJfMzUtNDU0OC04MWYwLTQ5MDY5ODY4NGNlMSJ9%asdfasdf=",
            # "scope": "viewing_activity_read"
            
            # Failed Example Result 
            #
            # "error": "Bad Request",
            # "status": 400,
            # "message": "Invalid refresh token"
             
        if( isset( $result['access_token'] ) && !isset( $result['error'] ) )
        {
            twitchpress_update_user_token( $user_id, $result['access_token'] );
            twitchpress_update_user_token_refresh( $user_id, $result['refresh_token'] );
            
            return $result['access_token'];
        }
        elseif( isset( $result['error'] ) ) 
        {
            return false;    
        }
        else
        {
            return false;    
        }
    } 

    /**
    * A method for administration accounts (not visitors). Call this when
    * all credentails are presumed ready in options table. Can pass $account
    * value to change which credentials are applied.
    * 
    * Recommended for admin requests as it generates notices.  
    * 
    * @author Ryan Bayne
    * @version 1.2
    */
    public function start_twitch_session_admin( $account = 'main' ) {
        // Can change from the default "main" credentails. 
        if( $account !== 'main' ) {
            self::set_application_credentials( $app = 'main' );
        }

        // The plugin will bring the user to their original admin view using the redirectto value.
        $state = array( 'redirectto' => admin_url( '/admin.php?page=twitchpress&tab=kraken&amp;' . 'section=entermaincredentials' ),
                        'userrole'   => 'administrator',
                        'outputtype' => 'admin' 
        );

        wp_redirect( twitchpress_generate_authorization_url( twitchpress_get_global_accepted_scopes(), $state ) );
        exit;                       
    }      
    
    public function get_main_streamlabs_user() {
                  
        // Endpoint
        $url = 'https://streamlabs.com/api/v1.0/user?access_token=' . $this->get_main_access_token();
     
        // Call Parameters
        $request_body = array(
            'client_id'        => $this->streamlabs_app_id,
            'client_secret'    => $this->streamlabs_app_secret,
            'redirect_uri'     => $this->streamlabs_app_uri,
        );                           

        $curl = new WP_Http_Curl();
        $curl_info = curl_version();

        $response = $curl->request( $url, 
            array( 
                'method'     => 'GET', 
                'body'       => $request_body,
                'user-agent' => 'curl/' . $curl_info['version'],
                'stream'     => false,
                'filename'   => false,
                'decompress' => false 
            ) 
        );

        if( isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {
            if( isset( $response['body'] ) ) {
                $response_body = json_decode( $response['body'] );
                return $response_body;
            }
        }
         
        return false;  
    }
    
    /**
     * Gets a users Twitch.tv object by their oAuth token stored in user meta.
     * 
     * @param $user - [string] Username to grab the object for
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $userObject - [array] Returned object for the query
     * 
     * @version 5.8
     */ 
    public function getUserObject_Authd( string $token, string $code ){
        
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'user_read', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) || $confirm_scope == false ) { return $confirm_scope; }
         
        $url = 'https://api.twitch.tv/kraken/user';

        return $userObject;
    }
    
    /**
    * User current users oauth token and the app code to get Twitch.tv user object.
    * 
    * @version 1.0
    */
    public function get_current_userobject_authd() {
    
        if( !$wp_user_id = get_current_user_id() ) {
            return false;    
        }
        
        return $this->getUserObject_Authd( get_user_meta( $wp_user_id, 'twitchpress_token', true ), $this->twitch_client_code );    
    }
    
    /**
     * Gets the channel object that belongs to the giving token.
     * 
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $object - [array] Keyed array of all channel data
     * 
     * @version 5.3
     */ 
    public function get_tokens_channel( $token = null ){        
                                 
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'channel_read', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = twitchpress_get_app_token();
        }

        $endpoint = 'https://api.twitch.tv/kraken/channel';
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint );
        
        $this->call();
        
        return $this->call_object->curl_reply_response;
    }  

    /**
     * Adds a user to a channel's blocked list
     * 
     * @param $chan - [string] channel to add the user to
     * @param $username - [string] username of newly banned user
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $success - [bool] Result of the query
     * 
     * @version 1.2
     */ 
    public function addBlockedUser( string $chan, string $username, string $token, string $code){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'user_blocks_edit', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
                
        $url = 'https://api.twitch.tv/kraken/users/' . $chan . '/blocks/' . $username;

        // What did we get returned status wise?
        if ($result = 200){                                                                    
            $success = true;
        } else {                                                                                  
            $success = false;
        }

        // Post handles successs, so pass the info on
        return $success;  
    }
    
    /**
     * Removes a user from being blocked on a channel
     * 
     * @param $chan     - [string] channel to remove the user from.
     * @param $username - [string] username of newly pardoned user
     * @param $token  - [string] Authentication key used for the session
     * @param $code     - [string] Code used to generate an Authentication key
     * 
     * @return $success - [bool] Result of the query
     * 
     * @version 1.5
     */ 
    public function removeBlockedUser( string $chan, string $username, string $token, string $code){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'user_blocks_edit', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $url = 'https://api.twitch.tv/kraken/users/' . $chan . '/blocks/' . $username;

        if ($success == '204'){
            // Successfully removed ' . $username . ' from ' . $chan . '\'s list of blocked users',
        } else if ($success == '422') {
            // Service unavailable or delete failed
        } else {
            // Do error here
        }
        
        // Basically we either deleted or they were never there
        return true;  
    }
    
    /**
     * Grabs a full channel object of all publically available data for the channel
     * 
     * @param $channel_id - [string] ID of the channel to grab the object for
     * @param $clientid - [string]
     * 
     * @return $object - [array] Keyed array of all publically available channel data
     * 
     * @version 5.3
     */
    public function getChannelObject( int $channel_id ){
        $url = 'https://api.twitch.tv/kraken/channels/' . $channel_id;
        $get = array( 'client_id'   => $this->twitch_client_id );

        $object = json_decode($this->cURL_get($url, $get, array(), false), true);
        
        if (!is_array($object)){
            $object = array(); // Catch to make sure that an array is returned no matter what, technically our fail state
        }
        
        return $object;
    }
    
    /**
     * Generates an OAuth token for chat login
     * 
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $chatToken - [string] complete login token for chat login
     * 
     * @version 1.0
     */
    public function chat_generateToken($token, $code){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'chat_login', 'both', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }        
        
        $prefix = 'oauth:';

        if( !$token ) {
            $token = $this->twitch_client_token;
        }
        
        $chatToken = $prefix . $token;

        return $chatToken;                
    }
            
    /**
     * Grabs the stream object of a given channel
     * 
     * @param $channel_id - [string] Channel ID to get the stream object for
     * 
     * @return $object - [array or null] Returned array of all stream object data or null if stream is offline
     * 
     * @version 5.0
     */ 
    public function getStreamObject( $channel_id ){
        $url = 'https://api.twitch.tv/kraken/streams/' . $channel_id;
        $get = array( 'client_id' => $this->twitch_client_id );

        $result = json_decode($this->cURL_get($url, $get, array(), false), true);
        
        if ($result['stream'] != null){
            $object = $result['stream'];
        } else {
            $object = null;
        }
        
        return $object;
    }
    
    /**
     * Gets a list of all users subscribed to a channel.
     * 
     * @param $chan - [string] Channel name to grab the subscribers list of
     * @param $limit - [int] Limit of channel objects to return
     * @param $offset - [int] Maximum number of objects to return
     * @param $direction - [string] Sorting direction, valid options are 'asc' and 'desc'
     * @param $token - [string] Token related to the channel being queried for sub data.
     * @param $code - [string] Code related to the channel being queried for sub data.
     * 
     * @version 5.6
     */ 
    public function get_channel_subscribers( $chan, $limit = -1, $offset = 0, $direction = 'asc', $token = null, $code = null ){
        
        if( $this->twitch_sandbox_mode ) { return $this->get_channel_subscriptions_sandbox(); }
                                                                                                    
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan . '/subscriptions';                          
                                                                                        
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.              
        $confirm_scope = twitchpress_confirm_scope( 'channel_subscriptions', 'channel', __FUNCTION__ );               
        if( is_wp_error( $confirm_scope) ) 
        {
            $this->bugnet->log_error( __FUNCTION__, __( 'Kraken5 was not giving sccope channel_subscriptions in the get_channel_subscribers() function.', 'twitchpress' ), array(), true ); 
            return $confirm_scope; 
        }                                            
                                                                                                 
        // Default to main channel credentials.                                                              
        if( !$token ){ $token = $this->twitch_client_token; }                                                
        if( !$code ){ $code = $this->twitch_client_code; }                                                   

        $get = array( 'oauth_token' => $token, 
                      'limit'       => $limit, 
                      'offset'      => $offset, 
                      'direction'   => $direction, 
                      'client_id' => $this->twitch_client_id );
         
        return json_decode( $this->cURL_get($url, $get, array( /* cURL options */), false, __FUNCTION__ ), true);
    }
    
    /**
    * Sandbox version of get_channel_subscriptions().
    * 
    * @version 1.0
    */
    public function get_channel_subscriptions_sandbox() { 
        return array( 
                        "_total" => 4,
                        "subscriptions" => array( 
                            array(
                                "_id"            => "e5e2ddc37e74aa9636625e8d2cc2e54648a30418",
                                "created_at"     => "2016-04-06T04:44:31Z",
                                "sub_plan"       => "1000",
                                "sub_plan_name"  =>  "Channel Subscription (mr_woodchuck)",
                                "user"               => array(
                                    "_id"            => "89614178",
                                    "bio"            => "Twitch staff member who is a heimerdinger main on the road to diamond.",
                                    "created_at"     => "2015-04-26T18:45:34Z",
                                    "display_name"   => "Mr_Woodchuck",
                                    "logo"           => "https://static-cdn.jtvnw.net/jtv_user_pictures/mr_woodchuck-profile_image-a8b10154f47942bc-300x300.jpeg",
                                    "name"           => "mr_woodchuck",
                                    "type"           => "staff",
                                    "updated_at"     => "2017-04-06T00:14:13Z" ),
                                    
                            )
                        )
        );
    }   
    
    /**
     * Gets a giving users subscription details for a giving channel
     * 
     * @param $user_id - [string] Username of the user check against
     * @param $chan - [string] Channel name of the channel to check against
     * @param $token - [string] Channel owners own user token, not the visitors.
     * 
     * @returns $subscribed - [mixed] the subscription details (array) or error details (array) or null if Twitch returns null.
     * 
     * @version 5.4
     */ 
    public function getChannelSubscription( $twitch_user_id, $chan_id, $token ){
        
        // I witnessed a possible empty string in $user resulting in wrong URL endpoint.
        if( !$twitch_user_id ){ $this->bugnet->log_error( __FUNCTION__, __( 'Twitch user ID not giving.', 'twitchpress' ), array(), true ); }            
                                                                   
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'channel_check_subscription', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        $url = 'https://api.twitch.tv/kraken/channels/' . $chan_id . '/subscriptions/' . $twitch_user_id;
        $get = array( 'oauth_token' => $token, 'client_id' => $this->twitch_client_id );
        
        $subscribed = json_decode( $this->cURL_get( $url, $get, array(), false, __FUNCTION__ ), true );
         
        // only check results here to log them and return the original response.
        if( isset( $subscribed['error'] ) ) 
        {
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'Error on GET subscription - User ID %s - Channel ID %s.', 'twitchpress' ), $twitch_user_id, $chan_id ), array(), false );
            return $subscribed;
        } 
        elseif( isset( $subscribed['sub_plan'] ) )
        {
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'Good Subscription GET - User ID %s - Channel ID %s.', 'twitchpress' ), $twitch_user_id, $chan_id ), array(), false, false );
            return $subscribed;   
        }
        elseif( $subscribed === null )
        {
            // Channel does not have a subscription scheme. 
            return null;
        }
             
        // We should never arrive here. 
        // These lines were added to debugging the new "null" response which the documentation says nothing about for this endpint. 
        // This bug may be the cause of 500 errors on returning from Twitch.
        $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'Unexpected response from request for subscribers data. User ID: %s Channel ID: %s.', 'twitchpress' ), $twitch_user_id, $chan_id ), array(), false );
        
        if( is_array( $subscribed ) ) 
        {
            $unexpected = error_log( print_r( $subscribed, TRUE ) );
        }
        elseif( is_string( $subscribed ) )
        {
            $unexpected = $subscribed;
        }
        elseif( empty( $subscribed ) ) 
        {
            $unexpected = __( 'json_decode() has returned an empty value!', 'twitchpress' );
        }
        
        $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'Examine the unexpected response: %s', 'twitchpress' ), $unexpected ), array(), false );
          
        return $subscribed;
    }
    
    /**
    * Uses a users own Twitch code and token to get their subscription
    * status for the sites main/default channel.
    * 
    * @param mixed $user_id
    * 
    * @version 3.0
    */
    public function is_user_subscribed_to_main_channel( $user_id ) {

        if( !$credentials = twitchpress_get_user_twitch_credentials( $user_id ) ) {
            return null;    
        }        

        // Returns boolean, false if no subscription else true.     
        return $this->get_users_subscription_apicall( 
            twitchpress_get_user_twitchid_by_wpid($user_id), 
            twitchpress_get_main_channels_twitchid(), 
            $credentials['token'] 
        );    
    }
    
    /**
     * Checks to see if a user is subscribed to a specified channel from the user side.
     * 
     * @param $user_id - [string] User ID of the user check against
     * @param $chan    - [string] Channel name of the channel to check against
     * @param $token   - [string] Authentication key used for the session
     * @param $code    - [string] Code used to generate an Authentication key
     * 
     * @return $subscribed - [bool] the status of the user subscription
     * 
     * @version 5.5
     */ 
    public function get_users_subscription_apicall( $twitch_user_id, $chan_id, $user_token = false ){

        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'channel_check_subscription', 'user', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) 
        {
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'TwitchPress Error: The function %s() requires the channel_check_subscription scope to be permitted.', 'twitchpress' ), __FUNCTION__ ), array(), true ); 
            return $confirm_scope; 
        }
                               
        $url = 'https://api.twitch.tv/kraken/users/' . $twitch_user_id . '/subscriptions/' . $chan_id;
        $get = array( 'oauth_token' => $user_token, 'client_id' => $this->twitch_client_id );   

        // Build our cURL query and store the array
        $subscribed = json_decode( $this->cURL_get( $url, $get, array(), true, __FUNCTION__ ), true );

        // Check the return
        if ( $subscribed == 403 ){      
            // Authentication failed to have access to channel account.  Please check user access.
            $subscribed = false;
        } elseif ( $subscribed == 422 ) {     
            // Channel ' . $chan . ' does not have subscription program available
            $subscribed = false;
        } elseif ( $subscribed == 404 ) {    
            // User ' . $user_id . ' is not subscribed to channel ' . $chan
            $subscribed = false;
        } else {
            // User ' . $user_id . ' is subscribed to channel ' . $chan
            $subscribed = true;
        }
                 
        return $subscribed;
    }

    /**
    * Get the giving WordPress users Twitch subscription plan for the
    * main channel using the users own oAuth2 code and token.
    * 
    * This method is done from the users side.
    * 
    * @param mixed $user_id
    * 
    * @version 5.1
    */
    public function getUserSubscriptionPlan( $user_id ) {
        if( !$credentials = twitchpress_get_user_twitch_credentials( $user_id ) ) {
            return null;    
        }        

        $sub = $this->getUserSubscription(             
            $user_id, 
            $this->twitch_channel_id, 
            $credentials['token'], 
            $credentials['code']  
        );    
          
        return $sub['sub_plan'];
    }
    
    /**
     * Gets the a users subscription data (array) for specified channel from the user side.
     * 
     * @param $twitch_user_id - [string] User ID of the user check against
     * @param $chan_id    - [string] Channel name of the channel to check against
     * @param $user_token   - [string] Authentication key used for the session
     * @param $code    - [string] Code used to generate an Authentication key
     * 
     * @return $subscribed - [array] subscription data.
     * 
     * @version 5.1
     */ 
    public function getUserSubscription( $twitch_user_id, $chan_id, $user_token ){   
        
        $call_authentication = 'channel_check_subscription';

        $endpoint = 'https://api.twitch.tv/kraken/users/' . $twitch_user_id . '/subscriptions/' . $chan_id;  
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'automatic', $endpoint );
        
        $this->call();        
    }
                                 
                                  
    /**
    * Sandbox-mode edition of getUserSubscription().
    * 
    * @param mixed $twitch_user_id
    * @param mixed $chan_id
    * @param mixed $user_token
    * 
    * @version 1.0
    */
    public function getUserSubscription_sandbox( $twitch_user_id, $chan_id, $user_token = false ){   
        
        $return = array();
        
        if( 'yes' == get_option( 'twitchress_sandbox_mode_generator_switch' ) )
        {
             /* Not doing anything here yet, but we could generate users based on real Twitch channels. */
        }

        // Return a subscriber as the affirmative response.
        $return[] = array( 
            "_id"           => "ac2f1248993eaf97e71721458bd88aae66c92330",
            "sub_plan"      => "3000",
            "sub_plan_name" => "Channel Subscription (forstycup) - $24.99 Sub",
            "channel" => array(
                "_id"                             => "19571752",
                "broadcaster_language"            => "en",
                "created_at"                      => "2011-01-16T04:35:51Z",
                "display_name"                    => "forstycup",
                "followers"                       => 397,
                "game"                            => "Final Fantasy XV",
                "language"                        => "en",
                "logo"                            => "https://static-cdn.jtvnw.net/jtv_user_pictures/forstycup-profile_image-940fb4ca1e5949c0-300x300.png",
                "mature"                          => true,
                "name"                            => "forstycup",
                "partner"                         => true,
                "profile_banner"                  => null,
                "profile_banner_background_color" => null,
                "status"                          => "[Blind] Moar Sidequests! Let's explore.",
                "updated_at"                      => "2017-04-06T09:00:41Z",
                "url"                             => "http://localhost:3000/forstycup",
                "video_banner"                    => "https://static-cdn.jtvnw.net/jtv_user_pictures/forstycup-channel_offline_image-f7274322063da225-1920x1080.png",
                "views"                           => 5705 
            ),
            "created_at" => "2017-04-08T19:54:24Z"
        );

        // If false returns not on, we only return the affirmative response.
        if( 'yes' !== get_option( 'twitchress_sandbox_mode_falsereturns_switch' ) )
        {
            return $return[0];
        }
                
        // Add the false returns and randomize which is returned (for a natural set of data)
        $return[] = array(
            "error"   => "Not Found",
            "message" => "dallas has no subscriptions to twitch",
            "status"  => 404
        );
        
        return array_rand( $return );
    } 
    
    ############################################################################
    #                                                                          #
    #                              NEW API (HELIX)                             #
    #                                                                          #
    ############################################################################    

    /**
    * Creates the $this->call_object using class TwitchPress_Curl()
    * and it is after this method we can add our options/parameters.
    * 
    * We then use $this->call() to execute. 
    * 
    * @version 1.0
    */
    public function curl( $file, $function, $line, $type = 'get', $endpoint ) {
        
        // Create our own special Curl object which uses WP_Http_Curl()
        $this->curl_object = new TwitchPress_Curl();
        $this->curl_object->originating_file = $file;
        $this->curl_object->originating_function = $function;
        $this->curl_object->originating_line = $line;
        $this->curl_object->type = $type;
        $this->curl_object->endpoint = $endpoint;
                
        // Add none API related parameters to the object...
        $this->curl_object->call_params(  
            false, 
            0, 
            false, 
            null, 
            false, 
            false 
        );

        // Add common/default headers...
        $this->curl_object->headers = array(
            'Authorization' => 'Bearer ' . twitchpress_get_app_token(),
        );
    }   
    
    /**
    * Using the values in $this->call_object execute a call to Twitch. 
    *  
    * @version 1.0
    */
    function call() {

        // Start + make the request to Twitch.tv API in one line... 
        $this->curl_object->do_call( 'twitch' );

        if( isset( $this->curl_object->response_code ) && $this->curl_object->response_code == '200' ) {
            // This will tell us that we should expect our wanted data to exist in $call_object
            // and we can use $this->call_result to assume that any database insert/update has happened also
            $this->curl_object->call_result = true;
        }
        else 
        {    
            $this->curl_object->call_result = false; 
             
            if( !isset( $this->curl_object->response_code ) ) {
                $this->bugnet->log( __FUNCTION__, sprintf( __( 'Response code not returned! Call ID [%s]', 'twitchpress' ), $this->curl_object->get_call_id() ), array(), true, false );            
            }
       
            if( $this->curl_object->response_code !== '200' ) {   
                $this->bugnet->log( __FUNCTION__, sprintf( __( 'Response code [%s] Call ID [%s]', 'twitchpress' ), $this->call_object->response_code, $this->curl_object->get_call_id() ), array(), true, false );            
            }
        }
    }  
    
    /**
    * Get Extension Analytics 
    * 
    * Gets a URL that extension developers can use to download analytics reports 
    * (CSV files) for their extensions. The URL is valid for 5 minutes. For detail 
    * about analytics and the fields returned, see the Insights & Analytics guide.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-extension-analytics
    * 
    * @param mixed $after
    * @param mixed $ended_at
    * @param mixed $extension_id
    * @param mixed $first
    * @param mixed $started_at
    * @param mixed $type
    * 
    * @version 1.0
    */
    function get_extension_analytics( string $after = null, string $ended_at = null, string $extension_id = null, integer $first = null, string $started_at = null, string $type = null ) {
        
        $call_authentication = 'scope';
        
        $scope = 'analytics:read:extensions';

        $endpoint = 'https://api.twitch.tv/helix/analytics/extensions';         
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );
        
        // We should now have $this->call_object with a response from the Twitch API...
        
    }

    private function sandbox_get_extension_analytics( $test ) {
        
    }
    
    /**
    * Get Game Analytics
    * 
    * Gets a URL that game developers can use to download analytics reports 
    * (CSV files) for their games. The URL is valid for 5 minutes. For detail 
    * about analytics and the fields returned, see the Insights & Analytics guide.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * games information elements and can contain a pagination field containing 
    * information required to query for more streams.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-game-analytics
    * 
    * @param string $after
    * @param string $ended_at
    * @param integer $first
    * @param string $game_id
    * @param string $started_at
    * @param string $type
    * 
    * @version 1.0
    */
    public function get_game_analytics( string $after = null, string $ended_at = null, integer $first = null, string $game_id = null, string $started_at = null, string $type = null ) {

        $call_authentication = 'scope';
        
        $scope = 'analytics:read:games';

        $endpoint = 'https://api.twitch.tv/helix/analytics/games';    
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );    
    }
    
    /**
    * Get Bits Leaderboard 
    * 
    * Gets a ranked list of Bits leaderboard information 
    * for an authorized broadcaster.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-bits-leaderboard
    * @version 1.0 
    * 
    * @param mixed $count
    * @param mixed $period
    * @param mixed $started_at
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function get_bits_leaderboard( integer $count = null, string $period = null, string $started_at = null, string $user_id = null ) {

        $call_authentication = 'scope';
        
        $scope = 'bits:read';

        $endpoint = 'https://api.twitch.tv/helix/bits/leaderboard';
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );
    }
    
    /**
    * Create Clip
    * 
    * Creates a clip programmatically. This returns both an ID 
    * and an edit URL for the new clip.
    * 
    * Clip creation takes time. We recommend that you query Get Clips, 
    * with the clip ID that is returned here. If Get Clips returns a 
    * valid clip, your clip creation was successful. If, after 15 seconds, 
    * you still have not gotten back a valid clip from Get Clips, assume 
    * that the clip was not created and retry Create Clip.
    * 
    * This endpoint has a global rate limit, across all callers. The limit 
    * may change over time, but the response includes informative headers:
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#create-clip
    * 
    * @param mixed $broadcaster_id
    * @param mixed $has_delay
    * 
    * @version 1.0
    */
    public function create_clip( string $broadcaster_id, boolean $has_delay = null ) {

        $call_authentication = 'scope';

        $scope = 'clips:edit';
 
        $endpoint = 'https://api.twitch.tv/helix/clips';   
        
        $this->post( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );        
    }
    
    /**
    * Get Clips
    * 
    * Gets clip information by clip ID (one or more), broadcaster ID (one only), 
    * or game ID (one only).
    * 
    * The response has a JSON payload with a data field containing an array 
    * of clip information elements and a pagination field containing 
    * information required to query for more streams.
    * 
    * @param mixed $broadcaster_id
    * @param mixed $game_id
    * @param mixed $id
    * @param mixed $after
    * @param mixed $before
    * @param mixed $ended_at
    * @param mixed $first
    * @param mixed $started_at
    * 
    * @version 1.0
    */
    public function get_clips( string $broadcaster_id, string $game_id, string $id, string $after = null, string $before = null, string $ended_at = null, integer $first = null, string $started_at = null ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/clips'; 
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );          
    }
        
    /**
    * Create Entitlement Grants Upload URL
    * 
    * Creates a URL where you can upload a manifest file and notify users that
    * they have an entitlement. Entitlements are digital items that users are 
    * entitled to use. Twitch entitlements are granted to users gratis or as 
    * part of a purchase on Twitch.
    * 
    * See the Drops Guide for details about using this 
    * endpoint to notify users about Drops.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#create-entitlement-grants-upload-url
    * 
    * @param mixed $manifest_id
    * @param mixed $type
    * 
    * @version 1.0
    */
    public function create_entitlement_grants_upload_url( string $manifest_id, string $type ) {

        $call_authentication = 'app_access_token';

        $endpoint = 'https://api.twitch.tv/helix/entitlements/upload';  
        
        $this->post( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );         
    }
         
    /**
    * Get Games
    * 
    * Gets game information by game ID or name. The response has a JSON 
    * payload with a data field containing an array of games elements.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-games
    * 
    * @param mixed $id
    * @param mixed $name
    * @param mixed $box_art_url
    * 
    * @version 1.0
    */
    public function get_games( string $id, string $name ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/games';  
        
        $this->curl();
        
        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );         
    }
         
    /**
    * Get Top Games
    * 
    * Gets games sorted by number of current viewers on Twitch, most popular first.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of games information elements and a pagination field containing 
    * information required to query for more streams.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-top-games
    * 
    * @param mixed $after
    * @param mixed $before
    * @param mixed $first
    * 
    * @version 1.0
    */
    public function get_top_games( string $after = null, string $before = null, string $first = null ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/games/top';    

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 
 
        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );    
    }
         
    /**
    * Get Streams
    * 
    * Gets information about active streams. Streams are returned sorted by 
    * number of current viewers, in descending order. Across multiple pages of 
    * results, there may be duplicate or missing streams, 
    * as viewers join and leave streams.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * stream information elements and a pagination field containing information 
    * required to query for more streams.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-streams
    * 
    * @param mixed $after
    * @param mixed $before
    * @param mixed $community_id
    * @param mixed $first
    * @param mixed $game_id
    * @param mixed $language
    * @param mixed $user_id
    * @param mixed $user_login
    * 
    * @version 1.0
    */
    public function get_streams( string $after = null, string $before = null, string $community_id = null, integer $first = null, string $game_id = null, string $language = null, string $user_id = null, string $user_login = null ) {
   
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/streams';     
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );     
    }
         
    /**
    * Get Streams Metadata
    * 
    * Gets metadata information about active streams playing Overwatch or 
    * Hearthstone. Streams are sorted by number of current viewers, in 
    * descending order. Across multiple pages of results, there may be 
    * duplicate or missing streams, as viewers join and leave streams.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * stream information elements and a pagination field containing information 
    * required to query for more streams.
    * 
    * This endpoint has a global rate limit, across all callers. The limit 
    * may change over time, but the response includes informative headers:
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-streams-metadata
    * 
    * @param mixed $after
    * @param mixed $before
    * @param mixed $community_id
    * @param mixed $first
    * @param mixed $game_id
    * @param mixed $language
    * @param mixed $user_id
    * @param mixed $user_login
    * 
    * @version 1.0
    */
    public function get_streams_metadata( string $after = null, string $before = null, string $community_id = null, integer $first = null, string $game_id = null, string $language = null, string $user_id = null, string $user_login = null ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/streams/metadata';    
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );       
    }
         
    /**
    * Create Stream Marker
    * 
    * Creates a marker in the stream of a user specified by a user ID. 
    * A marker is an arbitrary point in a stream that the broadcaster 
    * wants to mark; e.g., to easily return to later. The marker is 
    * created at the current timestamp in the live broadcast when the 
    * request is processed. Markers can be created by the stream owner 
    * or editors. The user creating the marker is identified by a Bearer token.
    * 
    * Markers cannot be created in some cases (an error will occur):
    *   ~ If the specified user’s stream is not live.
    *   ~ If VOD (past broadcast) storage is not enabled for the stream.
    *   ~ For premieres (live, first-viewing events that combine uploaded videos with live chat).
    *   ~ For reruns (subsequent (not live) streaming of any past broadcast, including past premieres).
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#create-stream-marker
    * 
    * @param mixed $user_id
    * @param mixed $description
    * 
    * @version 1.0
    */
    public function create_stream_markers( string $user_id, string $description = null ) {

        $call_authentication = 'scope';
        
        $scope = 'user:edit:broadcast';

        $endpoint = 'https://api.twitch.tv/helix/streams/markers';
        
        $this->post( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );           
    }
         
    /**
    * Get Streams Markers
    * 
    * Gets a list of markers for either a specified user’s most recent stream 
    * or a specified VOD/video (stream), ordered by recency. A marker is an 
    * arbitrary point in a stream that the broadcaster wants to mark; 
    * e.g., to easily return to later. The only markers returned are those 
    * created by the user identified by the Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * marker information elements and a pagination field containing information 
    * required to query for more follow information.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-stream-markers
    * 
    * @param mixed $user_id
    * @param mixed $video_id
    * @param mixed $after
    * @param mixed $before
    * @param mixed $first
    * 
    * @version 1.0
    */
    public function get_streams_markers( string $user_id, string $video_id, string $after = null, string $before = null, string $first = null ) {

        $call_authentication = 'scope';

        $scope = 'user:read:broadcast';
        
        $endpoint = 'https://api.twitch.tv/helix/streams/markers';    
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );       
    }
         
    /**
    * Get Users
    * 
    * Gets information about one or more specified Twitch users. 
    * Users are identified by optional user IDs and/or login name. 
    * If neither a user ID nor a login name is specified, the user is 
    * looked up by Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of user-information elements. If this is provided, the response 
    * includes the user’s email address.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users
    * 
    * @param mixed $id
    * @param mixed $login
    * 
    * @version 6.0
    */
    public function get_users( string $id = null, string $login = null ) {

        $call_authentication = 'scope';
        
        $endpoint = 'https://api.twitch.tv/helix/users';
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint ); 
        
        $this->call_object->scope = 'user:read:email';
        
        $this->call();
        
        // We should now have $this->call_object with a response from the Twitch API...
        twitchpress_var_dump( $this->call_object );          
    }
         
    /**
    * Get Users Follows [from giving ID]
    * 
    * Gets information on follow relationships between two Twitch users. 
    * Information returned is sorted in order, most recent follow first. 
    * This can return information like “who is lirik following,”, 
    * “who is following lirik,” or “is user X following user Y.”
    * 
    * The response has a JSON payload with a data field containing an array 
    * of follow relationship elements and a pagination field containing 
    * information required to query for more follow information.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users-follows
    * 
    * @param mixed $after
    * @param mixed $first
    * @param mixed $from_id
    * @param mixed $to_id
    * 
    * @version 1.0
    */
    public function get_users_follows_from_id( string $after = null, integer $first = null, string $from_id = null, string $to_id = null ) {
    
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/users/follows?from_id=<user ID>';  
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );         
    }
    
    /**
    * Get Users Follows [to giving ID]
    * 
    * Gets information on follow relationships between two Twitch users. 
    * Information returned is sorted in order, most recent follow first. 
    * This can return information like “who is lirik following,”, 
    * “who is following lirik,” or “is user X following user Y.”
    * 
    * The response has a JSON payload with a data field containing an array 
    * of follow relationship elements and a pagination field containing 
    * information required to query for more follow information.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users-follows
    * 
    * @param mixed $after
    * @param mixed $first
    * @param mixed $from_id
    * @param mixed $to_id
    * 
    * @version 1.0
    */
    public function get_users_follows_to_id( string $after = null, integer $first = null, string $from_id = null, string $to_id = null ) {
    
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/users/follows?to_id=<user ID>';  
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );        
    }
             
    /**
    * Update User
    * 
    * Updates the description of a user specified by a Bearer token.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#update-user
    * 
    * @version 1.0 
    */
    public function update_user() {
  
        $call_authentication = 'scope';

        $scope = 'user:edit';
        
        $endpoint = 'https://api.twitch.tv/helix/users?description=<description>';     
        
        $this->put( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );      
    }
         
    /**
    * Get User Extensions
    * 
    * Gets a list of all extensions (both active and inactive) for a 
    * specified user, identified by a Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of user-information elements.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-user-extensions
    * 
    * @version 1.0 
    */
    public function get_user_extensions() {
 
        $call_authentication = 'scope';
        
        $scope = 'user:read:broadcast';

        $endpoint = 'https://api.twitch.tv/helix/users/extensions/list';       
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );    
    }
         
    /**
    * Get User Active Extensions
    * 
    * Gets information about active extensions installed by a specified user, 
    * identified by a user ID or Bearer token.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-user-active-extensions
    * 
    * @param string $user_id 
    * 
    * @version 1.0 
    */
    public function get_user_active_extensions( string $user_id = null ) {

        $call_authentication = 'scope';

        $scope = array( 'user:read:broadcast', 'user:edit:broadcast' ); 
        
        $endpoint = 'https://api.twitch.tv/helix/users/extensions';      
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );     
    }
         
    /**
    * Update User Extensions
    * 
    * Updates the activation state, extension ID, and/or version number of 
    * installed extensions for a specified user, identified by a Bearer token. 
    * If you try to activate a given extension under multiple extension types, 
    * the last write wins (and there is no guarantee of write order).
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#update-user-extensions
    * 
    * @version 1.0 
    */
    public function update_user_extensions() {

        $call_authentication = 'scope';
        
        $scope = 'user:edit:broadcast';

        $endpoint = 'https://api.twitch.tv/helix/users/extensions';     
        
        $this->put( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );      
    }
         
    /**
    * Get Videos
    * 
    * Gets video information by video ID (one or more), user ID (one only), 
    * or game ID (one only).
    * 
    * The response has a JSON payload with a data field containing an array 
    * of video elements. For lookup by user or game, pagination is available, 
    * along with several filters that can be specified as query string parameters.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-videos
    * 
    * @param mixed $id
    * @param mixed $user_id
    * @param mixed $game_id
    * @param mixed $after
    * @param mixed $before
    * @param mixed $first
    * @param mixed $language
    * @param mixed $period
    * @param mixed $sort
    * @param mixed $type
    * 
    * @version 1.0
    */
    public function get_videos( string $id, string $user_id, string $game_id, string $after = null, string $before = null, string $first = null, string $language = null, string $period = null, string $sort = null, string $type = null ) {
  
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/videos';          
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
    }
         
    /**
    * Get Webhook Subscriptions
    * 
    * Gets Webhook subscriptions, in order of expiration.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of subscription elements and a pagination field containing information 
    * required to query for more subscriptions.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-webhook-subscriptions
    * 
    * @param mixed $after
    * @param mixed $first
    * @param mixed $callback
    * @param mixed $expires_at
    * @param mixed $pagination
    * @param mixed $topic
    * @param mixed $total
    * 
    * @version 1.0
    */
    public function get_webhook_subscriptions( string $after, string $first, string $callback = null, string $expires_at = null, string $pagination = null, string $topic = null, int $total = null ) {

        $call_authentication = 'app_access_token';

        $endpoint = 'https://api.twitch.tv/helix/webhooks/subscriptions';       
        
        $this->get( $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );    
    }        
}

endif;                         