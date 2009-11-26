<?php
/*
 * freetshirts.php - Redeem free tshirts.
 *
 */
 
// TO-DO: There is very little difference between this and index.php.
// Can they be combined? 
 
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

if (isset($_GET['tsort'])) {
	$sort = $_GET['tsort'];
} else {
	$sort = 'none';
}

if (isset($_GET['tsearch'])) {
	$search = $_GET['tsearch'];
	if ($search == 'Search for T-shirts')
		$search = '';	
} else {
	$search = '';
}

if (isset($_GET['tcategory'])) {
	$category = $_GET['tcategory'];
} else {
	$category = '';
}
 
?>

<style>
 <?php echo htmlentities(file_get_contents('css/page.css', true), ENT_NOQUOTES); ?>
</style>

<?php

/*
$ipp = items_per_page();

// Page header
$fbml .= render_header('Free');
$user_summary = get_user_summary($user);

// Body

$tbucks = $user_summary['tbucks'];

$can_redeem = FALSE;
if ($tbucks >= $price) {
	$can_redeem = TRUE;
}

$fbml .= render_free_tshirts($user, $user_summary, $qty, $price);
list($shirts, $num_shirts) = get_shirts($pagenum, $ipp, compact('sort', 'search', 'category'));
$fbml .= render_myshirts($shirts, $pagenum, 'Redeem', $sort, $search, $user, $num_shirts, $can_redeem);

// Page footer
// Build the parameters
$parms = array();
$parms['page'] = $pagenum;
$parms['tsearch'] = $search;
$parms['tsort'] = $sort;
$parms['tcategory'] = $category;

$query_string='?'.http_build_query($parms);

$fbml .= get_pagination_string($pagenum, $num_shirts, $ipp, '', '', $query_string); 
$fbml .= render_footer();
*/

$user_summary = get_user_summary($user);
$tbucks = $user_summary['tbucks'];
$price = free_tshirts_price();

$can_redeem = FALSE;
if ($tbucks >= $price) {
	$can_redeem = TRUE;
}

$rewriteid = 'main_page';
$selectedTab = 'Redeem';
$getShirts = 'get_shirts';
$renderResultsPage = 'render_results_page';

// If the user was just added then display the welcome page.

if ($userExists) {
	$search_parms = compact('sort', 'search', 'category', 'user');
	$fbml .= main_page($pagenum, $rewriteid, $selectedTab, $getShirts, $search_parms, $renderResultsPage, $can_redeem, 'render_free_tshirts', $fb);	
}
else {	
	$fbml .= welcome_page($selectedTab);	
}



echo $fbml;

?>

