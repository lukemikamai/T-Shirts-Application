<?php

/*
 * select-friends.php - allows the user to select friends for sending.
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
include_once CLIENT_PATH.'facebook.php';
include_once LIB_PATH.'db.php';
include_once LIB_PATH.'display.php';

// Facebook setup.
list($fb, $user) = getFaceBook(API_KEY, SECRET_KEY); 
$fbml = '';

// Create the user if it doesn't already exist.
$fbml .= createUser($user, $fb);


$app_url = "toddshirts";
$app_name = "t-shirts";

if (isset($_POST['Send'])) {
  $shirt_id = $_POST['shirt_ID'];
  $pagenum = $_POST['pagenum'];
  $image_link = $_POST['image'];
  $shirt_name = $_POST['shirt_name'];
} 

$canvas_url = $fb->get_facebook_url('apps') . '/' . APP_SUFFIX;

  $ret = '<h2>Send T-shirts to Friends</h2>';

  $ret .='<form id="selectFriends" fbtype="multiFeedStory" method="POST" action="'.ROOT_LOCATION.'/handlers/multiFeedHandler.php">';
    $ret .= '<div class="input_row"><fb:multi-friend-input/></div>';
  $ret .= '<p class="centeredImage"><img src="' . $image_link . '"/></p>';
  $ret .= '<input type="hidden" name="image" value="'.$image_link.'">'
    .'<input type="hidden" name="shirt_name" value="'.$shirt_name.'">'	
    .'<input type="hidden" name="shirt_ID" value="'.$shirt_id.'">'
    .'<input type="hidden" name="pagenum" value="'.$pagenum.'">'
	.'<div id="centerbutton" class="buttons">'
	.'<input type="submit" class="fb_button" id="shirt" label="Send T-Shirt" name="Send" value="Send">'
	.'</div>'
    .'</form></div>';
 
 echo render_header('Send');

 echo $ret;

 
 echo render_footer();
			
?>



