<?php

include_once 'utils.php';

// increment these when you change css or js files
define('JS_VERSION', '88');
define('CSS_VERSION', '40');
 
error_reporting(E_ALL);

function render_bool($res) {
  if ($res) {
    return 'true';
  } else {
    return 'false';
  }
}

function items_per_page() {
	$t = MAIN_COLS * MAIN_ROWS;
	return $t;
}

function friends_per_page() {
	$t = FRIEND_COLS * FRIEND_ROWS;
	return $t;
}

/**
 * Render the dashboard and header for a page
 *
 * @param  string $selected The tab that is currently selected
 * @return void        Or a type, with a description here
 * @author
 */

function render_header($selected ='Send') {
	$fbml = '<link rel="stylesheet" type="text/css" href="'.ROOT_LOCATION.'/css/page.css?id='.CSS_VERSION.'" />';
	
	// embeds the get_root_location() function into fbml so that the value of root location can be
	// accessed by other JS functions.
	$fbml .= '<script>
	function get_root_location () {
       var isTest = '.TEST_ENV. '
       if (isTest == 1) {
          return "http://www.toddbiz.com/tshirtstest";
       }
       else {
          return "http://www.toddbiz.com/tshirts";
       }
	}
	</script>';
	$fbml .= '<script type=\'text/javascript\' src="'.ROOT_LOCATION.'/js/base.js?id='.JS_VERSION.'" ></script>';

	// Firebug lite, so I can debug IE problems	
	if (TESTING_ON) {
		$fbml .= '<script type=\'text/javascript\' 
			src=\'http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js\'></script>';
	}
  
  $fbml .= '<fb:dashboard/>';

  $fbml .=
    '<fb:tabs>'
    .'<fb:tab-item title="Send T-Shirts"  href="index.php" '
      .'selected="' . ($selected == 'Send') .'" />'
    .'<fb:tab-item title="Invite Friends"  href="invite.php" selected="' . ($selected == 'Invite') . '" />'
    .'<fb:tab-item title="My T-Shirts"  href="mytshirts.php" selected="' . ($selected == 'Mine') . '" />'
    .'<fb:tab-item title="Compare Friends"  href="ftshirts.php" selected="' . ($selected == 'Friends') . '" />'
    .'<fb:tab-item title="Free T-Shirts"  href="freetshirts.php" selected="' . ($selected == 'Redeem') . '" />'
    .'<fb:tab-item title="Winners"  href="winners.php" selected="' . ($selected == 'Winners') . '" />'
	.'</fb:tabs>';
	
  $fbml .= '<div id="main_body">';

	// Empty Div for dynamically loading the "Select Friends" form into.
	// This is loaded using selectfriendsAJAX.php
	$fbml .= '<fb:js-string var="sf_dialog_text">';
	$fbml .= '<div id="sf_dialog_contents">';
	$fbml .= '<div class="dialog_loading">Loading...</div>';
	$fbml .= '</div>'; 
	$fbml .= '</fb:js-string>'; 

  
// Profile box
if ($selected == 'Mine') {
	$fbml .= 	'<fb:if-section-not-added section="profile">'
		. 	'Before you can wear t-shirts on your profile, you&rsquo;ll need to add the '.APP_NAME.' application to your profile.'
		.	'<div class="section_button">'
		.	'<fb:add-section-button section="profile"/>'
		.	'</div>'
		.	'</fb:if-section-not-added>';
} 
  
  return $fbml;
}

function render_footer() {

	$footer = '';
 
	'</div>';	// Main Body Div
	
	return $footer;
}

