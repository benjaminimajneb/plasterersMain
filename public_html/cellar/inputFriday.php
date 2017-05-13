<?php
include_once ('../../include/DB.php');

$verify = new Verify();

$publicDB = new DB($env['publicDB'], $env['DBServer'], $env['cellarUser'], $env['cellarPass']);
$publicDB->buildHead();

$post = $_POST;
$get = $_GET;
$title=$h1=$description=$time=$url=$imageUrl=$fileLocation=$dateTime="";
$numImages = 1;
$imagesToSend = [];
$error = "";
$editing = false;

///////////////////////////////////////////////////////////////
// LOAD PREVIOUSLY SUBMITTED DETAILS IF WE'RE EDITING A PAGE //
///////////////////////////////////////////////////////////////
if(array_key_exists('edit', $get)) {
	$editing = true;
	//get event details:
	$getSQL = "SELECT * from fridayIsTreatDay where fridayID=".$get['edit'];
	$event = $publicDB->SQuery($getSQL);
	$title = $event['title'];
	$h1 = $event['h1'];
	$description = $event['description'];
	$time = $event['time'];
	//remove 18:00:00 from time.
	$time = explode(" ", $time)[0];
	$fridayID = $get['edit'];
	//get saved images:
	$imgSQL = "SELECT * from fridayLinks where fridayID=".$fridayID;
	$imagesToSend = $publicDB->MQuery($imgSQL);
	$numImages = sizeOf($imagesToSend);
	
	//$url = $images[''];
	//$imageUrl = $image[''];
}

////////////////////////////////////////////
// FIRST CHECK ALL FORM ITEMS ARE FILLED: //
////////////////////////////////////////////

if($post){
	//get general details:
	if (!$post['title']) {
		$error.="please input meta-title. <br/>";
	} else {
		$title=$post['title'];
	}
	
	if (!$post['h1']) {
		$error.="please input h1 tag title. <br/>";
	} else {
		$h1=$post['h1'];
	}
	
	if (!$post['description']) {
		$error.="please input description. <br/>";
	} else {
		$description=$post['description'];
	}
	
	if (!$post['time']) {
		$error.="please input release date. <br/>";
	} else {
		$time = $post['time'];
		//format time:
		$dateTime = $time." 18:00:00";
	}
	
	//collect urls for images:
	$numImages=$post['numImages'];
	for($n=0; $n<$numImages; $n++){
		if (!array_key_exists('url'.$n, $post) || $post['url'.$n]==null){
		 $error.="please input a link url for Image ".($n+1).". <br/>";
			$imagesToSend[$n]['url']="";
		} else {
			$imagesToSend[$n]['url']=$post['url'.$n];
		}
		if (!array_key_exists('imageUrl'.$n, $post) || ($post['imageUrl'.$n]==null) && (!array_key_exists($n, $_FILES) || $_FILES[$n]['tmp_name']==null)) {
			$error.="please either upload a file or link to an online image for Image ".($n+1).". <br/>";
			$imagesToSend[$n]['imageUrl']="";
		} else if($_FILES[$n]['tmp_name']==null) {
			$imagesToSend[$n]['imageUrl']=$post['imageUrl'.$n];
		}
	}
}

/////////////////////////////////////
// UPLOAD AN IMAGE IF THERE IS ONE //
/////////////////////////////////////
foreach($_FILES as $fileNo=>$file){
	if ($file['tmp_name']!=null ){
		$target_dir = "../fridayIsTreatDay";
		$target_file = $target_dir . basename($file["name"]);
		$imageSuccess = true;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		if(isset($_POST['submit'])) {
			$check = getimagesize($file["tmp_name"]);
			if($check == false) {
				$error.=$file['name']." (image ".($fileNo+1).") is not an image. </br>";
				$imageSuccess = false;
			}
		}
		// Check if file already exists
		if (file_exists($target_file)) {
				$error.= "An image file with name  ".$file['name']." [image ".($fileNo+1)."] already exists in this location. <br/>";
				$imagesToSend[$fileNo]['imageUrl']=$target_file;
				$imageSuccess = false;
		}
		// Check file size
		if ($file["size"] > 1100000) {
				$error.=$file['name']." (image ".($fileNo+1).") is too large to upload. </br>";
				$imageSuccess = false;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"	&& $imageFileType != "gif" ) {
				$error.= "Invalid filetype for ".$file['name']." (image ".($fileNo+1).") - only JPG, JPEG, PNG & GIF are allowed.";
				$imageSuccess = false;
		}
		// submit if everything is fine.
		if ($imageSuccess == true) {
			if (move_uploaded_file($file["tmp_name"], $target_file)) {
				echo "The file ". basename( $file["name"]). " has been uploaded. </br>";
				$imageUrl=$target_file;
				$imagesToSend[$fileNo]['imageUrl']=$imageUrl;
			} else {
				$error.="There was an unknown error uploading ".$file['name']." (image ".($fileNo+1).").";
				$imagesToSend[$fileNo]['imageUrl']=false;
			}
		}
	}
}


