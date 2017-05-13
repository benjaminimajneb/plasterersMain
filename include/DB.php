<?php
date_default_timezone_set("Europe/London");
include_once('../../include/.environment.php');
include_once ('../../include/Verify.php');

class DB {
	
	public $currentDB=false;
	public $badWords=array('DESC', 'DROP', 'DELETE', 'SHOW', '--', 'ALTER', 'UPDATE', '<?', '<script'); 
	public $server='127.0.0.1';
	public $username='root';
	public $password='';
	
	function __construct($DBName=false, $serverName=false, $username=false, $password=false){
		if(!isset($_SESSION)) session_start();
		if ($serverName) $this->server = $serverName;
		if ($username) $this->username = $username;
		if ($password) $this->password = $password;
		session_set_cookie_params(88000);
		if ($DBName) $this->useDatabase($DBName);
	}	
	
	function __destruct(){
	}
	
		//establish mysqli connection
	function useDatabase($DBName) {
		if(!$DBName) return false;
		$this->mysqli=new mysqli($this->server, $this->username, $this->password, $DBName);
		if ($this->mysqli->connect_errno) {
			echo "MySQLi connection bloody well failed (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
			return false;
		}		
		$this->currentDB=$DBName;
	}

	function switchDB($DBName){
		if ($DBName==$this->currentDB) return false;
		if ($this->mysqli) $this->mysqli->close();
		$this->useDatabase($DBName);
	}	
	
	function close() {
		if ($this->mysqli) $this->mysqli->close();
	}
	
///////////////////////////////
	function fetch_mysql_result_object($sql){
		$result=false;
		if (strtoupper(substr($sql,0,6))=="SELECT" || strtoupper(substr(trim($sql),0,6))=="SELECT") {
    	$result=$this->mysqli->query($sql);
   	}
    if (!$result || $result->num_rows===0) return false;
		return $result;
	}

	function MQuery($sql, $numeric=false){ //for multiple row enquiries
		if($this->currentDB==false) return 'Please connect to a database first';
		$result=$this->fetch_mysql_result_object($sql);
    if (!$result) return false;
    if (!$numeric) {
			return $result->fetch_all(MYSQLI_ASSOC);
  	} else {
			return $result->fetch_all(MYSQLI_NUM);
  	}
  }
  
  // key an array by an id in each sub-array:
  function reKey($arr) {
  	//find key to use:
  	$firstKey = false;
  	foreach (array_keys($arr[0]) as $key) {
  		if (!$firstKey) $firstKey = $key;
  		if (strpos($key, 'ID')!=-1 || strpos($key, 'Id')!=-1 || strpos($key, 'id')!=-1) {
  			$firstKey = $key;
  			break;
  		}
  	}
  	//construct array:
  	$newArr = [];
  	foreach ($arr as $a) {
  		$newArr[$a[$firstKey]] = $a;
  	}
  	return $newArr;
  }
  
  function SQuery($sql, $numeric=false){ //for single row enquiries
 		if($this->currentDB==false) return 'Please connect to a database first';
		$result=$this->fetch_mysql_result_object($sql);
	  if (!$result) return false;
		return $result->fetch_array(MYSQLI_ASSOC); 
  }
  
  function valQuery($sql){
 		if($this->currentDB==false) return 'Please connect to a database first';
		if (stripos($sql, ',')) return "only one question please";
		$result=$this->fetch_mysql_result_object($sql);
		if (!$result) return false;
		$row=$result->fetch_row();
		return $row[0];
  }
		
	function insertRow($tableName, $dataArray){
 		if($this->currentDB==false) return 'Please connect to a database first';
 		$vals="'";
 		foreach($dataArray as $val){
 			$vals.=$this->cleanMe($val);
 			$vals.="', '";
 		}
 		$vals=substr($vals, 0, -3);
 		$keys=implode(', ', array_keys($dataArray));
 		$keys=$this->cleanMe($keys);
		$SQL="INSERT INTO ";
		$SQL.=$tableName." (";
		$SQL.=$keys;
		$SQL.=") VALUES (";
		$SQL.=$vals;
		$SQL.=")";
		if(!$this->mysqli->query($SQL)){
			return "oh no!";
		}
		return $this->mysqli->insert_id;
	}
	
	function updateRow($table, $newDataArray, $conditionArray){
	 	if($this->currentDB==false) return 'Please connect to a database first';
		$SQL= "UPDATE ".$table." SET ";
		foreach ($newDataArray as $key=>$val) {
			if (gettype($val)=='string') $val = "'".$val."'";
			$SQL.= $key."=".$val.", ";
		}
		$SQL=substr($SQL, 0,-2); //get rid of last comma.
		$SQL.=" WHERE ";
		foreach ($conditionArray as $k=>$v){ 
			$k=$this->cleanMe($k);
			$v=$this->cleanMe($v);
			$SQL.=$k."=".$v;
			$SQL.=" AND ";
		}
		$SQL=substr($SQL, 0,-4); //get rid of last and.
		$run = $this->mysqli->query($SQL);
		if (!$run) {
			return false;
		}
		return $run;
	}
	
/*--------------------------------------------------------------------------------------*/
	 
  //screen inputs
	function cleanMe($input){
		$string = (is_array($input)) ? implode(" ", $input) : $input; 
		foreach($this->badWords as $word) {
			if (stripos($word, $string)) return false;
		}
		if($this->mysqli) return $this->mysqli->real_escape_string($input);
		return $input;
	}
	
