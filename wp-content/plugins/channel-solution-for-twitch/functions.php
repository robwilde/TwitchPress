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
* Returns an array of scopes with user-friendly form input labels and descriptions.
* 
* @author Ryan R. Bayne
* @version 1.2
*/
function twitchpress_scopes( $scopes_only = false ) {

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
    if( $scopes_only ) { return $this->twitch_scopes; }
              
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
    