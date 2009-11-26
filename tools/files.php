<?php

function dir_list($path, $callbackFunc, $callbackParms)
{

    // Directories to ignore when listing output. Many hosts
	$ignore = array( '.', '..', '.svn' );
	
	// Open the directory
	$dir_handle = @opendir($path) or die("Unable to open $path");

	// Read each file

	while (false !== ($file = readdir($dir_handle)))
	{
		if( !in_array( $file, $ignore ) ) { 			
			if (is_dir($path.'\\'.$file)) {
				dir_list($path.'\\'.$file, $callbackFunc, $callbackParms);
			}
			else {
				call_user_func($callbackFunc, $path.'\\'.$file, $callbackParms);
			}
		}
	}

	// Close the directory
	closedir($dir_handle);

	
}


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
