<?
require_once "lib/couch.php";
require_once "lib/couchClient.php";
require_once "lib/couchDocument.php";
//common vars and such
require_once "lib/common.php";
date_default_timezone_set('America/Chicago');


if (isset($_REQUEST["type"])){
	$type = $_REQUEST["type"];
}else{
	$type = "none";
}
if (isset($_SERVER['PHP_AUTH_USER'])){
	//someone is logged in, we don't care who

}else{
	$username = "none";
	$access = 0;
}

$TEST_GAME = "c5aa7908b18d8ba39e3c326043000364";

switch($type){
	case "games":
		//shows listing of all games
		$client = new couchClient ($DB_ROOT,"puzzles");
		try{
			$results = $client->getView('listing','allactive');
		}
		catch (Exception $c){
			//map wasn't found
			handleError("nodoc", $_REQUEST["id"]);
		}
		$body = str_replace("###HEADING###", ((count($results) == 1)?"There is currently 1 game to join":"There are currently " . count($results) . " games to join"), $body);
		$content = '<div class="list-group">';
		$i = 0;
		while ($i < count($results->rows)){
			$content .= "<a href='game.php?id=" . $results->rows[$i]->id . "' class='list-group-item'>" . $results->rows[$i]->value[1] . " by " . $results->rows[$i]->value[0] . "<br>Dimensions: " . $results->rows[$i]->value[3]->width . "x" . $results->rows[$i]->value[3]->height . "<br>" . getDifficulty($results->rows[$i]->value[3], $results->rows[$i]->value[4]) . "</a>";
			$i++;
		}
		$content .= "</div>";
		break;
	case "about":
		//about this game and stuff
		$body = str_replace("###HEADING###", "About SoMaze", $body);
		$content = "<p>SoMaze is a game created by <a href='http://evilmousestudios.com'>Evil Mouse Studios</a> for use with various cryptocurrencies.  There will be more information here once it's available.</p>";
		break;
	case "contact":
		//contact me...or don't
		$body = str_replace("###HEADING###", "Contact the creator", $body);
		$content = "<p>The creator can be contacted via this <a href='http://evilmousestudios.com/contactme.html'>form</a></p>";
		break;
	default:
		//default landing page
		$body = str_replace("###HEADING###", "SoMaze - The crypto maze game", $body);
		$content = "<p>Much traps. Many deaths. Such coin. So maze. Wow.</p>";
		break;	
}
$body = str_replace("###CONTENT###", $content, $body);
//remove all the remaining tags
$body = preg_replace("/###.*###/", "", $body);
print $body;

//functions start here!

?>