<?php
/*
+--------+-----------------------------------+------------------+---------------------------------------------------------------------------------------------+----------------+----------+
| pageID | pageName                          | url              | description                                                                                 | userTypeAccess | pageType |
+--------+-----------------------------------+------------------+---------------------------------------------------------------------------------------------+----------------+----------+
|      0 | permissions                       | NULL             | false entry - key to permissions in this table. 0='all', 1='senior', 2='manager', 3='admin' |              3 | NULL     |
|      1 | View Staff Rota                   | viewRota.php     | See finished rota for current and future weeks (and previous weeks for admin)               |              0 | main     |
|      2 | Create, Edit or Update Staff Rota | editRota.php     | manually set, edit and save shifts for forthcoming rotas                                    |              2 | main     |
|      4 | Update Cellar Status of Beers     | beerStatus.php   | change status of beer in the cellar                                                         |              0 | main     |
|      5 | Add or Change on Database         | editBeers.php    | add beer to system or change beer properties                                                |              1 | main     |
|      6 | View Currently Serving Beers      | viewBeers.php    | view current status of beers. To be made publicly accessible.                               |              0 | public   |
|      7 | Change Staff Details              | editStaff.php    | change staff details                                                                        |              3 | main     |
|      8 | Events Calendar                   | viewCalendar.php | Load a calendar of forthcoming events                                                       |              0 | public   |
|      9 | Edit Events                       | editCalendar.php | Edit events on the calendar                                                                 |              0 | main     |
+--------+-----------------------------------+------------------+---------------------------------------------------------------------------------------------+----------------+----------+
*/

// Social Media Guide
// Style Guide (inc colours, logos, fonts)
// customer service guide
// beer guide
// cellar service guide
// Disciplinary procedure
// contract
// fire and H&S docs - risk assessments, action plans, COSHH
// holiday allowances.

include_once('../../include/DB.php');
$verify = new Verify();

//connect to DB
$DB = new DB($env['rotaDB'], $env['DBServer'], $env['cellarUser'], $env['cellarPass']);
$DB->buildHead();
$pagesSQL = ('SELECT pageID, url, pageName, userTypeAccess FROM pages where pageType != "hidden"'); //have currently set lots of page types to hidden!!
$pages = $DB->MQuery($pagesSQL);

$html = "
					<h1>Welcome to The Plasterers Arms e-Cellar</h1>
					<div id='linksPane'>	";
					
foreach ($pages as $page){
	if ($page['userTypeAccess'] <= $_SESSION['userType']){
		$html.="<a href=".$page['url'].">";
		$html.="<div class='linkButton' id='".$page['pageID']."link'>";
		$html.=$page['pageName'];
		$html.="</div></a>";
	}
}

$html.="</div> <!--linksPane-->
				<div id='notificationsPane'></div>";
	
		
$DB->buildBody($html);
$DB->buildFoot();


// build verify into top of each page...
// build header with logout, css and js files, and basic info.
// add in notes on the side (eg up to date mail outs).

?>