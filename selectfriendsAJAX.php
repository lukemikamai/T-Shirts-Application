<?php

/*
 * selectfriendsAJAX.php - Builds the FBML for selecting friends.
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
include_once LIB_PATH.'display.php';

	$fb = new Facebook(API_KEY, SECRET_KEY);
	$user = $fb->require_login();
	
	$app_url = "toddshirts";
	$app_name = "t-shirts";
	
	if (isset($_POST['Send'])) {
		$shirt_id = $_POST['shirt_ID'];
		$pagenum = $_POST['pagenum'];
		$image_link = $_POST['image'];
		$shirt_name = $_POST['shirt_name'];
		$shirt_name = str_replace("\\'", "'", $shirt_name);
		$shirt_name = str_replace("\\\\", "\\", $shirt_name);
	} 

	$ret = '<h2>Select your friends using the box below</h2>';
	
	$ret .='<div style="text-align: center"><form id="selectFriendsForm" fbtype="multiFeedStory" method="POST" action="'.ROOT_LOCATION.'/handlers/multiFeedHandler.php">';
	
	$ret .= '<div style="margin: 0 auto; width: 350px"><fb:multi-friend-input/></div>';
	
	$ret .= '<img class="shirt" src="' . $image_link . '"/>';
	
	$ret .= '<input type="hidden" name="image" value="'.$image_link.'">'
		.'<input type="hidden" name="shirt_name" value="'.$shirt_name.'">'	
		.'<input type="hidden" name="shirt_ID" value="'.$shirt_id.'">'
		.'<input type="hidden" name="pagenum" value="'.$pagenum.'">'
		.'<div><input type="submit" class="fb_button" id="shirt" label="Send T-Shirt" name="Send" value="Send"></div>'
		.'</form></div>';
		
	$ret .= '<fb:google-analytics uacct="UA-11149290-1" page="'.__FILE__.'" />';
		
	echo $ret;
 
?>