function render_search_controls($type='Mine', $get_shirts, $search_parms, $render_results)  {

	extract($search_parms);
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'params: '.print_r(compact('type', 'get_shirts', 'search_parms', 'render_results'), TRUE).'.');	
		
	$fbml = '';
    
	if ($search == '') {
		$search = 'Search for T-shirts';
	}

	// Header with sort and search controls
	if ($type == 'Send' || $type == 'Redeem') {
		
		$fbml .= '<div class="sort_bar clearfix">';
		$fbml .= '<div class="sort_options">';
//		$fbml .= '<h4>';

		
		// Sort	
		$classRP = '';
		$classPO = '';
		$classNA = '';
		$classAL = '';
		switch ($sort) {
			case 'RP':
				$classRP = 'current';
				break;
			case 'PO':
				$classPO = 'current';
				break;
			case 'NA':
				$classNA = 'current';
				break;
			case 'AL':
				$classAL = 'current';
				break;
		}		

		$parms = array();
		$parms['page'] = '1';
		$parms['rewriteid'] = 'search_page';
		$parms['get_shirts'] = $get_shirts;
		$parms['render_results'] = $render_results;	
		$parms['selected_tab'] = $type;		
		
		// Build the urls and fbml for the sort controls		
		$parms['tsort'] = 'RP';
		$query_string='?'.http_build_query($parms);
		$rewriteurl = ROOT_LOCATION.'/page.php'.$query_string;
		
	    $fbml .= '<form id="dummy_form_search"></form>';
		$fbml .= '<span class="'.$classRP.'"><a href="#" clickrewriteurl="'.$rewriteurl.'" clickrewriteform="dummy_form_search" clickrewriteid="search_page" clicktoshow="spinner">Recently Popular</a></span>';						

		$parms['tsort'] = 'PO';
		$query_string='?'.http_build_query($parms);
		$rewriteurl = ROOT_LOCATION.'/page.php'.$query_string;
		
		$fbml .= '<span class="pipe">|</span>';
		$fbml .= '<span class="'.$classPO.'"><a class="'.$classPO.'" href="#" clickrewriteurl="'.$rewriteurl.'" clickrewriteform="dummy_form_search" clickrewriteid="search_page" clicktoshow="spinner">Popular</a></span>';
		
		$parms['tsort'] = 'NA';
		$query_string='?'.http_build_query($parms);
		$rewriteurl = ROOT_LOCATION.'/page.php'.$query_string;		
		$fbml .= '<span class="pipe">|</span>';
		$fbml .= '<span class="'.$classNA.'"><a class="'.$classNA.'" href="#" clickrewriteurl="'.$rewriteurl.'" clickrewriteform="dummy_form_search" clickrewriteid="search_page" clicktoshow="spinner">Newly Added</a></span>';

		$parms['tsort'] = 'AL';
		$query_string='?'.http_build_query($parms);
		$rewriteurl = ROOT_LOCATION.'/page.php'.$query_string;		
		$fbml .= '<span class="pipe">|</span>';
		$fbml .= '<span class="'.$classAL.'"><a class="'.$classAL.'" href="#" clickrewriteurl="'.$rewriteurl.'" clickrewriteform="dummy_form_search" clickrewriteid="search_page" clicktoshow="spinner">All</a></span>';

		// Close the sort controls
//		$fbml .= '</h4>';		
		$fbml .= '</div>';		
		
		
		// Build the fbml for the search controls
		
		$fbml .= '<div style="float: right; padding: 0px 0px 00px 0px;">';
		$fbml  .=	'<form id="searchForm" action="" method="get">'
			.		'<input type="text" name="tsearch" class="search inputsearch inputtext" value="'.$search.'" size="30" onFocus="if(this.getValue()==\'Search for T-shirts\')this.setValue(\'\');"/>'
			.		'<select name="tcategory" class="drop_down_menu" size="1">';
			
			
		$fbml .= '<option class="menu_element" value="-1">- Category -</option>';
			
		$categories = get_categories();
		foreach ($categories as $ecategory) {
			$selected='';
			if ($ecategory['ID'] == $category) {
				$selected='SELECTED ';
			}
			$fbml .= '<option '.$selected.'class="menu_element" value="'.$ecategory['ID'].'">'.$ecategory['name'].'</option>';
		}

		$parms['rewriteid'] = 'search_page';
		
		// Build the urls and fbml for the sort controls		
		$parms['tsort'] = '';
		$query_string='?'.http_build_query($parms);
		$rewriteurl = ROOT_LOCATION.'/page.php'.$query_string;		
		
		$fbml .=	'</select>'
			.   	'<input type="hidden" name="selected_tab" value="'.$type.'">'
			.   	'<input type="hidden" name="render_results" value="'.$render_results.'">'
			.   	'<input type="hidden" name="get_shirts" value="'.$get_shirts.'">'
			.		'<input type="submit" class="fb_button" value="Search" clickrewriteurl="'.$rewriteurl.'" clickrewriteform="searchForm" clickrewriteid="search_page" clicktoshow="spinner" />'
			.		'</form>'
			.	 	'</div>'
			.		'';
			
		// End of sort and search div
	
		$fbml .= '</div>';	
	}

	return $fbml;
}

function render_myshirts($shirts, $pagenum, $type='Mine', $sort='RP', $search, $category, $user, $num_shirts, $can_redeem=FALSE) {
	$fbml = '';
 			

	$fbml .= '<div id="ttable" class="table" style="position: relative; left: 0px; opacity: 0; -moz-opacity: 0; filter: alpha(opacity=0);">';
	
	$fbml .= render_myshirt_rows($shirts, $pagenum, $type, $sort, $search, $category, $user, $num_shirts, $can_redeem);
  	  
	$fbml .= '</div>'; // End of ttable div
	
	// Hidden div for dynamically creating a submit form.
	$fbml .= '<div id="hiddenFormRedirect"></div>';
    
	return $fbml;  
	
}

