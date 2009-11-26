<?php

// Only included for "pre_debug()" function, which should be moved somewhere else!
include_once 'display.php';

if (TESTING_ON) {
	error_reporting(E_ALL);
}

// Database functions

function get_db_conn() {
  $conn = mysql_connect($GLOBALS['db_ip'], $GLOBALS['db_user'], $GLOBALS['db_pass']);
  mysql_select_db($GLOBALS['db_name'], $conn);
  return $conn;
}

function get_shirts($pagenum, $sort, $search, $category, $limit) {
 
	$conn = get_db_conn();

	// Paging stuff
	$max = ' LIMIT ' . (($pagenum - 1) * $limit) . ','.$limit;

	// Build the query
	
	// Search string
	$search_where = '';
	if ($search != '') {
		$search_where = ' AND `name` like \'%'.mysql_real_escape_string($search, $conn).'%\' '; 
	}

	// Category
	$category_where = '';
	if ($category != '') {
		$category_where = ' AND sc.category_ID = '.mysql_real_escape_string($category).' '; 
	}
	
  if ($sort == 'RP') {
	$query = 'select `s`.`ID`, `s`.`name`, concat(`s`.`image_base`,`s`.`image_link`) as image_link, concat(`s`.`affiliate_base`, `s`.`affilliate_url`) as `affilliate_url`, count(ifnull(`sg`.`shirt_id`, `s`.`ID`))
from `shirts` `s` LEFT OUTER JOIN `sent_shirts` `sg` on (`s`.`id` = `sg`.`shirt_id`)
where 
	ifnull(`sg`.`time`, \'1900-01-01\') < (SELECT max( `time` ) FROM `sent_shirts`) 
and ifnull(`sg`.`time`, now()) > (SELECT DATE_ADD( 
				( SELECT max( `time` ) FROM `sent_shirts` ) , 
				INTERVAL -7 DAY ) )	
and `s`.`ID` > 0 '. $search_where .
' group by 1
union 
select `s`.`ID`, `s`.`name`, concat(`s`.`image_base`,`s`.`image_link`) as image_link, concat(`s`.`affiliate_base`, `s`.`affilliate_url`) as `affilliate_url`, 1
from `shirts` `s` LEFT OUTER JOIN `sent_shirts` `sg` on (`s`.`id` = `sg`.`shirt_id`)
where 
	`sg`.`time` > (SELECT max( `time` ) FROM `sent_shirts`) 
	or `sg`.`time` < (SELECT DATE_ADD( 
				( SELECT max( `time` ) FROM `sent_shirts` ) , 
				INTERVAL -7 DAY ) )
and `s`.`ID` > 0 '. $search_where .' order by 5 desc, 1 asc '
				. $max;	
  } else {
	$query = 'SELECT s.ID, s.name, concat(`s`.`image_base`,`s`.`image_link`) as image_link, concat(`s`.`affiliate_base`, `s`.`affilliate_url`) as `affilliate_url`, sc.category_ID FROM shirts s, shirt_categories sc  where s.ID > 0 AND s.ID = sc.shirt_ID '.$search_where.$category_where.$max;
  }
  
 echo pre_debug('$query',$query);
  
  $res = mysql_query($query, $conn);	
  $shirts = array();
  while ($row = mysql_fetch_assoc($res)) {
    $shirts[] = $row;
  }
  return $shirts;
}


function get_myshirts($pagenum, $user, $limit) {
  $max = ' LIMIT ' . (($pagenum - 1) * $limit) . ','.$limit;
  
  $query = 'SELECT `shirts`.`ID`, `name`, concat(`image_base`,`image_link`) as image_link, concat(`affiliate_base`, `affilliate_url`) as `affilliate_url`  , sent_shirts.`from_user`, sent_shirts.`ID` as sent_shirts_ID FROM `shirts`, sent_shirts WHERE sent_shirts.to_user=' . $user . ' AND sent_shirts.shirt_id = shirts.id ' . $max;
  $conn = get_db_conn();
  $res = mysql_query($query, $conn);
  $shirts = array();
  while ($row = mysql_fetch_assoc($res)) {
    $shirts[] = $row;
  }
  return $shirts;
    
}

function get_shirt($shirt_id) {
  $query = 'SELECT `ID`, `name`, concat(`image_base`,`image_link`) as image_link, concat(`affiliate_base`, `affilliate_url`) as `affilliate_url` FROM `shirts` WHERE ID=' . $shirt_id;
  $conn = get_db_conn();
  $res = mysql_query($query, $conn);
  $row = mysql_fetch_assoc($res);
  return $row;
}

function get_number_myshirts($user) {
  $conn = get_db_conn();
  
  $res = mysql_query('SELECT count(*) FROM `shirts`, sent_shirts WHERE sent_shirts.to_user=' . $user . ' AND sent_shirts.shirt_id = shirts.id ', $conn);
    
  $row = mysql_fetch_assoc($res);
 
  return $row['count(*)'];
}

// TO-DO:  Need to add category
function get_number_shirts($sort, $search) {
  $conn = get_db_conn();
  
  $query = 'SELECT count(*) FROM `shirts` where `ID` > 0 ';
  
	if ($search != '') {
		$query .= sprintf(' AND `name` like \'%%%s%%\' ', mysql_real_escape_string($search, $conn));
	}

  $res = mysql_query($query, $conn);
  $row = mysql_fetch_assoc($res);
 
  return $row['count(*)'];
 }
 
 function do_publishing($fb, $user, $to, $shirt_name, $image) {
    // start batch operation 
    $fb->api_client->begin_batch();
  
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
	
   // TO-DO This can be deleted???   	
   // Publish feed story   
    $canvas_url = $fb->get_facebook_url('apps') . '/' . APP_SUFFIX;
	
    $images = array(array('src'  => $image,
                         'href' => $canvas_url));
	
	$template_data = array('tshirt' => $shirt_name, 'images' => $images );

	$target_ids = array($to);
	
	$result = $fb->api_client->feed_publishUserAction(FEED_STORY_1, $template_data, $to, null);	
	
	return $result;
}

function do_send($shirt_id, $from, $to) {
	$conn = get_db_conn();
	
	$mysqldate = date( 'Y-m-d H:i:s', time() );

	// Has user already sent a shirt to this user in the past 1 hour?
	$query = 'SELECT count(*) FROM `sent_shirts`
WHERE `from_user`='.$from.' AND to_user='.$to.' AND `time` > DATE_ADD( now(), INTERVAL -'.SEND_INTERVAL.' HOUR)';
	$result = mysql_query($query, $conn);
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	
	$row = mysql_fetch_assoc($result);
	$sent_user = $row['count(*)'];
	
	$query = 'INSERT INTO sent_shirts (`from_user`,`time`,`to_user`,`shirt_ID`,`status`) VALUES(\''.$from.'\', \''.$mysqldate.'\', \''.$to.'\', \''.$shirt_id.'\', \'S\')';	
	$result = mysql_query($query, $conn);
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	
	 
	$query = 'select sent from users where user_id=\''.$from.'\' ';
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
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
	
		if ($sent == 1) {
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
	
	$query = 'UPDATE users set sent=sent+1, tbucks=tbucks+'.$tbucks_earned.', modified=now() WHERE user_id=\''.$from.'\' ';
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	

	return $tbucks_earned;	
}

function do_UserSetup($user) {
	$conn = get_db_conn();

	$mysqldate = date( 'Y-m-d H:i:s', time() );

	
	$query = 'INSERT INTO users (`user_id`, `sent`, `changes`, `invites`, `last_visit_date`, `tbucks`, `created`, `modified`, `status`) VALUES(\''.$user.'\', \'0\', \'0\', \'0\', \''.$mysqldate.'\', \''.JOIN_BONUS.'\', \'' .$mysqldate. '\',\'' .$mysqldate.   '\', \'A\')';
	
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query 1: ' . mysql_error());
	}	

	
	// TO-DO: Check if the user already has the default shirt before 
    // inserting!!!	
	$query = 'INSERT INTO sent_shirts (`from_user`,`time`,`to_user`,`shirt_ID`,`status`) VALUES(\''.$user.'\', \''.$mysqldate.'\', \''.$user.'\', \'-1\', \'A\')';
	
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query 1: ' . mysql_error());
	}	

	$sent_shirt_ID = mysql_insert_id();
	$query = 'INSERT INTO wearing_shirt (`user`,`sent_shirts_ID`, `time`, `status`) VALUES(\''.$user.'\', \''.$sent_shirt_ID.'\', \''.$mysqldate.'\', \'A\')';
	
	$result = mysql_query($query, $conn);  
	if ((!$result) && TESTING_ON) {
		echo('Invalid query 2: ' . mysql_error());
	}	

	// -1 is the default T-Shirt
	return get_shirt(-1);
}

