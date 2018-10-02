=== TwitchPress ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/zypherevolved
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, Twitch.tv, Twitch Feed, Twitch Channel, Twitch Team, Twitch Embed, Twitch Stream, Twitch Suite, Twitch Bot, Twitch Chat 
Requires at least: 4.9
Tested up to: 4.9
Stable tag: 2.8.0
Requires PHP: 5.6
                        
Launch your own Twitch services using the TwitchPress plugin for WordPress.
                       
== Description ==

TwitchPress is an adaptable solution for the creation of a Twitch service that can do anything Twitch.tv allows. 
Marry your WordPress gaming site with your Twitch channel in everyway possible using the plugins extension system
or create a site that offersr channel management services to the public.

= Core Features =
The initial purpose of the free plugin is to share WP posts on Twitch channel feeds and collect feed updates
from Twitch.tv for publishing as new WordPress posts. All updates to the core will focus on improving this feature
and the plugins extension system. Using the extension system we can make TwitchPress and WP do anything possible
with the Twitch API. 

= Links =                                                                
*   <a href="https://twitchpress.wordpress.com" title="">Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress-Login-Extension" title="">GitHub</a>       
*   <a href="https://twitter.com/ryan_r_bayne" title="Follow the projects Tweets.">Developers Twitter</a
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">TwitchPress Twitter</a>
*   <a href="https://www.twitch.tv/zypherevolved" title="Follow my Twitch channel.">Authors Twitch</a>   
*   <a href="https://discord.gg/NaRB3wE" title="Chat about TwitchPress on Discord.">Discord Chat</a>     
*   <a href="https://www.patreon.com/zypherevolved" title="">Patreon Pledges</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="">PayPal Donations</a>       

= Features List = 
 
* Extension system 
* Custom post for each channel/user 
* Fully supported 
* Free and Premium levels of service 
* Multiple channel status shortcodes 
* Twitch API Version 5 supported
* Twitch API Version 6 in developement

= Features In Extensions = 

* Sign-In Via Twitch
* Registration Via Twitch
* Embed Live Streams
* Embed Live Chat
* Frequent data sync.
* Ultimate Member integration.

== Installation ==

1. Method 1: Move folder inside the .zip file into the "wp-content/plugins/" directory if your website is stored locally. Then upload the new plugin folder using your FTP program.
1. Method 2: Use your hosting control panels file manager to upload the plugin folder (not the .zip, only the folder inside it) to the "wp-content/plugins/" directory.
1. Method 3: In your WordPress admin click on Plugins then click on Add New. You can search for your plugin there and perform the installation easily. This method does not apply to premium plugins.

== Frequently Asked Questions ==

= Can I hire you to customize the plugin for me? =
Yes you can pay the plugin author to improve the plugin to suit your needs. Many improvements will be done free so
post your requirements on the plugins forum first. 

== Screenshots ==

1. Custom list of plugins for bulk installation and activation.
2. Example of how the WP admin is fully used. Help tab can be available on any page.
3. Security feature that helps to detect illegal entry of administrator accounts into the database.

== Languages ==

Translator needed to localize the Channel Solution for Twitch.

== Upgrade Notice ==

New setup step added. Please open the Help tab and go to the Installation section. Click on the Authorize Main Channel button. 

== Changelog ==

= 2.8.0: NOT RELEASED = 
* Bugfixes
    - No changes
* Feature Enhancements
    - Function var_dump_twitchpress() now returns if function wp_get_current_user() does not exist due to an issue experienced during development.
* Technical Enhancements
    - Function twitchpress_encode_transient_name() now requires three specific values instead of the entire request body.
    - New function missing_token() in class.twitchpress-set-app.php will replace an empty/null option value for app token (client access_token)
    - Improved application access_token renewal (then some performance improvements to come on this work)
* Configuration
    - No changes
* Database
    - No changes
    
= 2.7.0: 23rd September 2018 = 
* Bugfixes
    - Type hinting error caused
* Feature Enhancements
    - No changes
* Technical Enhancements
    - New curl class further integrated into setup wizard procedure (change in approach to getting access token)
* Configuration
    - No changes
* Database
    - No changes
    
