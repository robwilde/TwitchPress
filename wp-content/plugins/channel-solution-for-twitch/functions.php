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
* @version 2.0
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
    if( $scope_only ) { return array_keys( $scope ); }
              
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
    $new_code = $new_main_channels_scopes;
    update_option( 'main_channels_scopes', $new_main_channels_scopes, false ); 
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
function twitchpress_generate_authorization_url( array $permitted_scopes, $local_state ) {
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


/**
 * is_ajax - Returns true when the page is loaded via ajax.
 * 
 * The DOING_AJAX constant is set by WordPress.
 * 
 * @return bool
 */
function twitchpress_is_ajax() {          
    return defined( 'DOING_AJAX' );
}
    
/**
* Check if the home URL (stored during WordPress installation) is HTTPS. 
* If it is, we don't need to do things such as 'force ssl'.
*
* @return bool
*/
function twitchpress_is_https() {      
    return false !== strstr( get_option( 'home' ), 'https:' );
}

/**
* Determine if on the dashboard page. 
* 
* $current_screen is not set early enough for calling in some actions. So use this
* function instead.
*/
function twitchpress_is_dashboard() {      
    global $pagenow;
    // method one: check $pagenow value which could be "index.php" and that means the dashboard
    if( isset( $pagenow ) && $pagenow == 'index.php' ) { return true; }
    // method two: should $pagenow not be set, check the server value
    return strstr( $this->PHP->currenturl(), 'wp-admin/index.php' );
}

/**
* Use to check for Ajax or XMLRPC request. Use this function to avoid
* running none urgent tasks during existing operations and demanding requests.
*/
function twitchpress_is_background_process() {        
    if ( ( 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) )
            || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
            || ( defined( 'DOING_CRON' ) && DOING_CRON )
            || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                return true;
    }
               
    return false;
}

/**
 * Output any queued javascript code in the footer.
 */
function twitchpress_print_js() {
    global $twitchpress_queued_js;

    if ( ! empty( $twitchpress_queued_js ) ) {
        // Sanitize.
        $twitchpress_queued_js = wp_check_invalid_utf8( $twitchpress_queued_js );
        $twitchpress_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $twitchpress_queued_js );
        $twitchpress_queued_js = str_replace( "\r", '', $twitchpress_queued_js );

        $js = "<!-- TwitchPress JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $twitchpress_queued_js });\n</script>\n";

        /**
         * twitchpress_queued_js filter.
         *
         * @since 2.6.0
         * @param string $js JavaScript code.
         */
        echo apply_filters( 'twitchpress_queued_js', $js );

        unset( $twitchpress_queued_js );
    }
}

/**
 * Display a WordPress TwitchPress help tip.
 *
 * @since  2.5.0
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 * 
 * @version 2.0
 */
