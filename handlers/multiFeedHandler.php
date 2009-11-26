<?php
  /*
   * multiFeedHandler.php - Posting to other's feed form handler
   *
   */

include_once '../lib/constants.php';
include_once LIB_PATH.'display.php';
include_once LIB_PATH.'db.php';
include_once CLIENT_PATH.'facebook.php';

$fb = new Facebook(API_KEY, SECRET_KEY);
$user = $fb->require_login();

$image = $_POST['image'];
$shirt_name = $_POST['shirt_name'];
$shirt_name = str_replace("\\'", "'", $shirt_name);
$shirt_name = str_replace("\\\\", "\\", $shirt_name);
$shirt_id = $_POST['shirt_ID'];
$to_users = $_POST['ids'];
$pagenum = $_POST['pagenum'];
$tbucks_earned = 0;
$tbucks = 'TBC!'; 
$rank = 'TBC!'; 
 
foreach ($to_users as $to_user) {
	$tbucks_earned += do_send($shirt_id, $user, $to_user);
	$result = do_publishing($fb, $user, $to_user, $shirt_name, $image);
} 

$canvas_url = $fb->get_facebook_url('apps') . '/' . APP_SUFFIX . '?page=' . $pagenum;
// $canvas_url = $fb->get_facebook_url('apps') . '/' . APP_SUFFIX . '?page=' . $pagenum.'&tbucks_for_send='.$tbucks_earned;

$next_fbjs='selectFriendsNext('.$tbucks_earned.');';

  $feed = 	array(	'template_id' =>  FEED_STORY_1,
					'template_data' => array('tshirt' => $shirt_name,
											'tbucks' => $tbucks,
											'rank' => $rank,
											'images' => array(array('src' => $image,'href' => $canvas_url))
								)
			);
 
  $data = array('method'=> 'multiFeedStory',
				'user_message' => 'Test User Message',
				'user_message_prompt' => 'Test User Message Prompt',
                'content' => array( 'feed'    => $feed,
//                                    'next'    => $canvas_url,
									'next_fbjs' => $next_fbjs
                                    ));
echo json_encode($data)
?>