function render_myshirt_rows($shirts, $pagenum, $type='Mine', $sort='RP', $search, $category, $user, $num_shirts, $can_redeem=FALSE) {

  $fbml = '';
 
	// Render each row of shirts (or a message that no shirts were found
	
	if ($num_shirts < 1) {
		$fbml .= '<div class="row">';
		$fbml .= 'Sorry, we didn\'t find any t-shirts matching your search.  Please try again with different search criteria.';  
		$fbml .= '</div>';	
		$fbml .= '</div>'; // End of table div
	}
	else 
	{
		
		// Set up depending which page we are on.
		$cols = MAIN_COLS;;
		$col_cur = 0;
		$row_cur = 1;
	 
		switch ($type) {
			case 'Mine':
				$submit = 'Wear';
				$action = ROOT_LOCATION.'/handlers/feedHandler.php';
				$fbtype = 'fbtype="feedStory"';
				$submitType = 'submit';
				$disabled = '';			
				$style = '';			
				break;
			case 'Send':
				$submit = 'Send';
	//			$action = 'select-friends.php';
				$action = '';
				$fbtype = '';
				$submitType = 'button';
				$disabled = '';
				$style = '';
				break;
			case 'Redeem':
				$submit = 'Redeem';
				$action = '???.php';
				$fbtype = '';
				$submitType = 'button';
				$disabled = ' disabled="true"';
				$style = 'background-color: #7180A4; color: #BDBDBD;';			
				if ($can_redeem) {
					$disabled = '';
					$style = '';			
				}
				break;
		}
	
		foreach ($shirts as $post) {

			$col_cur += 1;
			
			if ($col_cur == 1) {
				$fbml .= '<div class="row">';
			}
			
			$query = array();
			
			if ($post['ID'] != -1) {
				$url = parse_url($post['affilliate_url']);
				$query = array();
				if (isset($url['query'])) {
					parse_str($url['query'], $query);
				}
				else {
					trigger_error('Invalid affilliate URL for , User, '.$user.', Shirt ID, '.$post['ID'].', $url, "'.print_r($url, TRUE).'",', E_USER_WARNING);
				}
			}
		//	print_r($query);
		//	echo '</pre>';
			
			// What an effing mess after this point!  Have to clean up and document
			// what the eff is going on here!  Mostly it's building
			// some dynamic java script to attach to the buy button
			// as well as building the form for each t-shirt.
			$script = 'var par_names = []; var par_values = []; ';
			
			foreach ($query as $name => $parm) {
				$script .= 'par_names.push(\''.$name.'\'); ';
				$script .= 'par_values.push(\''.$parm.'\'); ';
			}
			
			$script .= 'newredirect(\''.$post['affilliate_url']. '\', par_names, par_values, \''.$user.'\', \''.$post['ID'].'\');';

			if (substr($post['image_base'], 0, 4) === 'http') {
				$image_base = $post['image_base'];
			} 
			else {
				$image_base = IMAGE_LOCATION . $post['image_base'] ;
			}
			
			if (substr($post['image_link'], 0, 4) === 'http') {
				$image_link = $post['image_link'];
			}
			else {
				$image_link = $image_base . $post['image_link'];
			}

			if ( $post['image_zoom'] != '' ) {
				if (substr($post['image_zoom'], 0, 4) === 'http') {
					$image_zoom = $post['image_zoom'];
				}
				else {
					$image_zoom = $image_base . $post['image_zoom'];
				}
			}
			else {
				$image_zoom = $image_link;
			}
			
			// Build the Send/Wear/Redeem button
			$onclick = '';
			if ( $type == 'Send' ) {
				$onclick = 'onClick="selectFriends(\''
					.$post['ID'].'\', \''
					.$image_link.'\', \''
					. addslashes($post['name']).'\', \''
					.$pagenum.'\'); return false; " ';
			}
			
			$submit_button = '<input '.$onclick.'type="'.$submitType.'" class="fb_button" label="'. $submit . '"name="'. $submit . '" value="'. $submit . '"'.$disabled.' style="'.$style.'"/>';
			
			// Styling for first column, first row for the My Shirts page
			// This cell is outlined because it's the shirt the user is wearing.
			$cell_style = '';
			$wearing_text = '';
			$from_text = '';			
			if ($type == 'Mine') {
				$from_text = '<div style="color:#3B5998; font-weight:normal;"><i>From '.$post['from_name'].'</i></div>';			
				if ($col_cur == 1 && $row_cur == 1) {
					$cell_style = 'style="border: 1px solid #94A3C4; height: 217px; position: absolute; top: -10px; left: 0px"';
					$wearing_text .= '<div style="color:#3B5998; font-weight:bold;">You are wearing:</div>';
					$submit_button = '';
					// The extra cell is to make up for the absolute
					// position which screws up the float: left.
					$fbml .=  	'<div class="cell"></div>';
				}
			}
									
				$fbml .=  	'<div id="wearingDiv" class="cell" '.$cell_style.'>'.$wearing_text
				  .	  	'<form id="shirtsForm.id'.$post['ID'].'" method="POST"'.$fbtype.'action="'.$action.'">'
				  .   	'<img class="shirt" id="image.id'.$post['ID'].'" src="' . $image_link . '" onmouseover="roll(\'image.id'.$post['ID'].'\', \''.$image_zoom.'\')"  onmouseout="roll(\'image.id'.$post['ID'].'\',\''.$image_link.'\')"/>'
//				  .   	'<img class="shirt" src="' . $image_link . '"/>';
				  .   	'<div class="cellDesc">' . $post['name'] . '</div>'
				  .		$from_text
				  .   	'<input type="hidden" name="shirt_ID" value="'.$post['ID'].'"/>';
				  
			if ( isset($post['sent_shirts_ID'])) {
				$fbml  .=   '<input type="hidden" name="sent_shirts_ID" value="'.$post['sent_shirts_ID'].'"/>';
			}	
			
			
			$fbml .=   	'<input type="hidden" name="pagenum" value="'.$pagenum.'"/>'
				  .   	'<input type="hidden" name="image" value="'.$image_link.'">'
				  .   	'<input type="hidden" name="tsort" value="'.$sort.'">'
				  .   	'<input type="hidden" name="tsearch" value="'.$search.'">'
				  .   	'<input type="hidden" name="tcategory" value="'.$category.'">'
				  .   	'<input type="hidden" name="shirt_name" value="'.$post['name'].'">'
				  .   	'<div>'.$submit_button
				  .   	'<input type="button" class="fb_button" value="Buy" onClick="'.$script.'" />'
				  .		'</div>'  
				  .   	'</form>';

				$fbml  .=   '</div> <!-- End of cell div -->';
				  
			if ($col_cur == MAIN_COLS) {
				$fbml .= '</div> <!-- End of row div -->';
				$col_cur = 0;
				$row_cur += 1;	  

			}
		}		
	}	
	
	// I think I'm missing some divs here?
	
				

	
	return $fbml;  
  
}