function twitchpress_help_tip( $tip, $allow_html = false ) {
    if ( $allow_html ) {
        $tip = twitchpress_sanitize_tooltip( $tip );
    } else {
        $tip = esc_attr( $tip );
    }

    return '<span class="twitchpress-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 */
function twitchpress_enqueue_js( $code ) {
    global $twitchpress_queued_js;

    if ( empty( $twitchpress_queued_js ) ) {
        $twitchpress_queued_js = '';
    }

    $twitchpress_queued_js .= "\n" . $code . "\n";
}

/**
 * Get permalink settings for TwitchPress independent of the user locale.
 *
 * @since  1.0.0
 * @return array
 */
function twitchpress_get_permalink_structure() {
    if ( function_exists( 'switch_to_locale' ) && did_action( 'admin_init' ) ) {
        switch_to_locale( get_locale() );
    }
                      
    $permalinks = wp_parse_args( (array) get_option( 'twitchpress_permalinks', array() ), array(
        'twitchpress_base'       => '',
        'category_base'          => '',
        'tag_base'               => '',
        'attribute_base'         => '',
        'use_verbose_page_rules' => false,
    ) );

    // Ensure rewrite slugs are set.
    $permalinks['twitchfeed_rewrite_slug'] = untrailingslashit( empty( $permalinks['twitchfeed_base'] ) ? _x( 'twitchfeed',          'slug', 'twitchpress' )             : $permalinks['twitchfeed_base'] );
    $permalinks['category_rewrite_slug']   = untrailingslashit( empty( $permalinks['category_base'] )   ? _x( 'twitchfeed-category', 'slug', 'twitchpress' )   : $permalinks['category_base'] );
    $permalinks['tag_rewrite_slug']        = untrailingslashit( empty( $permalinks['tag_base'] )        ? _x( 'twitchfeed-tag',      'slug', 'twitchpress' )             : $permalinks['tag_base'] );
    $permalinks['attribute_rewrite_slug']  = untrailingslashit( empty( $permalinks['attribute_base'] )  ? '' : $permalinks['attribute_base'] );

    if ( function_exists( 'restore_current_locale' ) && did_action( 'admin_init' ) ) {
        restore_current_locale();
    }
    return $permalinks;
}

/**
* Log a PHP error with extra information. Bypasses any WP configuration.

* Common Use: twitchpress_error( 'DEEPTRACE', 0, null, null, __LINE__, __FUNCTION__, __CLASS__, time() );
* 
* @version 1.2
* 
* @param string $message
* @param int $message_type 0=PHP logger|1=Email|2=Depreciated|3=Append to file|4=SAPI logging handler
* @param string $destination
* @param string $extra_headers
* @param mixed $line
* @param mixed $function
* @param mixed $class
* @param mixed $time
*/
function twitchpress_error( $message, $message_type = 0, $destination = null, $extra_headers = null, $line = null, $function = null, $class = null, $time = null ) {
    $error = 'TwitchPress Plugin: ';
    $error .= $message;
    $error .= ' (get squeekycoder@gmail.com)';
    
    // Add extra information. 
    if( $line != null || $function != null || $class != null || $time != null )
    {
        if( $line )
        {
            $error .= ' Line: ' . $line;
        }    
        
        if( $function )
        {
            $error .= ' Function: ' . $function;
        }
        
        if( $class )
        {
            $error .= ' Class: ' . $class;    
        }
        
        if( $time )
        {
            $error .= ' Time: ' . $time;
        }
    }

    return error_log( $error, $message_type, $destination, $extra_headers );
}

/**
* Create a nonced URL for returning to the current page.
* 
* @param mixed $new_parameters_array
* 
* @version 1.2
*/
function twitchpress_returning_url_nonced( $new_parameters_array, $action, $specified_url = null  ) {
    $url = add_query_arg( $new_parameters_array, $specified_url );
    
    $url = wp_nonce_url( $url, $action );
    
    return $url;
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

/**
* Validate the value passed as a $_GET['code'] prior to using it.
* 
* @return boolean false if not valid else true
* 
* @version 1.0
*/
function twitchpress_validate_code( $code ) {
    if( strlen ( $code ) !== 30  ) {
        return false;
    }           
    
    if( !ctype_alnum( $code ) ) {
        return false;
    }
    
    return true;
}      

/**
* Validate a token value.
* 
* @return boolean false if not valid else true
* 
* @version 1.0
*/
function twitchpress_validate_token( $token ) {
    if( strlen ( $token ) !== 30  ) {
        return false;
    }           
    
    if( !ctype_alnum( $token ) ) {
        return false;
    }
    
    return true;
}    

/**
* Determines if the value returned by generateToken() is a token or not.
* 
* Does not check if the token is valid as this is intended for use straight
* after a token is generated. 
* 
* @returns boolean true if the value appears normal.
* 
* @version 1.0
*/
function twitchpress_was_valid_token_returned( $returned_value ){
    
    if( !array( $returned_value ) ) {
        return false;
    }
    
    if( !isset( $returned_value['access_token'] ) ) {
        return false;
    }

    if( !twitchpress_validate_token( $returned_value['access_token'] ) ) {
        return false;
    }
    
    return true;
}                     
      
/**
* Schedule an event for syncing feed posts into WP.
* 
* @version 1.0
*/
function twitchpress_schedule_sync_channel_to_wp() {
    wp_schedule_event(
        time() + 2,
        3600,
        'twitchpress_sync_feed_to_wp'
    );    
}

/**
* Queries the custom post type 'twitchchannels' and returns post ID's that
* have a specific meta key and specific meta value.
* 
* @version 1.0
*/
function twitchpress_get_channels_by_meta( $post_meta_key, $post_meta_value, $limit = 100 ) {
    // args to query for your key
    $args = array(
        'post_type' => 'twitchchannels',
        'meta_query' => array(
            array(
                'key' => $post_meta_key,
                'value' => $post_meta_value
            )
        ),
        'fields' => 'ids'
    );
    
    // perform the query
    $query = new WP_Query( $args );
  
    if ( !empty( $query->posts ) ) {     
        return true;
    }

    return false;    
}

/**
* Adds post meta that act as settings for the main channel.
* 
* @version 1.0
* 
* @deprecated 
* @since 2.0.4
*/
function twitchpress_activate_channel_feedtowp_sync( $channel_post_id ) {
    update_post_meta( $channel_post_id, 'twitchpress_sync_feed_to_wp' );      
}

/**
* Check if giving post name (slug) already exists in wp_posts.
* 
* @param mixed $post_name
* 
* @version 1.0
*/
function twitchpress_does_post_name_exist( $post_name ) {
    global $wpdb;
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = '%s'", $post_name ), 'ARRAY_A' );
    if( $result ) {
        return true;
    } else {
        return false;
    }
}

/**
* Checks if a channel ID exists in post meta for custom post type "twitchchannels"
* 
* @returns boolean true if the Twitch channel ID already exists in post meta.
*  
* @param mixed $channel_id
* 
* @version 1.0
*/
function twitchpress_channelid_in_postmeta( $channel_id ) {
    // args to query for your key
    $args = array(
        'post_type' => 'twitchchannels',
        'meta_query' => array(
            array(
                'key' => 'twitchpress_channel_id',
                'value' => $channel_id
            )
        ),
        'fields' => 'ids'
    );
    
    // perform the query
    $query = new WP_Query( $args );
  
    if ( !empty( $query->posts ) ) {     
        return true;
    }

    return false;
}

/**
* Converts "2016-11-29T15:52:27Z" format into a timestamp. 
* 
* @param mixed $date_time_string
* 
* @version 1.0
*/
function twitchpress_convert_created_at_to_timestamp( $date_time_string ) {  
    return date_timestamp_get( date_create( $date_time_string ) );      
}

/**
* Gets a channel post 
* 
* @param mixed $channel_id
*/
function twitchpress_get_channel_post( $channel_id ) {
    // args to query for your key
    $args = array(
        'post_type' => 'twitchchannels',
        'meta_query' => array(
            array(
                'key' => 'twitchpress_channel_id',
                'value' => $channel_id
            )
        ),
    );
    
    // perform the query
    $query = new WP_Query( $args );
                            
    if ( !empty( $query->posts ) ) {     
        return $query->posts[0]->ID;
    }

    return false;     
}

/**
* Checks if the giving post type is one that
* has been permitted for sharing to Twitch channel feeds.
* 
* @version 1.0
* 
* @param string $post_type
*/
function twitchpress_is_posttype_shareable( $post_type ) {
    if( get_option( 'twitchpress_shareable_posttype_' . $post_type ) ) {
        return true;
    }
    return false;
}

/**
* Handles redirects with log entries and added arguments to URL for 
* easy visual monitoring.
* 
* @param mixed $url
* @param mixed $line
* @param mixed $function
* @param mixed $file
* 
* @version 2.0
*/
function twitchpress_redirect_tracking( $url, $line, $function, $file = '', $safe = false ) {
    global $bugnet;

    $redirect_counter = 1;
    
    // Refuse the redirect and log if twitchpressredirected=2 in giving $url. 
    if( strstr( $url, 'twitchpressredirected=1' ) ) 
    {
        $bugnet->log_error( __FUNCTION__, __( 'Possible redirect loop in progress. The giving URL was used to redirect the visitor already.', 'twitchpress' ), array(), true );    
        ++$redirect_counter;
    }
    elseif( strstr( $url, 'twitchpressredirected=2' ) )
    {
        $bugnet->log_error( __FUNCTION__, __( 'Redirect loop in progress. The giving URL was used twice.', 'twitchpress' ), array(), true );    
        return;
    }
              
                       
    // Tracking adds more values to help trace where redirect was requested. 
    if( get_option( 'twitchress_redirect_tracking_switch' ) == 'yes' ) 
    {
        $url = add_query_arg( array( 'redirected-line' => $line, 'redirected-function' => $function ), esc_url_raw( $url ) );
              
        $bugnet->trace(
            'twitchpressredirects',
            $line,
            $function,
            $file,
            false,
            __( 'TwitchPress System Redirect Visitor To: ' . $url, 'twitchpress' )           
        );
    }    
    
    if( $safe ) 
    {
        wp_safe_redirect( add_query_arg( array( 'twitchpressredirected' => $redirect_counter ), $url ) );
        exit;
    }  
    
    // Add twitchpressredirected to show that the URL has had a redirect. 
    // If it ever becomes normal to redirect again, we can increase the integer.
    wp_redirect( add_query_arg( array( 'twitchpressredirected' => $redirect_counter ), $url ) );
    exit;
}

/**
* Determines if giving value is a valid Twitch subscription plan. 
* 
* @param mixed $value
* 
* @returns boolean true if the $value is valid.
* 
* @version 1.0
*/
function twitchpress_is_valid_sub_plan( $value ){
    $sub_plans = array( 'prime', 1000, 2000, 3000 );
    if( !is_string( $value ) && !is_numeric( $value ) ){ return false;}
    if( is_string( $value ) ){ $value = strtolower( $value ); }
    if( in_array( $value, $sub_plans ) ) { return true;}
    return false;
}

/**
* Generates a random 14 character string.
* 
* @version 2.0
*/
function twitchpress_random14(){ 
    return rand( 10000000, 99999999 ) . rand( 100000, 999999 );   
}

function var_dump_twitchpress( $var ) {     
    if( !bugnet_current_user_allowed() ) { return false; }
    echo '<pre>'; var_dump( $var ); echo '</pre>';
}

function wp_die_twitchpress( $html ) {
    if( !twitchpress_are_errors_allowed() ){ return; }
    wp_die( esc_html( $html ) ); 
}

/**
* Checks if the current user is permitted to view 
* error dumps for the entire blog.
* 
* Assumes the BugNet library.
* 
* @version 1.0
*/
function twitchpress_are_errors_allowed() {
    if( twitchpress_is_background_process() ) { 
        return false; 
    }        
                     
    if( !get_option( 'twitchpress_displayerrors' ) || get_option( 'twitchpress_displayerrors' ) !== 'yes' ) {
        return false;
    }

    // We can bypass the protection to display errors for a specified user.
    if( 'BYPASS' == get_option( 'bugnet_error_dump_user_id') ) {
        return true;    
    } 
    
    // A value of ADMIN allows anyone with "activate_plugins" permission to see errors.
    if( !current_user_can( 'activate_plugins' ) ) {
        return false;
    }  
    elseif( 'ADMIN' == get_option( 'bugnet_error_dump_user_id') ) {
        return true;    
    }
    
    // Match current users ID to the entered ID which restricts error display to a single user.
    if( get_current_user_id() != get_option( 'bugnet_error_dump_user_id') ) {
        return false;    
    } 
    
    return true;
}

/**
* Adds spaces between each scope as required by the Twitch API. 
* 
* @param mixed $scopes_array
* @param mixed $for_url
* 
* @version 1.5
*/
function twitchpress_prepare_scopes( array $scopes_array ) {
        $scopes_string = '';

        foreach ( $scopes_array as $s ){

            $scopes_string .= $s . '+';
        }

        $prepped_scopes = rtrim( $scopes_string, '+' );
        
        return $prepped_scopes;
}

function twitchpress_scopecheckbox_required_icon( $scope ){
    global $system_scopes_status;
 
    $required = false; 
    
    // Do not assume every extension has set this global properly. 
    if( !is_array( $system_scopes_status ) || empty( $system_scopes_status ) ) { return ''; }
    
    // Check if $scope is required for the admins main account. 
    foreach( $system_scopes_status['admin'] as $extension_slug => $scope_information )
    {
        if( in_array( $scope, $scope_information['required'] ) ) { $required = true; break; }                      
    }    
    
    if( $required ) 
    {
        $icon = '<span class="dashicons dashicons-yes"></span>';
    }
    else
    {
        $icon = '<span class="dashicons dashicons-no"></span>';
    }
    
    return $icon;
}

function twitchpress_scopecheckboxpublic_required_icon( $scope ){
    global $system_scopes_status;
                 
    $required = false; 
    
    // Do not assume every extension has set this global properly. 
    if( !is_array( $system_scopes_status ) || empty( $system_scopes_status ) ) { return ''; }

    // Check if $scope is required for visitors accounts. 
    foreach( $system_scopes_status['public'] as $extension_slug => $scope_information )
    {
        if( in_array( $scope, $scope_information['required'] ) ) { $required = true; break; }     
    }

    if( $required ) 
    {
        $icon = '<span class="dashicons dashicons-yes"></span>';
    }
    else
    {
        $icon = '<span class="dashicons dashicons-no"></span>';
    }
    
    return $icon;
}

/**
* Get a Twitch users Twitch ID.
* 
* @version 1.0
* 
* @return integer from Twitch user object or false if failure detected.
*/
function twitchpress_get_user_twitchid( $twitch_username ) {
    $kraken = new TWITCHPRESS_Twitch_API_Calls();
    $user_object = $kraken->get_users( $twitch_username );
    if( isset( $user_object['users'][0]['_id'] ) && is_numeric( $user_object['users'][0]['_id'] ) ) {
        return $user_object['users'][0]['_id'];
    } else {
        return false;
    }
    unset( $kraken );   
}

/**
* CSS for API Requests table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apirequests() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'kraken5requests_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-header { width: 30%; }';
    echo '.wp-list-table .column-url { width: 20%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apirequests');

/**
* CSS for API Errors table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apiresponses() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'apiresponses_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-httpdstatus { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-error_no { width: 10%; }';
    echo '.wp-list-table .column-result { width: 50%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apiresponses');

/**
* CSS for API Errors table.
* 
* @version 1.0
*/
function twitchpress_css_listtable_apierrors() {
    if( !isset( $_GET['page'] ) ) { return; }
    if( !isset( $_GET['tab'] ) ) { return; }
    if( $_GET['page'] !== 'twitchpress_data' ) { return; }
    if( $_GET['tab'] !== 'apierrors_list_tables' ) { return; }
    
    echo '<style type="text/css">';
    echo '.wp-list-table .column-time { width: 10%; }';
    echo '.wp-list-table .column-function { width: 20%; }';
    echo '.wp-list-table .column-error_string { width: 30%; }';
    echo '.wp-list-table .column-error_no { width: 10%; }';
    echo '.wp-list-table .column-curl_url { width: 40%; }';
    echo '</style>';
    
}
add_action('admin_head', 'twitchpress_css_listtable_apierrors');

/**
* Get the sync timing array which holds delays for top level sync activity.
* 
* This option avoids having to creation options per service at the top level
* but if needed services can have additional options to control individual
* processes.
* 
* @version 1.0
*/
function twitchpress_get_sync_timing() {
    $sync_timing_array = get_option( 'twitchpress_sync_timing' );
    if( !$sync_timing_array || !is_array( $sync_timing_array ) ) { return array(); }
    return $sync_timing_array;
}

function twitchpress_update_sync_timing( $sync_timing_array ) {
    update_option( 'twitchpress_sync_timing', $sync_timing_array, false );    
}

/**
* Add a new sync time for a giving procedure. 
* 
* @param mixed $file
* @param mixed $function
* @param mixed $line
* @param mixed $delay
* 
* @version 1.0
*/
function twitchpress_add_sync_timing( $file, $function, $line, $delay ) {
    $sync_timing_array = twitchpress_get_sync_timing();
    $sync_timing_array[$file][$function][$line]['delay'] = $delay;
    $sync_timing_array[$file][$function][$line]['time'] = time();
    twitchpress_update_sync_timing( $sync_timing_array );    
}

/**
* A standard method for establishing time delay and if a giving method is
* due to run. Use this within any procedure to end it short or continue. 
* 
* Sets new time() if due to make it easier to manage delays within procedures. 
* 
* @param mixed $function
* @param mixed $line
* @param mixed $file
* @param mixed $delay
* 
* @returns boolean true if delay has passed already else false.
* 
* @version 2.0
*/
function twitchpress_is_sync_due( $file, $function, $line, $delay ) {
    $sync_timing_array = twitchpress_get_sync_timing();
    
    // Init the delay for the first time
    if( !isset( $sync_timing_array[$file][$function][$line] ) )
    {
        twitchpress_add_sync_timing( $file, $function, $line, $delay );
        return true;    
    }    
    else
    {
        $last_time = $sync_timing_array[$file][$function][$line]['time'];
        $soonest_time = $last_time + $delay;
        if( $soonest_time > time() ) 
        {
            $sync_timing_array[$file][$function][$line]['delay'] = $delay;
            $sync_timing_array[$file][$function][$line]['time'] = time();
            twitchpress_update_sync_timing( $sync_timing_array );
            return true;    
        }   
        
        // Not enough time has passed since the last event. 
        return false;
    }
}

/**
* Determines if the current logged in user is also the owner of the main channel.
* 
* @version 2.0
*/
function twitchpress_is_current_user_main_channel_owner( $user_id = null ) {
    if( !$user_id )
    {
        $user_id = get_current_user_id();
    }
    
    // Avoid processing the owner of the main channel (might not be admin with ID 1)
    if( twitchpress_get_main_channels_wpowner_id() == $user_id ) { return true; }
    return false;    
}

/**
* Returns the user meta value for the last time their Twitch data
* was synced with WordPress. Value is 
* 
* @returns integer time set using time() or false/null. 
* @version 1.0
*/
function twitchpress_get_user_sync_time( $user_id ) {
    return get_user_meta( $user_id, 'twitchpress_sync_time', true );
}

function twitchpress_encode_transient_name( array $values_array ) {
    $serialized_values = array();
    foreach( $values_array as $value ) {
        $serialized_values[] = base64_encode( $value );
    }
    return base64_encode( $serialized_values );    
}

function twitchpress_decode_transient_name( array $encoded_string ) {
    $decoded_array = base64_decode( $encoded_string );
    $values_array = array();
    foreach( $decoded_array as $serialized_value ) {
        $values_array = base64_decode( $serialized_value );
    }
    return $values_array;     
}