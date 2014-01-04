<?php

$TITLE = "SoMaze";
$VERSION = ".01";

$CURRENCY = "DOGE";
$CURRENCY_FULL = "Dogecoin";
$CURRENCY_IMG = "<img src='img/dogecoin-d-16.png' class='currency' alt='DOGE'>";

$DB_ROOT = "http://127.0.0.1:5984";

//html snippets
$JS_GAME_SOURCE = '<script src="js/game.js"></script>';

//prep body
$body = file_get_contents('templates/body.inc');
$body = str_replace("###TITLE###", $TITLE, $body);

function getDifficulty($dimArr, $trapArr){
	//give it the traps array and the dimensions array, and it returns the correct HTMl code for that difficulty
	$tiles = intval($dimArr->height) * intval($dimArr->width);
	$traps = 0;
	foreach ($trapArr as $trap){
		$traps += intval($trap);
	}
	$difficulty = ($traps / $tiles)*100;
	if ($difficulty < 20){
		//easy
		$label = "label-success";
		$note = "Easy";
	}else if ($difficulty < 40){
		//medium
		$label = "label-warning";
		$note = "Medium";
	}else{
		//hard!
		$label = "label-danger";
		$note = "Hard";
	}
	return "Difficulty: $difficulty% <span class=\"label $label\">$note</span>";
}

function generateSession(){
	$bytes = openssl_random_pseudo_bytes(8, $strong);
    $hex = bin2hex($bytes);
    return $hex;
}

function handleError($error, $meta=null){
	global $body;
	$return = "<br>Click <a href='index.php'>here</a> to go home";
	switch($error){
		case "noid":
			$error = "No Game ID";
			$content = "<p>You didn't provide a game ID!</p>";
			break;
		case "noid":
			$error = "No API Command";
			$content = "<p>To use the API, you must provide a command!</p>";
			break;
		case "nodoc":
			$error = "Document Not Found";
			$content = "<p>No document found with ID: " . $meta . "</p>";
			break;
		case "noview":
			$error = "View Not Found";
			$content = "<p>The view you requested can not be found</p>";
			break;
		case "notile-move":
			$error = "No Tile Given";
			$content = "<p>The move command requires a tile the player is moving to</p>";
			break;
		case "nosession":
			$error = "No Session ID Given";
			$content = "<p>Session IDs are required for this request</p>";
			break;
		case "badsave":
			$error = "Unable to save document";
			$content = "<p>Save failed with document ID: " . $meta . "</p>";
			break;
		default:
			$content = "No listing for error: " . $error;
			$error = "Generic Error";
			break;
	}
	$body = str_replace("###HEADING###", "Error: " . $error, $body);
	$body = str_replace("###CONTENT###", $content . $return, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
	die();
}

?>