<?php

/*
 * db.php - This is the Model of MVC.  Provides business logic which then
 *          maintains state in the persistent data.
 * 
 */

// Only included for "pre_debug()" function, which should be moved somewhere else!
include_once 'display.php';
include_once 'utils.php';

if (TESTING_ON) {
	error_reporting(E_ALL);
}

function getFaceBook($api_key, $secret_key) {
		
	// Facebook setup.
	$fb = new Facebook($api_key, $secret_key);
	$user = $fb->require_login();

	return array($fb, $user);
	
}


// Database functions

function get_db_conn() {
	$conn = mysql_connect($GLOBALS['db_ip'], $GLOBALS['db_user'], $GLOBALS['db_pass']);
	if (!$conn) {
		trigger_error('DB connect, Sql error, "'.mysql_error().'"', E_USER_WARNING);
		return;
	}	
  
	$result = mysql_select_db($GLOBALS['db_name'], $conn);
	if (!$result) {
		trigger_error('DB select, Sql error, "'.mysql_error().'"', E_USER_WARNING);
		return;
	}	

	return $conn;
}

function shirts_build_search_sort($sort, $search, $category) {

	$conn = get_db_conn();

	// Build the query

	// Search string
	$where = '';
	if ($search != '') {
		$where .= ' AND `name` like \'%'.mysql_real_escape_string($search, $conn).'%\' ';
	}

	// Category
	if ($category != '' && $category != '-1') {
		$where .= ' AND sc.category_ID = '.mysql_real_escape_string($category).' ';
	}
	
	$order_by = ' ORDER BY internal_rank, id ';
	// Recently Popular	
	if ($sort == 'RP') {
		$order_by = ' ORDER BY recent_rank, internal_rank ';
		$where .= ' AND recent_rank <= 1000 '; 
		
	}
	// Popular
	elseif ($sort == 'PO') {
		$order_by = ' ORDER BY rank, internal_rank ';
		$where .= ' AND rank <= 1000 '; 

	}
	// New Arrivals, anything within 7 days of the last shirt added
	elseif ($sort == 'NA') {
		$order_by = ' ORDER BY created, internal_rank ';
		$where .= ' AND created > date_add((SELECT max(created)
FROM shirts x), interval -7 day) '; 
	}
	
	return $where.$order_by;
	
}
 
 
function get_shirts($pagenum, $limit, $search_parms, $fb) {
	$conn = get_db_conn();
 
	$sort = '';
	$search = '';
	$category = '';
	if (isset($search_parms)) {
		extract($search_parms);
	}

	// Paging stuff
	$max = ' LIMIT ' . (($pagenum - 1) * $limit) . ','.$limit;

	$search_sort = shirts_build_search_sort($sort, $search, $category);
	
	// NOTE: Even if we don't select any fields from the shirt_categories table
	// we use it for selects.
	$query = 'SELECT distinct s.ID, s.name, s.image_base, s.image_link, s.image_zoom, concat(`s`.`affiliate_base`, `s`.`affilliate_url`) as `affilliate_url` FROM shirts s LEFT JOIN shirt_categories sc ON ( s.ID = sc.shirt_ID ) where s.ID > 0 '.$search_sort.$max;
	  
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');	
	
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	
	$shirts = array();
	while ($row = mysql_fetch_assoc($result)) {
		$shirts[] = $row;
	}
  
	$num_shirts = get_number_shirts($search_sort);
  
	return array($shirts, $num_shirts);
}

function get_number_shirts($search_sort) {
	$conn = get_db_conn();
 
	$query = 'select count(ID) from(SELECT distinct s.ID, s.name, s.image_base, s.image_link, s.image_zoom, concat(`s`.`affiliate_base`, `s`.`affilliate_url`) as `affilliate_url` FROM shirts s LEFT JOIN shirt_categories sc ON ( s.ID = sc.shirt_ID ) where s.ID > 0 '.$search_sort.') as sq';
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
	
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	$row = mysql_fetch_assoc($result);
 
	return $row['count(ID)'];
 }

