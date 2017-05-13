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
*/

include_once ('../include/DB.php');
//$verify=verify();
$DB = new DB('beer');
$head=$DB->buildHead();
$beersRequest="select brewery, beerName from beerInfo order by brewery";

$beers=$DB->MQuery($beersRequest);

$updateForm="
	<form onsubmit=''>
		<select id='breweriesDrop' onchange='getBeers();' name='Choose Brewery'>
			<option val='' selected disabled hidden>Please Choose Brewery</option> ";
	$brewery="";
	foreach($beers as $beer) {
		if($beer['brewery']!=$brewery) {
			$brewery=$beer['brewery'];
			$updateForm.="
			<option>".$brewery."</option>";
		}
	}
	$updateForm.="
		</select>";
	// beers drop down to be populated on brewery one being set.
	$updateForm.="	
		<select id='beerNameDrop' onchange='getUpdateOptions();'></select>";
		
	$updateForm.="
		<div id='newBeerJobButton' class='stateUpdateButton'> ADD BEER TO CELLAR </div>

	
		<div class='buttonContainer'>
			<div id='currentState'> </div>
		
			<p> UPDATE BEER </p>
		
			<div id='rack' class='stateUpdateButton' onclick='updateState()'>
				RACK BEER
			</div>
			<div id='spile' class='stateUpdateButton' onclick='updateState()'>
				SPILE BEER
			</div>
			<div id='tap' class='stateUpdateButton' onclick='updateState()'>
				TAP BEER
			</div>
			<div id='on' class='stateUpdateButton' onclick='beerOn()'>
				BEER ON BAR
			</div>
			<div id='off' class='stateUpdateButton' onclick='beerOff()'>
				BEER OFF
			</div>
		</div>
		
	</form>
";

$newForm="
<form action='editBeers.php' ></form>";



$js="<script> var beerList=".json_encode($beers)."</script>";

$foot=$DB->buildFoot();
$body=$DB->buildBody([$updateForm, $newForm, $js]);
echo $head;
echo $body;
echo $foot;

?>