= 2.6.0: 21st September 2018 = 
* Bugfixes
    - Setup wizard being offered even when no credentials are missing. 
    - Recent changes to twitchpress_scopes() caused incorrect handling of the returned scope array.
    - twitchpress_setup_application_save() now uses new functions for storing application credentials and not just option_update()
    - Overall the new values for storing Twitch application credentials were not applied enough.
* Feature Enhancements
    - No changes
* Technical Enhancements
    - Function twitchpress_kraken_endpoints_feed() removed
    - Function var_dump_twitchpress() now uses a different function to determine if user is allowed to see ouput
    - App Status test "Get Application Token" no longer performs a call it just checks if token is stored.
    - Function establish_application_token() is no longer called in twitch-api.php set_all_credentials()
* Configuration
    - No changes
* Database
    - No changes
    
= 2.5.0: 16th September 2018 = 
* Bugfixes
    - Possible fix for activation error "WordPress Already Installed" related to plugins install.php file.  
* Feature Enhancements
    - No changes
* Technical Enhancements
    - Removed bad use of var_dump
    - functions.twitchpress-core.php contents moved to functions.php and file deleted
    - twitchpressformatting.php is now included in the main file
    - Defination of TWITCHPRESS_PLUGIN_BASENAME moved to main file. 
    - Defination of TWITCHPRESS_PLUGIN_DIR_PATH moved to main file
* Configuration
    - No changes
* Database
    - No changes
    
= 2.4.0: 16th September 2018 = 
* Bugfixes
    - New field names applied to setup application step.
    - New option for channel name was not being populated in wizard: twitchpress_main_channels_name.
    - New option for channel ID was not being populated in wizard: twitchpress_main_channels_id.
    - Function twitchpress_scopes( true ) was returning array of empty sub arrays, worked b ut not acceptable due to new bug.
    - Scopes bug causing failure on oAuth due to incorrect handling of scopes array. 
* Enhancements
    - No Changes
* Configuration
    - No changes
* Database
    - No changes
    
= 2.3.0: 5th September 2018 = 
* Bugfixes
    - Visit API service button for Twitch now stores the $state value in shortcode_visitor_api_services_buttons()
* Enhancements
    - Big shift in code as expensive objects are broken into smaller ones and methods moved to function files (making for an easier to understand project)
    - class TWITCHPRESS_Twitch_API() has been greatly reduced to focus on making requests and not the handling of local data or features.
    - function tool_authorize_main_channel() no longer creates API object as methods replaced with functions.
    - function twitchpress_setup_improvement_save() no longer creates Twitch API object.
    - File deleted: functions.twitchpress-credentials.php (containing functions moved to functions.php)
    - File deleted: class.twitchpress-feeds.php (Twitch.tv discontinued feed service)
    - Removed database query for clearing expired transients from the installation procedure.
    - Removed flush_rewrite_rules() call as it is done in the post-types file already.
    - New loader.php file now contains the main class moved from the main twitchpress.php file. 
    - Class TwitchPress_Install() renamed to TwitchPress_Extension_Installer() (general install removed, now has one purpose)
    - class.twitchpress-install.php renamed to class.twitchpress-extension-installer.php
    - New depreciated.php functions file. 
* Configuration
    - Multiple option keys/names have changed which should be automatically resolved else go through setup wizard. 
* Database
    - No changes
    
= 2.2.0: 20th August 2018 = 
* Bugfixes
    - No changes
* Enhancements
    - Plugin name value changed to a constant in load_debugger() replacing string "twitchpress"
* Configuration
    - No changes
* Database
    - No changes
    
= 2.1.0: 20th August 2018 = 
* Bugfixes
    - confirm_scope() using in_array() instead of array_key_exists() - caused login issues!
* Enhancements
    - PHP type-hinting is now being applied. 
    - [security] Sanitizing added to three lines in update_application()
    - Function administrator_main_account_listener() now uses sanitize_key( $_GET['code'] )
    - administrator_main_account_listener() is now depreciated - replaced by a new class.
    - Deleted file class.twitchpress-settings-feeds.php (Feed settings removed)
* Configuration
    - No changes
* Database
    - No changes

= 2.0.4: 9th August 2018 = 
* Bugfixes
    -
