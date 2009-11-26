<?php
include('SimpleImage.php');
include('..\lib\constants.php');
    
	$file = '14bec141ffe3.jpg';
	$image = new SimpleImage();
   	$image->load($file);
   
   	$w = $image->getWidth();
	$h = $image->getHeight();
                                                                      
   	echo "W: ".$w; 
	echo " H: ".$h; 

   	$white = imagecolorallocate($image->image, 255, 255, 255);
   	$image->fitInto(IMG_WIDTH, IMG_HEIGHT, $white);
   
	$name = array();
	$name = splitFilename($file);
	$image->save($name[0].'-resize.jpg', IMAGETYPE_JPEG, 100);
   
function splitFilename($filename)
{
    $pos = strrpos($filename, '.');
    if ($pos === false)
    { // dot is not found in the filename
        return array($filename, ''); // no extension
    }
    else
    {
        $basename = substr($filename, 0, $pos);
        $extension = substr($filename, $pos+1);
        return array($basename, $extension);
    }
}    
   
?>
