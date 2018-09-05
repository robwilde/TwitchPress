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

/**
 * Get slug from path
 * @param  string $key
 * @return string
 */
function twitchpress_format_plugin_slug( $key ) {
    $slug = explode( '/', $key );
    $slug = explode( '.', end( $slug ) );
    return $slug[0];
}

/**
 * Get custom capabilities for this package. These are assigned to
 * all administrators and are available for applying to moderator
 * level users.
 * 
 * Caps are assigned during installation or reset.
 *
 * @return array
 * 
 * @version 1.0
 */
function twitchpress_get_core_capabilities() {
    $capabilities = array();

    $capabilities['core'] = array(
        'manage_twitchpress',
    );

    return $capabilities;
}
    
/**
* Returns an array of scopes with user-friendly form input labels and descriptions.
* 
* @author Ryan R. Bayne
* @version 1.2
*/
function twitchpress_scopes( $scope_only = false ) {

    $scope = array(
        'channel_check_subscription' => array(),
        'channel_commercial'         => array(),
        'channel_editor'             => array(),
        'channel_feed_edit'          => array(),
        'channel_feed_read'          => array(),
        'channel_read'               => array(),
        'channel_stream'             => array(),
        'channel_subscriptions'      => array(),
        'chat_login'                 => array(),
        'collections_edit'           => array(),
        'communities_edit'           => array(),
        'communities_moderate'       => array(),
        'user_blocks_edit'           => array(),
        'user_blocks_read'           => array(),
        'user_follows_edit'          => array(),
        'user_read'                  => array(),
        'user_subscriptions'         => array(),
        'viewing_activity_read'      => array(),
        'openid'                     => array()        
    );

    // We can return scopes without additional information.
    if( $scope_only ) { return $scope; }
              
    // Add form input labels for use in form input labels. 
    $scope['user_read']['label']                  = __( 'General Account Details', 'twitchpress' );
    $scope['user_blocks_edit']['label']           = __( 'Ignore Users', 'twitchpress' );
    $scope['user_blocks_read']['label']           = __( 'Get Ignored Users', 'twitchpress' );
    $scope['user_follows_edit']['label']          = __( 'Follow Users', 'twitchpress' );
    $scope['channel_read']['label']               = __( 'Get Channel Data', 'twitchpress' );
    $scope['channel_editor']['label']             = __( 'Edit Channel', 'twitchpress' );
    $scope['channel_commercial']['label']         = __( 'Trigger Commercials', 'twitchpress' );
    $scope['channel_stream']['label']             = __( 'Reset Stream Key', 'twitchpress' );
    $scope['channel_subscriptions']['label']      = __( 'Get Your Subscribers', 'twitchpress' );
    $scope['user_subscriptions']['label']         = __( 'Get Your Subscriptions', 'twitchpress' );
    $scope['channel_check_subscription']['label'] = __( 'Check Viewers Subscription', 'twitchpress' );
    $scope['chat_login']['label']                 = __( 'Chat Permission', 'twitchpress' );
    $scope['channel_feed_read']['label']          = __( 'Get Channel Feed', 'twitchpress' );
    $scope['channel_feed_edit']['label']          = __( 'Post To Channels Feed', 'twitchpress' );
    $scope['communities_edit']['label']           = __( 'Manage Users Communities', 'twitchpress' );
    $scope['communities_moderate']['label']       = __( 'Manage Community Moderators', 'twitchpress' );
    $scope['collections_edit']['label']           = __( 'Manage Video Collections', 'twitchpress' );
    $scope['viewing_activity_read']['label']      = __( 'Viewer Heartbeat Service', 'twitchpress' );
    $scope['openid']['label']                     = __( 'OpenID Connect Service', 'twitchpress' );
            
    // Add official api descriptions - copied from official API documention.
    $scope['user_read']['apidesc']                  = __( 'Read access to non-public user information, such as email address.', 'twitchpress' );
    $scope['user_blocks_edit']['apidesc']           = __( 'Ability to ignore or unignore on behalf of a user.', 'twitchpress' );
    $scope['user_blocks_read']['apidesc']           = __( 'Read access to a user’s list of ignored users.', 'twitchpress' );
    $scope['user_follows_edit']['apidesc']          = __( 'Access to manage a user’s followed channels.', 'twitchpress' );
    $scope['channel_read']['apidesc']               = __( 'Read access to non-public channel information, including email address and stream key.', 'twitchpress' );
    $scope['channel_editor']['apidesc']             = __( 'Write access to channel metadata (game, status, etc).', 'twitchpress' );
    $scope['channel_commercial']['apidesc']         = __( 'Access to trigger commercials on channel.', 'twitchpress' );
    $scope['channel_stream']['apidesc']             = __( 'Ability to reset a channel’s stream key.', 'twitchpress' );
    $scope['channel_subscriptions']['apidesc']      = __( 'Read access to all subscribers to your channel.', 'twitchpress' );
    $scope['user_subscriptions']['apidesc']         = __( 'Read access to subscriptions of a user.', 'twitchpress' );
    $scope['channel_check_subscription']['apidesc'] = __( 'Read access to check if a user is subscribed to your channel.', 'twitchpress' );
    $scope['chat_login']['apidesc']                 = __( 'Ability to log into chat and send messages', 'twitchpress' );
    $scope['channel_feed_read']['apidesc']          = __( 'Ability to view to a channel feed.', 'twitchpress' );
    $scope['channel_feed_edit']['apidesc']          = __( 'Ability to add posts and reactions to a channel feed.', 'twitchpress' );
    $scope['communities_edit']['apidesc']           = __( 'Manage a user’s communities.', 'twitchpress' );
    $scope['communities_moderate']['apidesc']       = __( 'Manage community moderators.', 'twitchpress' );
    $scope['collections_edit']['apidesc']           = __( 'Manage a user’s collections (of videos).', 'twitchpress' );
    $scope['viewing_activity_read']['apidesc']      = __( 'Turn on Viewer Heartbeat Service ability to record user data.', 'twitchpress' );
    $scope['openid']['apidesc']                     = __( 'Use OpenID Connect authentication.', 'twitchpress' );
            
    // Add user-friendly descriptions.
    $scope['user_read']['userdesc']                  = __( 'Get email address.', 'twitchpress' );
    $scope['user_blocks_edit']['userdesc']           = __( 'Ability to ignore or unignore other users.', 'twitchpress' );
    $scope['user_blocks_read']['userdesc']           = __( 'Access to your list of ignored users.', 'twitchpress' );
    $scope['user_follows_edit']['userdesc']          = __( 'Permission to manage your followed channels.', 'twitchpress' );
    $scope['channel_read']['userdesc']               = __( 'Read your non-public channel information. Including email address and stream key.', 'twitchpress' );
    $scope['channel_editor']['userdesc']             = __( 'Ability to update meta data like game, status, etc.', 'twitchpress' );
    $scope['channel_commercial']['userdesc']         = __( 'Access to trigger commercials on channel.', 'twitchpress' );
    $scope['channel_stream']['userdesc']             = __( 'Ability to reset your channel’s stream key.', 'twitchpress' );
    $scope['channel_subscriptions']['userdesc']      = __( 'Read access to all subscribers to your channel.', 'twitchpress' );
    $scope['user_subscriptions']['userdesc']         = __( 'Permission to get your subscriptions.', 'twitchpress' );
    $scope['channel_check_subscription']['userdesc'] = __( 'Read access to check if a user is subscribed to your channel.', 'twitchpress' );
    $scope['chat_login']['userdesc']                 = __( 'Ability to log into your chat and send messages', 'twitchpress' );
    $scope['channel_feed_read']['userdesc']          = __( 'Ability to import your channel feed.', 'twitchpress' );
    $scope['channel_feed_edit']['userdesc']          = __( 'Ability to add posts and reactions to your channel feed.', 'twitchpress' );
    $scope['communities_edit']['label']              = __( 'Manage your user’s communities.', 'twitchpress' );
    $scope['communities_moderate']['label']          = __( 'Manage your community moderators.', 'twitchpress' );
    $scope['collections_edit']['label']              = __( 'Manage your collections (of videos).', 'twitchpress' );
    $scope['viewing_activity_read']['label']         = __( 'Turn on Viewer Heartbeat Service to record your user data.', 'twitchpress' );
    $scope['openid']['label']                        = __( 'Allow your OpenID Connect for authentication on this site.', 'twitchpress' );
    
    return $scope;  
}   
    