if($error=="" && isset($post['submit'])){
	$fridayID=false;
	//check we don't have anything of the same date:
	$timeTest=$publicDB->SQuery("SELECT time, h1, title, description, fridayID FROM fridayIsTreatDay WHERE time='".$dateTime."'");
	if ($timeTest && !$editing){
		//check if details match
		if ($timeTest['h1']==$h1 && $timeTest['description']==$description && $timeTest['title']==$title){
			//upload pictures to this $fridayID
			$fridayID=$timeTest['fridayID'];
		} else {
			$error="there is already a beer programmed for this date.<br/>";
		}
	} else 	{
		//submit data - this is a first timer.
		$data=[
			'title'=>$title,
			'h1'=>$h1,
			'description'=>$description,
			'time'=>$dateTime
		];
		if ($editing) {
			//update details:
			$fridayID = $publicDB->updateRow("fridayIsTreatDay", $data, ['fridayID', $fridayID]);
		} else {//check new image array against old one - may need to update old images.	
			$fridayID=$publicDB->insertRow('fridayIsTreatDay',$data);
		}
	}
	if(!$fridayID) {
		$error=($error!='')? $error : 'there has been a problem uploading your data. Please check you have entered all values in correct format.<br/>';
	} else {
		//details submitted!
		//test for duplicate images:
		foreach ($imagesToSend as $n=>$image) {
			$alreadyThere=$publicDB->SQuery("SELECT linkID FROM fridayLinks WHERE fridayID=".$fridayID." AND sourceUrl='".$image['imageUrl']."'");
			if ($alreadyThere) {
				$error.= "Image ".$n." already uploaded for this event. <br/>";
			}	else {
				$data=[
					'url'=>$image['url'],
					'imageUrl'=>$image['imageUrl'],
					'fridayID'=>$fridayID
				];
				$successfulImage = $publicDB->insertRow('fridayLinks',$data);
				if (!$successfulImage) $error.="there was a problem saving image ".$n." to this event. <br/>";
			}		
		}
	}	
	if ($error==""){
		$error = "event details successfully saved.";
		$title = $h1=$description=$time=$url=$imageUrl=$fileLocation=$dateTime="";
		$imagesToSend = [];
		$_POST['numImages'] = 1;
		$_POST = [];
		$_GET = [];
	}
}

/*--------------------------------------------------------------------------------------*/

////////////////
//BUILD PAGE: //
////////////////

$form = "
	<div style='padding:100px'>
		<form method='POST' action='inputFriday.php' enctype='multipart/form-data'>
			<table>
				<tr>
					<th colspan='2'>DETAILS</th>
				</tr>
				<tr>
					<td>meta-title : </td>
					<td><input type='text' maxlength='512'  name='title' id='title' value='".$title."'> </td>
				</tr>
				<tr>
					<td>h1 title:  </td>
					<td><input type='text' maxlength='512'  size='150' name='h1' id='h1' value='".$h1."'> </td>
				</tr>
				<tr>
					<td> description : </td>
					<td> <textarea rows='10' cols='150' name='description' id='description'>".$description." </textarea></td>
				</tr>
				<tr>
					<td> date : </td> 
					<td> <input type='date' name='time' id='time' value='".$time."'> </td>
				</tr>
				<tr> <th colspan='2'> IMAGES <th> 
				</tr>
				<tr>
					<td colspan='2'><table id='imagesSubTable' ></table></td> 
				</tr>
	
				<tr>
				<tr><td colspan='2' style='text-align:center'><button class='button' type='button' onclick='addNewImage(); return false;' > ADD ANOTHER IMAGE </button> </td></tr>
			</table>	
	
			<div class='centre'>
				<input class='button' type='submit' value='";
				