function get_myshirts($pagenum, $limit, $search_parms, $fb) {
	$conn = get_db_conn();
	
	extract($search_parms);
		
	// Leave space for the shirt the user is wearing which always takes up the first
	// first item for every page.
	$limit = $limit - 1;

	$max = ' LIMIT ' . (($pagenum - 1) * $limit) . ','.$limit;

	// Get the shirt being worn right now.
	$query = 'SELECT `shirts`.`ID`, `shirts`.`name`, `shirts`.`image_base`,`shirts`.`image_link`, `shirts`.`image_zoom`, concat(`shirts`.`affiliate_base`, `shirts`.`affilliate_url`) as `affilliate_url`, sent_shirts.`from_user`, sent_shirts.`ID` as sent_shirts_ID '
		.  	'FROM `shirts`, sent_shirts, users '
		.	'WHERE users.user_id = '.$user.' '
		.	'AND sent_shirts.to_user='.$user.' '
		.	'AND sent_shirts.shirt_id = shirts.id '
		.	'AND shirts.id = users.shirt_ID ';  

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
			
	$shirts = array();
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	
	$row = mysql_fetch_assoc($result);
	
	if (!$row) {
		$row = array();
		error_log('User, '.$user.', Function, '.  __FUNCTION__ 
			.', File, '.__FILE__
			.', Line, '.__LINE__
			.', Msg, User is not wearing a t-shirt.  Database inconsistency problem.'
			, 0);		
		return array($row, 0);
	}

	// Get the facebook user name of the user who sent the t-shirt
	$user_details = $fb->api_client->users_getInfo($row['from_user'], 'name');
	$row['from_name'] = $user_details[0]['name'];	

	$shirts[] = $row;

		
	// Get the rest of the shirts  
	$query = 	'SELECT `shirts`.`ID`, `name`, `image_base`, `image_link`, `image_zoom`, concat(`affiliate_base`, `affilliate_url`) as `affilliate_url` , sent_shirts.`from_user`, sent_shirts.`ID` as sent_shirts_ID '
			.	'FROM `shirts`, sent_shirts '
			.	'WHERE sent_shirts.to_user=' . $user . ' '
			.	'AND sent_shirts.shirt_id = shirts.id '
			.	'AND sent_shirts.id <>'.$shirts[0]['sent_shirts_ID'].' '.$max;

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');

	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	while ($row = mysql_fetch_assoc($result)) {
		// Get the facebook user name of the user who sent the t-shirt
		$user_details = $fb->api_client->users_getInfo($row['from_user'], 'name');
		$row['from_name'] = $user_details[0]['name'];			
		
		$shirts[] = $row;
	}
	
	$num_shirts = get_number_myshirts($user);
  
	return array($shirts, $num_shirts);
    
}

function get_number_myshirts($user) {
	$conn = get_db_conn();
	
	$query = 	'select count(ID) from (SELECT distinct `shirts`.`ID`, `name`, `image_base`, `image_link`, `image_zoom`, concat(`affiliate_base`, `affilliate_url`) as `affilliate_url` , sent_shirts.`from_user`, sent_shirts.`ID` as sent_shirts_ID '
			.	'FROM `shirts`, sent_shirts '
			.	'WHERE sent_shirts.to_user=' . $user . ' '
			.	'AND sent_shirts.shirt_id = shirts.id ) as sq';
		
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
	
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	$row = mysql_fetch_assoc($result);
 
	return $row['count(ID)'];
 }

function get_shirt($shirt_id) {

	$conn = get_db_conn();

	$query = 'SELECT `ID`, `name`, concat(`image_base`,`image_link`) as image_link, concat(`affiliate_base`, `affilliate_url`) as `affilliate_url` FROM `shirts` WHERE ID=' . $shirt_id;
  
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
  
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	$row = mysql_fetch_assoc($result);
	return $row;
}

function shirt_exists_where($where) {
	$conn = get_db_conn();
	$return_val = false;
  
	$query = 'SELECT ID FROM `shirts` WHERE ' . $where . ' ;';
		
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
  
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, admin, Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}  
 
	if (mysql_num_rows($result) > 0) {
		$return_val = true;
	}
	
	return $return_val;	
}