function render_shirts($shirts, $pagenum, $type='Mine') {
  $fbml = '';
  $cols = MAIN_COLS;
  $rows = MAIN_ROWS;
  $col_cur = 0;
  
	switch ($type) {
		case 'Mine':
			$submit = 'Wear';
			$action = 'mytshirts.php';
			break;
		case 'Send':
			$submit = 'Send';
			$action = 'select-friends.php';			
			break;
	}
  
  $fbml .= '<div class="table">';
  
  foreach ($shirts as $post) {

    $col_cur += 1;	  

	if ($col_cur == 1) {
        $fbml .= '<div class="row">';
	}
	
    $fbml .=  '<div class="cell">'
		  .	  '<form method="POST" action="'.$action.'">'
	      .   '<img class="shirt" src="' . IMAGE_LOCATION . $post['image_link'] . '"/>'
		  .   '<div class="cellDesc">' . $post['name'] . '</div>'
		  .   '<input type="hidden" name="shirt_ID" value="'.$post['ID'].'"/>'
		  .   '<input type="hidden" name="pagenum" value="'.$pagenum.'"/>'	  
          .   '<div><input type="submit" class="fb_button" name="'. $submit . '" value="'. $submit . '"/>'
          .   '<a href="'.$post['affilliate_url'] 
		      . '" class="fb_button" target="_blank">Buy</a></div>'
		  .   '</form></div>';


	if ($col_cur == MAIN_COLS) {
        $fbml .= '</div>';
		$col_cur = 0;
	}		  
  }	
  
  $fbml .= '</table>';
  
  return $fbml;  
}

function render_inline_style() {
 return  '<style>
  h2 {
   font-size: 20pt;
   text-align: center;
  }

  .box {
  padding: 10px;
  width : 100px;
  height : 90px;
  display : block;
  float : left;
  text-align: center;
  border: black 1px;
  margin-right: 10px;
  margin-left: 10px;
  cursor: pointer;
  border: black solid 2px;
  background: orange;
  margin-left: 32px;
  margin-top: 30px;
  }
  h3 {
  text-align: center;
  font-size: 11px;
  color:#3B5998;

  }

  .big_box {
  padding: 10px;
  width : 300px;
  height : 300px;
  margin: auto;
  text-align: center;
  border: black 1px;
  cursor: pointer;
  border: black solid 2px;
  background: orange;
  color: black;
  text-decoration: none;
  }
  a.box {
   color: black;
  }


  a:hover.box {
   text-decoration: none;
  }

  .smiley {
  font-size: 25pt;
  font-weight: bold;
  padding: 10px;
  color: black;
  text-decoration: none;
  }


  .big_smiley {
  font-size: 100pt;
  font-weight: bold;
  padding: 40px;
  }

.past {
 margin:auto;
 width: 500px;
}
</style>
';
}

