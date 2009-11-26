<?php
  /*
   * feedHandler.php - Feed form handler
   *
   */
include_once '../lib/constants.php';
include_once LIB_PATH.'db.php';
include_once LIB_PATH.'display.php';
include_once CLIENT_PATH.'facebook.php';

$fb = new Facebook(API_KEY, SECRET_KEY);
$user = $fb->require_login();

$image = $_POST['image'];
$shirt_name = $_POST['shirt_name'];
$shirt_id = $_POST['shirt_ID'];
$pagenum = $_POST['pagenum'];
$category = $_POST['tcategory'];
$search = $_POST['tsearch'];
$sort = $_POST['tsort'];
$pagenum = $_POST['pagenum'];
$sent_shirts_ID = $_POST['sent_shirts_ID'];
$tbucks = 'TBC!';
$rank = 'TBC!';

$main_box = get_user_profile_box($shirt_name, $image);
$fb->api_client->profile_setFBML(null, $user, null, null, null, $main_box);
$tbucks_earned = wear_shirt($user, $sent_shirts_ID, $shirt_id);


$next_fbjs='wearNext(\''.$tbucks_earned.'\', \''.$search.'\', \''.$sort.'\', \''.$category.'\',\''.$pagenum.'\', \''.$user.'\');';

  
  $canvas_url = $fb->get_facebook_url('apps') . '/' . APP_SUFFIX .'/mytshirts.php?page=' . $pagenum.'&tbucks_for_change='.$tbucks_earned;
  $img_url = $fb->get_facebook_url('apps') . '/' . APP_SUFFIX . '/';
  

  $feed = 	array(	'template_id' =>  FEED_STORY_2,
					'template_data' => array('tshirt' => $shirt_name,
											'tbucks' => $tbucks,
											'rank' => $rank,
											'images' => 
												array(array('src' => $image,'href' => $img_url))
								)
			);
 
  $data = array('method'=> 'feedStory',
				'user_message' => 'Test User Message',
				'user_message_prompt' => 'Test User Message Prompt',
                'content' => array( 	'feed'    => $feed,
//                                    	'next'    => $canvas_url,
										'next_fbjs' => $next_fbjs
                                    ));
echo json_encode($data);