function shirt_stage_exists_where($where) {
	$conn = get_db_conn();
	$return_val = false;
  
	$query = 'SELECT ID FROM `shirts_stage` WHERE ' . $where . ' ;';
		
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
  
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, admin, Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}  
 
	if (mysql_num_rows($result) > 0) {
		$return_val = true;
	}
	
	return $return_val;	
}

function insert_shirts_stage($name, $image_base, $image_link, $image_zoom, $affiliate_base, $affiliate_url) {
	$conn = get_db_conn();	
	
	$query = 'INSERT INTO shirts_stage (`name`, `image_base`, `image_link`, `image_zoom`, `affiliate_base`, `affilliate_url`) VALUES (\''.mysql_real_escape_string($name).'\', \''.$image_base.'\', \''.$image_link.'\', \''.$image_zoom.'\', \''.$affiliate_base.'\', \''.$affiliate_url.'\');';

	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
}

function do_publishing($fb, $user, $to, $shirt_name, $image) {
    // start batch operation 
    $fb->api_client->begin_batch();

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Shirt name: '.$shirt_name.'.');

//    $main_box =  get_user_profile_box($from);
//    $facebook->api_client->profile_setFBML(null, $from, null, null, null, $main_box);

    // Send notification
    // Notice the use of reference '&'
    $result = & $fb->api_client->notifications_send($to, ' sent a '.$shirt_name. ' T-Shirt to you.  '.
      '<a href="http://apps.facebook.com/toddshirts/">See T-Shirts</a>.', 'user_to_user');

	// End batch operation. This will actually send queued API call to Facebook in
    // a single HTTP request
    $fb->api_client->end_batch();

	return $result;	
	
   // TO-DO: This can be deleted???   	
   // Publish feed story   
    $canvas_url = $fb->get_facebook_url('apps') . '/' . APP_SUFFIX;
	
    $images = array(array('src'  => $image,
                         'href' => $canvas_url));
	
	$template_data = array('tshirt' => $shirt_name, 'images' => $images );

	$target_ids = array($to);
	
	$result = $fb->api_client->feed_publishUserAction(FEED_STORY_1, $template_data, $to, null);	
	
	return $result;
}

function do_send($shirt_id, $user, $to) {
	
	$conn = get_db_conn();
	
	$mysqldate = date( 'Y-m-d H:i:s', time() );

	// Has user already sent a shirt to this user in the past 1 hour?
	$query = 'SELECT count(*) FROM `sent_shirts`
WHERE `from_user`='.$user.' AND to_user='.$to.' AND `time` > DATE_ADD( now(), INTERVAL -'.SEND_INTERVAL.' HOUR)';

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');

	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	
	$row = mysql_fetch_assoc($result);
	$sent_user = $row['count(*)'];
	
	$query = 'INSERT INTO sent_shirts (`from_user`,`time`,`to_user`,`shirt_ID`,`status`) VALUES(\''.$user.'\', \''.$mysqldate.'\', \''.$to.'\', \''.$shirt_id.'\', \'S\')';

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');

	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
		 
	$query = 'select sent from users where user_id=\''.$user.'\' ';

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
		
	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	
	$row = mysql_fetch_assoc($result);
	$sent = $row['sent'];

	// Calculate tbucks
	
	// TO-DO: This could be generalized so the payoff info is stored
	// in an array and a function is called.
	
	if ($sent_user > 0 ) {
		$tbucks_earned = 0;
	} 
	else {
	
		if ($sent == 0) {
			$tbucks_earned = 50;
		}
		else {

			$chance = 5;
			$low = SEND_BONUS * 4;
			$high = SEND_BONUS * 6;
			
			if ($sent <= 4) {
				$chance = 60;
				$low = SEND_BONUS * 0.7;
				$high = SEND_BONUS * 1.3;
			}
			elseif ($sent <= 8) {
				$chance = 50;
				$low = SEND_BONUS * 1.3;
				$high = SEND_BONUS * 1.9;
			}
			elseif ($sent <= 13) {
				$chance = 30;
				$low = SEND_BONUS * 1.5;
				$high = SEND_BONUS * 2.5;
			}
			elseif ($sent <= 19) {
				$chance = 15;
				$low = SEND_BONUS * 2;
				$high = SEND_BONUS * 4;
			}
		
			if (probability($chance)) {
				$tbucks_earned = mt_rand($low, $high);
			}
			else {
				$tbucks_earned = 0;
			}
		}		
	}
	
	$query = 'UPDATE users set sent=sent+1, tbucks=tbucks+'.$tbucks_earned.', modified=now() WHERE user_id=\''.$user.'\' ';
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
		
	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	
	return $tbucks_earned;	
}


