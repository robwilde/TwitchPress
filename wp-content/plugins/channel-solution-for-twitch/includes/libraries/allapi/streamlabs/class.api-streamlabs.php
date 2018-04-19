<?php
/**
 * All-API Streamlabs API Class for TwitchPress.
 *
 * @link https://dev.streamlabs.com/
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TWITCHPRESS_All_API' ) ) :

class TWITCHPRESS_All_API_Streamlabs extends TWITCHPRESS_All_API {

    public function __construct(){
      
    } 
    
    /**
     * Listen for administrators main account, for a giving service, being
     * put through oAuth2 request at the point of redirection from service to WordPress. 
     * 
     * @version 1.23
     */
    public static function init() {   
        add_action( 'plugins_loaded', array( __CLASS__, 'administrator_main_account_listener' ), 50 );
    }
    
    /**
    * Listen for administration only oAuth2 return/redirect. 
    * 
    * Return when a negative condition is found.
    * 
    * Add methods between returns, where arguments satisfy minimum security. 
    * 
    * @version 1.23
    */
    public static function administrator_main_account_listener() {
        
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {   
            return;
        }
                 
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {    
            return;
        }
         
        if( defined( 'DOING_CRON' ) && DOING_CRON ) {    
            return;    
        }        
         
        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {      
            return;    
        }
        
        // This listener is for requests started on administration side only.  
        if( !is_user_logged_in() ) {         
            return;
        }      
        
        $wp_user_id = get_current_user_id();  
            
        if( isset( $_GET['error'] ) ) {  
            return;
        } 
         
        if( !isset( $_GET['scope'] ) ) {       
            return;
        }     
            
        if( !isset( $_GET['state'] ) ) {       
            return;
        }    
        
        // Change to true when $_REQUEST cannot be validated. 
        $return = false;
        $return_reason = '';
        
        // Start a trace that continues throughout the oauth2 procedure. 
        global $bugnet;
        $bugnet->trace( 'allapi_oauth2mainaccount',
                        __LINE__,
                        __FUNCTION__,
                        __FILE__,
                        false,
                        sprintf( __( 'Streamlabs Listener: doing listener for main Streamlabs account setup.', 'twitchpress' ), $this->allapi_service_title )
        );
                     
        if( !isset( $_GET['code'] ) ) {       
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: No code returned.', 'twitchpress' ), $this->allapi_service_title );
        }          

        // We require the local current state value stored in transient.
        // This transient is created when generating the oAuth2 URL and used to validate everything about the request. 
        elseif( !$transient_state = get_transient( 'twitchpress_oauth_' . $_GET['state'] ) ) {      
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: No matching transient.', 'twitchpress' ), $this->allapi_service_title );
        }  
        
        // Ensure the reason for this request is an attempt to set the main channels credentials
        elseif( !isset( $transient_state['reason'] ) ) {
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: no reason for request.', 'twitchpress' ), $this->allapi_service_title );            
        }              
         
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( $transient_state['reason'] !== 'mainadminaccountsetup' ) {         
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: reason rejected.', 'twitchpress' ), $this->allapi_service_title );    
        }
                 
        // Ensure we have the admin view or page the user needs to be sent to. 
        elseif( !isset( $transient_state['redirectto'] ) ) {         
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: "redirectto" value does not exist.', 'twitchpress' ), $this->allapi_service_title );    
        } 
          
        // For this procedure the userrole MUST be administrator.
        elseif( !isset( $transient_state['userrole'] ) ) {        
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: unexpected request, related to the main account.', 'twitchpress' ), $this->allapi_service_title );    
        }          
        
        elseif( !isset( $transient_state['userrole'] ) || 'administrator' !== $transient_state['userrole'] ) {        
            $return = true;
            $return_reason .= __( 'Streamlabs Listener: not an administrator.', 'twitchpress' );    
        }         
                
        // NEW IF - Validate the code as a measure to prevent URL spamming that gets further than here.
        elseif( !twitchpress_validate_code( $_GET['code'] ) ) {        
            $return = true;
            $return_reason .= sprintf( __( 'Streamlabs Listener: invalid code.', 'twitchpress' ), $this->allapi_service_title );
        }

        // If we have a return reason, add it to the trace then do the return. 
        if( $return === true ) {
            // We can end the trace here early but more trace entries will follow. 
            $bugnet->trace( 'streamlabs_oauth2mainaccount',
                __LINE__,
                __FUNCTION__,
                __FILE__,
                true,
                $return_reason
            );
            
            return false;
        } 
        
        // Create API calls object for the current service. 
        $service_calls_object = $this->load_calls_object( $transient_state['app_service'] );
        
        // Generate oAuth token (current user, who is admin, for the giving profile)
        $token_array = $this->request_user_access_token( $_GET['code'], __FUNCTION__ );
        
        // Update this administrators access to the giving service.
        $this->update_user_code( $wp_user_id, $_GET['code'] );
        $this->update_user_token( $wp_user_id, $token_array['access_token'] );
        $this->update_user_refresh_token( $wp_user_id, $token_array['refresh_token'] );

        // Start storing main channel credentials.  
        $this->update_app_code( $service, $_GET['code'] );
        $this->update_app_wpowner_id( $service, $wp_user_id );
        $this->update_app_token( $service, $token_array['access_token'] );
        $this->update_app_refresh_token( $service, $token_array['refresh_token'] );
        $this->update_app_scope( $service, $token_array['scope'] );

        // Token notice
        TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_mainapplicationsetup', sprintf( __( '%s provided a token, allowing this site to access your channel based on the permissions gave.'), $this->allapi_service_title )  );
               
        // Run a test to ensure all credentials are fine and that the services subject exists i.e. the users Twitch username/channel.
        // The response from the service is stored, the data is used to populate required values.  
        $test_result = $this->app_credentials_test( $service, $service_calls_object );
        
        if( !$test_result )
        {
            TwitchPress_Admin_Notices::add_custom_notice( 'streamlabs_listener_test_failed', __( '<strong>Final Test Failed:</strong> The administrator account listener has passed validation but failed the first attempt to request data from the services server.', 'twitchpress' ) );      
            
            $bugnet->trace( 'streamlabs_oauth2mainaccount',
                __LINE__,
                __FUNCTION__,
                __FILE__,
                true,
                __( 'Streamlabs Listener: the giving subject cannot be confirmed as the server response indicates a failure.', 'twitchpress' )
            );
            
            return;
        }     
               
        switch ( $this->allapi_service ) {
           case 'twitch':
                // Subject (Twitch.tv channel) is owned or under control by the admin user.  
                twitchpress_update_user_oauth( 
                    get_current_user_id(), 
                    $_GET['code'], 
                    $token_array['access_token'], 
                    $user_objects['users'][0]['_id'] 
                );
             break;
           case 'streamlabs':
        
             break;
           case 'youtube':
        
             break;
        }
  
        // Not going to end trace here, will end it on Setup Wizard. 
        $bugnet->trace( 'streamlabs_oauth2mainaccount',
            __LINE__,
            __FUNCTION__,
            __FILE__,
            true,
            __( 'Streamlabs Listener: Pass, forwarding user to: ' . $transient_state['redirectto'], 'twitchpress' )
        );
               
        // Forward user to the custom destinaton i.e. where they were before oAuth2. 
        twitchpress_redirect_tracking( $transient_state['redirectto'], __LINE__, __FUNCTION__ );
        exit;
    }     
    
    /**
    * Runs a different test for each service.  
    * 
    * @param mixed $service
    * @param mixed $service_object
    * 
    * @version 1.0
    */
    public function app_credentails_test() {

        return false;  
    }
         
    /**
    * Returns an array of scopes with user-friendly form input labels and descriptions.
    * 
    * @author Ryan R. Bayne
    * @version 1.23
    */
    public function scopes( $scopes_only = false) {
        // We can return scopes without additional information.
        if( $scopes_only ) { return $this->streamlabs_scopes; }
              
        $scope = array(
            'EMPTY' => array(),
        );
        
        // Add form input labels for use in form input labels. 
        $scope['empty']['label']    = __( 'EMPTY', 'twitchpress' );

        // Add official api descriptions - copied from official API documention.
        $scope['empty']['apidesc']  = __( 'OI DEV EMPTY AND EMPTY AND EMPTY.', 'twitchpress' );

        // Add user-friendly descriptions.
        $scope['empty']['userdesc'] = __( 'HELLO USER THIS IS EMPTY.', 'twitchpress' );

        return $scope;  
    }   

    /**
     * This operates a GET style command through cURL.  Will return raw data as an associative array
     * 
     * @param $url - [string] URL supplied for the connection
     * @param $get - [array]  All supplied data used to define what data to get
     * @param $options - [array] Set options for the cURL session
     * @param $returnStatus - [bool] Sets the function to return the numerical status instead of the raw result
     * 
     * @return $result - [mixed] The raw return of the resulting query or the numerical status
     * 
     * @version 1.6
     */
    protected function cURL_get($url, array $get = array(), array $options = array(), $returnStatus = false, $function = '' ){

        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = (( $this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;
        $header = (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $get) === 1) 
                        || (array_key_exists('oauth_token', $get) === true))) 
                                ? array_merge($header, array('Authorization: OAuth ' . $get['oauth_token'])) : $header ;
                                                        // v6 Authorization: Bearer    <access token>"  https://api.twitch.tv/helix/

        if (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $get) === 1) || (array_key_exists('oauth_token', $get) === true))) {
            unset($get['oauth_token']);
        }

        $cURL_URL = rtrim($url . '?' . http_build_query($get), '?');
              
        $default = array(
            CURLOPT_URL => $cURL_URL, 
            CURLOPT_HEADER => 0, 
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CONNECTTIMEOUT => TWITCHPRESS_DEFAULT_RETURN_TIMEOUT,
            CURLOPT_TIMEOUT => TWITCHPRESS_DEFAULT_TIMEOUT,
            CURLOPT_HTTPHEADER => $header
        );
    
        // Do we have a certificate to use?  if OpenSSL is available, there will be a certificate
        if ( TWITCHPRESS_CERT_PATH != '' ){

            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }

        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            curl_setopt_array($handle, ($options + $default));
        } else { 
            // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults
            foreach (($default + $options) as $key => $opt) {
                curl_setopt($handle, $key, $opt);
            }
        }
        
        $result = curl_exec( $handle );
        $httpdStatus = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
        
        // Check our HTTPD status that was returned for error returns
        $error_string = '';
        $error_no = '';
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) 
        {
            $error_string = curl_error($handle);
            $error_no = curl_errno($handle);
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'TwitchPress Error %s: %s', 'twitchpress' ), $error_no, $error_string ), array(), true );
        }
        
        curl_close($handle);
        
        // Log the HTTP status in more detail if it isn't a good response. 
        if( $httpdStatus !== 200 ) 
        {
            $status_meaning = kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'TwitchPress HTTPDStatus: %s - %s', 'twitchpress' ), $httpdStatus, $status_meaning ), array(), true, false );
        }
        
        if ($returnStatus) {
            $result_details = $httpdStatus;
        } else {
            $result_details = $result; 
        }
        
        // Store the get request - this is done using transients. 
        $this->store_curl_get( $function, 
                               json_decode( $result_details ), 
                               $httpdStatus, 
                               $header, 
                               $get, 
                               $url,
                               $cURL_URL, 
                               $error_string, 
                               $error_no, 
                               array( /* args */)     
        ); 
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            return $httpdStatus;
        } else {
            return $result; 
        }
    }

    /**
     * This operates a POST style cURL command.  Will return success.
     * 
     * @param $url - [string] URL supplied for the connection
     * @param $post - [array] All supplied data used to define what data to post
     * @param $options - [array] Set options for the cURL session
     * @param $returnStatus - [bool] Sets the function to return the numerical status instead of the raw result
     * 
     * @return $result - [mixed] The raw return of the resulting query or the numerical status
     * 
     * @version 1.7
     */ 
    protected function cURL_post($url, array $post = array(), array $options = array(), $returnStatus = false){
        $postfields = '';
        
        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) ? array_merge($header, array('Authorization: OAuth ' . $post['oauth_token'])) : $header;
        $header = (( $this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;                           // v6 Authorization: Bearer    <access token>"  https://api.twitch.tv/helix/
    
        if (( TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) {
            unset($post['oauth_token']);
        }

        // Custom build the post fields
        foreach ($post as $field => $value) {
            $postfields .= $field . '=' . $value . '&';
        }
        
        // Strip the trailing &
        $postfields = rtrim($postfields, '&');
        
        $default = array( 
            CURLOPT_CONNECTTIMEOUT => TWITCHPRESS_DEFAULT_RETURN_TIMEOUT,
            CURLOPT_TIMEOUT => TWITCHPRESS_DEFAULT_TIMEOUT,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_URL => $url, 
            CURLOPT_POST => count($post),
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FRESH_CONNECT => 1, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_HTTPHEADER => $header
        );
                  
        // Do we have a certificate to use?  if OpenSSL is available, there will be a certificate
        if ( TWITCHPRESS_CERT_PATH != '' ){
            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }

        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                curl_setopt($handle, $key, $opt);
            }
        }
      
        $result = curl_exec( $handle );
        
        $httpdStatus = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
        
        // Check our HTTPD status that was returned for error returns
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) {
            $error_string = curl_error($handle);
            $error_no = curl_errno($handle);
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'TwitchPress Error %s: %s', 'twitchpress' ), $error_no, $error_string ), array(), true );
        }
        
        curl_close($handle);
        
        // Log anything that isn't a good response. 
        if( $httpdStatus !== 200 ) {
            $status_meaning = kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'TwitchPress HTTPDStatus: %s - %s', 'twitchpress' ), $httpdStatus, $status_meaning ), array(), true, false );
        }
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            return $httpdStatus;
        } else {
            return $result; 
        }
    }
  
    /**
     * This operates a PUT style cURL command.  Will return success.
     * 
     * @param $url - [string] URL supplied for the connection
     * @param $put - [array] All supplied data used to define what data to put
     * @param $options - [array] Set options for the cURL session
     * @param $returnStatus - [bool] Sets the function to return the numerical status instead of the raw result
     * 
     * @return $result - [mixed] The raw return of the resulting query or the numerical status
     * 
     * @version 1.6
     */ 
    protected function cURL_put($url, array $put = array(), array $options = array(), $returnStatus = false) {
        $postfields = '';

        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $put) === 1) || (array_key_exists('oauth_token', $put) === true))) ? array_merge($header, array('Authorization: OAuth ' . $put['oauth_token'])) : $header ;
        $header = (($this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;                         // v6 Authorization: Bearer    <access token>"  https://api.twitch.tv/helix/
        
        if ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $put) === 1) || (array_key_exists('oauth_token', $put) === true))) {
            unset($put['oauth_token']);
        }

        // Custom build the post fields
        $postfields = (is_array($put)) ? http_build_query($put) : $put;
        
        $default = array( 
            CURLOPT_CONNECTTIMEOUT => TWITCH_DEFAULT_RETURN_TIMEOUT,
            CURLOPT_TIMEOUT => TWITCH_DEFAULT_TIMEOUT,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FRESH_CONNECT => 1, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_HTTPHEADER => $header
        );
        
        // Do we have a certificate to use?  if OpenSSL is available, there will be a certificate
        if ( TWITCHPRESS_CERT_PATH != '' ){

            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath( TWITCHPRESS_CERT_PATH ) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }

        $handle = curl_init();
        
        if ( function_exists('curl_setopt_array') ) {
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                curl_setopt($handle, $key, $opt);
            }
        }
        
        $result = curl_exec($handle);
        $httpdStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        
        // Check our HTTPD status that was returned for error returns
        if (($httpdStatus == 404) || ($httpdStatus == 0) || ($httpdStatus == 503)) {
            $error_string = curl_error($handle);
            $error_no = curl_errno($handle);
            $this->bugnet->log_error( __FUNCTION__, sprintf( __( 'TwitchPress Error %s: %s', 'twitchpress' ), $error_no, $error_string ), array(), true );       
        }

        curl_close($handle);
        
        // Log anything that isn't a good response. 
        if( $httpdStatus !== 200 ) {
            $status_meaning = kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'TwitchPress HTTPDStatus: %s - %s', 'twitchpress' ), $httpdStatus, $status_meaning ), array(), true, false );
        }
        
        // Are we returning the HHTPD status?
        if ($returnStatus) {
            return $httpdStatus;
        } else {
            return $result; 
        }
    }

    /**
     * This operates a POST style cURL command with the DELETE custom command option.
     * 
     * @param $url - [string] URL supplied for the connection
     * @param $post = [array]  All supplied data used to define what data to delete
     * @param $options - [array] Set options for the cURL session
     * @param $returnStatus - [bool] Sets the function to return the numerical status instead of the raw result {DEFAULTS TRUE}
     * 
     * @return $result - [mixed] The raw return of the resulting query or the numerical status
     * 
     * @version 1.2
     */ 
    protected function cURL_delete($url, array $post = array(), array $options = array(), $returnStatus = true) {
        // Specify the header
        $header = array('Accept: application/vnd.twitchtv.v' . TWITCHPRESS_API_VERSION . '+json'); // Always included
        $header = ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) ? array_merge($header, array('Authorization: OAuth ' . $post['oauth_token'])) : $header ;
        $header = (($this->twitch_client_id !== '') && ($this->twitch_client_id !== ' ')) ? array_merge($header, array('Client-ID: ' . $this->twitch_client_id)) : $header;                           // v6 Authorization: Bearer    <access token>"  https://api.twitch.tv/helix/
        
        if ((TWITCHPRESS_TOKEN_SEND_METHOD == 'HEADER') && ((array_key_exists('oauth_token', $post) === 1) || (array_key_exists('oauth_token', $post) === true))) {
            unset($post['oauth_token']);
        }
        
        $default = array(
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => TWITCHPRESS_DEFAULT_RETURN_TIMEOUT, 
            CURLOPT_TIMEOUT => TWITCHPRESS_DEFAULT_TIMEOUT,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $header
        );
                
        // Do we have a certificate to use?  if OpenSSL is available, there will be a certificate
        if (TWITCHPRESS_CERT_PATH != '') {
            // Overwrite outr defaults to include the SSL cert and options
            array_merge($default, array(
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_SSL_VERIFYHOST => 1,
                CURLOPT_CAINFO         => realpath(TWITCHPRESS_CERT_PATH) // This requires the real path of the certificate (Strict, may use CAPATH instead if it causes problems)
            ));
        }
        
        $handle = curl_init();
        
        if (function_exists('curl_setopt_array')) {
            curl_setopt_array($handle, ($options + $default));
        } else { // nope, set them one at a time
            // Options are set last so you can override anything you don't want to keep from defaults.
            foreach (($default + $options) as $key => $opt) {
                curl_setopt($handle, $key, $opt);
            }
        }

        ob_start();
        $result = curl_exec($handle);
        $httpdStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle); 
        ob_end_clean();
        
        // Log anything that isn't a good response. 
        if( $httpdStatus !== 200 ) {
            $status_meaning = kraken_httpstatuses( $httpdStatus, 'wiki' );
            if( !is_string( $status_meaning ) ) { $status_meaning = __( 'Sorry, no more information could be retrieved for this status.', 'twitchpress' ); }
            $this->bugnet->log( __FUNCTION__, sprintf( __( 'TwitchPress HTTPDStatus: %s - %s', 'twitchpress' ), $httpdStatus, $status_meaning ), array(), true, false );
        }
        
        // Are we returning the HHTPD status?
        if ($returnStatus){
            return $httpdStatus;
        } else {
            return $result; 
        }
    }
  
    /**
     * This function iterates through calls.  Put in here to keep the code the exact same every time
     * This assumes that all values are checked before being passed to here, PLEASE CHECK YOUR PARAMS
     * 
     * @param $functionName - [string] The calling function's identity, used for logging only
     * @param $url - [string] The URL to iterate on
     * @param $options - [array] The array of options to use for the iteration
     * @param $limit - [int] The limit of the query
     * @param $offset - [int] The starting offset of the query
     * 
     * -- OPTIONAL PARAMS --
     * The following params are all optional and are specific the the calling funciton.  Null disables the param from being passed
     * 
     * @param $arrayKey - [string] The key to look into the array for for data
     * @param $authKey - [string] The OAuth token for the session of calls
     * @param $hls - [bool] Limit the calls to only streams using HLS
     * @param $direction - [string] The sorting direction
     * @param $channels - [array] The array of channels to be included in the query
     * @param $embedable - [bool] Limit query to only channels that are embedable
     * @param $client_id - [string] Limit searches to only show content from the applications of the supplied client ID
     * @param $broadcasts - [bool] Limit returns to only show broadcasts
     * @param $period - [string] The period of time in which  to limit the search for
     * @param $game - [string] The game to limit the query to
     * @param $returnTotal - [bool] Sets iteration to not ignore the _total key
     * @param $sortBy - [string] Sets the sorting key
     * 
     * @return $object - [arary] unkeyed array of data requested or rmpty array if no data was returned
     * 
     * @version 1.5
     */ 
    protected function get_iterated( $url, $options, $limit, $offset, $arrayKey = null, $authKey = null, $hls = null, $direction = null, $channels = null, $embedable = null, $client_id = null, $broadcasts = null, $period = null, $game = null, $returnTotal = false, $sortBy = null) {

        // Check to make sure limit is an int
        if ((gettype($limit) != 'integer') && (gettype($limit) != 'double') && (gettype($limit) != 'float')) {
            // Either the limit was not valid
            $limit = -1;
        } elseif (gettype($limit != 'integer')) {
            // Make sure we have an int
            $limit = floor($limit);
            
            if ($limit < 0) {
                // Set to unlimited
                $limit = -1;
            }
        }

        // Perform the same check on the offset
        if ((gettype($offset) != 'integer') && (gettype($offset) != 'double') && (gettype($offset) != 'float')){
            $offset = 0;
        } elseif (gettype($offset != 'integer')) {
            // Make sure we have an int
            $offset = floor($offset);
            
            if ($offset < 0){
                // Set to base
                $offset = 0;
            }
        }

        // Init some vars
        $channelBlock = '';
        $grabbedRows = 0;
        $toDo = 0;
        $currentReturnRows = 0;
        $counter = 1;
        $iterations = 1;
        $object = array();
        if ($limit == -1){
            $toDo = 100000000; // Set to an arbritrarily large number so that we can itterate forever if need be
        } else {
            $toDo = $limit; // We have a finite amount of iterations to do, account for the _links object in the first return
        }
        
        // Calculate the starting limit
        if ($toDo > ( TWITCHPRESS_CALL_LIMIT_SETTING + 1)){
            $startingLimit = TWITCHPRESS_CALL_LIMIT_SETTING;
        } else {
            $startingLimit = $toDo;                                                             
        }
        
        // Build our GET array for the first iteration, these values will always be supplied
        $get = array('limit' => $startingLimit,
            'offset' => $offset);
            
        // Now check every optional param to see if it exists and att it to the array
        if ($authKey != null) {
            $get['oauth_token'] = $authKey;                                       
        }
        
        if ($hls != null) {
            $get['hls'] = $hls;                                                          
        }
        
        if ($direction != null) {
            $get['direction'] = $direction;                                          
        }
        
        if ($channels != null) {
            foreach ($channels as $channel) {
                $channelBlock .= $channel . ',';
                $get['channel'] = $channelBlock;
            }
            
            $channelBlock = rtrim($channelBlock, ',');                             
        }
        
        if ($embedable != null) {
            $get['embedable'] = $embedable;                                        
        }
        
        if ($client_id != null) {
            $get['client_id'] = $client_id;                                         
        }
        
        if ($broadcasts != null) {
            $get['broadcasts'] = $broadcasts;                                            
        }
        
        if ($period != null) {
            $get['period'] = $period;                                            
        }
        
        if ($game != null) {
            $get['game'] = $game;                                              
        }
        
        if ($sortBy != null) {
            $get['sortby'] = $sortBy;                                            
        }

        // Build our cURL query and store the array
        $return = json_decode($this->cURL_get($url, $get, $options), true);

        // check to see if return was 0, this indicates a staus return
        if ($return == 0) {
            for ($i = 1; $i <= TWITCHPRESS_RETRY_COUNTER; $i++) {
                $return = json_decode($this->cURL_get($url, $get, $options), true);
                if ($return != 0) {
                    break;
                }
            }
        }
        
        // How many returns did we get?
        if ($arrayKey != null) {
            if ((array_key_exists($arrayKey, $return) == 1) || (array_key_exists($arrayKey, $return) == true)){
                $currentReturnRows = count($return[$arrayKey]);
            } else {
                // Retry the call if we can
                for ($i = 1; $i <= TWITCHPRESS_RETRY_COUNTER; $i++){
                    $return = json_decode($this->cURL_get($url, $get, $options), true);
                    
                    if ((array_key_exists($arrayKey, $return) == 1) || (array_key_exists($arrayKey, $return) == true)){
                        $currentReturnRows = count($return[$arrayKey]);
                        break;
                    }
                }                
            }
            
        } else {
            $currentReturnRows = count($return);
        }

        // Iterate until we have everything grabbed we want to have
        while (($toDo > TWITCHPRESS_CALL_LIMIT_SETTING + 1) && ($toDo > 0) || ($limit == -1)){
            // check to see if return was 0, this indicates a staus return
            if ($return == 0){
                for ($i = 1; $i <= TWITCHPRESS_RETRY_COUNTER; $i++){
                    $return = json_decode($this->cURL_get($url, $get, $options), true);
                    
                    if ($return != 0){
                        break;
                    }
                }
            }
            
            // How many returns did we get?
            if ($arrayKey != null){
                if ((array_key_exists($arrayKey, $return) == 1) || (array_key_exists($arrayKey, $return) == true)) {
                    $currentReturnRows = count($return[$arrayKey]);
                } else {
                    // Retry the call if we can
                    for ($i = 1; $i <= TWITCHPRESS_RETRY_COUNTER; $i++){
                        $return = json_decode($this->cURL_get($url, $get, $options), true);
                        
                        if ((array_key_exists($arrayKey, $return) == 1) || (array_key_exists($arrayKey, $return) == true)){
                            $currentReturnRows = count($return[$arrayKey]);
                            break;
                        }
                    }                
                }
                
            } else {
                $currentReturnRows = count($return);
            }
            
            $grabbedRows += $currentReturnRows;

            // Return the data we requested into the array
            foreach ($return as $key => $value){
                // Skip some of the data we don't need
                if (is_array($value) && ($key != '_links')) {
                    foreach ($value as $k => $v) {
                        if (($k === '_links') || ($k === '_total') || !(is_array($v))){
                            continue;
                        }
                        
                        $object[$counter] = $v;
                        $counter ++;
                    }                        
                } elseif ($returnTotal && ($key == '_total') && !(key_exists('_total', $object) == 1)) {
                    // Are we on the _total key?  As well, have we already set it? (I might revert the key check if it ends up providing odd results)
                    $object['_total'] = $value;
                }
            }
            
            // Calculate our returns and our expected returns
            $expectedReturns = $startingLimit * $iterations;
            $currentReturns = $counter - 1;
            
            // Have we gotten everything we requested?
            if ($toDo <= 0){
                break;
            }
            
            // Are we no longer getting data? (Some fancy math here)
            if ($currentReturns != $expectedReturns) {
                break;
            }
            
            if ($limit != -1){
                $toDo = $limit - $currentReturns;
            }
            
            if ($toDo == 1){
                $toDo = 2; // Catch this, it will drop one return
            }
            
            // Check how many we have left
            if (($toDo > $startingLimit) && ($toDo > 0) && ($limit != -1)){

                $get = array('limit' => $currentReturns + $startingLimit,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;
                }
                
                if ($hls != null) {
                    $get['hls'] = $hls;            
                }
                
                if ($direction != null) {
                    $get['direction'] = $direction;   
                }
                
                if ($channels != null) {
                    foreach ($channels as $channel) {
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ','); 
                }
                
                if ($embedable != null) {
                    $get['embedable'] = $embedable;                                         
                }
                
                if ($client_id != null) {
                    $get['client_id'] = $client_id;                                        
                }
                
                if ($broadcasts != null) {
                    $get['broadcasts'] = $broadcasts;                                            
                }
                
                if ($period != null) {
                    $get['period'] = $period;                                            
                }
                
                if ($game != null) {
                    $get['game'] = $game;
                }
                
                if ($sortBy != null) {
                    $get['sortby'] = $sortBy;
                }
            } elseif ($limit == -1) {
                
                $get = array('limit' => $currentReturns + $startingLimit,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;
                }
                
                if ($hls != null) {
                    $get['hls'] = $hls;            
                }
                
                if ($direction != null) {
                    $get['direction'] = $direction;   
                }
                
                if ($channels != null) {
                    foreach ($channels as $channel) {
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ',');   
                }
                
                if ($embedable != null) {
                    $get['embedable'] = $embedable;
                }
                
                if ($client_id != null) {
                    $get['client_id'] = $client_id;
                }
                
                if ($broadcasts != null) {
                    $get['broadcasts'] = $broadcasts; 
                }
                
                if ($period != null) {
                    $get['period'] = $period;       
                }
                
                if ($game != null) {
                    $get['game'] = $game;         
                }
                
                if ($sortBy != null) {
                    $get['sortby'] = $sortBy;  
                }
                
            // Last return in a limited case    
            } else { 

                $get = array('limit' => $toDo + 1,
                    'offset' => $currentReturns);
                    
                // Now check every optional param to see if it exists and att it to the array
                if ($authKey != null) {
                    $get['oauth_token'] = $authKey;

                }
                
                if ($hls != null){
                    $get['hls'] = $hls;            
                }
                
                if ($direction != null){
                    $get['direction'] = $direction;   
                }
                
                if ($channels != null){
                    foreach ($channels as $channel){
                        $channelBlock .= $channel . ',';
                        $get['channel'] = $channelBlock;
                    }
                    
                    $channelBlock = rtrim($channelBlock, ','); 
                }
                
                if ($embedable != null){
                    $get['embedable'] = $embedable;
                }
                
                if ($client_id != null){
                    $get['client_id'] = $client_id;
                }
                
                if ($broadcasts != null){
                    $get['broadcasts'] = $broadcasts;
                }
                
                if ($period != null){
                    $get['period'] = $period;
                }
                
                if ($game != null){
                    $get['game'] = $game;
                }
                
                if ($sortBy != null){
                    $get['sortby'] = $sortBy;
                }
            }

            // Run a new query
            unset($return); // unset for a clean return
            $return = json_decode($this->cURL_get($url, $get, $options), true);
            
            $iterations ++;
        }

        // Run this one last time, a little redundant, but we could have skipped a return
        foreach ($return as $key => $value){
            // Skip some of the data we don't need
            if (is_array($value) && ($key != '_links')) {
                foreach ($value as $k => $v){
                    if (($k === '_links') || ($k === '_total') || !(is_array($v))){
                        continue;
                    }
                    
                    $object[$counter] = $v;
                    $counter ++;
                }                        
            } elseif ($returnTotal && ($key == '_total') && !(key_exists('_total', $object) == 1)) {
                // Are we on the _total key?  As well, have we already set it? (I might revert the key check if it ends up providing odd results)
                $object['_total'] = $value;
            }
        }
        
        if ($returnTotal && !key_exists('_total', $object) == 1){
            $object['_total'] = count($object);
        }
        
        return $object;
    }    
    
    /**
    * Confirms if the $scope has been permitted for the
    * $side the call applies to.
    * 
    * Should be called at the beginning of most calls methods. 
    * 
    * The $function is passed to aid debugging. 
    * 
    * @param mixed $scope
    * @param mixed $side
    * @param mixed $function
    * 
    * @version 1.2
    */
    public function confirm_scope( $scope, $side, $function ) {
        global $bugnet;
        
        // Confirm $scope is a real scope. 
        if( !in_array( $scope, $this->twitch_scopes ) ) {
            return $bugnet->log_error( 'twitchpressinvalidscope', sprintf( __( 'A Kraken5 call is using an invalid scope. See %s()', 'twitchpress' ), $function ), true );
        }    
        
        // Check applicable $side array scope.
        switch ( $side ) {
           case 'user':
                if( !in_array( $scope, $this->get_user_scopes() ) ) { return $bugnet->log_error( 'twitchpressscopenotpermittedbyuser', sprintf( __( 'TwitchPress requires visitor scope: %s for function %s()', 'twitchpress' ), $scope, $function ), true ); }
             break;           
           case 'channel':
                if( !in_array( $scope, $this->get_global_accepted_scopes() ) ) { return $bugnet->log_error( 'twitchpressscopenotpermittedbyadmin', sprintf( __( 'TwitchPress scope %s was not permitted by administration and is required by %s().', 'twitchpress' ), $scope, $function ), true ); }
             break;         
           case 'both':
                // This measure is temporary, to avoid faults, until we confirm which $side some calls apply to. 
                if( !in_array( $scope, $this->get_global_accepted_scopes() ) &&
                        !in_array( $scope, $this->get_user_scopes() ) ) { 
                            return $bugnet->log_error( 'twitchpressscopenotpermitted', sprintf( __( 'A Kraken5 call requires a scope that has not been permitted.', 'twitchpress' ), $function ), true ); 
                }
             break;
        }
        
        // Arriving here means the scope is valid and was found. 
        return true;
    }   
}

endif;