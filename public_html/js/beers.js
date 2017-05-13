//TO DO:

// form validation
//	- check datatypes
//	- check all entries filled.

// calculating prices

// disable scrolling on hidden elements, and reset scroll after pop up close

//sorting of beer list.



function sendInfoToAjax(params, action, callback) {
console.log(params);
	params['action'] = action;
	$.ajax({
		'url': "beersAjax.php", 
		'data': params, 
		'dataType':'json',
		'method': 'POST', 
		'success': function(dat) {callback(dat);},
		'error': function(dat) {console.log(dat);},
	});
}



/////////////////////////////////////
/*  POPULATING AND HIDING POP-UPS  */
/////////////////////////////////////

function showPopup(id, beerID) {
	$('.popup').hide();
	$('#popupBlackout').show();
	$('#'+id).show();
	if (beerID) { //we're editing a beer's details not adding new.
		// swap title and buttons
		$('.addBeer').hide();
		$('.editBeer').show();
		// change value of hidden input.
		$('#beerID').val(beerID); 
		// change event for save button
		$('#editBeerSubmitButton').off('click');
		$('#editBeerSubmitButton').click(submitBeerDetails.bind(false, beerID));
		// populate form from values.
		$.each(beerList[beerID], function(key, value){
			if (key=='styles') {
				if (value) {
					//deal with de-concatenating string.
					var stylesArray = value.split(',');
					for (var s in stylesArray) {
						$('#newBeerPopup input[value='+stylesArray[s]+']').prop('checked', true);
					}
				}
			} else if ($('#newBeerPopup select[name='+key+']').length>0) {
			console.log(key);
				$('#newBeerPopup select[name='+key+']').val(value);
			} else if (key=='active') {
				console.log(key);
				$('#newBeerPopup input[name="active"]').prop('checked', parseInt(value));
			} else {
						console.log(key);

				$('#newBeerPopup input[name='+key+']').val(value);
			}	
		});
	} else {
		// clear hidden beerID input value.
		$('#beerID').val(''); 
		$('.addBeer').show();
		$('.editBeer').hide();
	}
}

// clear things after AJAX
function hidePopup() {
	$('.popup').hide();
	$('.popup form')[0].reset();
}


function generalPopupCallback(dat) {
	addBeerToList(dat);
	hidePopup();
}


//////////////////////////////////////////
/* FORMAT & REACT FORM FILLING IN POPUP */
//////////////////////////////////////////

function calcPrices() {
	console.log(this);
}

function filterFormatOptions() {
	console.log(this);
}

/////////////////////////////////////
/*  SAVING NEW & EDITED BEER INFO  */
/////////////////////////////////////

//get info from form and send to ajax.
function submitBeerDetails(beerID) {
	var form = $('#newBeerPopup > form').serializeArray();	
	console.log(form);
	params = {};
	if (beerID) {
		// this ID is already on the DB, so let's work out what's different and just submit the change
		var oldDetails = beerList[beerID];
		var changes = {};
		var active = false;

		for (var d in form) {
			if (form[d].name=='active') active = true;
			if (form[d].value == '') form[d].value = null;
			if (form[d].name == 'styles') {
				//we have styles of which there is an array, so add to the end.
				changes[form[d].name] = addValToString(form[d].value, changes[form[d].name]);
			} else if (form[d].value != oldDetails[form[d].name]){		
				changes[form[d].name] = form[d].value;
			}
		}
		if (changes.styles == oldDetails.styles) delete (changes.styles);
		// if we've detected a value for active, and it wasn't active before, 
		if (!active && parseInt(oldDetails['active'])) changes['active'] = 0;
		if (Object.keys(changes).length !== 0) {
			params = {
				'beerID': beerID,
				'changes': changes
			}
			//ajax the changes		
			sendInfoToAjax(params, 'editBeerInfo', generalPopupCallback);		
		}
	} else {
		for (var d in form) {
			if (form[d].value) {			
				if (form[d].name == 'styles') {
					//we have styles of which there is an array, so add to the end.
					params[form[d].name] = addValToString(form[d].value, params[form[d].name]);
				} else {		
					params[form[d].name] = form[d].value;
				}
			}
		}
		sendInfoToAjax({'params':params}, 'addBeer', generalPopupCallback);
	}
	
}

