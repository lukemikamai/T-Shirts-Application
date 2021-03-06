<?php
/*
 * page.php - provides fbml for the main contents of the page.
 *
 */
 
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
$fbml = '';

if (!(isset($_GET['page']))) {
	$pagenum = 1;
}
else {
	$pagenum = $_GET['page'];
}
 
$sort = '';
if (isset($_GET['tsort'])) {
	$sort = $_GET['tsort'];
}

if (isset($_POST['tsort'])) {
	$sort = $_POST['tsort'];
}

$search = '';
if (isset($_GET['tsearch'])) {
	// TO-DO: This should really be cleared during form submission (display.php)
	// TO-DO: To be changed in freetshirts.php too!
	if ($_GET['tsearch'] != 'Search for T-shirts') {
		$search = $_GET['tsearch'];
	}
}	
if (isset($_POST['tsearch'])) {
	// TO-DO: This should really be cleared during form submission (display.php)
	// TO-DO: To be changed in freetshirts.php too!
	if ($_POST['tsearch'] != 'Search for T-shirts') {
		$search = $_POST['tsearch'];
	}
}

$category = '';
if (isset($_GET['tcategory'])) {
	$category = $_GET['tcategory'];
}

if (isset($_POST['tcategory'])) {
	$category = $_POST['tcategory'];
}

$rewriteid = '';
if (isset($_GET['rewriteid'])) {
	$rewriteid = $_GET['rewriteid'];
}
if (isset($_POST['rewriteid'])) {
	$rewriteid = $_POST['rewriteid'];
}

$selected_tab = '';
if (isset($_GET['selected_tab'])) {
	$selected_tab = $_GET['selected_tab'];
}
if (isset($_POST['selected_tab'])) {
	$selected_tab = $_POST['selected_tab'];
}

$render_results = '';
if (isset($_GET['render_results'])) {
	$render_results = $_GET['render_results'];
}
if (isset($_POST['render_results'])) {
	$render_results = $_POST['render_results'];
}

$can_redeem = false;
if (isset($_GET['can_redeem'])) {
	$can_redeem = $_GET['can_redeem'];
}
if (isset($_POST['can_redeem'])) {
	$can_redeem = $_POST['can_redeem'];
}

$get_shirts = '';
if (isset($_GET['get_shirts'])) {
	$get_shirts = $_GET['get_shirts'];
}
if (isset($_POST['get_shirts'])) {
	$get_shirts = $_POST['get_shirts'];
}

$search_parms = '';
if (isset($_GET['search_parms'])) {
	$search_parms = $_GET['search_parms'];
}
$search_parms = array();
if (isset($_POST['search_parms'])) {
	$search_parms = $_POST['search_parms'];
}

$render_user_summary = '';
if (isset($_GET['render_user_summary'])) {
	$render_user_summary = $_GET['render_user_summary'];
}
$render_user_summary = array();
if (isset($_POST['render_user_summary'])) {
	$render_user_summary = $_POST['render_user_summary'];
}

$search_parms = compact('sort', 'search', 'category', 'user');

$fbml .= main_page($pagenum, $rewriteid, $selected_tab, $get_shirts, $search_parms, $render_results, $can_redeem, $render_user_summary, $fb);

echo $fbml;
?>