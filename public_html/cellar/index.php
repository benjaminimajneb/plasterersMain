<?php

include_once ('../../include/DB.php');

$security = new Verify();
$html = "";
//check if already logged on?
if (isset($_SESSION['userID']) && isset($_SESSION['username'])){
	header("Location: home.php");
} else {
	//login options:
	$html.=	"
	<html>
	<head>
	<style>
	.loginName{
		width:200px;
		text-align:right;
		display:inline-block;
	}
	</style>
	</head>
		<body>
			<form action='index.php' method='post'>
				<div class='loginName'>Username or email: </div><input type='text' name='username'>	</input>
				<br/>
				<div class='loginName'>	Password:  </div><input type='password' name='password'>	</input>
				<br/>
				<input type='submit'></input>
			</form>
		<body>
	</html>
	";
}

echo $html;
?>