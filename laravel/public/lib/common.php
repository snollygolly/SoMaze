<?php
require_once "lib/couch.php";
require_once "lib/couchClient.php";
require_once "lib/couchDocument.php";

$VERBOSE = true;

$TITLE = "SoMaze";
$VERSION = ".01";

$CURRENCY = "DOGE";
$CURRENCY_FULL = "Dogecoin";
$CURRENCY_IMG = "<img src='img/dogecoin-d-16.png' class='currency' alt='DOGE'>";

$MIN_PUZZLE_SIZE = 3;
$MAX_PUZZLE_SIZE = 25;

$DB_ROOT = "http://127.0.0.1:5984";

//switch these for openID deployment
//$DOMAIN = "somaze.evilmousestudios.com";
$DOMAIN = "127.0.0.1";
//$DOMAIN = "192.168.1.101";
//html snippets
$JS_GAME_SOURCE = '<script src="js/game.js"></script>';
$JS_CREATE_SOURCE = '<script src="js/create.js"></script>';

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

function makeJS($vars){
	//makes a JS snippet based on the args array you give it
	$snippet = "<script>\n\t";
	foreach ($vars as $k => $var){
		$snippet .= "var " . $k . " ='" . $var . "';\n\t";
	}
	$snippet .= "</script>";
	return $snippet;
}

function handleError($error, $meta=null){
	global $body;
	$return = "<br>Click <a href='index.php'>here</a> to go home";
	switch($error){
		case "noid":
			$error = "No Game ID";
			$content = "<p>You didn't provide a game ID!</p>";
			break;
		case "nocommand":
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
		case "badrm":
			$error = "Unable to delete document";
			$content = "<p>Delete failed with document ID: " . $meta . "</p>";
			break;
		case "badlogin":
			$error = "Unable to log you in";
			$content = "<p>Something went wrong during the OpenID login: (" .  $meta . ")</p>";
			break;
		case "nonick":
			$error = "No nickname provided";
			$content = "<p>To change your nickname, you must provide a nickname!</p>";
			break;
		case "notingame":
			$error = "You are not in this game";
			$content = "<p>You can't submit commands to a game that you haven't joined.</p>";
			break;
		case "notloggedin":
			$error = "You are not logged in";
			$content = "<p>You must be logged in to do that.</p>";
			break;
		case "nofunds":
			$error = "Not enough money";
			$content = "<p>You have insufficient funds to do this.</p>";
			break;
		case "badparams":
			$error = "Bad parameters";
			$content = "<p>The parameters you supplied are invalid.</p>";
			break;
		case "youredead":
			$error = "You're dead, you can't do things";
			$content = "<p>You're a ghost, you can't be going around and doing things.  Stick to being spooky.  Much scared.</p>";
			break;
		default:
			$content = "No listing for error: " . $error;
			$error = "Generic Error";
			break;
	}
	error_log("handleError: " . $error . " - " . $content);
	//adds account specific html to the body
	$body = formatLogin($body);
	$body = str_replace("###HEADING###", "Error: " . $error, $body);
	$body = str_replace("###CONTENT###", $content . $return, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
	die();
}


function formatLogin($body){
	//calls to generate proper html for the log in button (or username if logged in
	if (session_status() == PHP_SESSION_NONE) {
    	session_start();
	}
	if (isset($_SESSION['user'])){
		//this user was already logged in
		$signin = "<p class='navbar-text navbar-right'>Signed in as <a href='index.php?type=account' class='navbar-link'>" . $_SESSION['nickname'] . "</a> - <a href='login.php?logout=true' class='navbar-link'>Logout</a></p>";
		$body = str_replace("###NAVBAR###", "<li><a href='index.php?type=create'>Create</a></li><li><a href='index.php?type=account'>Account</a></li>", $body);
	}else{
		//this user hasn't yet logged in
		$signin = <<<'EOT'
		<form class="navbar-form navbar-right" role="form" action="login.php" method="get">
		<input type="hidden" name="login" value="true">
            <button type="submit" class="btn btn-success">Sign in with Google OpenID</button>
		</form>
EOT;
	}
	$body = str_replace("###LOGIN###", $signin, $body);
	return $body;
}

function getDoc($id, $db){
	global $DB_ROOT, $VERBOSE;
	//gets a document from the database
	$client = new couchClient ($DB_ROOT,$db);
	try{
		return $client->getDoc($id);
	}
	catch (Exception $e){
		//doc
		handleError("nodoc", $id . " db: " . $db . (($VERBOSE == true)?" - " . json_encode($e):""));
	}
}

function setDoc($id, $db){
	global $DB_ROOT, $VERBOSE;
	//stores a document in the database
	$client = new couchClient ($DB_ROOT,$db);
	try {
		return $client->storeDoc($id);
	} catch (Exception $e) {
		handleError("badsave", $id->_id . " db: " . $db . (($VERBOSE == true)?" - " . $e:""));
	}	
}

function deleteDoc($id, $db){
	global $DB_ROOT, $VERBOSE;
	//stores a document in the database
	$client = new couchClient ($DB_ROOT,$db);
	try {
		return $client->deleteDoc($id);
	} catch (Exception $e) {
		handleError("badrm", $id->_id . " db: " . $db . (($VERBOSE == true)?" - " . json_encode($e):""));
	}	
}






?>