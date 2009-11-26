<?php
/*
 * freetshirts.php - Redeem free tshirts.
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

if (TESTING_ON) {
	error_reporting(E_ALL);
}

$fb = new Facebook(API_KEY, SECRET_KEY);
$user = $fb->require_login();
 
?>

<style>
 <?php echo htmlentities(file_get_contents('css/page.css', true), ENT_NOQUOTES); ?>
</style>

<?php

$fbml = '';

// Page header
$fbml .= render_header('Invite');

// Body
$user_summary = get_user_summary($user);
$fbml .= render_invite($user, $user_summary); 

// Footer
$fbml .= render_footer();

echo $fbml;

?>

