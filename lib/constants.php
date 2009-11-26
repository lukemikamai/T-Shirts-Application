<?php

// REMEMBER TO CHANGE base.js TOO!!!
// changing something


if(dirname($_SERVER['PHP_SELF']) == '/tshirtstest') {
	
	// constants for the test app
	define('TEST_ENV', 1); // for checking if we are in test or production environment from other files.
		
	$db_ip = 'localhost';         
	
	$db_user = 'toddbiz2';
	$db_pass = 'Cmvjgut7';
	
	// the name of the database.
	$db_name = 'toddbiz2_toddshirtstest';
	
	define('ROOT_LOCATION', 'http://www.toddbiz.com/tshirtstest');
	define('APP_SUFFIX', 'toddshirtstest');
	define('API_KEY', '0886c18158c2b542741873d1538ddbbe');
	define('SECRET_KEY', 'b5237d681cfcbd7b3bcf6cd23923d94d');
	define('MAIN_PATH', '/home/toddbiz2/public_html/tshirtstest/');
	
} else {
	
	//constants for the live app
	define('TEST_ENV', 0); // for checking if we are in test or production environment from other files.
	
	
	// The IP address of your database
	$db_ip = 'localhost';         
	
	$db_user = 'toddbiz2_toddshi';
	$db_pass = 'er98!.jj';
	
	// the name of the database.
	$db_name = 'toddbiz2_toddshirts';
	
	define('ROOT_LOCATION', 'http://www.toddbiz.com/tshirts');
	define('APP_SUFFIX', 'toddshirts');
	define('API_KEY', 'b316e169ccbadbf1ad658bbb55d53d14');
	define('SECRET_KEY', 'b00d8b3b57644c7a4cf5758937910c74');
	define('MAIN_PATH', '/home/toddbiz2/public_html/tshirts/');

}


//constants shared between test and live app.

define('LIB_PATH', MAIN_PATH . 'lib/');
define('CLIENT_PATH', MAIN_PATH . 'client/');
define('APP_NAME', 'T-Shirts');
define('IMAGE_LOCATION', 'http://www.toddbiz.com/tshirts'); // all images are stored on the production server to avoid duplicates
define('FEED_STORY_1', '136624544398');
define('FEED_STORY_2', '136612734398');
define('TESTING_ON', FALSE);
define('MAIN_COLS', 4);
define('MAIN_ROWS', 3);
define('FRIEND_COLS', 1);
define('FRIEND_ROWS', 4);
define('JOIN_BONUS', 500);
define('SEND_BONUS', 50);
define('SEND_INTERVAL', 1); // Number of hours to wait before sending again to same friend
define('INVITE_INTERVAL', 24); // Number of hours to wait before inviting the same friend again
define('MAX_RANK', 18446744073709551615);
define('MAX_BIGINT', 18446744073709551615);
define('IMG_WIDTH', 150);
define('IMG_HEIGHT', 150)

?>