<?php

include('SimpleImage.php');
include('..\lib\constants.php');
include('files.php');



// Recurse the image directory

//define the path as relative
$path = "..\images\shirts";

echo "Directory Listing of $path\n";

dir_list($path, 'imageRename');


function imageRename($file) {
	$file_size = getImageSize ($file);

	$width = $file_size[0];
	$height = $file_size[1];
	
	if (isset($width) && isset($height)) {	
		if ($width != IMG_WIDTH || $height != IMG_HEIGHT) {

			$name = array();
			$name = splitFilename($file);
			rename($file, $name[0].'.orig.'.$name[1]);			
		}
	}	
}
?>