######################################################################
#                                                                    #
#                              USER                                  #
#                                                                    #
######################################################################

/**
* Checks if the giving user has Twitch API credentials.
* 
* @returns boolean false if no credentials else true
* 
* @param mixed $wp_user_id
* 
* @version 2.5
*/
function twitchpress_is_user_authorized( int $wp_user_id )  
{ 
    if( !get_user_meta( $wp_user_id, 'twitchpress_code', false ) ) {
        return false;
    }    
    if( !get_user_meta( $wp_user_id, 'twitchpress_token', false ) ) {
        return false;
    }    
    return true;
}

/**
* Gets a giving users Twitch credentials from user meta and if no user
* is giving defaults to the current logged in user. 
* 
* @returns mixed array if user has credentials else false.
* @param mixed $user_id
* 
* @version 2.0
*/
function twitchpress_get_user_twitch_credentials( int $user_id ) 
{
    if( !$user_id ) {
        return false;
    } 
    
    if( !$code = twitchpress_get_user_code( $user_id ) ) {  
        return false;
    }
    
    if( !$token = twitchpress_get_user_token( $user_id ) ) {  
        return false;
    }

    return array(
        'code'  => $code,
        'token' => $token
    );
}

/**
* Updates user code and token for Twitch.tv API.
* 
* We always store the Twitch user ID that the code and token matches. This
* will help to avoid mismatched data.
* 
* @param integer $wp_user_id
* @param string $code
* @param string $token
* 
* @version 1.0
*/
function twitchpress_update_user_oauth( int $wp_user_id, string $code, string $token, int $twitch_user_id ) {
    twitchpress_update_user_code( $wp_user_id, $code );
    twitchpress_update_user_token( $wp_user_id, $token ); 
    twitchpress_update_user_twitchid( $wp_user_id, $twitch_user_id );     
}

