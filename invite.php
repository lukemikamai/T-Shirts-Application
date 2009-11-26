<?php

/*
 * invite.php - allows the user to select friends for inviting.
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

list($fb, $user) = getFaceBook(API_KEY, SECRET_KEY);
$fbml = '';

// Create the user if it doesn't already exist.
$fbml .= createUser($user, $fb);

$app_url = "toddshirts";
$fbml = '';

if (isset($_POST['ids'])) {
	$invite_ids = $_POST['ids'];
	$num_invites = sizeof($_POST['ids']);
	$fbml .= pre_debug('$num_invites', $num_invites);
	$fbml .= pre_debug('$user', $user);	
	$num_tbucks = do_invite($user, $invite_ids);
	
	// Pop-up dialog showing that user earned XXX tbucks
	$fbml .= '<script>';
	$fbml .= 'document.onload = showInvites();'; 
	$fbml .= 'function showInvites() {';
	$fbml .= 'var myDialog = new Dialog(Dialog.DIALOG_POP);';
	$fbml .= 'myDialog.showMessage(\'Thank you!\', button_confirm=\'You just earned '.$num_tbucks.' tbucks for inviting '.$num_invites.' friends.\');';
	$fbml .= '}';
	$fbml .= '</script>'; 	

}

// Use FQL to find the list of users who have installed this app already and are
// friends with this user
$friends = $fb->api_client->fql_query('SELECT uid FROM user WHERE has_added_app=1 and uid IN (SELECT uid2 FROM friend WHERE uid1 = '.$user.')');
// Parse into a comma-separated list
$excludeList = '';
if($friends){
	for( $counter = 0; $counter < count($friends); $counter++ ){
		if($excludeList != ''){
			$excludeList .= ',';
		}
		$excludeList .= $friends[$counter]['uid'];
	}
}

// Now also add to the list the friends that have already been invited in the 
// past x hours.  (x is defined in constants.php)
$recent_invites = get_recent_invites($user);
if($recent_invites){
	for( $counter = 0; $counter < count($recent_invites); $counter++ ){
		if($excludeList != ''){
			$excludeList .= ',';
		}
		$excludeList .= $recent_invites[$counter]['uid'];
	}
}

// Build your invite text
$inviteContent = htmlentities('<fb:name uid="' 
	. $user 
	. '" firstnameonly="true" shownetwork="false"/> has started sending t-shirts to <fb:pronoun uid="' 
	. $user 
	. '" possessive="true" useyou="false"/> friends and thought you might like to try it too.  Get started by adding the T-Shirts application to your profile and earn 500 T-bucks&trade; on your first visit.');
$inviteContent .= htmlentities('<fb:req-choice url="http://apps.facebook.com/toddshirts/index.php"
label="Add T-Shirts to your profile" />');

$fbml .= render_header('Invite');
$user_summary = get_user_summary($user);

if ($user%2 == 0) {
	$fbml .= render_user_summary($user_summary);
}

echo $fbml;

?>

<fb:request-form action="http://apps.facebook.com/toddshirts/invite.php"
	invite="true"
	type="T-Shirts"
	method="POST" 
	content="<?php echo $inviteContent?>">
	<fb:multi-friend-selector
	actiontext="Here is a list of your friends who don't use the T-shirts application yet:"
	<?php if($excludeList != ''){?>exclude_ids="<?php echo $excludeList?>"<?php }?> rows="4"/>
</fb:request-form>

<?

if ($user%2 != 0) {
	echo render_user_summary($user_summary);
}

 echo render_footer();
			
?>



