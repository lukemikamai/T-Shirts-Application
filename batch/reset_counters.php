<?php

include_once '../lib/constants.php';
include_once LIB_PATH.'db.php';

if (TESTING_ON) {
	error_reporting(E_ALL);
}

$result = reset_user_counter();

// TO-DO: Add logging!



