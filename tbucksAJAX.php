<?php

/*
 * tbucksAJAX.php - Returns the number of tbucks for the user
 *
 */
 
// SECURITY FIRST:  Check if this was called from a FB Canvas 
// (Or at least that someone was good enough to forge the headers)
// if (!(isset($_POST['fb_sig_in_canvas']))) {
// 	return;
// }
// elseif ($_POST['fb_sig_in_canvas'] != '1') {
// 	return;
// }

include_once './lib/constants.php';
include_once CLIENT_PATH.'facebook.php';
include_once LIB_PATH.'db.php';

	$fb = new Facebook(API_KEY, SECRET_KEY);
	$user = $fb->require_login();
	$user_summary = get_user_summary($user);
	$fbml = render_user_summary($user_summary);
	
	echo $fbml;
?>



