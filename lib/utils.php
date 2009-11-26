<?php
function debug_log($function, $file, $line, $msg, $user='-UNKNOWN-') {	
	if (TESTING_ON) { 
		error_log(' Debug: User, '.$user.', Function, '.$function.', File, '.$file.', Line, '.$line.', Msg, '.$msg.'.', 0);
	}
}

