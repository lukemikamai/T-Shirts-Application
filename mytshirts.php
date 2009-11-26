<?php
/*
 * mytshirts.php
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

// User just earned tbucks for changing t-shirt?
if (isset($_GET['tbucks_for_change']) && false) {

	$tbucks_earned = $_GET['tbucks_for_change'];

	// Pop-up dialog showing how much the user earned for sending.
	$fbml .= '<script>';
	$fbml .= 'document.onload = showChangeEarned();';
	$fbml .= 'function showChangeEarned() {';
	$fbml .= 'var myDialog = new Dialog(Dialog.DIALOG_POP);';
	$fbml .= 'title = \'T Bucks!\';';

	if ($tbucks_earned > 0) {
		$fbml .= 'content = \'Congratulations, you just earned '.$tbucks_earned.' tbucks for changing your t-shirt.\';';	
	}
	
	else {
		$fbml .= 'content = \'Sorry you didn\\\'t earn any tbucks this time for changing your t-shirt.  Try again later.\';';	
	}
	
	$fbml .= 'myDialog.showMessage(title, content, button_confirm=\'OK\');';
	$fbml .= 'return;';	
	$fbml .= '}';
	$fbml .= '</script>'; 
}


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

/*
$ipp = items_per_page();

// Page header
$fbml .= render_header('Mine');
$user_summary = get_user_summary($user);
$fbml .= render_user_summary($user_summary);	

// Body

$shirts = get_myshirts($pagenum, $user, $ipp);
$num_shirts = get_number_myshirts($user);
$fbml .= render_myshirts($shirts, $pagenum, 'Mine', '', '', $user, $num_shirts); 

// Footer
// - Build the query string for pagination
// - Pagination
// - Page footer
$parms = array();
$parms['page'] = $pagenum;
$query_string='?'.http_build_query($parms);

$fbml .= get_pagination_string($pagenum, $num_shirts, $ipp); 	
$fbml .= render_footer();

 
*/

$search = '';
$sort = '';
$category = '';
$search_parms = compact('sort', 'search', 'category', 'user');
$rewriteid = 'main_page';
$selectedTab = 'Mine';
$getShirts = 'get_myshirts';
$renderResultsPage = 'render_results_page';

if ($userExists) {
	$fbml .= main_page($pagenum, 'main_page', $selectedTab, $getShirts, 
		$search_parms, $renderResultsPage, false, 'render_user_summary', $fb);
}
else {
	$fbml .= welcome_page($selectedTab);	
}

echo $fbml;

?>

