<?php

		// Prevent timeout.
		ini_set('max_execution_time', 0);
		
		//set initial variables
        $file = ini_get('error_log');
        $curPosition = 0;
        $curAtime = 0;
        $curSize = filesize($file);

        //set functions
        function closeFile(&$handle)
        {
                fclose($handle);
        }

        function openFile(&$file, &$handle)
        {
                $handle = fopen($file, 'r');
        }

        function resetFile(&$file, &$handle)
        {
                closeFile($handle);
                openFile($file, $handle);
        }

        //main
        openFile($file, $handle);
        while (file_exists($file))
        {
				clearstatcache();
                if(filemtime($file) == $curAtime)
                {
						sleep(1);
                        continue;
                }
				else {
					echo " - - - - - - - - - - - - - <br/>";					
					$curAtime = filemtime($file);
				}
				
                if(filesize($file) < $curSize)
                {
						echo "Reseting";
                        resetFile($file, $handle);
						$curSize = filesize($file);
                }
				
                fseek($handle, $curPosition);
                while (feof($handle) != true)
                {
						$line=fgets($handle);
						if (substr($line, 0, 1) == '[') {
							$date=explode(']', $line, 2);
							echo '<div style="font-weight: bold; display:inline">';
							echo $date[0].']';
							echo '</div>';
							$type=explode(':', $date[1], 2);
							echo '<div style="font-weight: bold; color:DarkRed; display:inline;">';
							echo $type[0].':';
							echo '</div>';
							echo '<div style="display:inline;">';							
							echo $type[1];
							echo '</div>';
						}
						else {
							echo $line;
						}
						echo "<br/>";
                        $curPosition = ftell($handle);
                }
				
				
        }
        closeFile($handle);
?>

