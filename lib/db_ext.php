<?php

include_once './lib/constants.php'; 

if (TESTING_ON) {
	error_reporting(E_ALL);
}


$tables = show_tables();

foreach ($tables as $post) {
echo '<pre>';
print_r($post);
$row = describe_table($post['Tables_in_toddbiz2_toddshirts']);
	foreach ($row as $post2) {
		print_r($post2);
	}
echo '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ';	
echo '</pre>';
}

$rows = list_rows('users');
echo '<pre>';
foreach ($rows as $row) {
print_r($row);
}
echo '</pre>';

return false;

// Database functions

function get_db_conn() {
  $conn = mysql_connect($GLOBALS['db_ip'], $GLOBALS['db_user'], $GLOBALS['db_pass']);
  mysql_select_db($GLOBALS['db_name'], $conn);
  return $conn;
}


function show_tables() {
 
	$conn = get_db_conn();

	$query = 'show tables';
  
    $res = mysql_query($query, $conn);
	if ((!$res) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}		
	
  $tables = array();
  while ($row = mysql_fetch_assoc($res)) {
    $tables[] = $row;
  }
  return $tables;	
}

function describe_table($table) {
 
	$conn = get_db_conn();

	$query = 'describe ' . $table;
  
    $res = mysql_query($query, $conn);
	if ((!$res) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}		
	
	$fields = array();
	while ($row = mysql_fetch_assoc($res)) {
		$fields[] = $row;
	}
	return $fields;	

	
}


function list_rows($table) {
 
	$conn = get_db_conn();

	$query = 'select * from ' . $table;
  
    $res = mysql_query($query, $conn);
	if ((!$res) && TESTING_ON) {
		echo('Invalid query: ' . mysql_error());
	}		
	
	$rows = array();
	while ($row = mysql_fetch_assoc($res)) {
		$rows[] = $row;
	}
	return $rows;	




}


?>