function userExists($user) {
	$conn = get_db_conn();
	
	$return_val = true;
	
	$query = 'SELECT user_id from users where user_id='.$user;

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');
	
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	if (mysql_num_rows($result) == 0) {
		$return_val = false;
	}
	
	return $return_val;
}
	
function do_UserSetup($user, $fb) {
	$conn = get_db_conn();

	$mysqldate = date( 'Y-m-d H:i:s', time() );
	
	// Get the facebook user name
	$user_details = $fb->api_client->users_getInfo($user, 'name');
	
	// Insert the user if it doesn't already exist
	if (userExists($user) == false) { 
		
		// bug-fixed: $user_details[0]['name'] is passed through mysql_real_escape_string() to make it accept names with apostrophes in.
 		$query = 'INSERT INTO users (`user_id`, `name`, `sent`, `changes`, `invites`, `last_visit_date`, `tbucks`, `shirt_ID`, `rank`, `created`, `modified`, `status`) VALUES(\''.$user.'\', \''.mysql_real_escape_string($user_details[0]['name']).'\', \'0\', \'0\', \'0\', \''.$mysqldate. '\', \''.JOIN_BONUS.'\', \'-1\', \''.MAX_BIGINT.'\', \''.$mysqldate.'\', \''.$mysqldate. '\', \'A\')';

		debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');

		$result = mysql_query($query, $conn);  
		if (!$result) {
			trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
			return;
		}	
	}	
	
	// Add the default t-shirt if it doesn't already exist
	$query = 'SELECT ID from sent_shirts where from_user = \''.$user.'\' and shirt_ID=\'-1\'';

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');	

	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}		
	if (mysql_num_rows($result) == 0) { 		
	              
		$query = 'INSERT INTO sent_shirts (`from_user`,`time`,`to_user`,`shirt_ID`,`status`) VALUES(\''.$user.'\', \''.$mysqldate.'\', \''.$user.'\', \'-1\', \'A\')';

		debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');	
		
		$result = mysql_query($query, $conn);
		if (!$result) {
			trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
			return;
		}				
		// Set the "wearing shirt" to the default shirt
		$sent_shirts_ID = mysql_insert_id();
		wear_shirt($user, $sent_shirts_ID, '-1', false); 		
	}	

	// -1 is the default T-Shirt
	return get_shirt(-1);
}

function wear_shirt($user, $sent_shirts_ID, $shirt_ID, $add_tbucks=true) {
	$conn = get_db_conn();
	$changes = 1;

	$mysqldate = date( 'Y-m-d H:i:s', time() );
	$query = 'UPDATE wearing_shirt set status=\'I\' WHERE user=\''.$user.'\' AND status=\'A\'';
	
	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	
	$query = 'INSERT INTO wearing_shirt (`user`,`sent_shirts_ID`, `time`, `status`) VALUES(\''.$user.'\', \''.$sent_shirts_ID.'\', \''.$mysqldate.'\', \'A\')';

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');	

	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	
	// Update tbucks
	if ($add_tbucks) {	
		$query = 'select changes from users where user_id=\''.$user.'\' ';
		$result = mysql_query($query, $conn); 
		if (!$result) {
			trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
			return;
		}
		$row = mysql_fetch_assoc($result);
		$changes = $row['changes'];
	}
	
	if (($changes < 1) && $add_tbucks) {
		$tbucks_earned = 500;
	} 
	else {
		$tbucks_earned = 0;
	}
	
	$query = 'UPDATE users set changes=changes+1, tbucks=tbucks+'.$tbucks_earned.', shirt_ID='.$shirt_ID.', modified=now() WHERE user_id=\''.$user.'\' ';
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');	
		
	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	
	return $tbucks_earned;
}

