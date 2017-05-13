<?php
/*
+---------------+--------------+------+-----+---------+----------------+
| Field         | Type         | Null | Key | Default | Extra          |
+---------------+--------------+------+-----+---------+----------------+
| beerID        | int(8)       | NO   | PRI | NULL    | auto_increment |
| beerName      | varchar(64)  | YES  |     | NULL    |                |
| brewery       | varchar(64)  | YES  |     | NULL    |                |
| style         | varchar(64)  | YES  |     | NULL    |                |
| abv           | decimal(2,1) | YES  |     | NULL    |                |
| unitCostPrice | decimal(2,2) | YES  |     | NULL    |                |
| salePrice     | decimal(2,2) | YES  |     | NULL    |                |
| active        | varchar(1)   | YES  |     | NULL    |                |
+---------------+--------------+------+-----+---------+----------------+

+-------------+----------+------+-----+---------+----------------+
| Field       | Type     | Null | Key | Default | Extra          |
+-------------+----------+------+-----+---------+----------------+
| beerJobID   | int(7)   | NO   | PRI | NULL    | auto_increment |
| beerID      | int(8)   | YES  |     | NULL    |                |
| spileDate   | datetime | YES  |     | NULL    |                |
| tapDate     | datetime | YES  |     | NULL    |                |
| onDate      | datetime | YES  |     | NULL    |                |
| offDate     | datetime | YES  |     | NULL    |                |
| spileUserID | int(4)   | YES  |     | NULL    |                |
| tapUserID   | int(4)   | YES  |     | NULL    |                |
| onUserID    | int(4)   | YES  |     | NULL    |                |
| offUserID   | int(4)   | YES  |     | NULL    |                |
| tap         | int(2)   | YES  |     | NULL    |                |
+-------------+----------+------+-----+---------+----------------+
*/

// Include current beer count on website.

include_once ('../../include/DB.php');
//$verify=verify();
$DB = new DB($env['beerDB'], $env['DBServer'], $env['cellarUser'], $env['cellarPass']);
$action = $_POST['action'];

//$beerIDRequest="select beerID from beerInfo where brewery='".$brewery."' and beerName='".$beerName."'";
//$beerID=$DB->valQuery($beerIDRequest); //CURRENTLY THIS IS AN ARRAY INSTEAD OF A VALUE!!

switch ($action){
	case 'getState':

		$stateRequest="select * from beerJobs where beerID=".$beerID." and offDate is null";

		$currentState=$DB->MQuery($stateRequest);
		if (!$currentState || $currentState=='no results') {
			$beerInfo=['beerID'=>$beerID, 'state'=>false, 'req'=>$stateRequest];
		} else {
			// here we could have more than one answer - prompt to update off dates etc.
			$beerInfo=$currentState;
			$beerInfo['brewery']=$brewery;
			$beerInfo['beer']=$beerName;
			$beerInfo['state']=true;
		}
		echo json_encode($beerInfo);
		
	case 'setState':
		$stateUpdate='';

	case 'editBeerInfo':
		$changes = $_POST['changes'];
		$beerID = $_POST['beerID'];
		$go = $DB->updateRow("beerInfo", $changes, ['beerID' => $beerID]);
		if ($go) echo json_encode(['beerID' => $beerID, 'changes' => $changes]);
		break;
		
	case 'updateBreweryInfo':
	
	case 'addBeer':
		$params = $_POST['params'];
		$id = $DB->insertRow('beerInfo', $params);
		if ($id) {
			$params['beerID'] = $id;
			echo json_encode($params);
		} 
		break;
		
	case 'addBrewery':
		$params = $_POST['params'];
		$go = $DB->insertRow('breweryInfo', $params);
		if ($go) echo json_encode(['breweryID' => $go, 'name' => $params['name']]);
		break;
		
	default:
		echo "bums";
}

?>