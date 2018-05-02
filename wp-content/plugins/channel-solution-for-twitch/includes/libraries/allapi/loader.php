<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include main class.
require( 'class.all-api.php' );

// Include services call methods. 
require( 'streamlabs/class.api-streamlabs-calls.php' );

// Include service API class which extend the AllAPI class.
require( 'streamlabs/class.api-streamlabs.php' );
?>