function render_handler_css() {
  $css  = '<style>.box {

  height :70px;
  width : 70px;
  float : left;
  text-align: center;
  border: black 1px;
  margin-right: 10px;
  margin-left: 10px;
  cursor: pointer;
  border: black solid 2px;
  background: orange;
  margin-left: 32px;
  margin-top: 20px;
}
.smiley {
  font-size: 20pt;
  font-weight: bold;
  padding: 0px;
  padding-top: 20px;
}

.box_selected {
  border: 2px dashed black;
  background: #E1E1E1;
}

.title {
 padding-top: 10px;
 font-size: 10px;
 visibility: hidden;
}

.box_selected .title {
  visibility: visible;
}

.box_over .title {
 visibility: visible;
}

</style>';
  return $css;
}

function render_handler_js() {
  $code .= '
<script>
var cur_picked = -1;
function over(id) {
  document.getElementById("sm_"+id).addClassName("box_over");
}
function out(id) {
  document.getElementById("sm_"+id).removeClassName("box_over");
}

function select(title, mood, id, feed) {
  document.getElementById("sm_"+id).addClassName("box_selected");
  document.getElementById("picked").setValue(id);
  if (feed) {
    Facebook.showFeedDialog("http://www.srush3.devrs001.facebook.com/intern/howareyoufeeling/feedHandler.php", {"picked":id});
  } else {
    Facebook.setPublishStatus(true);
  }
}

function unselect(id) {
  document.getElementById("sm_"+id).removeClassName("box_selected");
}

function picked(i) {
  if (cur_picked!=-1) {
    unselect(cur_picked);
  }
  cur_picked = i;
  select(i);
}
</script>
';
  return $code;

}

// Creates the FBML for the User Profile Box shown on the user's
// front page.  
// TO-DO: Add a link to the image so it's clickable
// TO-DO: Don't hard code the link to the application
function get_user_profile_box($shirt_name, $image) {

  return  '<style>

  h2 {
  text-align: center;
  font-size: 11px;
  color:#3B5998;
  }

  .shirtProfileBox {
  padding: 10px;
  width : 150px;
  float : left;
  text-align: center;
  border: black 1px;
  margin-right: 5px;
  margin-left: 5px;
  cursor: pointer;
  border: black solid 0px;
  background: white;
  margin-left: 10px;
  margin-top: 10px;
  margin-bottom: 10px;
  }
  
.shirtImg {
  width : 150px;
  height : 150px;
 }
 

  </style>
  <h2><fb:name useyou="false" uid="profileowner" /> is wearing:</h2>
  <div class="shirtProfileBox"><div><img class="shirtImg" src="'.$image.'"/><div><div >'. $shirt_name.'</div></div><div ><a href="http://apps.facebook.com/toddshirts/" requirelogin=1>Visit T-Shirts</a></div></div>';

}

