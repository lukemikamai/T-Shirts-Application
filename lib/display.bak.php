<?php

// increment these when you change css or js files
define('CSS_VERSION', '20');
define('JS_VERSION', '07');

error_reporting(E_ALL);

function render_bool($res) {
  if ($res) {
    return 'true';
  } else {
    return 'false';
  }
}

/**
 * Render the dashboard and header for a page
 *
 * @param  string $selected The tab that is currently selected
 * @return void        Or a type, with a description here
 * @author
 */

function render_header($selected ='Send') {
  $header = '<link rel="stylesheet" type="text/css" href="'.ROOT_LOCATION.'/css/page.css?id='.CSS_VERSION.'" />';
// $header .= '<script src="'.ROOT_LOCATION.'/js/select-friends.js?id='.JS_VERSION.'" ></script>';
  
  $header .= '<fb:dashboard/>';

  $header .= '<p>Bookmarking image goes here</p>';
  $header .= '<p>Ad banner goes here</p>';

  $header .=
    '<fb:tabs>'
    .'<fb:tab-item title="Send T-Shirts"  href="index.php" '
      .'selected="' . ($selected == 'Send') .'" />'
    .'<fb:tab-item title="My T-Shirts"  href="mytshirts.php" selected="' . ($selected == 'Mine') . '" />'
    .'<fb:tab-item title="Invite Friends"  href="invite.php" selected="' . ($selected == 'Invite') . '" />'
    .'</fb:tabs>';
  $header .= '<div id="main_body">';
  return $header;
}

function render_footer() {
  $footer = '</div>';
  return $footer;

}

function render_shirts($shirts, $pagenum) {
  $fbml = '';
  $cols = 3;
  $rows = 3;
  $col_cur = 0;
  $row_cur = 1;
  
  $fbml .= '<table cellpadding="0" cellspacing="30" border=0><tr>';
  
  foreach ($shirts as $post) {

    $col_cur += 1;	  

	if ($col_cur == 1) {
        $fbml .= '<tr>';
	}
	
    $fbml .=  '<td>'
		  .   '<form method="POST" action="select-friends.php">'
//	      .   '&nbsp;' . $post['ID'] . '<br/>'
	      .   '<img src="' . ROOT_LOCATION . $post['image_link'] . '"/>'
		  .   '<br/>' . $post['name']
          .   '<br/><input type="submit" class="fb_button" name="Send" value="Send"/>'
		  .   '<input type="hidden" name="shirt_ID" value="'.$post['ID'].'"/>'
		  .   '<input type="hidden" name="pagenum" value="'.$pagenum.'"/>'	  
          .   '<a href="'.$post['affilliate_url'] 
		      . '" class="fb_button" target="_blank">Buy</a>'
		  .   '</form>'
	      .    '</td>';

	if ($col_cur == 3) {
        $fbml .= '</tr>';
		$col_cur = 0;
	}
		  
  }	
  
  $fbml .= '</table>';
  
  return $fbml;  
}

function render_myshirts($shirts, $pagenum, $type='Mine') {
  $fbml = '';
  $cols = 3;
  $rows = 3;
  $col_cur = 0;
  $row_cur = 1;
  
	switch ($type) {
		case 'Mine':
			$submit = 'Wear';
			break;
		case 'Send':
			$submit = 'Send';
			break;
	}
  
  $fbml .= '<table cellpadding="0" cellspacing="30" border=0 width="750px">';
  
  foreach ($shirts as $post) {

    $col_cur += 1;	  

	if ($col_cur == 1) {
        $fbml .= '<tr>';
	}
	
    $fbml .=  '<td>'
		  .   '<form method="POST" action="select-friends.php">'
//	      .   '&nbsp;' . $post['ID'] . '<br/>'
		  .	  '<table><tr><td>'
	      .   '<img src="' . ROOT_LOCATION . $post['image_link'] . '"/>'
		  .   '</td></tr>'
		  .   '<tr><td>' . $post['name'] . '</td></tr>'
          .   '<tr><td><input type="submit" class="fb_button" name="'. $submit . '" value="'. $submit . '"/>'
		  .   '<input type="hidden" name="shirt_ID" value="'.$post['ID'].'"/>'
		  .   '<input type="hidden" name="pagenum" value="'.$pagenum.'"/>'	  
          .   '<a href="'.$post['affilliate_url'] 
		      . '" class="fb_button" target="_blank">Buy</a></td></tr>'
	      .   '</table>'
		  .   '</form></td>';


	if ($col_cur == 3) {
        $fbml .= '</tr>';
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


function render_emoticon_grid($moods, $js="select(") {
  $ret = '';
  $i = 0;
  $ret.='<div class="table"><div class="row">';
  foreach($moods as $mood) {
    list($title,$smiley) = $mood;
    if ($i%3==0 && $i!=0) {
      $ret.='</div><div class="row">';
    }
    $ret .= '<div onclick="'.$js.'\''.$title.'\',\''.$smiley.'\','.$i.')" onmouseover="over('.$i.')" onmouseout="out('.$i.')" class="box" id="sm_'.$i.'"><div class="smiley">'.$smiley.'</div><div id="smt_'.$i.'" class="title">'.$title.'</div></div>';
    $i++;
  }
  $ret .= '</div></div>';
  return $ret;

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

function get_user_profile_box($tshirt) {
  $mood = array();
  return  '<style>

  h2 {
  text-align: center;
  font-size: 11px;
  color:#3B5998;
  }

  .smiley {
    font-size: 35pt;
    font-weight: bold;
    padding: 20px;
  }
  .smile {
  padding: 10px;
  width : 100px;
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
  margin-bottom: 20px;
  }

  </style>
  <h2>T&M are pleased to say that <fb:name useyou="false" uid="profileowner" /> is feeling:</h2>
  <div class="smile"><div class="smiley">'.$tshirt[1].'</div><div >'.$tshirt[0].'</div></div>
  <br /><p><a href="http://apps.facebook.com/tcsmiley/" requirelogin=1>Visit TCSmiley</a></p>';

}

