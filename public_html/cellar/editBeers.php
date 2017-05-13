<?php
/*
+-----------------+--------------+------+-----+---------+----------------+
| Field           | Type         | Null | Key | Default | Extra          |
+-----------------+--------------+------+-----+---------+----------------+
| beerID          | int(8)       | NO   | PRI | NULL    | auto_increment |
| name            | varchar(64)  | YES  |     | NULL    |                |
| brewery         | varchar(64)  | YES  |     | NULL    |                |
| styles          | varchar(64)  | YES  |     | NULL    |                |
| abv             | decimal(3,1) | YES  |     | NULL    |                |
| unitCostPrice   | decimal(4,2) | YES  |     | NULL    |                |
| salePrice       | decimal(4,2) | YES  |     | NULL    |                |
| active          | int(1)       | YES  |     | NULL    |                |
| format          | varchar(8)   | YES  |     | NULL    |                |
| standardMeasure | varchar(4)   | YES  |     | NULL    |                |
| packVolume      | varchar(16)  | YES  |     | NULL    |                |
| breweryID2      | int(8)       | YES  |     | NULL    |                |
| breweryID3      | int(8)       | YES  |     | NULL    |                |
| breweryID1      | int(8)       | YES  |     | NULL    |                |
+-----------------+--------------+------+-----+---------+----------------+
*/

include_once ('../../include/DB.php');
$verify = new Verify();

$DB = new DB($env['beerDB'], $env['DBServer'], $env['cellarUser'], $env['cellarPass']);

//get list of all beers
$beersRequest = "select * from beerInfo order by active desc, breweryID1, name";
$beers = $DB->MQuery($beersRequest);
$breweriesRequest = 'select * from breweryInfo order by breweryID';
$breweries = $DB->Mquery($breweriesRequest);
$breweries = $DB->reKey($breweries);

$beerList = "";
$updateForm = "";
$newBreweryForm = "";
$html = "";
$html.= " <div class='linkButton' onclick='showPopup(\"newBeerPopup\")'> ADD NEW BEER </div>
					<div class='linkButton' onclick='showPopup(\"newBreweryPopup\")'> ADD NEW BREWERY </div>";

/* PRINT LIST OF EXISTING BEERS */
$keyedBeers = [];
$beerList.= "
	<h2> AVAILABLE BEER LIST </h2>
		<div id='beerList'>
			<table id='editBeerList'>";

foreach ($beers as $beer) {
	$breweryName = isset($breweries[$beer['breweryID1']])? $breweries[$beer['breweryID1']]['name'] : "";
	$beerList.= "
				<tr class='beerRow_".$beer['beerID']."'>
					<td class='beerInfo_breweryName'>".$breweryName."</td>
					<td class='beerInfo_name'>".$beer['name']."</td>
					<td class='beerInfo_salePrice'> £".$beer['salePrice']."</td>
					<td class='beerInfo_abv'>".$beer['abv']."% </td>
					<td> <div class='stateUpdateButton' onclick='activateBeer({$beer['beerID']})'> TURN ".($beer['active']? "OFF" : "ON" )." </div> </td>
					<td> <div id='edit".$beer['beerID']."' class='beerEditButton' onclick='showPopup(\"newBeerPopup\",".$beer['beerID'].")'> Edit Details </div> </td>
				</tr>";	
	$keyedBeers[$beer['beerID']] = $beer;	
}

$beerList.= "
			</table>
		</div>
";


/* ADD NEW BEER */
$keyedBreweries;
$brewerySelect = "
		<select id='breweriesDrop' onchange='getBeers();' name='breweryID1'>
			<option val='' selected disabled hidden>Please Choose Brewery</option> ";
	//build a list of each unique brewery
	foreach ($breweries as $brewery) {
			$breweryName = $brewery['name'];
			$keyedBreweries[$brewery['breweryID']] = $brewery['name'];
			$brewerySelect.= "
			<option value='".$brewery['breweryID']."'>".$breweryName."</option>";
	}
$brewerySelect.= "</select>";
	