function wear_shirt($user, $sent_shirts_ID) {
	$conn = get_db_conn();
	
	$mysqldate = date( 'Y-m-d H:i:s', time() );
	$query = 'UPDATE wearing_shirt set status=\'I\' WHERE user=\''.$user.'\' AND status=\'A\'';
	
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	
	
	$query = 'INSERT INTO wearing_shirt (`user`,`sent_shirts_ID`, `time`, `status`) VALUES(\''.$user.'\', \''.$sent_shirts_ID.'\', \''.$mysqldate.'\', \'A\')';
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}
	
	// Update tbucks
	$query = 'select changes from users where user_id=\''.$user.'\' ';
	$result = mysql_query($query, $conn); 
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	
	$row = mysql_fetch_assoc($result);
	$changes = $row['changes'];
	
	if ($changes < 1 ) {
		$tbucks_earned = 500;
	} 
	else {
		$tbucks_earned = 0;	
	}
	
	$query = 'UPDATE users set changes=changes+1, tbucks=tbucks+'.$tbucks_earned.', modified=now() WHERE user_id=\''.$user.'\' ';
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	
	
	return $tbucks_earned;
}

function do_Buy($shirt_id, $user) {
	$conn = get_db_conn();
	
	$mysqldate = date( 'Y-m-d H:i:s', time() );
	
	$query = 'INSERT INTO user_actions (`user`, `time`, `action`, `shirt_ID`) VALUES(\''.$user.'\', \''.$mysqldate.'\', \'BUY\', \''.$shirt_id.'\')';
	
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}
	$ID = mysql_insert_id();  
	return $ID;	
}

