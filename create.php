<?php
//common vars and such
require_once "lib/common.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Chicago');

//set vars / error trap
if (!isset($_SESSION['user'])){
	//they aren't logged in, so they can't play until they do
	handleError("notloggedin");
}

if (isset($_REQUEST["api"])){
	//they are trying to use the API to perform tasks. DON'T serve them a webpage
	if (!isset($_REQUEST["command"])){
		//they didn't include a command with the API
		handleError("nocommand");
	}
	$api = true;
	$command = $_REQUEST["command"];
}else{
	$api = false;
	$command = "none";
}

switch ($command){
		case "getTiles":
			//used to get tiles
			$content = json_encode(getDoc("tiles", "misc"));
			break;
		case "getMap":
			//used to get blank map
			$content = json_encode($_SESSION['puzzle']);
			break;
		case "evalMap":
			$puzzle = json_decode(file_get_contents("php://input"), true);
			$returnObj = new stdClass();
			if (isValid($puzzle) == true){
				$returnObj->valid = true;
				$returnObj->fee = scoreMap($puzzle);
				$_SESSION['puzzle']->map = $puzzle['map'];
				$_SESSION['puzzle']->fees->creation = $returnObj->fee;
			}else{
				$returnObj->valid = false;
			}
			$content = json_encode($returnObj);
			header("Content-type: application/json");
			break;
		case "getFeeForm":
			$content =<<<EOT
	<div class="form-group">
		<table>
		<tr>
		<td><b>Creation Fee (paid by you)</b></td>
		<td>{$_SESSION['puzzle']->fees->creation}</td>
		</tr>
		<tr>
		<td><label for="entry">Entry Fee (paid by the player)</label></td>
		<td><input type="number" id="entry" name="entry" min="{$_SESSION['puzzle']->fees->creation}"></td>
		</tr>
		<tr>
		<td><label for="reward">Reward (paid by you)</label></td>
		<td><input type="number" id="reward" name="reward" min="{$_SESSION['puzzle']->fees->creation}"></td>
		</tr>
		</table><br>
	</div>		
EOT;
			break;
		default:
			if ($_REQUEST['width'] < $MIN_PUZZLE_SIZE || $_REQUEST['width'] > $MAX_PUZZLE_SIZE){
				handleError("badparams");
			}
			if ($_REQUEST['height'] < $MIN_PUZZLE_SIZE || $_REQUEST['height'] > $MAX_PUZZLE_SIZE){
				handleError("badparams");
			}
			$puzzle = createPuzzle($_REQUEST['width'], $_REQUEST['height']);
			$_SESSION['puzzle'] = $puzzle;
			$body = str_replace("###HEADING###", "Puzzle creation", $body);
			$content = "To create a puzzle, click on the tile you want in the library, and after you do, click on all the tiles you want to look like that on your puzzle.  When you are done, click the 'Next Step' to continue";
			$divcontent = <<<EOT
<div id="game">
</div>
<div id="alerts">
</div>
<div id="tiles">
<div id="tileinfo">
Select a tile
</div>
</div>
<br>
<button id="nextstep" class="btn btn-primary btn-lg">Next Step</button><br>
<div id="metaform">
<form role="form" action="create.php" method="post">
	<div class="form-group">
    	<label for="title">Title of the puzzle</label>
		<input type="text" class="form-control" name="title" placeholder="{$_SESSION['nickname']}'s super awesome puzzle">
		<input type="hidden" name="api" value="true">
		<input type="hidden" name="command" value="saveMap">
	</div>
	<div class="form-group">
    	<label for="desc">Description for the puzzle</label>
		<input type="text" class="form-control" name="desc" placeholder="A super awesome puzzle that's made by a super awesome person.">
	</div>
	<div id="feeform">
	</div>
		<button type="submit" class="btn btn-success btn-lg">Finalize</button>
	</form>
</div>
EOT;
			
			
			$body = str_replace("###DIV###", $divcontent, $body);
			$body = str_replace("###JS###", $JS_CREATE_SOURCE, $body);
			$body = str_replace("###SNIPPET###", makeJS(array("WIDTH"=>$_REQUEST['width'], "HEIGHT"=>$_REQUEST['height'])), $body);

			break;
		
}

//pre body and send it out
if ($api == true){
	//if we're using the api, just return what they want
	print $content;
}else{
	//if we're not, make it pretttty
	//adds account specific html to the body
	$body = formatLogin($body);
	//format the rest
	$body = str_replace("###CONTENT###", $content, $body);
	//remove all the remaining tags
	$body = preg_replace("/###.*###/", "", $body);
	print $body;
}
die();

//functions start here!
function createPuzzle($width, $height){
	global $CURRENCY;
	$returnObj = new stdClass();
	$returnObj->active = false;
	$returnObj->created = time();
	$returnObj->nickname = $_SESSION['nickname'];
	$returnObj->dimensions = new stdClass();
	$returnObj->fees = new stdClass();
	$returnObj->traps = new stdClass();
	$returnObj->dimensions->width = intval($width);
	$returnObj->dimensions->height = intval($height);
	$returnObj->map = array_fill(0, (($width * $height)), 0);
	$returnObj->currency = $CURRENCY;
	return $returnObj;
}

function scoreMap($puzzle){
	global $CURRENCY;
	//scores a puzzle
	$tiles = getDoc("tiles", "misc");
	$fee = 0;
	foreach($puzzle['map'] as $tile){
	
		$fee += $tiles->tiles[$tile]->cost->{$CURRENCY};
	}
	return $fee;
}

function isValid($puzzle){
	//checks to make sure the puzzle has exactly 1 entrance and exit
	$values = array_count_values($puzzle['map']);
	if ($values[1] == 1 && $values[2] == 1){
		return true;
	}else{
		return false;
	}
}
?>