function do_Buy($shirt_id, $user) {
	$conn = get_db_conn();
	
	$mysqldate = date( 'Y-m-d H:i:s', time() );
	
	$query = 'INSERT INTO user_actions (`user`, `time`, `action`, `shirt_ID`) VALUES(\''.$user.'\', \''.$mysqldate.'\', \'BUY\', \''.$shirt_id.'\')';

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');	
	
	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	
	$ID = mysql_insert_id();  
	return $ID;	
}

function reset_user_counter() {
	$conn = get_db_conn();
	
	$mysqldate = date( 'Y-m-d H:i:s', time() );
	
	$query = 'UPDATE users set sent=0, changes=0, invites=0 ';

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');	
	
	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}

	return $result;	
}	

function get_user_summary($user) {
	$conn = get_db_conn();
  
	$query = 'SELECT `sent`, `changes`, `invites`, `tbucks`, `s`.`image_base`, `s`.`image_link`, users.rank FROM `users`, `shirts` as s WHERE users.user_id=\'' . $user . '\' AND  users.shirt_ID=s.ID';
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');	
	
	$result = mysql_query($query, $conn);
	// TO-DO: Add error handling if result is not good!
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	
	
	$row = mysql_fetch_assoc($result);
	
	return $row;
}

function get_user_tbucks($user) {
	$conn = get_db_conn();
	  
	$query = 'SELECT `tbucks` FROM `users` WHERE users.user_id=\'' . $user . '\'';
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query:  '.$query.'.');	
	
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	$row = mysql_fetch_assoc($result);
	
	return $row['tbucks'];
}


function get_app_users($fb, $user, $pagenum, $limit) {
 
	$conn = get_db_conn();

	// Paging stuff
	$max = ' LIMIT ' . (($pagenum - 1) * $limit) . ','.$limit;
	
	// Build the query
	// Get all application users that are friends of the current user.
	$query = 'SELECT uid, name, pic_square FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=' . $user . ') AND is_app_user or uid = ' . $user;
	
	$app_users = $fb->api_client->fql_query($query);
	if (count($app_users) < 1) {
		return array();
	}
	

	// Build the list of user ids.
	$users_in = '(';
	foreach ($app_users as $i => $app_user) {
		$users_in .= $app_user['uid'] . ',';
	}
	$users_in[strlen($users_in)-1] = ')';

	// Now get additional information from the toddshirts DB
	$rank_query = '(SELECT count(*)+1 as rank from (select tbucks from users u1 where user_id in '.$users_in.') as s1 where tbucks > (select tbucks from (select user_id, tbucks from users u2 where user_id in '.$users_in.') as s2 WHERE s2.user_id = u3.user_id))';
	
	$query = 'SELECT '.$rank_query.' as rank, `user_id`, `sent` , `changes` , `invites` , `tbucks` , `s`.`image_base`, `s`.`image_link`, `s`.`name` as tname FROM `users` u3, `shirts` s WHERE u3.user_id IN '.$users_in.' AND s.id=u3.shirt_ID order by rank';
	$query .= $max;

	// TO-DO: The query above may need to be simplified?
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: (needs to be simplified??? performance???) '.$query.'.');	
		
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	
	// Index on the uids
	foreach ($app_users as $i => $app_user) {
		$uid_key[$app_user['uid']] = $i;
	}
	
	$app_users_ranked = array();
	while ($row = mysql_fetch_assoc($result)) {
		$i = $uid_key[$row['user_id']];
		$row['name'] = $app_users[$i]['name'];
		$row['pic_square'] = $app_users[$i]['pic_square'];
		$app_users_ranked[] = $row;
	}

// pre_debug('$app_users_ranked', $app_users_ranked);

	return $app_users_ranked;
	
	// Now that we have the list of users get shirt info
	foreach ($app_users as $i => $app_user) {
		$user_summary = get_user_summary($app_user['uid']);
	
		$app_users[$i]['tbucks'] = $user_summary['tbucks'];
		$app_users[$i]['image_base'] = $user_summary['image_base'];
		$app_users[$i]['image_link'] = $user_summary['image_link'];
		
	}
}
	