function reset_user_counter() {
	$conn = get_db_conn();
	
	$mysqldate = date( 'Y-m-d H:i:s', time() );
	
	$query = 'UPDATE users set sent=0, changes=0, invites=0 ';
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	

	return $result;	
}	

function get_user_summary($user) {
  
	$query = 'SELECT `sent`, `changes`, `invites`, `tbucks`, concat(`s`.`image_base`,`s`.`image_link`) as image_link FROM `users`, `wearing_shirt` as w, `sent_shirts` as ss, `shirts` as s WHERE users.user_id=\'' . $user . '\' and w.user = users.user_id and w.status="A" AND ss.ID=w.sent_shirts_ID AND ss.shirt_ID=s.ID';
	$conn = get_db_conn();
	$res = mysql_query($query, $conn);
	// TO-DO: Add error handling if result is not good!
	if ((!$res) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	
	$row = mysql_fetch_assoc($res);
	
	return $row;
}

function get_app_users($fb, $user, $pagenum, $limit) {
 
	$conn = get_db_conn();

	// Paging stuff
	$max = ' LIMIT ' . (($pagenum - 1) * $limit) . ','.$limit;
	
	// Build the query
	// Get ALL application users.
	$query = 'SELECT uid, name, pic_square FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=' . $user . ') AND is_app_user or uid = ' . $user;
	
	$app_users = $fb->api_client->fql_query($query);

	// Build the list of user ids.
	$users_in = '(';
	foreach ($app_users as $i => $app_user) {
		$users_in .= $app_user['uid'] . ',';
	}
	$users_in[strlen($users_in)-1] = ')';

	// Now get additional information from the toddshirts DB
	$rank_query = '(SELECT count(*)+1 as rank from (select tbucks from users u1 where user_id in '.$users_in.') as s1 where tbucks > (select tbucks from (select user_id, tbucks from users u2 where user_id in '.$users_in.') as s2 WHERE s2.user_id = u3.user_id))';
	
	$query = 'SELECT '.$rank_query.' as rank, `user_id`, `sent` , `changes` , `invites` , `tbucks` , concat(`s`.`image_base`,`s`.`image_link`) as image_link, `s`.`name` as tname FROM `users` u3, `wearing_shirt` w, `sent_shirts` ss, `shirts` s WHERE u3.user_id IN '.$users_in.' AND u3.user_id = w.user AND w.status = "A" AND ss.ID = w.sent_shirts_ID AND ss.shirt_ID = s.ID order by rank';
	$query .= $max;

	$res = mysql_query($query, $conn);
	// TO-DO: Add error handling if result is not good!
	if ((!$res) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}
	
	// Index on the uids
	foreach ($app_users as $i => $app_user) {
		$uid_key[$app_user['uid']] = $i;
	}
	
	$app_users_ranked = array();
	while ($row = mysql_fetch_assoc($res)) {
		$i = $uid_key[$row['user_id']];
		$row['name'] = $app_users[$i]['name'];
		$row['pic_square'] = $app_users[$i]['pic_square'];
		$app_users_ranked[] = $row;
	}

//	pre_debug('$app_users_ranked', $app_users_ranked);

	return $app_users_ranked;
	
	// Now that we have the list of users get shirt info
	foreach ($app_users as $i => $app_user) {
		$user_summary = get_user_summary($app_user['uid']);
	
		$app_users[$i]['tbucks'] = $user_summary['tbucks'];
		$app_users[$i]['image_link'] = $user_summary['image_link'];		
	}
}
	
function get_num_app_users($fb, $user) {
 
	// Build the query
	$query = 'SELECT uid, name, pic_square FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=' . $user . ') AND is_app_user or uid = ' . $user;
	
	$num_app_users = $fb->api_client->fql_query($query);
	
	return sizeof($num_app_users);	
}

function free_tshirts_avail() {
	$avail = 0;
	$conn = get_db_conn();
	
	$query = 'select value from globals where name=\'FREE_TSHIRTS\'';
	$res = mysql_query($query, $conn);
	// TO-DO: Add error handling if result is not good!
	if ((!$res) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	
	$row = mysql_fetch_assoc($res);
	
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
	
	$res = mysql_query($query, $conn);
	// TO-DO: Add error handling if result is not good!
	if ((!$res) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	
	$row = mysql_fetch_assoc($res);
	
	$price = $row['price'];
	
	return $price;	
}

function do_invite($user, $invite_ids) {
	$conn = get_db_conn();
	$tbucks_earned = 0;
	$invites = 0;
	$mysqldate = date( 'Y-m-d H:i:s', time() );
	
	foreach($invite_ids as $id) {
		// Has user already invited this user in the past x hours?
		$query = 'SELECT count(*) FROM `invites`
	WHERE `from_user`='.$user.' AND to_user='.$id.' AND `time` > DATE_ADD( now(), INTERVAL -'.INVITE_INTERVAL.' HOUR)';
		$result = mysql_query($query, $conn);
		if ((!$result) && TESTING_ON) {
			echo('Invalid query: ' . mysql_error());
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
			// TO-DO: Add error handling if result is not good!
			if ((!$result) && TESTING_ON) {
				echo('Invalid query: ' . mysql_error());
			}			
		}
		
	}
		
		// Update the total invites this user has made today.
		$query = 'UPDATE users set invites=invites+'.$invites.', tbucks=tbucks+'.$tbucks_earned.', modified=now() WHERE user_id=\''.$user.'\' ';
		$result = mysql_query($query, $conn);  
		// TO-DO: Add error handling if result is not good!
		if ((!$result) && TESTING_ON) {
			echo('Invalid query: ' . mysql_error());
		}	
	
	return $tbucks_earned;
}

function get_recent_invites($user) {
	$conn = get_db_conn();

	$query = 'SELECT to_user as uid FROM `invites`
WHERE `from_user`='.$user.' AND `time` > DATE_ADD( now(), INTERVAL -'.INVITE_INTERVAL.' HOUR)';

	$result = mysql_query($query, $conn);
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
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
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
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

	echo 'Reseting shirt ranking...';	
	$query = 'update shirts set rank = '.MAX_RANK.', recent_rank = '.MAX_RANK.';';
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}	

	echo 'Updating recently popular shirts...';
	// Recently popular
	$query = 'SELECT shirt_ID, count(ID) as count_ID FROM `sent_shirts` WHERE `time` > DATE_ADD(now(), INTERVAL -7 DAY) group by shirt_ID having shirt_ID > 0 order by count_ID desc;';
	$result = mysql_query($query, $conn);
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
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
		// TO-DO: Add error handling if result is not good!
		if ((!$result) && TESTING_ON) {
			echo('Invalid query: ' . mysql_error());
		}
	}	

	echo 'Updated '.$rank.' recently popular shirts.';

	
	// All time popular
	$query = 'SELECT shirt_ID, count(ID) as count_ID FROM `sent_shirts` group by shirt_ID having shirt_ID > 0 order by count_ID desc;';
	$result = mysql_query($query, $conn);  
	// TO-DO: Add error handling if result is not good!
	if ((!$result) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
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
		// TO-DO: Add error handling if result is not good!
		if ((!$result) && TESTING_ON) {
			echo('Invalid query: ' . mysql_error());
		}
	}	

	echo 'Updated '.$rank.' popular shirts.';

	echo 'Finished script.';
	
	return;	
}	