function twitchpress_get_user_twitchid_by_wpid( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_twitch_id', true );
}

/**
* Update users Twitch ID (in Kraken version 5 user ID and channel ID are the same).
* 
* @param integer $user_id
* @param integer $twitch_user_id
* 
* @version 1.0
*/
function twitchpress_update_user_twitchid( $user_id, $twitch_user_id ) {
    update_user_meta( $user_id, 'twitchpress_twitch_id', $twitch_user_id );    
}

function twitchpress_get_user_code( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_code', true );    
}

/**
* Update giving users oauth2 code.
* 
* @param mixed $user_id
* @param mixed $code
* 
* @version 1.0
*/
function twitchpress_update_user_code( $user_id, $code ) { 
    update_user_meta( $user_id, 'twitchpress_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_code', $code );    
}

function twitchpress_get_user_token( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_token', true );    
}

/**
* Update users oauth2 token.
* 
* @param mixed $user_id
* @param mixed $token
* 
* @version 1.0
*/
function twitchpress_update_user_token( $user_id, $token ) { 
    update_user_meta( $user_id, 'twitchpress_auth_time', time() );
    update_user_meta( $user_id, 'twitchpress_token', $token );    
}

function twitchpress_get_users_token_scopes( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_token_scope', true );    
}
 
/**
* Get the token_refresh string for extending a session. 
* 
* @param integer $user_id
* @param boolean $single
* 
* @version 1.0
*/
function twitchpress_get_user_token_refresh( $user_id, $single = true ) {
    return get_user_meta( $user_id, 'twitchpress_token_refresh', $single );
}

/**
* Update users oauth2 token_refresh string.
* 
* @param integer $user_id
* @param boolean $token
* 
* @version 1.0
*/
function twitchpress_update_user_token_refresh( $user_id, $token ) { 
    update_user_meta( $user_id, 'twitchpress_token_refresh', $token );    
}

function twitchpress_get_sub_plan( $wp_user_id, $twitch_channel_id ) {
    return get_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, true  );    
}

/**
* Get the main channel name.
* This is entered by the key holder during the setup wizard.
* 
* @version 2.0
*/
function twitchpress_get_main_channels_name() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_name ) ? $obj->main_channels_name : null; 
}

/**
* Get the main/default/official channel ID for the WP site.
* 
* @version 2.0
*/
function twitchpress_get_main_channels_twitchid() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_id ) ? $obj->main_channels_id : null;  
}

/**
* Get the channels token which is the same value as the channel owners token but this
* can make it easier to obtain that value outside of a user based procedure.
* 
* @version 2.0 
*/
function twitchpress_get_main_channels_token() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_token ) ? $obj->main_channels_token : null;
}

/**
* Get the main channels code which is the same as the channel owners code. 
* 
* @version 2.0
*/
function twitchpress_get_main_channels_code() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_code ) ? $obj->main_channels_code : null;
}

/**
* Returns the WordPress ID of the main channel owner.
* This is added to the database during the plugin Setup Wizard.
* 
* @version 2.0
*/
function twitchpress_get_main_channels_wpowner_id() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_wpowner_id ) ? $obj->main_channels_wpowner_id : null;
}

