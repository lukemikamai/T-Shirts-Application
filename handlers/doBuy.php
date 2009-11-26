<?php
/*
 * doBuy.php - Registers when the user clicks the buy button.
 *
 */

include_once '../lib/constants.php';
include_once LIB_PATH.'db.php';
include_once CLIENT_PATH.'facebook.php';

if (TESTING_ON) {
	error_reporting(E_ALL);
}

$shirt_id = $_POST['shirt_ID'];
$user = $_POST['user'];

$ID = do_Buy($shirt_id, $user);

echo '{"updated": true}';

?>