	//hunt for values in http request methods:
	function verbNum($field) {
		$p = $_POST;
		$g = $_GET;
		$val = false;
		if (array_key_exists($field, $g)) $val = $g[$field];
		//prioritise post over get.
		if (array_key_exists($field, $p)) $val = $p[$field];
		
		$type = gettype($val);
		if ($type != "NULL" || $type != "boolean" || $type != "integer" || $type != "double") $val = false;
		return $val;
	}
	
	function verbString($field) {
		$p = $_POST;
		$g = $_GET;
		$val = false;
		if (array_key_exists($field, $g)) $val = $g[$field];
		//prioritise post over get.
		if (array_key_exists($field, $p)) $val = $p[$field];
		
		$type = gettype($val);
//		if ($type=='integer' || $type=='double') $val = string()$val;
		if ($type != "NULL" || $type != "boolean" || $type != "string") $val = false;
		return $val;		
	}

	function findVerb($key, $numOrString, $safety=0){
		//see if it's there immediately
		if ($numOrString=='num') $this->verbNum($key);
		
		//if not, loop through keys and check sub-arrays.
		
		
	}

//////////////////////////////////////////////////////////////////////////////////////////
/*--------------------------------------------------------------------------------------*/
// PAGE THINGS:

  function buildBody($html, $frontEnd=false, $error=false){
  	//screen for body tags
  	//if not add body tags
  	$return="";
  	
		if ($error){
			$return.="<div class='errorBox'>	".$error." </div>";
		}
		$return.="
			<div id='website'>
		";
  	if (is_array($html)){
  		foreach ($html as $section){
  			$return.= $section;
  		}
  	} else {
  		 $return.= $html;
  	}
  	$return.= "</div></body>";
  	echo $return;
  }
  
	function buildHead($frontEnd=false, $css=false, $js=false, $title="MOTHERFUCKING BEER WEBSITE ISN'T IT", $keyWordArray=false, $description="A website for The Plasterers Arms which is a nice pub in Norwich"){
		$style = "
				<link rel='stylesheet' type='text/css' href='".(($frontEnd)? "../css/min.css" : "../css/backend.css")."'>";
		$style.= ($css)? "<style>".$css."</style>" : "";
		$script = "					
				<script src='../js/jquery-1.10.2.min.js'></script>
				<script src='../js/raphael.js'></script>
				<script src='../js/beers.js'></script>
				<script src='../js/beerView.js'></script>";
		//check whether $js/$css have <script>/<style> tags in
		$script.= $js? "<script>".$js."</script>" : "";
		$keywords = "";
		if ($keyWordArray){
			foreach($keyWordArray as $word){
				$keywords.= ", ".$word;
			}
		}
		$keywords = substr($keywords, 0, -1);
		$html = "
		<!DOCTYPE html>
			<html lang='en'>
				<head>
					<title> ".$title."</title>
					<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
					<meta name='author' content='Benjamin Andrew Hugo Thompson'>
					<meta name='description' content='".$description."'>
					<meta name='keywords' content='Plasterers, Arms, Beer, Norwich, Norfolk, Pub, craft beer, real ale, festival, artisan, gin, wine, sky sports, bt sports, pizza, music, vibes".$keywords."'>
					<meta charset='UTF-8'>
					".$style."
					".$script."
				</head>	
				<body>	
		";
		if ($frontEnd) {
			$html.= "		
					<div id='topBar'>
						<a href='index.html'> <img src='img/pint.gif' /></a>
						<a href='index.html'> THE PLASTERERS ARMS </a>
					</div>
					<div id='website'>
			";
		} else { 
			$html.= "
					<div id='headerBar'>
						<div id='headerHome'><a href='home.php'> HOME </a> </div> 
							<div id='headerLogOut'><a href='index.php?logout=true'> Log out. </a> </div>
					</div>
			";
		}
		echo $html;
	}
	
	
	function buildFoot($frontEnd=false){
		$html = "";
		if ($frontEnd) "</div> <!-- #website -->";
		$html="
				</body>
			</html>
		";
		echo $html;
	}

}


// copied!
function test($obj,$txt=false) {
		echo "<div style='text-align:left; font-family:Courier;'>";
		if ($obj===false) echo "<i>false</i>";
		if (is_array($obj)) echo testArray($obj,false,($txt==2)); else if (is_string($obj)) echo $obj; else var_export($obj,true);
		echo "</div>";	
}
function testArr($a,$cram=false) { echo testArray($a,$cram); }
function testArray($a,$cram=false,$alwaysShow=false) {
	if (!is_array($a)) return "<i>Not an array! Is [".sTest($a)."]</i>";
	if (sizeOf($a)==0) return "<i>Array is empty</i>";
	if (isset($_SERVER['REMOTE_ADDR'])) {
		$c="<table style='border: 1px solid red;".(($cram)?"font-size:0.8em;":"")."'>".(($cram)?"<tr>":"");
		foreach ($a as $key=>$d) { $c.=(($cram)?"":"<tr>")."<td valign='top'><b>".$key."</b>:</td><td valign='top'>".((is_array($d))?testArray($d,$cram,$alwaysShow):((is_object($d))?((is_callable(array($d,"toString")))?$d->toString():"[object]"):($d)))."</td>".(($cram)?"":"</tr>")."\n"; }
		$c.="</table>";
	} else {
		// CLI version
		$c="";
		foreach ($a as $key=>$d) { $c.="".$key.": ".((is_array($d))?testArray($d,$cram,$alwaysShow):((is_object($d))?((is_callable(array($d,"toString")))?$d->toString():"[object]"):$d))."\n"; }
	}
	return $c;
	
}

?>