function twitchpress_get_main_channels_refresh() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_refresh ) ? $obj->main_channels_refresh : null;
}

/**
* Get the scopes that the channel owner agreed to. The value is also stored in user-meta.
* 
* @version 1.0
*/
function twitchpress_get_main_channels_scopes() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_scopes ) ? $obj->main_channels_scopes : null;
}

/**
* Get the main/default/official channels related post ID.
* 
* @version 1.0
*/
function twitchpress_get_main_channels_postid() {
    $obj = TwitchPress_Object_Registry::get( 'mainchannelauth' );
    return isset( $obj->main_channels_postid ) ? $obj->main_channels_postid : null;
}

######################################################################
#                                                                    #
#                        MAIN CHANNEL [UPDATE]                       #
#                                                                    #
######################################################################

function twitchpress_update_main_channels_code( $new_code ) {
    $new_code = sanitize_key( $new_code );
    update_option( 'twitchpress_main_channels_code', sanitize_key( $new_code ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_code', $new_code );
}

function twitchpress_update_main_channels_wpowner_id( $wp_user_id ) {
    $new_code = sanitize_key( $wp_user_id );
    update_option( 'twitchpress_main_channels_wpowner_id', sanitize_key( $wp_user_id ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_wpowner_id', $wp_user_id );
}

function twitchpress_update_main_channels_token( $new_token ) { 
    $new_code = sanitize_key( $new_token );
    update_option( 'twitchpress_main_channels_token', sanitize_key( $new_token ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_token', $new_token );
}

function twitchpress_update_main_channels_refresh_token( $new_refresh_token ) {
    $new_code = sanitize_key( $new_refresh_token );
    update_option( 'main_channels_refresh_token', sanitize_key( $new_refresh_token ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_refresh_token', $new_refresh_token );
}

function twitchpress_update_main_channels_scopes( $new_main_channels_scopes ) {
    $new_code = sanitize_key( $new_main_channels_scopes );
    update_option( 'main_channels_scopes', sanitize_key( $new_main_channels_scopes ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_scopes', $new_main_channels_scopes );
}

function twitchpress_update_main_channels_name( $new_main_channels_name ) {
    $new_code = sanitize_key( $new_main_channels_name );
    update_option( 'main_channels_name', sanitize_key( $new_main_channels_name ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_name', $new_main_channels_name );
}

function twitchpress_update_main_channels_id( $new_main_channels_id ) {
    $new_code = sanitize_key( $new_main_channels_id );
    update_option( 'main_channels_id', sanitize_key( $new_main_channels_id ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_id', $new_main_channels_id );
}

function twitchpress_update_main_channels_postid( $new_main_channels_postid ) {
    $new_code = sanitize_key( $new_main_channels_postid );
    update_option( 'main_channels_postid', sanitize_key( $new_main_channels_postid ), false ); 
    return TwitchPress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_postid', $new_main_channels_postid );
}
    
######################################################################
#                                                                    #
#                        APPLICATION [GET]                           #
#                                                                    #
######################################################################
         
function twitchpress_get_app_id() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_id ) ? $obj->app_id : null;
}          

function twitchpress_get_app_secret() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_secret ) ? $obj->app_secret : null;    
}   

function twitchpress_get_main_client_token() {   
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_token ) ? $obj->app_token : null;
}  

function twitchpress_get_app_redirect() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_redirect ) ? $obj->app_redirect : null; 
}

function twitchpress_get_app_token() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->app_token ) ? $obj->app_token : null;    
}

function twitchpress_get_app_token_scopes() {
    $obj = TwitchPress_Object_Registry::get( 'twitchapp' );
    return isset( $obj->token_scopes ) ? $obj->token_scopes : null;    
}

######################################################################
#                                                                    #
#                      APPLICATION [UPDATE]                          #
#                                                                    #
######################################################################

function twitchpress_update_app_id( $new_app_id ) {
    update_option( 'twitchpress_app_id', $new_app_id, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_id', $new_app_id );    
}

function twitchpress_update_app_secret( $new_app_secret ) {
    update_option( 'twitchpress_app_secret', $new_app_secret, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_secret', $new_app_secret );    
}

function twitchpress_update_app_redirect( $new_app_redirect ) {
    update_option( 'twitchpress_app_redirect', $new_app_redirect, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_redirect', $new_app_redirect );    
}

function twitchpress_update_app_token( $new_app_token ) {
    update_option( 'twitchpress_app_token', $new_app_token, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_token', $new_app_token );    
}

function twitchpress_update_app_token_scopes( $new_app_scopes ) {
    update_option( 'twitchpress_app_scopes', $new_app_scopes, true );
    return TwitchPress_Object_Registry::update_var( 'twitchapp', 'app_scopes', $new_app_scopes );    
}
                        
/**
* Gets the required visitor scope setup by administrator.
* 
* @version 2.0
*/
function twitchpress_get_visitor_scopes() {
    $visitor_scopes = array();
    
    foreach( twitchpress_scopes( true ) as $scope => $empty ) {
        if( get_option( 'twitchpress_visitor_scope_' . $scope ) == 'yes' ) {
            $visitor_scopes[] = $scope;
        }
    }       

    return $visitor_scopes;        
} 

/**
* Each scope is stored in an individual option. Use this method when
* an array of them is required. 
* 
* Usually when a scope name exists in options, it is an accepted scope. We will
* not assume it though. 
* 
* @version 2.5
*/
function twitchpress_get_global_accepted_scopes() {
    $global_accepted_scopes = array();

    foreach( twitchpress_scopes( true ) as $scope => $empty ) {
        if( get_option( 'twitchpress_scope_' . $scope ) == 'yes' ) {
            $global_accepted_scopes[] = $scope;
        }
    }       
    
    return $global_accepted_scopes;
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
* @version 2.0
*/
function twitchpress_confirm_scope( $scope, $side, $function ) {
    global $bugnet;
    
    // Confirm $scope is a real Twitch API permission. 
    if( !array_key_exists( $scope, twitchpress_scopes() ) ) {
        return $bugnet->log_error( 'twitchpressinvalidscope', sprintf( __( 'Twitch API request is using an invalid scope. See %s()', 'twitchpress' ), $function ), true );
    }    
    
    // Check applicable $side array scope.
    switch ( $side ) {
       case 'user':
            if( !in_array( $scope, twitchpress_get_visitor_scopes() ) ) { return $bugnet->log_error( 'twitchpressscopenotpermittedbyuser', sprintf( __( 'TwitchPress requires visitor scope: %s for function %s()', 'twitchpress' ), $scope, $function ), true ); }
         break;           
       case 'channel':
            if( !in_array( $scope, twitchpress_get_global_accepted_scopes() ) ) { return $bugnet->log_error( 'twitchpressscopenotpermittedbyadmin', sprintf( __( 'TwitchPress scope %s was not permitted by administration and is required by %s().', 'twitchpress' ), $scope, $function ), true ); }
         break;         
       case 'both':
            // This measure is temporary, to avoid faults, until we confirm which $side some calls apply to. 
            if( !in_array( $scope, twitchpress_get_global_accepted_scopes() ) &&
                    !in_array( $scope, twitchpress_get_visitor_scopes() ) ) { 
                        return $bugnet->log_error( 'twitchpressscopenotpermitted', sprintf( __( 'A Kraken5 call requires a scope that has not been permitted.', 'twitchpress' ), $function ), true ); 
            }
         break;
    }
    
    // Arriving here means the scope is valid and was found. 
    return true;
}

/**
* Generate an oAuth2 Twitch API URL for an administrator only. The procedure
* for public visitors will use different methods for total clarity when it comes to
* security. 
* 
* @author Ryan Bayne
* @version 6.0
* 
* @param array $permitted_scopes
* @param array $state_array
*/
function twitchpress_generate_authorization_url( $permitted_scopes, $local_state ) {
    global $bugnet;
        
    // Scope value will be a random code that can be matched to a transient on return.
    if( !isset( $local_state['random14'] ) ) { $local_state['random14'] = twitchpress_random14();}

    $bugnet->log( __FUNCTION__, sprintf( __( 'oAuth2 URL has been requested.', 'twitchpress' ), $local_state['random14'] ), array(), true, false );
    
    // Primary request handler - value is checked on return from Twitch.tv
    set_transient( 'twitchpress_oauth_' . $local_state['random14'], $local_state, 6000 );

    $scope = twitchpress_prepare_scopes( $permitted_scopes, true );

    // Build oauth2 URL.
    $url = 'https://api.twitch.tv/kraken/oauth2/authorize?' .
        'response_type=code' . '&' .
        'client_id=' . twitchpress_get_app_id() . '&' .
        'redirect_uri=' . twitchpress_get_app_redirect() . '&' .
        'scope=' . $scope . '&' .
        'state=' . $local_state['random14'];
        
    $bugnet->log( __FUNCTION__, sprintf( __( 'The oAuth2 URL is %s.', 'twitchpress' ), $url ), array(), true, false );
    
    return $url;       
}