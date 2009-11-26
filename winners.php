<?php

/*
 * winners.php - Displays a list of winners of free t-shirts
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

if ($userExists) {
	$user_summary = get_user_summary($user);
	// Header with tabs
	$fbml .= render_header('Winners');
	
	// User summary, with emphasis on ability (or not) to redeem.
	// Body
	$qty = free_tshirts_avail();	
	$price = free_tshirts_price();
	
	$fbml .= render_free_tshirts($user_summary);		
	
	$fbml .= '<div id="results_page">';
	
	$ipp = items_per_page();
	//	$winners = get_winners($pagenum, $sort, $search, $ipp);
	//	$num_winners = get_number_winners($sort, $search);
	//	$fbml .= render_results_page($search, $sort, $pagenum, $num_winners, $ipp, $winners, $user);
	
	$fbml .= '<div style="text-align: center;">';
	$fbml .= '<br/><h1>The first free t-shirt will be available at noon (New York city time) on the 31st of October 2009<br/>The person with the most t-bucks will be eligible to redeem a free t-shirt!<br/>Will we find your picture here on October 31st???</h1><br/>';
	$fbml .= '</div>';
	
	$fbml .= '<h2>Free T-Shirt Winners</h2>';
	
	// Get the user
	$query = 'SELECT name, pic_big FROM user WHERE uid='.$user;
	
	$user_info = $fb->api_client->fql_query($query);
	
	$pic = $user_info[0]['pic_big'];
	$name = $user_info[0]['name'];
	
	if ($pic == '') {
		$pic = 'http://static.ak.fbcdn.net/pics/d_silhouette.gif';
	}
	
	
	list($width, $height) = getimagesize($pic);
	
	$fbml .= '<div id="winnerOne" style="text-align: center;">';
	
	$fbml .= '<div><h1>Winner #1 '.$name.'???</h1></div><br/>';	
	
	// FB Profile Pic
	
	$fbml .= '<div id="userPic" style="overflow: hidden; width: '.$width.'px; height: '.$height.'px; display: block; margin-left: auto; margin-right: auto;">';
	$fbml .= '<img style="display: block; margin-left: auto; margin-right: auto;" alt="'.$name.'" src="'. $pic .'">';
	
	//	$fbml .= '<span style="width: '.$width.'px; height: '.$height.'px; background-image: url('.ROOT_LOCATION.'/images/question_overlay50.gif); background-repeat: no-repeat; display: inline; margin-left: auto; margin-right: auto; position: relative;"><img onload="Animation(this).duration(5000).checkpoint().to(\'opacity\', 0.5).from(0).duration(9000).ease(Animation.ease.end).go();" style="opacity: 0; -moz-opacity: 0; filter: alpha(opacity=0); clip:rect(0px 50px 200px 0px)" src="'.ROOT_LOCATION.'/images/question_overlay50.gif"></span>';	
	
	$offset=600-$height;
	
	$fbml .= '<img onload="Animation(this).duration(5000).checkpoint().to(\'opacity\', 0.5).from(0).by(\'height\',\'0px\').duration(9000).ease(Animation.ease.end).go(); Animation(document.getElementById(\'winnerOne\')).duration(13000).checkpoint().to(\'opacity\', 0).from(1).by(\'height\',\'0px\').duration(5000).ease(Animation.ease.end).go();" style="display: inline; margin-left: auto; margin-right: auto; position: relative; top: -'.$height.'px; opacity: 0; -moz-opacity: 0; filter: alpha(opacity=0); clip:rect(0px '.$width.'px '.$height.'px 0px); overflow: hidden;" src="'.ROOT_LOCATION.'/images/question_overlay50.gif">';
		
		
	//	$fbml .= '<img onload="Animation(this).duration(5000).checkpoint().to(\'opacity\', 0.5).from(0).duration(9000).ease(Animation.ease.end).go();" style="display: inline; margin-left: auto; margin-right: auto; position: relative; top: -600px; opacity: 0; -moz-opacity: 0; filter: alpha(opacity=0); clip:rect('.$offset.'px 200px 600px 0px); overflow: hidden;" src="'.ROOT_LOCATION.'/images/question_overlay50.gif">';
		
	$fbml .= '</div>';	
	
	
	$fbml .= '<div>';
	
	$fbml .= '</div> <!-- End of results_page div -->';
}
// User doesn't exist
else {
	$fbml .= welcome_page($selectedTab);
}


echo $fbml;


?>
