<?php
include_once ('../../include/DB.php');

$DB = new DB($env['publicDB'], $env['DBServer'], $env['publicUser'], $env['publicPass']);

$deetsSQL="	";
$details=$DB->SQuery($deetsSQL);
if(!$details){
	echo header("Location: http://theplasterersarms.co.uk");
	die;
}

$title=$details['title']?$details['title']:false;

$css="		
		#time{
					color:#bc0f0f;
					text-align:center;
		}
		body {			
			background-color:#f0f0e7;
			
		}
		h1{
			color:white;
			font-size:0px;
		}
		
		#pagecontent{
			margin-top:20px;
			width:100%;
			text-align:center;
		}
		img{
			width:400px;
		}	
		p{
			color:black;
			padding-left:150px;
			padding-right:150px;
			
		}
		a {
			color:black;
		}
		img{
			width:auto;
			max-height:200px;
			padding:50px;
		}
";
$extraKeywords=['Friday', 'treat'];
$metaDesc="Website for the Plasterers Arms, who issue treats forth on Fridays";
$head=$DB->buildHead($css, false, $title, $extraKeywords, $metaDesc);

$h1=$details['h1'];
$description=$details['description'];

$imagesSQL="SELECT * FROM fridayLinks WHERE fridayID=".$details['fridayID'];
$images=$DB->MQuery($imagesSQL);


/*
| linkID   | int(16)      | NO   | PRI | NULL    |       |
| url      | varchar(128) | YES  |     | NULL    |       |
| imageUrl | varchar(128) | YES  |     | NULL    |       |
| width    | int(8)       | YES  |     | NULL    |       |
| height   | int(8)       | YES  |     | NULL    |       |
| altText  | varchar(256)

| fridayID    | int(4)       | NO   | PRI | NULL    |       |
| title       | varchar(256) | YES  |     | NULL    |       |
| h1          | varchar(256) | YES  |     | NULL    |       |
| description | text         | YES  |     | NULL    |       |
| time        | date         

*/

$body="
		<div id='pagecontent'>
			<h1>".$h1."</h1>
";
	if($images){
	foreach($images as $image){
		$body.="
				<a href='".$image['url']."'>	
					<img src='".$image['imageUrl']."'";
		if ($image['height']) { 
			$body.="height='".$image['height']."px' ";
		}				
		if ($image['width']) { 
			$body.="width='".$image['width']."px' ";
		}
		$body.="
					 /> 
				</a>";
	}
}			
$body.="
			<h2 id='time'></h2>
			
			<p>
".$description."
		</p>
		</div>
";

$js="
	<script>

		var startDate=new Date('".$details['time']."');
 		
		
		setInterval(function(){
			var t = Date.parse(startDate) - Date.parse(new Date());
			var seconds = Math.floor( (t/1000) % 60 );
			var minutes = Math.floor( (t/1000/60) % 60 );
			var hours = Math.floor( (t/(1000*60*60)) % 24 );
			var days = Math.floor( t/(1000*60*60*24) );
			var html=(seconds>=0)? days+' DAYS, '+hours+' HOURS, '+minutes+' MINUTES, '+seconds+' SECONDS ': 'ON THE BAR NOW';
			$('#time').html(html);
			
		},10);
	</script>
";
echo $head;
echo $DB->buildBody($body, true);
echo $DB->buildFoot();
echo $js;