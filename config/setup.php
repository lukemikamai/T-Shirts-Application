<?php
$settings = parse_ini_file('settings.ini');
chdir('..');
$settings['MAIN_PATH'] = getcwd() . '/';

include_once $settings['MAIN_PATH'].'client/facebook.php';

$fb = new Facebook($settings['API_KEY'], $settings['SECRET_KEY']);
define('ROOT_LOCATION', $settings['ROOT_LOCATION']);
define('APP_SUFFIX', $settings['APP_SUFFIX']);

// Set feed template 1 (for them)
$one_line_story = array('{*actor*} sent a <b>{*tshirt*}</b> t-shirt to {*target*}');
$short_story = array(array('template_title' => '{*actor*} sent a <b>{*tshirt*}</b> t-shirt to {*target*}.',
                      'template_body' => 'Why don\'t you send a t-shirt now too?'));
// 'template_body' => '{*actor*} now has a total of {*tbucks*} tbucks and is ranked {*rank*} amongst <fb:pronoun uid="actor" possessive="true" useyou="false"/> friends.  Why don\'t you send a T-shirt too?'));

$action_links = array(); 
$action_links[] = array('text'=>'Send a t-shirt', 'href'=>'http://apps.facebook.com/toddshirts'); 
// $action_links[] = array('text'=>'See this t-shirt', 'href'=>"http://apps.facebook.com/toddshirts/mytshirts.php?shirt={*tshirt*}");

echo '<pre>';
print_r($action_links);
echo '</pre>'; 						   
						   
$res = $fb->api_client->feed_registerTemplateBundle($one_line_story, $short_story, null, $action_links);
echo 'Feed story ID= '.$res;
