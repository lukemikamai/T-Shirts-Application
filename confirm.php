<?php
/*
 * confirm.php - For accepting requests
 * This is no longer used?
 *
 */

include_once './lib/constants.php';
include_once LIB_PATH.'db.php';
include_once CLIENT_PATH.'facebook.php';
include_once LIB_PATH.'paginator_fb.php';

$testing_on = TRUE;

if ($testing_on) {
	error_reporting(E_ALL);
}

// Facebook setup.
list($fb, $user) = getFaceBook(API_KEY, SECRET_KEY); 
$fbml = '';

// Create the user if it doesn't already exist.
$fbml .= createUser($user, $fb);


// GET

if ($testing_on && isset($_GET)) {
   echo '<pre>';
   print_r($_GET);
   echo '</pre>';
}

 
// POST

if ($testing_on && isset($_POST)) {
   echo '<pre>';
   print_r($_POST);
   echo '</pre>';
}

?>

<style>
 <?php echo htmlentities(file_get_contents('css/page.css', true), ENT_NOQUOTES); ?>
</style>

<?php

// This is where we should update the DB record to show the request has been accepted.

// Can we then go to the index page and display "MyShirts"?

?>