function render_myfriends($app_users, $pagenum, $type, $user) {

	$col_cur = 0;
	$fbml = '';
	

//	$border_style = ' border: black solid 2px; ';
	$border_style = '';

	
	if ($app_users) {
		foreach ($app_users as $app_user) {

			// Get data
			$uid = $app_user['user_id'];
			$pic_small = $app_user['pic_square'];
			$name = $app_user['name'];
			$tbucks = $app_user['tbucks'];
//			$image_link = ROOT_LOCATION .$app_user['image_link'];
			$t_name = $app_user['tname'];
			$rank = $app_user['rank'];
			if ($pic_small == '') {
				$pic_small = 'http://static.ak.fbcdn.net/pics/q_silhouette.gif';
			}

			$col_cur += 1;	  

			if ($col_cur == 1) {
				$fbml .= '<div class="row" style="'.$border_style.' height: 160px; direction: ltr; position: relative; padding: 0px 0px; margin: 10px 0px 0px 0px;">';
			}
			
			if (substr($app_user['image_base'], 0, 4) === 'http') {
				$image_base = $app_user['image_base'];
			} 
			else {
				$image_base = IMAGE_LOCATION . $app_user['image_base'] ;
			}
			
			if (substr($app_user['image_link'], 0, 4) === 'http') {
				$image_link = $app_user['image_link'];
			}
			else {
				$image_link = $image_base . $app_user['image_link'];
			}
			
			
			// Cell for each friend
//			$fbml .= '<div class="cell" style="width:550px;'.$border_style.' min-height: 200px;">';
					
			// FB Profile Pic
			$fbml .= '<img style="position: absolute; left: 0; top: 0; display: block; overflow: hidden; vertical-align: middle; background:transparent none repeat scroll 0 0 !important;" src="'. $pic_small .'" width="50px" height="50px">';
			
			// Div for Body
			$fbml .= '<div style="'.$border_style.' margin: 0px 0px; padding: 0 0 0 60px;">';
						
			// Stats Div
			$fbml .= '<div style="'.$border_style.' text-align: left; padding-right: 10px; float: left; width: 150px">';
			$fbml .= '<a style="font-size: 13px; color: #444444; font-weight: bold;" href="/profile.php?id='.$uid.'">'.$name.'</a>';
			$fbml .= '<div style="color: gray">Rank '.$rank.'</div>';
			$fbml .= '<div>'.number_format($tbucks).' tbucks</div>';
			$fbml .= '<div>Wearing <i>'.$t_name.'</i> T-shirt</div>';
						
			// Stats CLOSE
			$fbml .= '</div>'; 

			// Tshirt Pic
			$fbml .= '<div style="'.$border_style.'">'; // Tshirt
			$fbml .= '<img src="'. $image_link .'" width="130px" height="130px">';
			$fbml .= '</div>';

			// Body CLOSE
			$fbml .= '</div>'; 
			
			// Cell for each friend CLOSE
//			$fbml .= '</div>';

			// Are we at the last column?
			if ($col_cur == FRIEND_COLS) {
				$fbml .= '</div>'; // Close Row Div
				$col_cur = 0;
			}
			
		}
	}

	
	return $fbml;

}

function render_user_summary($user_summary) {

	$fbml = '';
	$fbml .= '<div class="bar summary_bar clearfix">';
	$fbml .= '<h2>';	
	$fbml .= 'You have <div id="user_summary_tbucks" style="display: inline;">' .number_format($user_summary['tbucks']). ' </div> T-bucks&trade;';
	$fbml .= '</h2>';		
	$fbml .= '<h3>';	
	$fbml .= 'Send T-shirts, invite friends, and change your T-shirt to earn more T-bucks&trade;';
	$fbml .= '</h3>';
	$fbml .= '<h4 style="text-align:center;">';	
	$fbml .= 'You have sent '.$user_summary['sent'].' T-shirts today.';
	$fbml .= ' You have invited '.$user_summary['invites'].' friends today.';
	if ($user_summary['changes'] >= 1) {
		$fbml .= ' You have changed your T-shirt today.';
	}
	else {
		$fbml .= ' You have not changed your T-shirt yet today.';
	}

	$fbml .= '</h4>';
	$fbml .= '<br/>';	
	$fbml .= '</div>';	
	return $fbml;
}
	
function pre_debug($name, $var) {

	$html = '';
	
	if (TESTING_ON) {	
		$html = '<pre>'.$name.' ';
		$html .= print_r($var, TRUE);
		$html .= '</pre>';
		return $html;
	}
}

function render_invite($user, $user_summary) {
	$fbml = '';
  
	// TBUCKS SUMMARY
	$fbml .= render_user_summary($user_summary);	
	
	$fbml .= '<p>TBC - Invite Page';
	
	return $fbml;
}

// Free t-shirts user summary
function render_free_tshirts($user_summary) {
	$fbml = '';

	$qty = free_tshirts_avail();	
	$price = free_tshirts_price();
	
	// Free tshirts
	$fbml .= '<div class="bar summary_bar clearfix">';	
//	$fbml .= '<h2> X,XXX free T-shirts given away so far</h2>';
	$fbml .= '<h2>';
	$fbml .= 'You have ' .number_format($user_summary['tbucks']). ' T-bucks&trade;. ';	
	$fbml .= '</h2>';
	$fbml .= '<h3>';	
	$fbml .= 'You need '.number_format($price).' T-bucks&trade; to redeem a free t-shirt';
	$fbml .= '</h3>';
	$fbml .= '<h3>Buy t-shirts to lower the number of T-bucks&trade; needed to redeem a free t-shirt</h3>';
	$fbml .= '<br/>';	
	$fbml .= '</div>';	
	
//	$fbml .= pre_debug('$user_summary', $user_summary);
	
	return $fbml;
	
}

