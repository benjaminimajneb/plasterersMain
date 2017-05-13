/*

+-----------+--------------+------+-----+---------+----------------+
| Field     | Type         | Null | Key | Default | Extra          |
+-----------+--------------+------+-----+---------+----------------+
| shiftID   | int(8)       | NO   | PRI | NULL    | auto_increment |
| date      | datetime     | YES  |     | NULL    |                |
| startTime | time         | YES  |     | NULL    |                |
| endTime   | time         | YES  |     | NULL    |                |
| staffID   | int(4)       | YES  |     | NULL    |                |
| notes     | varchar(128) | YES  |     | NULL    |                |
+-----------+--------------+------+-----+---------+----------------+


+----------+-------------+------+-----+---------+----------------+
| Field    | Type        | Null | Key | Default | Extra          |
+----------+-------------+------+-----+---------+----------------+
| staffID  | int(4)      | NO   | PRI | NULL    | auto_increment |
| forename | varchar(64) | YES  |     | NULL    |                |
| surname  | varchar(64) | YES  |     | NULL    |                |
+----------+-------------+------+-----+---------+----------------+

// ADD IN check to see that all AMs and PMs area covered
// on ajax return of a successful save, give success warning

//add in log of changes? check for which shifts moved and log who and when they were changed.

CLASSES:
shiftHole
dayName
amPm

*/
var weekNames={
	0:'Monday',
	1:'Tuesday',
	2:'Wednesday',
	3:'Thursday',
	4:'Friday',
	5:'Saturday',
	6:'Sunday'
}
//fill table with columns
function createCalendar(){
	var day=['am','pm'];
	var week=[0,1,2,3,4,5,6];
	var staff=[0,1,2,3,4,5]// pull this from database in real life
	for (day in week){
		var rowHTML='<td rowspan="2" class="dayName">'+weekNames[day]+'</td>'; // oh shit, actually the second half needs to be the date, right?!
		for (halfday in day) {
			rowHTML=(rowHTML!='<td rowspan="2" class="dayName">'+weekNames[day]+'</td>')? '<td class="amPm">AM</td>':rowHTML+'<td class="amPm">PM</td>'; //every second row (the PMs) we don't need to restate the name of the day
			for (member in staff){
				rowHTML+='<td id="'+week[day]+day[halfday]+staff[member]+'" class="shiftHole"></td>';
			}	
			$('#'+week[day]+day[halfday]).html(rowHTML);
		}
	}
	var topRowHTML='<td class="dayName"></td> <td class="amPm"> </td>';
	for (member in staff){
		topRowHTML+='<td id="staffName'+staff[member]+'"></td>';
	}	
	$('#'+week[day]+day[halfday]).html(rowHTML);
}

//WORK OUT WHERE THE FUCK WE ARE
var today=new Date();
var currentMonth=today.getMonth(); //month of year 0-11
var currentDay=today.getDay(); //day of week 0-6
var startOfWeek=6-currentDay; //better check this.

var currentWC;

function nextWeek(){
	currentWC+=7;
	fillCalendar(today.getFullYear(), currentWC);
}	
function previousWeek(){
	currentWC-=7;
	fillCalendar(today.getFullYear(), currentWC);
}
	
function fillCalendar(year,month){ //month 0-11, 4-digit year eg 2015

	var firstDay=new Date(year,month,1); //first day of month
	var shift=firstDay.getDay(); //value between 0-6
	var monthLength=new Date(year, month + 1, 0).getDate(); //Will this handle leap years?
	var lastMonthLength=new Date(year, month, 0).getDate();
	var monthName=months[new Date(year, month).getMonth()]
	
	//fill title bar
		$('#monthTitle').html(monthName +' '+ year);
	
	//fill days
	for (var i=1;i<shift;i++){
		$('#day'+i).children('.dateNumber').html(lastMonthLength-shift+i+1);
		$('#day'+i).addClass('empty');
	}
	for (var i=0;i<(42-monthLength-shift+1);i++){
		$('#day'+(shift+monthLength+i)).children('.dateNumber').html(i+1);
		$('#day'+(shift+monthLength+i)).addClass('empty');
	}
	
	for (var i=0; i<(monthLength); i++){
		$('#day'+(i+shift)).children('.dateNumber').html(i+1);
	
		//here we chuck in events.
		if (exampleJSON[year][month][i+1]){
			console.log(exampleJSON[year][month][i+1]);	
			for(j in exampleJSON[year][month][i+1]){
				var eventTimeText=(exampleJSON[year][month][i+1][j].hours && + exampleJSON[year][month][i+1][j].hours!=null)?exampleJSON[year][month][i+1][j].hours+':':'';
				eventTimeText+=(exampleJSON[year][month][i+1][j].minutes && exampleJSON[year][month][i+1][j].minutes!=null)?exampleJSON[year][month][i+1][j].minutes:'';
				var eventHtml='<p class="event"><b>'+eventTimeText+'</b> '+exampleJSON[year][month][i+1][j].description+'</p>'; 
				$('#day'+(i+shift)).children('.events').html(eventHtml);
			}
		}
	}
	console.log(today.getMonth());
	console.log(month);
	
	if(today.getMonth()==month && today.getFullYear()==year){
		$('#day'+(today.getDate()+shift-1)).addClass('today');
	}
}


$(document).ready(function(){
	//fillCalendar(today.getFullYear(), today.getMonth());
});