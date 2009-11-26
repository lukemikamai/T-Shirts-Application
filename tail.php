<?php


echo error_log;
return;

        //set initial variables
        $file = "/var/log/messegaes";
        $curPosition = 0;
        $curAtime = 0;
        $curSize = 0;

        //set functions
        function closeFile(&$handle)
        {
                fclose($handle);
        }

        function openFile(&$file, &$handle)
        {
                $handle = fopen($file, r);
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
                if(fileatime($file) == $curAtime)
                {
			sleep(1);
                        continue;
                }
                if(filesize($file) < $curSize)
                {
                        resetFile($file, $handle);
                }
                fseek($handle, $curPosition);
                while (feof($handle) != true)
                {
                        print(fgets($handle));
                        $curPosition = ftell($handle);
                }
        }
        closeFile($handle);
?>