$form.= ($editing)? "SAVE CHANGES" : "UPLOAD EVENT";
$form.=
							"' name='submit'>
			</div>
			<input type='hidden' name='numImages' id='numImagesValue' value='".$numImages."'/>
	
		</form>
	</div>

";


/**************************/
/* PREVIOUSLY SAVED DATES */
/**************************/

$fridaySQL = "SELECT time, h1, description, fridayID from fridayIsTreatDay where time > NOW() group BY fridayID";
$imageSQL = "SELECT fridayID, imageUrl from fridayLinks where fridayID in (SELECT fridayID from fridayIsTreatDay where time > NOW()) ORDER by fridayID";
$events = $publicDB->MQuery($fridaySQL);
$events = $events ?: [];
$images = $publicDB->MQuery($imageSQL);
$images = $images ?: [];
$imagesOrdered = [];
foreach($images as $img){
	$ID=$img['fridayID'];
	if (!array_key_exists($ID,$imagesOrdered)) $imagesOrdered[$ID]=[];
	array_push($imagesOrdered[$ID],$img);
}



$savedEvents = "
<div id='savedEvents'>
	<form action='inputFriday.php' method='get'>
		<table>
			<tr>
				<th> Date </th>
				<th> Title </th>
				<th> Images </th>
				<th></th>
			</tr>
";
foreach ($events as $e){
	$savedEvents.="
			<tr id='fridayID".$e['fridayID']."'>
				<td> ".$e['time']." </td>
				<td> ".$e['h1']." </td>
				<td> 
	";
	foreach ($imagesOrdered[$e['fridayID']] as $img) {
		$savedEvents.= "
					<img style='width:80px' src='".$img['imageUrl']."' />";
	}
	$savedEvents.= " 
				</td>
				<td > 				
					<button class='button' type='submit' name='edit' value='".$e['fridayID']."'> EDIT </button>
				</td>
			</tr>";
}
$savedEvents.= "
		</table>
	</form>
</div>
";





$js="<script>
	var images=".json_encode($imagesToSend).";
	console.log(images);
</script>
";
$html = $form.$savedEvents;

$publicDB->buildbody($html, false, $error);
echo $js;
$publicDB->buildFoot();


?>



<script>
function outputImageRows(url, imageUrl, i){
	i=parseInt(i);
	var send="\
		<tr>\
			<th colspan='2' id='image"+i+"'>Image "+(i+1)+"</th>\
		</tr>\
		<tr>\
			<td> Link to beer info (when pic is clicked): </td>\
			<td><input type='text' maxlength='512' name='url"+i+"' id='url"+i+"' value='"+url+"'> </td>\
		</tr>\
		<tr>\
			<td>	Link to use existing online image:</td>\
			<td><input type='text' maxlength='512' name='imageUrl"+i+"' id='imageUrl"+i+"' value='"+imageUrl+"'> </td>\
		</tr>\
		<tr>\
			<td>	Or upload a new file: </td>\
			<td>	<input type='file' name='"+i+"' id='"+i+"'> </td>\
		</tr>";
	return send;
}


var numImages=0;

function addNewImage(){
	$('#imagesSubTable').append(outputImageRows('', '', numImages));
	numImages++;
	$('#numImagesValue').val(numImages);
}

$(document).ready(function(){
	if (images.length>0) {
		for(var i in images){	
			$('#imagesSubTable').append(outputImageRows(images[i].url, images[i].imageUrl, i));
			numImages++;
		}
	} else {
		$('#imagesSubTable').append(outputImageRows('', '',0));
		numImages++;
	}
});

// numImages is carrying over into new page after an event successfully loads.
// so if two images are loaded into an event, the next event looks for two images.

// edit to allow 'edit' variable to be passed from Get to Post so that we can overwrite image details.  Change name of submit button.
// add in delete image button


//add in bit to delete unwanted images from table if updating an event.  We need a delete button for images too then!
</script>
