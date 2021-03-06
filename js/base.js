var cur_picked = -1;
var dlg = new Dialog(Dialog.DIALOG_POP);
var join_bonus = false;

// root location is now defined in PHP.
// display.php line 40 is a JS function get_root_location() which is embedded into
// the page before base.js is loaded, so calls to get_root_location() in base.js will
// return the root_location.

function roll(img_name, img_src) {
	document.getElementById(img_name).setSrc(img_src);
}


function over(id) {
  document.getElementById("sm_"+id).addClassName("box_over");
}
function out(id) {
  document.getElementById("sm_"+id).removeClassName("box_over");
}
function select(title, mood, id) {
  document.getElementById("sm_"+id).addClassName("box_selected");
  document.getElementById("picked").setValue(id);
}

function final(template_id, image_src, base, callback, title, emote, id) {
  select(title, emote, id);
  var image =  image_src + id + ".jpg";
  var template_data = {'mood': title,
                       'emote': emote,
                       'mood_src': image,
                       'images' : [{'href':'http://www.facebook.com' , 'src' : image}]};


  var ajax = new Ajax();
  ajax.responseType = Ajax.RAW;
  ajax.post(callback+'handlers/jsFeed.php', {'picked':id});

  Facebook.showFeedDialog(template_id, template_data, '', [],
                          function() {document.setLocation(base + 'mysmilies.php');});
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

function openwindow() {
return false;
}

function newredirect(url, par_names, par_values, user, shirt_ID) {

	// Update the database using AJAX
	var message = '';
	var doBuy = new Ajax();
	var queryParams = { "user" : user, "shirt_ID" : shirt_ID };
	doBuy.responseType = Ajax.JSON;
	doBuy.ondone = function(data){

		console.log(data);
		if(data.updated){
			message = 'Updated!';
		}else{
			message = 'Problem updating!';
		}
		
		// Now open the new window
		console.log('newredirect: About to invoke openBuyURL');
		openBuyURL(url, par_names, par_values);
		
	}

//	doBuy.onerror = function(){
//		console.log('AJAX ERROR!!!');
//	}
	
	doBuy.post(get_root_location() + '/handlers/doBuy.php', queryParams);

	return message;

}	

function openBuyURL(url, par_names, par_values) {
	
	// Open the new window or tab by dynamically creating and submiting a form.
	var form = document.createElement("form");
	var input;

    form.setTarget("_blank");
    form.setMethod("get");
    form.setAction(url);
 //	console.dir(form); 
 // in Safari and Firefox, you could just submit now, but IE insists the form is appended to the document
    // you need the id of an element to append the form to, any will do (we'll make sure the form isn't visible) but a div is probably safest
    form.setStyle({display: "none"});
    document.getElementById('hiddenFormRedirect').appendChild(form);
	// Create input parameters.
	for (var i=0; i<par_names.length; i++) {
		input = document.createElement("input");
		input.setName(par_names[i]);
		input.setValue(par_values[i]);
		form.appendChild(input);
	}

    // call submit on the form
	console.log('openBuyURL: About to submit form');
	form.submit();

	return;	
}

function showSendEarned(tbucks) {

	var myDialog = new Dialog(Dialog.DIALOG_POP);
	
	title = 'T Bucks!';
	content = 'Congratulations, you just earned ' + tbucks + ' for sending t-shirts.';
	myDialog.showMessage(title, content, button_confirm='OK');
	return;
}

function showJoinBonus(tbucks) {

	if (join_bonus != true) {
		
		var myDialog = new Dialog(Dialog.DIALOG_POP);

		join_bonus = true;
		title = 'Welcome to Tbucks for Tshirts';
		content = 'Congratulations, you just earned ' + tbucks + ' tbucks for joining!';
		myDialog.showMessage(title, content, button_confirm='OK');
	
	}
	
	return;
}

function selectFriends(shirtID, imageLink, shirtName, pageNum) {

	var params={"Send":'1',"shirt_ID":shirtID,"pagenum":pageNum,"image":imageLink,"shirt_name":shirtName};
	var ajax = new Ajax(); 
	
	title = 'Send a T-Shirt';

// Build the AJAX object to request the dialog contents. 
	ajax.responseType = Ajax.FBML;
	ajax.requireLogin = true;
	ajax.ondone = function(data) { document.getElementById('sf_dialog_contents').setInnerFBML(data); }

	// The below code doesn't work because it's not FBML: I.e.
	// it hasn't be parsed by FB servers.  So we have to use
	// ajax.
/*
	var data =  '<div id="testID" style="text-align: center"><form fbtype="multiFeedStory" method="POST" action="' + root_location + '/handlers/multiFeedHandler.php">' +
    '<div style="margin: 0 auto; width: 350px"><fb:multi-friend-input/></div>' +
  '<img class="shirt" src="' + imageLink + '"/>' +
  '<input type="hidden" name="image" value="'+imageLink+'">' +
  '<input type="hidden" name="shirt_name" value="'+shirtName+'">' +
   '<input type="hidden" name="shirt_ID" value="'+shirtID+'">' +
   '<input type="hidden" name="pagenum" value="'+pageNum+'">' +
   '<div><input type="submit" class="fb_button" id="shirt" label="Send T-Shirt" name="Send" value="Send"></div>' +
   '</form></div>';
   
  */
  
//	dlg.onconfirm = function() { varForm.submit(); } 

	// Show the dialog. sf_dialog_text is already set to 
	// "Loading..." using <fb:js-string> 
	dlg.showMessage(title, sf_dialog_text, button_confirm='Cancel');
  
	// Get the FBML that will replace the div in the dialog box
	ajax.post(get_root_location() + '/selectfriendsAJAX.php', params);    
   
	return;
			
}

// To be executed after the user is done with the publishing part
// of sending t-shirts.  I.e. this is specified as the next_fbjs
// parameter in the JSON that the multiFeedStory handler returns.
// You are probably more confused now that you have read the 
// comments.
function selectFriendsNext(tbucks_earned) {
	
//	dlg.hide();
	
	var title = 'T Bucks!';
	var content = '';

	if (tbucks_earned > 0) {
		content = 'Congratulations, you just earned '+tbucks_earned+' tbucks for sending t-shirts.';	
	}	
	else {
		content = 'Sorry you didn\'t earn any t-bucks this time. Try again later or try sending to different friends.';	
	}
	
	
	dlg.showMessage(title, sf_dialog_text, button_confirm='OK');
	dlg.showMessage(title, content, button_confirm='OK');
	
	
	// Ajax to update the user summary. 
	var ajax = new Ajax(); 
	
	// Build the AJAX object to request the dialog contents. 
	ajax.responseType = Ajax.FBML;
	ajax.requireLogin = true;
	ajax.ondone = function(data) { document.getElementById('user_summary').setInnerFBML(data); }

	// Get the FBML that will replace the div in the dialog box
	ajax.post(get_root_location() + '/tbucksAJAX.php', '');
	
   
	return;
	
}

// To be executed after the user has changed his/her t-shirt
// I.e. this is specified as the next_fbjs
// parameter in the JSON that the Feed Story handler returns.
// You are probably more confused now than before you read the 
// comments.
function wearNext(tbucks_earned, search, sort, category, pageNum, user) {
	

//	console.log('search_parms'); console.dir(search_parms)
	var wDlg = new Dialog(Dialog.DIALOG_POP);
	
	var params={"tsearch":search,
		"tsort":sort,
		"tcategory":category,
		"pagenum":pageNum, 
		"get_shirts":'get_myshirts',
		"render_results":'render_results_page',
		"rewriteid":'results_page',
		"render_user_summary":'render_user_summary',
		"selected_tab":'Mine',
		"search_parms":{"user":user}};
		
	var title = 'T Bucks!';
	var content = '';

	if (tbucks_earned > 0) {
		content = 'Congratulations, you just earned '+tbucks_earned+' tbucks for changing your t-shirt.';	
	}	
	else {
		content = 'Sorry you didn\'t earn any t-bucks this time.';	
	}
	
	
	wDlg.showMessage(title, sf_dialog_text, button_confirm='OK');
	wDlg.showMessage(title, content, button_confirm='OK');
	
	
	// Ajax to update the user summary. 
	var ajax = new Ajax();
	
	// Build the AJAX object to request the dialog contents. 
	ajax.responseType = Ajax.FBML;
	ajax.requireLogin = true;
	ajax.ondone = function (data) { 	 document.getElementById('user_summary').setInnerFBML(data); }

	// Get the FBML that will replace the div in the dialog box
	ajax.post(get_root_location() + '/tbucksAJAX.php', '');

	// Ajax to update the page. 
	var ajax2 = new Ajax();
	
	// Build the AJAX object to request the dialog contents. 
	ajax2.responseType = Ajax.FBML;
	ajax2.requireLogin = true;
	ajax2.ondone = function (data2) { console.log("Got Here"); document.getElementById('results_page').setInnerFBML(data2); }

	// Get the FBML that will replace the div in the dialog box
	ajax2.post(get_root_location() + '/page.php', params);
	  	  
	return;
	
}

function fadeIn(id) {

	var elem = document.getElementById(id);
	
	console.log('About to animate using fadeIn');
	
	Animation(elem).to('opacity', 1).from(0).duration(500).go();

	console.log('Done animating using fadeIn');

	
	return;
}

