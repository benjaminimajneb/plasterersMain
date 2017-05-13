<?php

/*
+---------------+--------------+------+-----+---------+----------------+
| Field         | Type         | Null | Key | Default | Extra          |
+---------------+--------------+------+-----+---------+----------------+
| beerID        | int(8)       | NO   | PRI | NULL    | auto_increment |
| name     			| varchar(64)  | YES  |     | NULL    |                |
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
include_once ('../../include/DB.php');
$verify = new Verify();

$DB = new DB($env['beerDB'], $env['DBServer'], $env['publicUser'], $env['publicPass']);

$beersComingRequest = 'select * from beers where spileDate is NOT NULL or tapDate is NOT NULL and onDate is NULL';
$beersRequest = 'select * from beerInfo where active=1 order by beerID desc';
$breweriesRequest = 'select * from breweryInfo order by breweryID';
$breweries = $DB->Mquery($breweriesRequest);
$breweries = $DB->reKey($breweries);
$beers=$DB->Mquery($beersRequest);
$html="
<div>
	<div id='formatChooser'>
		<div id='draughtChooser' class='format formatSelected' onclick='toggleFormat(\"draught\")'> DRAUGHT </div>
		<div id='smallpackChooser' class='format' onclick='toggleFormat(\"smallpack\")'> BOTTLE/CAN </div>
	</div>

	<h1 class='current'>CURRENT BEERS</h1>
</div>
<div id='tableContainer'>
	<table id='beerList'>
		<tr>
			<th class='breweryTd'>Brewery</th>
			<th class='beerTd'>Beer Name</th>
			<th>ABV</th>
			<th>Price</th>
			<th>Style</th>
		</tr>
";
foreach($beers as $beer){
	$styles = $beer['styles'];
	$styles = str_replace(",", " / ", $styles);
	$breweryName = isset($breweries[$beer['breweryID1']])? $breweries[$beer['breweryID1']]['name'] : "";
	$format = (strtolower($beer['format'])=='keg' || strtolower($beer['format'])=='cask')? 'draught' : 'smallpack';
	$html.= "
	<tr data-format='".$format."'>
		<td class='breweryTd'>".$breweryName."</td>
		<td class='beerTd name'>".$beer['name']."</td>
		<td class='abv'>".$beer['abv']."</td>
		<td class='salePrice'>".$beer['salePrice']." (".$beer['standardMeasure'].($beer['standardMeasure']=='half'? ' pint' : 'ml').")</td>
		<td class='style'>".$styles."</td>
		<td class='format'>".$beer['format']."</td>
	</tr>
	";
}
$html.= "
	</table>
</div>
";


$js = "
	$(document).ready(function() { initialCollapse(); });
";

echo $DB->buildHead(true, false, $js);
echo $DB->buildBody($html, true);
echo $DB->buildFoot();

?>