// Renders: 
// - The pagination stuff
// - A page of shirts.  
// The list of shirts is determined by the $get_shirts variable function
// in order to allow this function to be used by for different tabs
// such as 'Send Shirts' and 'My Shirts'
function render_results_page($pagenum, $user, $selected_tab, $get_shirts, $search_parms, $render_results, $can_redeem, $fb)
{
	
	extract($search_parms);
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'params: '.print_r(compact('pagenum', 'user', 'selected_tab', 'get_shirts', 'search_parms', 'render_results', 'can_redeem'), TRUE).'.');		
	
	$ipp = items_per_page();	
		
	$shirts = array();
	list($shirts, $num_shirts) = call_user_func($get_shirts, $pagenum, $ipp, $search_parms, $fb);
	
	$fbml = '';
	// Pagination
	// Build the parameters
	$parms = array();
	// $parms['page'] = $pagenum;
	$parms['tsearch'] = $search;
	$parms['tsort'] = $sort;
	$parms['tcategory'] = $category;
	$parms['rewriteid'] = 'results_page';
	$parms['selected_tab'] = $selected_tab;
	$parms['get_shirts'] = $get_shirts;
	$parms['search_parms'] = $search_parms;
	$parms['render_results'] = $render_results;
	
	$query_string='?'.http_build_query($parms);

	$ajax=array();
	$ajax['rewriteurl'] = ROOT_LOCATION.'/page.php'.$query_string;
	$ajax['rewriteid'] = 'results_page';
	$ajax['rewriteform'] = 'dummy_form';
	$ajax['loadingimg'] = 'spinner';
	$ajax['hide'] = '';

	$fbml .= '<form id="dummy_form"></form>';
	$pagination = get_pagination_string($pagenum, $num_shirts, $ipp, $ajax, '', $query_string, 'T-shirts<img src="http://www.facebook.com/images/loaders/indicator_blue_small.gif" id="spinner" style="display:none;"/>');
	$fbml .= $pagination;

	// Shirts 
	$fbml .= render_myshirts($shirts, $pagenum, $selected_tab, $sort, $search, $category, $user, $num_shirts, $can_redeem); 

	// This will get displayed when going to another page.
	// For now it is hidden.
	// We also use this image for a hack.
	// Since divs don't have an onload, we use the image's onload
	// to fade in the div.
	$fbml .= '<img src="http://www.facebook.com/images/loaders/indicator_blue_large.gif" id="spinner2" style="display:none;"/>';
	$ajax['loadingimg'] = 'spinner2';

	
	// Script for fading in the ttable div.
	// TO-DO: Parameterize this by putting it in php
    $url = ROOT_LOCATION.'/js/fadeIn.js';
    $fbml .= '<script>';
    $fbml .= file_get_contents($url);
    $fbml .= '</script>';
		
//	$fbml .= '<img onload="Animation(document.getElementById(\'ttable\')).duration(400).checkpoint().to(\'opacity\', 1).from(0).duration(400).ease(Animation.ease.both).go();" src="http://www.facebook.com/images/loaders/indicator_blue_large.gif" id="spinner2" style="display:none;"/>';
	
	
	
	// Page footer
	$fbml .= get_pagination_string($pagenum, $num_shirts, $ipp, $ajax, '', $query_string, 'T-shirts', 'footer');
	
	// Fade in the page

//	$fbml .= '<script>Animation(document.getElementById(\'ttable\')).from(\'left\', \'800px\').to(\'left\', \'0px\').to(\'opacity\', 1).duration(200).go();</script>';


// Commented out because it might be interfering with the script link
// in the header.
//	$fbml .= '<script>Animation(document.getElementById(\'ttable\')).to(\'opacity\', 1).from(0).duration(200).go();</script>';	
	
	return $fbml;

}

