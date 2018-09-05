<?php
/**
 * TwitchPress - Depreciated functions from the entire TwitchPress system. 
 * 
 * Move extension functions here and avoid creating file like this in every extension.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* @deprecated use twitchpress_get_main_channels_code() 
*/
function twitchpress_get_main_channel_code() {
    return get_option( 'twitchpress_main_code' );
}

/**
* @deprecated use twitchpress_get_main_channels_code() 
*/
function twitchpress_get_main_client_code() {
    return twitchpress_get_main_channel_code();
}

/**
* @deprecated use twitchpress_get_app_id()
*/
function twitchpress_get_main_client_id() {
    return get_option( 'twitchpress_main_client_id' );
}  
          
/**
* Controlled by CRON - sync feed posts into wp for a giving channel.
* 
* Assumes settings have been checked.                          
* 
* @version 1.0
* 
* @deprecated due to feed service being shutdown by Twitch.tv
*/
function twitchpress_sync_feed_to_wp( $channel_id = false ) {
    $new_posts_ids = array();

    // If no $channel_id we assume we are syncing the main channel. 
    if( !$channel_id ) { 
        $channel_id = twitchpress_get_main_channels_twitchid();   
    }
   
    if( !$channel_id ) {
        return false;
    }
    
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/kraken5/class.kraken-interface.php' );
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/kraken5/class.kraken-calls.php' );
      
    // Make call to Twitch for the latest feed post. 
    $kraken = new TWITCHPRESS_Twitch_API_Calls();
    $feed_posts = $kraken->getFeedPosts( $channel_id, 5 );
    unset( $kraken );
    if( !$feed_posts) { return; }

    // Check which feed ID's do not exist in the blog and treat them as new Twitch entries.
    foreach( $feed_posts as $entry_id => $entry_array ) {
   
        // Skip feed entries alrady in the database.
        if( twitchpress_does_feed_item_id_exist( $entry_id ) ) {
            continue;
        }
            
        // Set WP post author ID based on the feed entry author (channel owner).
        $post_author_id = twitchpress_feed_owner_wpuser_id( $channel_id );    
            
        $new_post_id = twitchpress_insert_feed_post( $channel_id, $entry_array, $post_author_id );
        
        if( is_numeric( $new_post_id ) ) {   
            $new_posts_ids[] = $new_post_id;    
        }      
    }

    return $new_posts_ids;
}

/**
* Determines if a giving feed item ID exists already or not.
*      
* @param mixed $feed_item_id
* 
* @returns boolean true if the item ID is found in post meta else returns false.
* 
* @version 1.0
* 
* @deprecated due to feed service being shutdown by Twitch.tv 
*/
function twitchpress_does_feed_item_id_exist( $feed_item_id ){ 
    $args = array(
        'post_type' => 'twitchfeed',
        'meta_query' => array(
            array(
                'key' => 'twitchpress_feed_item_id',
                'value' => $feed_item_id
            )
        ),
        'fields' => 'ids'
    );
    
    $query = new WP_Query( $args );
  
    if ( !empty( $query->posts ) ) {     
        return true;
    }

    return false;    
}      

/**
* Insert a new "twitchfeed" post.
* 
* @param mixed $channel_id
* @param mixed $feed_entry pass the feed item object as returned from the Twitch API.
* @param mixed $post_author author must be determined based on channel owner if the owner is also a user.
* @param string $process channeltowp|csvimport|customui
* 
* @returns integer post ID or a string explaining why the post was not created.
* 
* @version 1.0
* 
* @deprecated due to feed service being shutdown by Twitch.tv
*/
function twitchpress_insert_feed_post( $channel_id, $feed_entry, $post_author, $process = 'channeltowp' ) {
   
    // Ensure feed item does not already exist based on it's ID.
    if( twitchpress_does_feed_item_id_exist( $feed_entry['id'] ) ) {
        return __( 'The channel feed item already exists in this WordPress site. This was establishing by checking the items ID which was found in the database already.', 'twitchpress' );
    }    
                                           
    $post = array(
        'post_author' => 1,
        'post_title' => __( 'Latest Update by', 'twitchpress' ) . ' ' .  $feed_entry['user']['display_name'],
        'post_content' => $feed_entry['body'],
        'post_status' => 'draft',
        'post_type' => 'twitchfeed',
    );
    
    $post_id = wp_insert_post( $post, true );
    
    if( is_wp_error( $post_id ) ) {     
        return false;
    }
    
    // Add Twitch channel ID to the post as a permanent pairing. 
    add_post_meta( $post_id, 'twitchpress_channel_id', $channel_id );
    add_post_meta( $post_id, 'twitchpress_feed_item_id', $feed_entry['id'] );

    return $post_id;    
}

/**
* Determine the owner of a channel within the WP site i.e. if administrator
* entered the channel, then they own it 100% and no other user can be linked.
* 
* But what we want to establish is a linked WP user who is a subscriber to the Twitch channel
* or even just a follower. If the service allows them to enter their own channel and own
* the channel on this site then we will return their WP user ID. 
* 
* @param mixed $channel_id
* @return mixed
* 
* @version 1.0
* 
* @deprecated due to feed service being shutdown by Twitch.tv
*/
function twitchpress_feed_owner_wpuser_id( $channel_id ) {
    
    /**
    * A channels ID is the same as user ID and they will be stored in user meta. 
    * 
    * So here we will get the WP user ID that has the channel ID in their meta else
    * return a default ID. 
    */
    
    return 1;// WIP - other areas of the plugin and extensions need to progress    
}

/**
* Stores the main application token and main application scopes
* as an option value.
* 
* @param mixed $token
* @param mixed $scopes
* 
* @version 2.0
* 
* @deprecated 2.3.0 Use object registry approach.
* @see TwitchPress_Object_Registry()
*/
function twitchpress_update_main_client_token( $token, $scopes ) {
    update_option( 'twitchpress_main_token', $token );
    update_option( 'twitchpress_main_token_scopes', $scopes );
}