* Enhancements
    - Twitch API application credentials are now set in the core plugins main file for easier access.
    - Twitch user oauth credentials are now set in the core plugins main file. 
    - New object registry approach added for making class objects globally available without using global.
    - Feed box removed from Edit Post view. 
    - Feed post type disabled (Twitch.tv no longer offers the Feed feature) 
* Configuration
    - No changes
* Database
    - No changes
 
= 2.0.3: July 15, 2018 =

* Bugfixes:
  - Function was missing "twitchpress_sync_currentusers_twitchsub_mainchannel", copied from sync plugin to new sync class.
  - Subscribers table was outputting the first item for all rows, reported by Shady in Discord. 
  - Changed boolean in twitchpress_is_user_authorized() which would cause a false positive if the values are not set for a user. 
  
* Enhancements:
  - check_application_token() will now log the current token stated to be invalid even when it is very new.
  - AllAPI now allows the Streamlabs Extension to load it's own API. 
  - Other API view will no longer display switches for services that are not installed.
  - twitchpress_redirect_tracking() now uses wp_redirect() instead of wp_safe_redirect() 
  - Setup wizard now uses two methods to check if channel exists. See function twitchpress_setup_application_save().
  - Main file now has a list of $GLOBALS that will make it easier to development extensions. 
  - New shortcode for creating a WP subscriber (logged-in visitor) page that allows third-party services to be authorized. 
  - Disabled the share to feed service as Twitch.tv has disabled that feature on all channels. 
  - New shortcode [twitchpress_sync_buttons_public] for listing manual data sync buttons, intended for tests and as a backup. 
  - Moved function twitchpress_shortcode_procedure_redirect() from UM extension to the frontend-notices.php (temporary approach).
  
* Configuration: 
  - No changes.

* Database: 
  - No Changes. 
 
= 2.0.2 = 
* DONE - Identical "Channel Not Confirmed" notices have been changed to help identify what instance is being displayed. 
 
= 2.0.1 = 
* FIX - Tools screen broke due to $this->sync_user() on a none object. 

= 2.0.0 = 
* NEW  - Subscribers extension support added, subscribers extension also in development. 
* DEV  - Constant TWITCHPRESS_API_NAME replaced with TWITCHPRESS_API_NAME. 
* DEV  - Twitch API files moved to new "libraries/twitch" directory.
* DEV  - Twitch API files renamed, "kraken" replaced with "twitch" for easier switching between versions.
* FIX  - Visitor Scopes checkboxes in Setup Wizard now populate and are saved. 
* DEV  - Class TWITCHPRESS_Kraken_API renamed to TWITCHPRESS_Twitch_API.
* DEV  - Class TWITCHPRESS_Kraken_Calls renamed to TWITCHPRESS_Twitch_API_Calls.
* DONE - Andsim added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - GamingFroggie added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - Scarecr0w12 added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - ImChrisP added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - theBatclam added to endorsed channels in new Twitch API version 6 (Helix directory).
* DONE - GideontheGreyFox added to endorsed channels in new Twitch API version 6 (Helix directory).
* DEV  - Typo 84600 in BugNet changed to 86400, would not cause a bug, just earlier expiry of transient caches. 
* DEV  - New option for removing all TwitchPress options during deletion of plugin (option key: twitchpress_remove_options)
* DEV  - New option for removing all feed posts during deletion of plugin (option key: twitchpress_remove_feed_posts)
* DEV  - New option for removing all TwitchPress database tables during deletion of plugin (option key: twitchpress_remove_database_tables)
* DEV  - New option for removing user data (user meta mainly) during deletion of plugin (option key: twitchpress_remove_user_data)
* DEV  - New option for removing all media generated by the TwitchPress system, during deletion of plugin (option key: twitchpress_remove_media)
* DEV  - uninstall.php has been improved.
* FIX  - Corrected wpseed_bugnet_handlerswitch_tracing by removing "wpseed_".
* DEV  - New options.php file contains arrays of all entries to the WP options table.
* DEV  - class.twitchpress-admin-uninstall.php has been removed and replaced with class.twitchpress-admin-deactivate.php 
* DEV  - New meta.php file contains arrays of meta keys used in the TwitchPress system. 
* DEV  - $twitch_wperror removed from the Twitch API library as it is not in use.
* DEV  - Streamlabs API endpoints added to All-API library.
* DEV  - Removed $twitch_call_id from Twitch API class as it is not in use.
* DEV  - Deepbot settings removed - extension on hold pending a strictly localhost only phase.
* DONE - New Sandbox Mode switch in Advanced settings.  
* DEV  - Incorrect use of TwitchPress_ListTable_Krakencalls in daily logs file change to TwitchPress_ListTable_Daily_Logs
* DEV  - New class file created for storing and managing changes history, a log specifically for key change. 
* DEV  - Subscription sync notices swapped as they were in the wrong places. 
* DEV  - confirm_scope() now returns boolean false instead of the results of error logging. 
* DEV  - getUserObject_Authd() now checks for boolean false on result from confirm_scope()
* DONE - Improved the notice that shows when the user_read permission for visitors is not ready and someone attempts to login using TwitchPress Login Extension.  
* DEV  - Sync class object now being added to the core object.
* DEV  - Sync class init now done in core plugins main class. 
* DEV  - New message.php offers notice title and sentence/paragraph management.
* DEV  = Renamed load_dependencies() in core and extensions to load_global_dependencies(). 

