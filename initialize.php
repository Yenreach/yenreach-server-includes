<?php
    // Defining the core paths so that require_once as expected
	//DIRECTORY SEPARATOR is a PHP pre-defined constant
	// (\ for Windows, / for Unix)
	
	defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

	// defined('SITE_ROOT') ? null : define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);
	
	defined('SITE_ROOT') ? null : define('SITE_ROOT', '/home'.DS.'omotropn');
	
	defined('HTML_ROOT') ? null : define('HTML_ROOT', SITE_ROOT.DS.'yenreach');
		
	defined('LIB_PATH') ? null : define('LIB_PATH', SITE_ROOT.DS.'includes_yenreach');
	
	//loading config file first	
	require_once(LIB_PATH.DS."config.php");
		
	//loading basic functions next so that everything after can use them
	require_once(LIB_PATH.DS."functions.php");
	
	//loading core objects
	require_once(LIB_PATH.DS."database.php");
	
	//loading database-relayed objects
	require_once(LIB_PATH.DS."mailings.php");
	require_once(LIB_PATH.DS."admins.php");
	require_once(LIB_PATH.DS."activitylogs.php");
	require_once(LIB_PATH.DS."users.php");
	require_once(LIB_PATH.DS."cardtokens.php");
	require_once(LIB_PATH.DS."sections.php");
	require_once(LIB_PATH.DS."products.php");
	require_once(LIB_PATH.DS."productphotos.php");
	require_once(LIB_PATH.DS."productcategories.php");
	require_once(LIB_PATH.DS."productcategorylist.php");
	require_once(LIB_PATH.DS."categories.php");
	require_once(LIB_PATH.DS."terms.php");
	require_once(LIB_PATH.DS."feedback.php");
	require_once(LIB_PATH.DS."privacypolicy.php");
	require_once(LIB_PATH.DS."states.php");
	require_once(LIB_PATH.DS."blogpost.php");
	require_once(LIB_PATH.DS."comments.php");
	require_once(LIB_PATH.DS."localgovernments.php");
	require_once(LIB_PATH.DS."businesses.php");
	require_once(LIB_PATH.DS."businesscategories.php");
	require_once(LIB_PATH.DS."businesssubscriptions.php");
	require_once(LIB_PATH.DS."branches.php");
	require_once(LIB_PATH.DS."businessvideolinks.php");
	require_once(LIB_PATH.DS."businessphotos.php");
	require_once(LIB_PATH.DS."businessworkinghours.php");
	require_once(LIB_PATH.DS."facilities.php");
	require_once(LIB_PATH.DS."subscriptions.php");
	require_once(LIB_PATH.DS."subscriptionpaymentplans.php");
	require_once(LIB_PATH.DS."subscriptionpayments.php");
	require_once(LIB_PATH.DS."subscribers.php");
	require_once(LIB_PATH.DS."moneyrecieveds.php");
	require_once(LIB_PATH.DS."businessfacilities.php");
	require_once(LIB_PATH.DS."businessweek.php");
	require_once(LIB_PATH.DS."pagevisits.php");
	require_once(LIB_PATH.DS."savedbusinesses.php");
	require_once(LIB_PATH.DS."emaillist.php");
	require_once(LIB_PATH.DS."usercookies.php");
	require_once(LIB_PATH.DS."businessreviews.php");
	require_once(LIB_PATH.DS."advertpaymenttypes.php");
	require_once(LIB_PATH.DS."billboardapplications.php");
?>