<?php
/*
 * index.php - (and For setting app boxes)
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
include_once LIB_PATH.'display.php';
include_once LIB_PATH.'db.php';
include_once CLIENT_PATH.'facebook.php';
include_once LIB_PATH.'paginator_fb.php';

$fb = new Facebook(API_KEY, SECRET_KEY);
$user = $fb->require_login();

$conn = get_db_conn();

$query = 'select sent from users where user_id=\''.$user.'\' ';
$result = mysql_query($query, $conn);
// TO-DO: Add error handling if result is not good!
if ((!$result) && TESTING_ON) {
	echo('Invalid query: ' . mysql_error());
}	
$row = mysql_fetch_assoc($result);
$sent = $row['sent'];
	

echo 'Sent: '.$sent;
	
?>
	
	