function main_page($pagenum, $rewriteid='main_page', $selected_tab, $get_shirts, 
	$search_parms, $render_results, $can_redeem=FALSE, 
	$render_user_summary='render_user_summary', $fb) 
{
	
	extract($search_parms);
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'params: '.print_r(compact('pagenum', 'rewriteid', 'selected_tab', 'get_shirts', 'search_parms', 'render_results', 'can_redeem', 'render_user_summary'), TRUE).'.');	

	$fbml = '<fb:google-analytics uacct="UA-11149290-1" page="'.$selected_tab.'" />';
	
	// If the user did a search then record the search terms.
	// TODO: Ideally this would be done async...  but since we don't have an
	// async architecture yet it's done syncronously.
	update_searches($search, $category, $user);
	
	// $fbml .= pre_debug('function main_page: $rewriteid', $rewriteid);
	
	if ( $rewriteid == 'main_page' ) {
	
		$user_summary = get_user_summary($user);
		// Header with tabs
		$fbml .= render_header($selected_tab);

		// Render the user summary at the top for even users
		if ($user%2 == 0) {		
			$fbml .= '<div id="user_summary">';
			$fbml .= call_user_func($render_user_summary, $user_summary); 
			$fbml .= '</div>';
		}
			
			$fbml .= '<div id="search_page">';
	
	}
	
			if ( $rewriteid == 'search_page' || $rewriteid == 'main_page' ) {
	
				$fbml .= render_search_controls($selected_tab, $get_shirts, $search_parms, 
					$render_results);				
	
				$fbml .= '<div id="results_page">';
			}
			
				$fbml .= call_user_func($render_results, $pagenum, $user, $selected_tab, 
					$get_shirts, $search_parms, $render_results, $can_redeem, $fb);
												
			if ( $rewriteid == 'search_page' || $rewriteid == 'main_page' ) {
	
				$fbml .= '</div>';
			}
	
	// Render the user summary at the bottom for odd users
	if ($user%2 != 0) {
		$render_user_summary = 'render_user_summary'; //this value is getting set wrongly when this branch occurs, so manually set it now.
		$user_summary = get_user_summary($user);				
		$fbml .= '<div id="user_summary">';
		$fbml .= call_user_func($render_user_summary, $user_summary); 
		$fbml .= '</div>';
	}
			
	if (TESTING_ON && isset($_POST)) {
	   $fbml .= pre_debug('$_POST', $_POST);
	}
	
	if (TESTING_ON && isset($_GET)) {
	   $fbml .= pre_debug('$_GET', $_GET);
	}
	
			
	if ( $rewriteid == 'main_page' ) {
		$fbml .= render_footer();
	}
	
	return $fbml;

}

function welcome_page($selectedTab) 
{
	
	$fbml = '';
	
	debug_log(__FUNCTION__, __FILE__, __LINE__, 'Building Welcome Page');	
				
	$fbml .= render_header($selectedTab);
		
	$fbml .= '<div class="bar summary_bar clearfix">';
	$fbml .= '<h2>';	
	$fbml .= 'Welcome to '.APP_NAME;
	$fbml .= '</h2>';
	$fbml .= '<h3>';	
	$fbml .= 'You just earned '.JOIN_BONUS.' T-bucks&trade; for joining!';
	$fbml .= '</h3>';
	$fbml .= '<h3>';	
	$fbml .= 'Send T-Shirts, Invite Friends, and change your t-shirt to earn more T-bucks&trade;';
	$fbml .= '</h3>';
	$fbml .= '<br/>';		
	$fbml .= '</div>';	

	// TODO: Put pictures of the tabs in the text.
	$fbml .= '<br/>';		
	$fbml .= '<div style="font-size: 14px">';
	$fbml .= '<br/>';
	$fbml .= '<b>Send T-Shirts</b> to your friends for fun and to earn T-bucks&trade;.';

	$fbml .= '<br/><br/>';
	$fbml .= 'T-bucks&trade; can be used to redeem real t-shirts for free.<sup>1</sup>';

	$fbml .= '<br/><br/>';
	$fbml .= '<b>Invite Friends</b> to earn more T-bucks&trade;.<sup>2</sup>';

	
	$fbml .= '<br/><br/>';
	$fbml .= 'Nobody likes a dirty t-shirt so you even get T-bucks&trade; for changing your t-shirt. Just go to the <b>My T-Shirts</b> tab.<sup>3</sup>';

	$fbml .= '<br/><br/>';
	$fbml .= '<h3>Now the only thing left to do is to remember to have fun!<sup>4</sup></h3>';
	$fbml .= '<h3>Click on the <i>Send T-Shirts</i> tab above to start sending t-shirts to your friends!</h3>';

	$fbml .= '<br/><br/>';
	$fbml .= '<div style="font-size: 9px;"><i><sup>1&nbsp;</sup>Free T-shirts shipping not included. See terms of service. Our attorneys made us put this phrase here. Even though we are fair and use common sense just like you.</i></div>';	

	$fbml .= '<br/>';
	$fbml .= '<div style="font-size: 9px;"><sup>2&nbsp;</sup><i>Invite friends but only if you think they would like this application. No one likes SPAM.</i></div>';

	$fbml .= '<br/>';
	$fbml .= '<div style="font-size: 9px;"><sup>3&nbsp;</sup><i>We recommend changing your t-shirt at least once a day. More often in hot weather or after physical activity. Our mothers made us put this phrase here. Which goes to show that Mom\'s advice makes a lot more sense than what the attornies make us say. And it\'s way cheaper too. Thanks to all the mothers of the world.</i></div>';

	$fbml .= '<br/>';
	$fbml .= '<div style="font-size: 9px;"><sup>4&nbsp;</sup><i>Did you forget about having fun? What are you doing reading all this fine print? Get out there and send some t-shirts!</i></div>';

	$fbml .= '</div>';
	
	return $fbml;

}
?>