function get_num_app_users($fb, $user) {
 
	// Build the query
	$query = 'SELECT uid, name, pic_square FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=' . $user . ') AND is_app_user or uid = ' . $user;

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'FQL Query: '.$query.'.');	
	
	$num_app_users = $fb->api_client->fql_query($query);
	
	return sizeof($num_app_users);	
}

function free_tshirts_avail() {
	$avail = 0;
	$conn = get_db_conn();
	
	$query = 'select value from globals where name=\'FREE_TSHIRTS\'';
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');	
		
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	$row = mysql_fetch_assoc($result);
	
	$avail = $row['value'];
	
	return $avail;	
}

function free_tshirts_price() {
	$price = 0;
	$conn = get_db_conn();
	
	$qty = free_tshirts_avail();
	
	if ($qty < 1) {
		$query = 'SELECT avg(tbucks) as price FROM (select (tbucks*2) as tbucks from `users` order by tbucks desc limit 0, 2) as s1';
	}
	else {
		$query = 'SELECT avg(tbucks) as price FROM (select tbucks from `users` order by tbucks desc limit 0, '.$qty.') as s1';	
	}
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');	
		
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}
	$row = mysql_fetch_assoc($result);
	
	$price = $row['price'];
	
	return $price;	
}

function do_invite($user, $invite_ids) {
	$conn = get_db_conn();
	$tbucks_earned = 0;
	$invites = 0;
	$mysqldate = date( 'Y-m-d H:i:s', time() );
		
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Using the following invite_ids'.print_r($invite_ids, TRUE).'.');	
	
	foreach($invite_ids as $id) {
		// Has user already invited this user in the past x hours?
		$query = 'SELECT count(*) FROM `invites`
	WHERE `from_user`='.$user.' AND to_user='.$id.' AND `time` > DATE_ADD( now(), INTERVAL -'.INVITE_INTERVAL.' HOUR)';
		$result = mysql_query($query, $conn);
		if (!$result) {
			trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
			return;
		}
		$row = mysql_fetch_assoc($result);
		$invite_user = $row['count(*)'];

		if ($invite_user < 1 ) {
			// TO-DO: Make this a little more random.
			$tbucks_earned += 100;
			$invites += 1; 
			
			// Insert the invite.
			$query = 'INSERT INTO invites (`from_user`,`time`,`to_user`,`status`) VALUES(\''.$user.'\', \''.$mysqldate.'\', \''.$id.'\', \'S\')';	
			$result = mysql_query($query, $conn);
			if (!$result) {
				trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
				return;
			}
		}
		
	}
		
		// Update the total invites this user has made today.
		$query = 'UPDATE users set invites=invites+'.$invites.', tbucks=tbucks+'.$tbucks_earned.', modified=now() WHERE user_id=\''.$user.'\' ';
		$result = mysql_query($query, $conn);  
		if (!$result) {
			trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
			return;
		}
	
	return $tbucks_earned;
}

function get_recent_invites($user) {
	$conn = get_db_conn();

	$query = 'SELECT to_user as uid FROM `invites`
WHERE `from_user`='.$user.' AND `time` > DATE_ADD( now(), INTERVAL -'.INVITE_INTERVAL.' HOUR)';

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Query: '.$query.'.');	
		
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}

	$uids = array();
	while ($row = mysql_fetch_assoc($result)) {
		$uids[] = $row;
	}
	
	return $uids;		
}

function get_categories() {
	$conn = get_db_conn();

	$query = 'SELECT * FROM `categories`';

	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}

	$list = array();
	while ($row = mysql_fetch_assoc($result)) {
		$list[] = $row;
	}
	
	return $list;		
}

function probability($chance, $out_of = 100) {
    $random = mt_rand(1, $out_of);
    return $random <= $chance;
}