= 1.7.4 = 
* DONE - Sync extension has been merged into this plugin.
* DEV  - Manual subscription sync tool function added to core tools class. 

= 1.7.3 = 
* DONE - Setup Wizard links updated on Application step to take users to more applicable pages. 
* DONE - Added new links to the top of the Setup Wizard Application step - just makes more sense! 
* DONE - Fixed broken link to the ZypheREvolved Twitch channel in Help tab.  
* DEV  - Added defaults to parameters in function add_wordpress_notice().
* DEV  - do_action( 'twitchpress_manualsubsync' ) added to visitor procedure for manual Twitch sub data sync.
* DONE - Improvement program step in Setup Wizard changed to "Options".
* DONE - Setup wizard now includes the authorising of the main channel when submitting Options step. 
* DONE - Final step in the Setup Wizard looks better after some text changes.  

= 1.7.2 = 
* FIX - Corrected variable name $functions to $function in the new twitchpress_is_sync_due() function.

= 1.7.1 = 
* DONE - Added new FAQ to Help tab. 
* DONE - Corrected text domain "appointments" to "twitchpress" in around 20 locations. 
* DONE - Prevented direct access to some files in the library directory including library.twitchbot.php for better security. 
* DONE - New functions for managing sync event delays added to core functions file. 

= 1.7.0 = 
* FIX - Authorization of main account was taking user to a broken URL.
* FIX - PHP 7 does not accept rand( 10000000000000, 99999999999999 ) so broken it down into two separate rand(). 
* FIX - Above changes fixes problem when authorizing main account and having missing credentials. 

= 1.6.5 = 
* DEVS - The $code value in class.kraken-api.php is no longer url escaped. 
* DEVS - oAuth part removed from Setup Wizard when submitting application credentials. 
* DEVS - twitchpress_setup_application_save() no longer stores channel ID as current users Twitch ID. 
* DEVS - Help tab has been updated to display User and App statuses with the permitted scope for each.
* DONE - Added textareas to the Result column of the API Requests table to compact rows. 
* DONE - API Requests time column now shows the time that has passed and now raw time() value. 
* DEVS - API calls for checking app token using check_application_token() will no longer be logged as it is too common. 
* DONE - Use of get_top_games() in Help section is now logged better by adding the using function. 
* DEVS - Status sections in Help tab are now cached for 120 seconds due to the increasing number of calls within the feature. 
* DEVS - checkUserSubscription() no longer defaults token to the application token despite being a user side request. 
* DEVS - $code parameter removed from checkUserSubscription() as is no longer in use. 
* DEVS - $code parameter removed from getUserSubscription() as it is no longer in use. 
* DONE - User Status section in the Help tab now displays subscription data. 
* BUGS - The is_user_subscribed_to_main_channel() function was using WordPress user ID where Twitch user ID should be used. 
* DEVS - Removed $code parameter from checkUserSubscription() 
* DONE - Removed the Change Log link in Help tab. There is no currently an external change log.
* DEVS - Changed multiple if to elseif in administrator_main_account_listener) to reduce the script time as currently multiple if are being checked in all situations. 
* DEVS - Credential related functions moved from functions.twitchpress-core.php to the new functions.twitchpress-credentials.php 
* DEVS - twitchpress_update_user_twitchid() no longer updates twitchpress_auth_time which has not been used as far as I can tell. 
* INFO - The new functions.twitchpress-credentials.php file intends to clear up some confusion with credential management. 
* DEVS - Added security check to the Setup Wizard (now requires user to have activate_plugins capability to enter the wizard). 
* DEVS - Renamed checkUserSubscription() to get_users_subscription_apicall()

