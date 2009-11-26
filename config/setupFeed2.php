<?php
// $settings = parse_ini_file('settings.ini');
// chdir('..');
// $settings['MAIN_PATH'] = getcwd() . '/';

include_once '../lib/constants.php';
include_once '../client/facebook.php';

$fb = new Facebook(API_KEY, SECRET_KEY);

// Set feed template 2 (for the user)
$one_line_story = array('{*actor*} changed <fb:pronoun uid="actor" possessive="true" useyou="false"/> t-shirt and is now wearing <fb:pronoun uid="actor" possessive="true" useyou="false"/> <b>{*tshirt*}</b> t-shirt.');
$short_story = array(array('template_title'   => '{*actor*} changed <fb:pronoun uid="actor" possessive="true" useyou="false"/> t-shirt and is now wearing <fb:pronoun uid="actor" possessive="true" useyou="false"/> <b>{*tshirt*}</b> T-shirt.', 
	'template_body'    => 'Isn\'t it time you changed your t-shirt too?'));
//	'template_body'    => '{*actor*} now has a total of {*tbucks*} tbucks and is ranked {*rank*} amongst <fb:pronoun uid="actor" possessive="true" useyou="false"/> friends. <br/>Isn\'t it time you changed your t-shirt too?'));

$action_links = array(); 
$action_links[] = array('text'=>'Send a t-shirt', 'href'=>'http://apps.facebook.com/toddshirts'); 
$action_links[] = array('text'=>'Change your t-shirt', 'href'=>"http://apps.facebook.com/toddshirts/mytshirts.php");

echo '<pre>';
print_r($action_links);
echo '</pre>'; 						   
						   
$res = $fb->api_client->feed_registerTemplateBundle($one_line_story, $short_story, null, $action_links);
echo '<p>Feed story 2 ID= '.$res.'</p>';
echo '<p>Make sure you update FEED_STORY_2 constants in constants.php.</p>';
