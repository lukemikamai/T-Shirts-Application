<?php

include('SimpleImage.php');
include('..\lib\constants.php');
include('files.php');

$tmp_image = imagecreatetruecolor(1, 1);
$white = imagecolorallocate($tmp_image, 255, 255, 255);

//define the path as relative
$path = '..\images\shirts\teemarto';

echo "Resizing images in page $path\n";

// Recurse the path resizing all images found
dir_list($path, 'imageResize', $white);

// Files should be named something like image.orig.jpg unless
// you want them overwritten
function imageResize($file, $background) {
	$file_size = getImageSize ($file);
	$width = $file_size[0];
	$height = $file_size[1];
	
	if (isset($width) && isset($height)) {	
		if ($width != IMG_WIDTH || $height != IMG_HEIGHT) {
			$image = new SimpleImage();
			$image->load($file);
			$image->fitInto(IMG_WIDTH, IMG_HEIGHT, $background);
			$name = array();
			$name = splitFilename($file);
			$name = splitFilename($name[0]);
			$new_name = $name[0].'.jpg';
			echo "Saving $new_name\n";
			$image->save($new_name, IMAGETYPE_JPEG, 75);
		}
	}	
}


?>