function rank_shirts() {
	$conn = get_db_conn();

	$mysqldate = date( 'Y-m-d H:i:s', time() );

	// This echo is ok because this is called via a batch job
	// and this will be the output.
	$msg = 	'Reseting shirt ranking...';
	echo $msg;	
	$query = 'update shirts set rank = '.MAX_RANK.', recent_rank = '.MAX_RANK.';';
	debug_log(__FUNCTION__, __FILE__, __LINE__, $msg.', query: '.$query.'.');	
	
	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}

	$msg = 'Updating recently popular shirts...';
	echo $msg;
	// Recently popular
	$query = 'SELECT shirt_ID, count(ID) as count_ID FROM `sent_shirts` WHERE `time` > DATE_ADD(now(), INTERVAL -7 DAY) group by shirt_ID having shirt_ID > 0 order by count_ID desc;';
	debug_log(__FUNCTION__, __FILE__, __LINE__, $msg.', query: '.$query.'.');	
	
	$result = mysql_query($query, $conn);
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	

	$sent_shirts = array();
	while ($row = mysql_fetch_assoc($result)) {	
		$sent_shirts[] = $row;
	}
	
	$rank = 0;
	foreach($sent_shirts as $sent_shirt) {
		$rank += 1;
		$query = 'update shirts set recent_rank = '.$rank.' where ID='.$sent_shirt['shirt_ID'];
		$result = mysql_query($query, $conn);
		if (!$result) {
			trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
			return;
		}	
	}	

	$msg = 'Updated '.$rank.' recently popular shirts.';
	echo $msg;
	debug_log(__FUNCTION__, __FILE__, __LINE__, $msg.', query: '.$query.'.');	

	// All time popular
	$query = 'SELECT shirt_ID, count(ID) as count_ID FROM `sent_shirts` group by shirt_ID having shirt_ID > 0 order by count_ID desc;';
	$result = mysql_query($query, $conn);  
	if (!$result) {
		trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
		return;
	}	

	$sent_shirts = array();
	while ($row = mysql_fetch_assoc($result)) {	
		$sent_shirts[] = $row;
	}
	
	$rank = 0;
	foreach($sent_shirts as $sent_shirt) {
		$rank += 1;
		$query = 'update shirts set rank = '.$rank.' where ID='.$sent_shirt['shirt_ID'];
		$result = mysql_query($query, $conn);
		if (!$result) {
			trigger_error('Invalid query, User, '.$user.', Sql error, "'.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
			return;
		}	
	}	

	$msg = 'Updated '.$rank.' popular shirts.';
	echo $msg;
	debug_log(__FUNCTION__, __FILE__, __LINE__, $msg.', query: '.$query.'.');	

	$msg = 'Finished script.';
	echo $msg;
	debug_log(__FUNCTION__, __FILE__, __LINE__, $msg);	
	
	return;	
}

// TO-DO: Remove the fbml stuff.
function createUser($user, $fb) {
	
	$fbml = '';
	
	$userExists = userExists($user);

	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Checking if user already exists $usesExists: '.$userExists.'.', $user);
	
	// If the user doesn't exist then do all the setup
	// - DB creation
	// - User profile box
	// - FBML for bonus popup
	if (!($userExists)) {
		
		$tshirt = do_UserSetup($user, $fb);
		$main_box =  get_user_profile_box($tshirt['name'], $tshirt['image_link']);
		$fb->api_client->profile_setFBML(null, $user, null, null, null, $main_box);
  
		// Pop-up dialog showing that user earned 500 tbucks
		$fbml .= '<script>';
		$fbml .= 'showJoinBonus('.JOIN_BONUS.');'; 
		$fbml .= '</script>';
	}
	
//	return $userExists;
	return $userExists;
}

function update_searches($search, $category, $user) {
	$conn = get_db_conn();
	$mysqldate = date( 'Y-m-d H:i:s', time() );
		
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Starting');
		
	if ($search != '' || $category > 0) {
		// Insert the search term.  
		$query = 'INSERT into searches (words, category_ID, user_ID, search_date)'
			. 'VALUES ('
			. '\'' .mysql_real_escape_string($search). '\''
			. ', \'' . $category . '\''
			. ', \'' . $user . '\''
			. ', \'' . $mysqldate . '\''
			. ')';
			
		$result = mysql_query($query, $conn);  
		if (!$result) {
			trigger_error('Invalid query, User, '.$user.', Sql error, "'
				.mysql_error().'", Query, "'.$query.'",', E_USER_WARNING);
			return;
		}
	}
	
}

