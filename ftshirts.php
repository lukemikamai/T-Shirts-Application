<?php
/*
 * ftshirts.php - Shows your friends' t-shirts
 *
 */

// SECURITY FIRST:  Check if this was called from a FB Canvas 
// (Or at least that someone was good enough to forge the headers)
if (!(isset($_POST['fb_sig_in_canvas']))) {
	return;
}
elseif ($_POST['fb_sig_in_canvas'] != '1') {
	return;
}
 
 
include_once './lib/constants.php';
include_once LIB_PATH.'display.php';
include_once LIB_PATH.'db.php';
include_once CLIENT_PATH.'facebook.php';
include_once LIB_PATH.'paginator_fb.php';
$fbml = '';

if (TESTING_ON) {
	error_reporting(E_ALL);
}

// Facebook setup.
list($fb, $user) = getFaceBook(API_KEY, SECRET_KEY); 
$fbml = '';

// Create the user if it doesn't already exist.
$userExists = createUser($user, $fb);


// Processing for initial page and navigating through
// pages using the pagination links at the bottom of the page

if (!(isset($_GET['page']))) {
$pagenum = 1;
}
else {
$pagenum = $_GET['page'];
}
 
?>

<style>
 <?php echo htmlentities(file_get_contents('css/page.css', true), ENT_NOQUOTES); ?>
</style>

<?php

// Page header
$fbml .= render_header('Friends');

// Body

// TODO: This needs to be made ajax? Yes!
if ($userExists) {
	
	$num_friends = 0;
	$fpp = friends_per_page();
	
	// Get the list of friends that use this app. 
	$app_users = get_app_users($fb, $user, $pagenum, $fpp);
	$num_app_users = get_num_app_users($fb, $user);
	
	// $num_app_users = get_num_app_users();
	$user_summary = get_user_summary($user);
	
	// TBUCKS SUMMARY
	if ($user%2 == 0 ) {
		$fbml .= render_user_summary($user_summary);
	}	
		
	$fbml .= render_myfriends($app_users, $pagenum, 'Friends', $user);
	
	// Footer
	$fbml .= get_pagination_string($pagenum, $num_app_users, $fpp);

	if ($user%2 != 0 ) {
		$fbml .= render_user_summary($user_summary);
	}	
	
	$fbml .= render_footer();
}
else {
	$fbml .= welcome_page($selectedTab);
}

echo $fbml;

?>

