<?php

	if(empty($message)) {
		$message =  '<div id="success">The file '. basename( $_FILES['uploaded']['name']). ' has been uploaded successfully.</div>';
		$message .= '<br/>';
		$message .= '<div id="proceed"><a href="deck/'. $deck_num .'">Click link to proceed to the presentation</a></div>';
	}

	echo $message;

?>