= 1.6.4 = 
* BUGS - Introduced a bug to scope checkboxes in 1.6.3 

= 1.6.3 = 
* DEVS - Isset applied to display_name to avoid notice.
* DEVS - Changed die() to wp_die() in class.twitchpress-admin-settings.php function save().
* BUGS - Notice will now be displayed when saving General settings. 
* DEVS - Removed twitchpress_redirect_tracking() and exit() line from class.twitchpress-settings-general.php. 
* INFO - The redirect for refresh in general settings prevented notice output. No reason for the redirect/refresh. 
* DONE - Submitting the Sync Values view will no longer request a new application token which resulted in a notice. 
* DONE - Added twitchstatus.com link to the Status section in Help tab to encourage indepedent investigation. 
* DEVS - Changed scope checkboxes to a new input type that allows an icon to be displayed indicating required status. 
* INFO - Scope list now indicates which scopes are required with a tick and all others with a cross. 

= 1.6.2 = 
* DONE - Improved the Status section in Help tab. 
* TEXT - Changed "Invalid user token" to "Token has expired" to seem less like a fault. 
* DEVS - Removed 2nd of 2 parameters from postFeedPost() as it would never be used. 
* DEVS - publish_to_feed() now gets the current users token and passes it to postFeedPost(). 
* DEVS - postFeedPost() now requires a user token to be passed. 

= 1.6.1 = 
* FILE - Deleted class.twitchpress-admin-main-views.php (not in use).
* FILE - Delete "includes/admin/mainviews/" as it was never used.
* BUGS - User token problems fixed. 

= 1.6.0 = 
* DEV - Scope value removed from request_app_access_token()
* DEV - request_app_access_token() now updates the stored token.
* DEV - WP posts will be shareable by default now to avoid confusion. 
* FIX - Ripple of changes through Kraken 5 library to improve token handling. 
* DEV - Function generateToken() is now request_user_access_token(). 
* DEV - $code parameter removed from getChannelObject_Authd(). 
* DEV - getChannelObject_Authd() replaced with get_tokens_channel().
* DEV - twitchpress_prepare_scopes() adds + and not literal spaces.
* DEV - Status section of Help tab now performs more tests. 
* INFO - 400, 401 and 500 errors returned again but have been addressed.
* DEV - get-channel-subscribers() no longer uses add_query_args(). 
* DEV - Added new user meta "twitchpress_token_refresh". 
* DEV - Removed wp_setcookie_twitchoauth2_ongoing() (not in use or complete). 
* DEV - administrator_main_account_listener() now uses establish_user_token() instead of request_user_access_token().
* TEX - Changed "Channel Name" on Setup Wizard to "Main Channel Name".
* TEX - Changed "ZypheREvolved, StarCitizen, TESTSquadron" to "ZypheREvolved, StarCitizen, nookyyy". 


= When To Update = 

Browse the changes log and decide if an update is required. There is nothing wrong with skipping version if it does not
help you - look for security related changes or new features that could really benefit you. If you do not see any you may want
to avoid updating. If you decide to apply the new version - do so after you have backedup your entire WordPress installation 
(files and data). Files only or data only is not a suitable backup. Every WordPress installation is different and creates a different
environment for WTG Task Manager - possibly an environment that triggers faults with the new version of this software. This is common
in software development and it is why we need to make preparations that allow reversal of major changes to our website.

== Contributors ==
Donators, GitHub contributors and developers who support me when working on TwitchPress will be listed here. 

* nookyyy      - A popular Twitch.tv streamer who done half of the testing.
* IBurn36360   - Author of the main Twitch API class on GitHub.
* Automattic   - The plugins initial design is massively based on their work.  
* Ashley Rich  - I used a great class by Ashley (Username A5shleyRich).

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the TwitchPress plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.
Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.



                  