$updateForm.= "
	

	<div id='newBeerPopup' class='popup'>
		<h3 id='newBeerTitle' class='addBeer'> ADD NEW BEER TO SYSTEM </h3>
		<h3 id='editBeerTitle' class='editBeer'> EDIT BEER DETAILS </h3>

		<form> 
			<div> ".$brewerySelect." or <div class='linkButton' onclick='showPopup(\"newBreweryPopup\")'>ADD NEW BREWERY</div></div>
			
			<div> <h4> Beer Name </h4> <input name='name'></input></div>
			
			<div> <h4> ABV  </h4> <input name='abv' size='5'></input></div>
			<div id='newBeerStyles'>		
				<h4> Styles </h4>
				<br>
				<div id='stylesOptionsContainerContainer'>
					<div class='styleOptionsContainer'>
						<h5> Pale Styles </h5>
						<input type='checkbox' name='styles' value='IPA'> IPA  <br>
						<input type='checkbox' name='styles' value='DIPA'> Double IPA  <br>
						<input type='checkbox' name='styles' value='SIPA' value=1> Session IPA  <br>
						<input type='checkbox' name='styles' value='NEIPA' value=1> New England IPA  <br>
						<input type='checkbox' name='styles' value='pale' value=1> Pale  <br>
						<input type='checkbox' name='styles' value='lager' value=1> Lager  <br>
					</div>
					<div class='styleOptionsContainer'>
						<h5> Darker Styles </h5>
						<input type='checkbox' name='styles' value='bitter'> Bitter  <br>
						<input type='checkbox' name='styles' value='amber'> Amber  <br>
						<input type='checkbox' name='styles' value='stout'> Stout  <br>
						<input type='checkbox' name='styles' value='porter'> Porter  <br>
						<input type='checkbox' name='styles' value='smoked'> Smoked  <br>
						<input type='checkbox' name='styles' value='coffee'> Coffee  <br>
						<input type='checkbox' name='styles' value='BA'> Barrel Aged  <br>
						<input type='checkbox' name='styles' value='spiced'> Spiced  <br>
					</div>
					<div class='styleOptionsContainer'>
						<h5> Foreigny Styles </h5>
						<input type='checkbox' name='styles' value='fruit' value=1> Fruit <br> 
						<input type='checkbox' name='styles' value='sour' value=1> Sour  <br>
						<input type='checkbox' name='styles' value='belgian' value=1> Belgian <br> 
						<input type='checkbox' name='styles' value='wheat' value=1> Wheat  <br>
						<input type='checkbox' name='styles' value='wild' value=1> Wild  <br>
						<input type='checkbox' name='styles' value='saison' value=1> Saison  <br>
					</div>
					<div class='styleOptionsContainer'>
						<h5> Non-beer Styles </h5>
						<input type='checkbox' name='styles' value='female' value=1> Fem.Ale  <br>
						<input type='checkbox' name='styles' value='cider' value=1> Cider  <br>
						<input type='checkbox' name='styles' value='dry' value=1> Dry (cider) <br>
						<input type='checkbox' name='styles' value='sweet' value=1> Sweet (cider) <br>
					</div>
				</div>
			</div>
			
			<div> <h4> Format </h4> 				
				<select name='format' onchange='filterFormatOptions()'>
					<option value='cask'> cask </option>
					<option value='keg'> keg </option>
					<option value='bottle'> bottle </option>
					<option value='can'> can </option>
				</select>
			</div>
			
			
			<div> 
				<h4> Standard serving measure (glass/bottle size) </h4> 
				<select name='standardMeasure' onchange='filterFormatOptions()'>
					<option value='half' class='draught'> half pint </option>
					<option value='330' class='smallPack'> 330ml </option>
					<option value='335' class='smallPack'> 335ml </option>
					<option value='440' class='smallPack'> 440ml </option>
					<option value='500' class='smallPack'> 500ml </option>
					<option value='660' class='smallPack'> 660ml </option>
					<option value='750' class='smallPack'> 750ml </option>
				</select>
			</div>
			

			<div> <h4> Pack Cost Price (ex VAT) </h4> <input size='5' onchange='calcPrices()'></input></div>
			
			<div> <h4> Pack volume (keg size, number of bottles etc.) </h4> 
				<select name='packVolume' onchange='calcPrices()'>
					<option value='9' class='cask'> 9 gallon cask </option>
					<option value='30' class='keg'> 30L keg </option>
					<option value='24' class='smallPack'> 24 pack </option>
					<option value='12' class='smallPack'> 12 pack </option>
					<option value='18' class='smallPack'> 18 pack </option>
					<option value='20' class='keg'> 20L keg </option>
					<option value='4.5' class='cask'> 4.5 gallon pin </option>
					<option value='50' class='keg'> 50L keg </option>					
				</select>
			</div>

			<div> <h4> Unit Cost Price </h4> <input size='5' name='unitCostPrice' onchange='calcPrices()'></input></div>

			<div> <h4> Unit Sale Price </h4> <input size='5' name='salePrice'></input></div>
			
			<div> <h4> Active </h4> <input type='checkbox' name='active' value=1></div>
			
			<!-- ADD SECOND BREWERY BUTTON -->
			
			<div id='editBeerSubmitButton' class='linkButton editBeer'> SAVE CHANGES </div>
			<div id='newBeerSubmitButton' class='linkButton addBeer' onclick='submitBeerDetails()'> ADD BEER </div>

		</form>
	</div>	
	
";

$updateForm.= "	<div id='popupBlackout' class='popup' onclick='hidePopup();'></div>";


$newBreweryForm = "
	<div id='newBreweryPopup' class='popup'>
		<h3 id='addNewBreweryTitle'> ADD NEW BREWERY TO SYSTEM </h3>
		<form>
			<div> Brewery Name <input name='name'></input></div>
			<div> Country 
				<select name='country'>
					<option> UK </option>
					<option> USA </option>
					<option> Belgium </option>
					<option> Denmark </option>
					<option> Netherlands </option>
					<option> Sweden </option>
					<option> Ireland </option>
					<option> Spain </option>
					<option> France </option>
					<option> Czech Republic </option>
				</select>
			</div>
			<div> Location/Area <input name='area'></input></div>
			<div id='newBrewerySubmitButton' class='linkButton' onclick='submitBreweryDetails()'> SAVE </div>
		</form>
	</div>
";





/* send beer list to page: */

$js="
	var beerList=".json_encode($keyedBeers)."; 
	var breweriesList=".json_encode($keyedBreweries)."; 
	console.log(beerList);
	function openEditBeerForm(num){console.log(num);}
	$(document).ready(function() { \$('.popup').hide(); });
	//
	// ALSO SEND BREWERY SELECT HTML FOR THE 'ADD SECOND BREWERY BUTTON'
	//
";
$head=$DB->buildHead(false, false, $js);
$foot=$DB->buildFoot();
$body=$DB->buildBody([$html, $beerList, $updateForm, $newBreweryForm]);
echo $head;
echo $body;
echo $foot;

?>