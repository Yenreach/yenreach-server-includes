<?php
    //Localhost
    // defined('DB_SERVER')	? null:define("DB_SERVER", "localhost");
    // defined('DB_USER')	? null:define("DB_USER", "root");
    // defined('DB_PASS')	? null:define("DB_PASS", "");
    // defined('DB_NAME')	? null:define("DB_NAME", "omotropn_yenreach");
    // defined('SITE_NAME')	? null:define("SITE_NAME", "");
    // defined('FOOTER_CONTENT')	? null:define("FOOTER_CONTENT", "");
        
    /* Handle CORS */

    // Specify domains from which requests are allowed
    header('Access-Control-Allow-Origin: *');
    
    // Specify which request methods are allowed
    header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
    
    // Additional headers which may be sent along with the CORS request
    header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');
    
    // Set the age to 1 day to improve speed/caching.
    header('Access-Control-Max-Age: 86400');
    
    // Exit early so the page isn't fully loaded for options requests
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
        exit();
    }

    //Live
    defined('DB_SERVER')	? null:define("DB_SERVER", "localhost");
    defined('DB_USER')	? null:define("DB_USER", "cfciruxn_yenreach");
    defined('DB_PASS')	? null:define("DB_PASS", "Z{S.UDr+SZx~l2BRqt");
    defined('DB_NAME')	? null:define("DB_NAME", "cfciruxn_yenreach");
    defined('SITE_NAME')	? null:define("SITE_NAME", "");
    defined('FOOTER_CONTENT')	? null:define("FOOTER_CONTENT", "");
?>