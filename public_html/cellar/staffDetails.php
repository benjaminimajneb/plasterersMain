<?php
/*
| staffID  | int(4)       | NO   | PRI | NULL    | auto_increment |
| forename | varchar(64)  | YES  |     | NULL    |                |
| surname  | varchar(64)  | YES  |     | NULL    |                |
| userType | int(1)       | YES  |     | NULL    |                |
| email    | varchar(128) | YES  |     | NULL    |                |
| username | varchar(255) | YES  |     | NULL    |                |shitString
| password | varchar(255) |
*/

include_once ('../../include/DB.php');
include_once ('../../include/login.php');

$env['verify'];//=verify();

$DB = new DB($env['rotaDB'], $env['DBServer'], $env['cellarUser'], $env['cellarPass']);
echo $DB->buildHead();

$post=$_POST;

//$DB->getPagePriveleges(); Don't need this now as in verify().

$priv=$_SESSION['userType'];
$userID=array_key_exists('userToEdit', $post) || $_SESSION['userID'];

if ($priv==3){
	//get all users and make drop down.
	$usersSQL="SELECT staffID, username, forename, surname, userType FROM staff ORDER BY surname";
	$users=$DB->MQuery($usersSQL);
	$dropper="
	<form method='POST' action='staffDetails.php'>
		<select name='userToEdit'>";
		foreach($users as $user){
			$dropper.="<option value='".$user['staffID']."'>".$user['forename']." ".$user['surname']."</option>";
		}			
		$dropper.="</select>";
		echo $dropper;
} else {
	//get just my details.
	$deets=$DB->SQuery("select staffID, forename, surname, email, username from staff where userID=".$userID);
	
	$username=$_SESSION['username'];
	$surname="";
	$email="";
}


//PRINT DETAILS:



//CHANGE PASSWORD: 

//if user ID not mine, allow password reset with no old passowrd
$passwordForm="
	<h2> CHANGE USER PASSWORD </h2>

		<table>
			<tr>
				<td> Old password </td><td> <input type='password' name='oldPass'></td>
			</tr>
			<tr>	
				<td> New password </td><td> <input type='password' name='newPass'></td>
			</tr>
		</table>
		<input type='submit' value='SUBMIT MY ASS'> 
	</form>
";

//form submissions:
$reset=false;
if (array_key_exists('oldPass', $post) && array_key_exists('newPass', $post)){
	$oldMatch=checkCredentials($userID,$post['oldPass']);
	if ($oldMatch['reason']=='huzzah!') {
		$reset=setNewPassword($userID, $post['newPass']);
	} else {
		$passwordForm.="<p> ".$oldMatch['reason']." </p>";
	}
	if ($reset) $passwordForm.="<p> Password successfully reset. </p>";
	
}

//else require old password.
	//verify, and if match, run update.

//

$form="
	<div class='errorBox'>	".$priv." </div>
	<div style='padding:100px'>
		<form method='POST' action='staffDetails.php'>
			<table>
				<tr>
					<th colspan='2'>MY DETAILS</th>
				</tr>
				<tr>
					<td>Forename: </td>
					<td><input type='text' maxlength='512'  name='title' id='title' value='".$priv."'> </td>
				</tr>				<tr>
					<td>Surname: </td>
					<td><input type='text' maxlength='512'  name='title' id='title' value='".$priv."'> </td>
				</tr>
				<tr>
					<td>h1 User Name  </td>
					<td><input type='text' maxlength='512'  size='150' name='h1' id='h1' value='".$priv."'> </td>
				</tr>
				<tr>
					<td> Email Address</td>
					<td> <textarea rows='10' cols='150' name='description' id='description'>".$priv." </textarea></td>
				</tr>
				<tr>
					<td> Phone Number</td> 
					<td> <input type='date' name='time' id='time' value='".$priv."'> </td>
				</tr>
				<tr> <th colspan='2'> PASSWORD <th> 
				</tr>
				<tr>
					<td colspan='2'><table id='imagesSubTable' ></table></td> 
				</tr>
	
				<tr>
				<tr><td colspan='2' style='text-align:center><button type='button' onclick='addNewImage(); return false;' > ADD ANOTHER IMAGE </button> </td></tr>
			</table>	
	
			<div class='centre'>
				<input class='button' type='submit' value='Upload Details' name='submit'>
			</div>
			<input type='hidden' name='numImages' id='numImagesValue' value='".$priv."'/>
	
		</form>
	</div>

";
echo $passwordForm;


?>