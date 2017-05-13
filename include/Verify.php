<?php

class Verify {

	private $DB = false;
	private $verified = false;
	private $warning = false;
	private $pageName = "index.php";
	
	function __construct() {
		global $env;

		if (array_key_exists('logout', $_GET)) {
			$this->logout();
		} else {
			//access DB to get page info
			$this->DB = new DB($env['rotaDB'], $env['DBServer'], $env['cellarUser'], $env['cellarPass']);
		
			//check priveleges on page. 
			$this->pageName = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
			$access = $this->DB->SQuery("SELECT userTypeAccess, pageType FROM pages WHERE url LIKE '".$this->pageName."'");
		
			if (isset($access['pageType']) && $access['pageType'] == 'public') {
				//everything's fine let's just get on with this.
				$this->verified = true;
			} else {
				$this->verified = $this->verify($access['userTypeAccess']);
			}	
		}
		if (!$this->verified) die;
	}
	
	function __destruct(){
		print $this->warning;
		if ($this->DB) $this->DB->close();
	}
	
	//FIRST CHECK BEFORE DB CONNECTION ESTABLISHED.
	function verify($userType) {
		if (!isset($_SESSION)) session_start();

		if (!$_SESSION || !$_SESSION['userID'] || !$_SESSION['username'] || !isset($_SESSION['userType'])) {
			//check we're not actually trying to log in?
			if ($this->loginProvided($_POST)) {
				$usr = $_POST['username'];
				$pass = $_POST['password'];
				$verify = $this->checkCredentials($usr, $pass);
				$this->warning = $verify['reason'];
				return $verify['result'];
			} else if ($this->pageName == "index.php") {
				// not logged in, not yet tried.
				$this->warning = "Please enter your username and password";
				return true;
			} else {
				// not logged in, wrong page.
				$this->warning = "SORRY MATE.
				<br />
				<br />
				<a href='index.php'>	Log in. </a>
				";
				return false;
			}
		}
		// okay check we're allowed to actually access this page with our permissions?
		if ($userType > $_SESSION['userType']) {
			$this->warning = "<p>
							Actually, you shouldn't be here.
							<br />
							<a href='home.php'>	GO HOME. </a>
						</p>
			";
			return false;
		}
		
		//everything's fine!
		return true;
	}



	/*******************************/
	/*  FOR USE ON THE LOGIN PAGE  */
	/*******************************/

	function loginProvided($post){
		if (isset($post['username']) && isset($post['password'])) return true; 
		return false;
	}	

	function checkCredentials($usr, $pass){ //returns an array with user details (why?) and/or an explanation
		$return = ['result'=>false, 'reason'=>''];
		$userSQL = "SELECT * FROM staff WHERE userName='".$usr."' OR email='".$usr."' OR staffID='".$usr."'";
		//run sql
		$result = $this->DB->SQuery($userSQL);
		//does user exist?!
		if (!$result) {
			$return['reason'] = 'Username is not recognised please try again';
			$return['result'] = true;
		} else if (password_verify($pass, $result['password'])){
			//success, build login deets into $_SESSION
			$_SESSION['userID'] = $result['staffID'];
			$_SESSION['userType'] = $result['userType'];
			$_SESSION['username'] = $result['username'];
			$return['result'] = $result;
			$return['reason'] = 'huzzah!';
		} else {
			$return['reason'] = 'Username and password do not match, please re-enter';
			$return['result'] = true;
		}
		return $return;
	}

	function setNewPassword($userID, $password){
		$hash = password_hash($password, PASSWORD_BCRYPT);
		$update = $this->DB->updateRow('staff', 'password', $hash, ['staffID'=>$userID]);
		if ($update) return true;
		return false;
	}

	function logout(){
    session_start();
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    session_regenerate_id(true);
		header("Location: index.php");
	}

}

?>