//for updating the returned information on the page.
function addBeerToList(dat) {
	beerID = dat['beerID'];
	if (beerList[beerID]) { //we're updating an existing entry.
		console.log(beerList[beerID]);
		params = dat['changes'];
		for (var d in params) {
			console.log(d);
			// find the beer in the beerList and update our info.
			if (!beerList[beerID][d] || beerList[beerID][d] != params[d]) {
				beerList[beerID][d] = params[d];
			}
			//find it on the page
			val = params[d];
			switch (d) {
				case 'breweryID1':
					$('.beerRow_' + beerID +' .beerInfo_breweryName').html(breweriesList[val]);
					break;
				
				case 'salePrice':
					$('.beerRow_' + beerID +' .beerInfo_salePrice').html("£" + val);
					break;
			
				case 'name':
					$('.beerRow_' + beerID +' .beerInfo_name').html(val);
					break;	
			
				case 'abv':
					$('.beerRow_' + beerID +' .beerInfo_abv').html(val + "%");
					break;
			
				case 'active':
					val = (val==1)? "OFF" : "ON";
					$('.beerRow_' + beerID +' .stateUpdateButton').html("TURN " + val);
					break;
			}
		}	
	} else {
		//okay it's a new entry then.
		//add to beerList
		beerList[dat['beerID']] = dat;
		// add to page.
		var breweryName = breweriesList[dat['breweryID1']];
		beer = dat;
		var row = "\
		<tr> \
			<td class='beerInfo_breweryName'>" + breweryName + "</td> \
			<td class='beerInfo_name'>" + beer['name'] + "</td> \
			<td class='beerInfo_salePrice'> £" + beer['salePrice'] + "</td> \
			<td class='beerInfo_abv'>" + beer['abv'] + "% </td> \
			<td> <div class='stateUpdateButton' onclick='activateBeer(" + beer['beerID'] + ")'> TURN " + ( beer['active']? "OFF" : "ON" ) + " </div> </td> \
			<td> <div id='edit" + beer['beerID'] + "' class='beerEditButton' onclick='showPopup(\"newBeerPopup\"," + beer['beerID'] + ")'> Edit Details </div> </td> \
		</tr> ";	
		$('#editBeerList').append(row);
	}
}



///////////////////////////////
/*  SAVING NEW BREWERY INFO  */
///////////////////////////////

function submitBreweryDetails() {
	var formArray = $('#newBreweryPopup > form').serializeArray();	
	var params = {};
	for (var a in formArray) {
		var name = formArray[a].name;
		var val = formArray[a].value;
		params[name] = val;
	}
	sendInfoToAjax({'params':params}, 'addBrewery', newBreweryCallback);
}

function newBreweryCallback(dat) {
	//add to json list
	breweriesList[dat.breweryID] = dat.name;
	//add brewery and ID to dropper
	var newSelect = "<option value='"+dat.breweryID+"' selected='selected'>"+dat.name+"</option>";
	$('select[name=breweryID1]').append(newSelect);
	//open beer form again.
	$('.popup form')[0].reset();
	showPopup('newBeerPopup');
}






/////////////////////////
/*  BEER STATE CHANGES */
/////////////////////////

function activateBeer(beerID) {
	var currentState = beerList[beerID]['active'];
	var params = {
		'beerID': beerID,
		'changes': {
			'active': (currentState != 1)? 1 : 0
		}
	}
	sendInfoToAjax(params, 'editBeerInfo', addBeerToList)
}







var beerStates={
	0:'delivered',
	1:'racked',
	2:'spiled',
	3:'tapped',
	4:'on',
	5:'off'
}

function getBeers(){
	if (!beerList) return false;
	var brewery=$('#breweriesDrop').val();
	var beerOptions='<option val="" selected disabled hidden>Please Choose Beer</option>';
	for (var beer in beerList){
		if (beerList[beer].brewery==brewery){
			beerOptions+="<option>"+beerList[beer].beerName+"</option>";
		}
	}	
	$('#beerNameDrop').html(beerOptions);
}

function getUpdateOptions(){
	var brewery=$('#breweriesDrop').val();
	var beer=$('#beerNameDrop').val();
	
	var options=$.ajax({
		'url':"beersAjax.php", 
		'data':{
			'action':'getState', 
			'brewery':brewery, 
			'beerName':beer
		}, 
		'method':'POST', 
		'success':function(dat){
			console.log(dat);
			if (dat.state==false) {
				$('#newBeerJobContainer').css({'visibility':'visible'});
			} else {
				var currentState='';
				//make relevant states visible - attach onclicks/ class to each button
			}
		}
	});
	
	console.log(options);
}

function changeState(){
	//send update for relevant button
	
	//change class on page
	
	// allow undo button
	
	//update bottom list

}





////////////////
/*  UTLITIES  */
////////////////

function addValToString(value, string){
console.log(value, string);
	if (string && string!='') {
		string = string+","+value;
	} else {
		string = value;
	}
	return string;
}