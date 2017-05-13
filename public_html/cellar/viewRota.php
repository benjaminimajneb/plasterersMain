<?php

include_once('../include/DB.php');
//set up
$DB=new DB('rota');
$header=$body='';
addJS('rota.js');

//finding the correct date period and fishing for relevant shifts in this period
$now=date_create();
$nowInfo=getDate();
$dayOffset=($nowInfo['wday']==0)? 7 : ($nowInfo['wday']); 
$start=date_sub($now, date_interval_create_from_date_string($dayOffset.' days'));;
$end=date_sub($now, date_interval_create_from_date_string('14 days'));
$start=date_format($start, 'Y-m-d');
$end=date_format($end, 'Y-m-d');

$sql="SELECT * FROM shifts WHERE date < ".$start;
$shifts=$DB->DBQuery($sql);
//test($shifts);

$body="
	<table id='rotaContainer'>
		
		<tr id='staffNames'>
			
		</tr>		
		
		<tr id='0am'>
		
		</tr>
		<tr id='0pm'>
		
		</tr>
		<tr id='1am'>
		
		</tr>
		<tr id='1pm'>
		
		</tr>
		<tr id='2am'>
		
		</tr>
		<tr id='2pm'>
		
		</tr>
		<tr id='3am'>
		
		</tr>
		<tr id='3pm'>
		
		</tr>
		<tr id='4am'>
		
		</tr>
		<tr id='4pm'>
		
		</tr>
		<tr id='5am'>
		
		</tr>
		<tr id='5pm'>
		
		</tr>
		<tr id='6am'>
		
		</tr>
		<tr id='6pm'>
		
		</tr>
		
	</table><!--calendarContainer-->

	<div class='arrow' id='rightArrow' onclick='nextWeek()'>&gt Next Week</div>
	<div class='arrow' id='leftArrow' onclick='previousweek()'>&lt Previous Week</div>
";
sendJSON($shifts,'shifts');
runJSFunction('fillCalendar',['shifts']);
outputPage();

function outputPage(){
	global $header,$body;
	$output="<html> <head>	";	
	$output.="<script src='jquery-1.11.2.min.js'></script>";
	$output.="<link rel='stylesheet' type='text/css' href='../../css/plaster1.css'>";
	$output.=$header."</head>";
	$output.="<body>".$body."</body> </html>";
	echo $output;
}

function addJS($filename){
	global $header;
	$header.="<script src='".$filename."'></script>";
}
function sendJSON($array, $arrayname){
	global $body;
	$body.="<script> var ".$arrayname."=".json_encode($array)."; </script>";
}
function runJSFunction($functionName, $paramsArray=false){
	global $body;
	//if ( typeOf($paramsArray)=='string' ) $paramsArray=[$paramsArray];
	$body.="<script> ".$functionName."(";
	if($paramsArray){
	$count=1;
		foreach($paramsArray as $param){
			$body.=$param;
			$body.= ($count<count($paramsArray))? "," : "";
			$count++;
		}			
	}
	$body.="); </script>";
}
?>