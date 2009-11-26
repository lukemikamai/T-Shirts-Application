<?php

/*
 * post-invite.php - processing after the user invites friends.
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

if (TESTING_ON) {
	error_reporting(E_ALL);
}

include_once './lib/constants.php';
include_once CLIENT_PATH.'facebook.php';
include_once LIB_PATH.'db.php';
include_once LIB_PATH.'display.php';

$fb = new Facebook(API_KEY, SECRET_KEY);
$user = $fb->require_login();
$fbml = '';

$app_url = "toddshirts";

$num_invites = sizeof($_POST['ids']);
$fbml .= pre_debug('$num_invites', $num_invites);
do_invite($user, $num_invites);

$fbml .= render_footer();

echo $fbml;
			
?>



