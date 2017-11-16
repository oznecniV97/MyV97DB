<?php
try{

include "class/V97DB.class.php";
$DBManager = new V97DB();

//check if is a post request
if(strtoupper($_SERVER['REQUEST_METHOD'])!="POST"){
	$DBManager->reply(V97DB::$responses["ERR_WRONG_METHOD"], $_SERVER["REQUEST_METHOD"]);
}

//check if have a body filled
$sql = file_get_contents('php://input');
if($sql==NULL || trim($sql)==""){
	$DBManager->reply(V97DB::$responses["ERR_EMPTY_INPUT"]);
}else if(strpos($sql, ';') !== false){
	$arrSQL = explode(';',$sql);
	for($i=0;$i<count($arrSQL);$i++){
		$t = $arrSQL[$i];
		$t = str_replace(PHP_EOL, '', $t);
		$t = str_replace('\r', '', $t);
		$t = str_replace('\n', '', $t);
		$t = str_replace('\t', '', $t);
		$arrSQL[$i] = $t;
	}
	//check if there's only one query
	foreach($arrSQL as $key => $sql){
		if($sql==NULL || trim($sql)==""){
			header('chiave_cancellata:'.$key);
			unset($arrSQL[$key]);
		}
	}
	if(count($arrSQL)<=1){
		header('cancellazione_array:true');
		unset($arrSQL);
	}else{
		//check if all the query are DELETE/UPDATE
		foreach($arrSQL as $key => $sql){
			$operation = strtoupper(strstr(trim($sql), ' ', true));
			if($operation != 'UPDATE' && $operation != 'DELETE'){
				$DBManager->reply(V97DB::$responses["ERR_WRONG_MULTIPLE_TYPE"]);
			}
		}
	}
}

//check if there's the header 'db_address'
$servername = $_SERVER["HTTP_DB_ADDRESS"];
$username = NULL;
$password = NULL;
if($servername!=NULL && trim($servername)!=""){
	//check headers 'db_username' and 'db_password'
	$username = $_SERVER["HTTP_DB_USERNAME"];
	$password = $_SERVER["HTTP_DB_PASSWORD"];
	if($username==NULL || trim($username)==""
	|| $password==NULL || trim($password)==""){
		//check the presence of a config file related to this db_address
		include "configs/".$servername.".php";
		if($username==NULL || trim($username)==""
		|| $password==NULL || trim($password)==""){
			$DBManager->reply(V97DB::$responses["ERR_NO_DATA"], $servername);
		}
	}
}else{
	$DBManager->reply(V97DB::$responses["ERR_NO_DB"]);
}

//check if there's a restricted whitelist for this configuration and if this client is in it
if (!isset($clients))
	$clients = [];
if ((!empty($clients))){
	$present = false;
	foreach($clients as $client){
		if(preg_match("/".$client."/", $_SERVER['REMOTE_ADDR'])){
			$present = true;
			break;
		}
	}
	if(!$present){
		$DBManager->reply(V97DB::$responses["ERR_NOT_ALLOWED"], $_SERVER['REMOTE_ADDR']);
	}
}

$status = NULL;
// Open connection
$schema = $_SERVER["HTTP_DB_SCHEMA"];
if($schema==NULL || trim($schema)==""){
	$status = $DBManager->connect($servername, $username, $password);
}else{
	$status = $DBManager->connect($servername, $username, $password, $schema);
}
if($status!==true)
	$DBManager->reply(V97DB::$responses["ERR_NO_CONN"], $status);

//multiple executions
if (isset($arrSQL)){
	foreach($arrSQL as $key => $sql){
		//execute query and check result
		$result[$key] = $DBManager->executeQuery($sql);
		if(!$result[$key]){
			$DBManager->reply(V97DB::$responses["ERR_WRONG_QUERY"], $DBManager->getConnectionError());
		}
	}
}else{
	//execute query and check result
	$result = $DBManager->executeQuery($sql);
	if(!$result){
		$DBManager->reply(V97DB::$responses["ERR_WRONG_QUERY"], $DBManager->getConnectionError());
	}
}

//response based on the query type
if (!isset($arrSQL)){
	switch(strtoupper(strstr(trim($sql), ' ', true))){
		case 'INSERT':
			$DBManager->reply(V97DB::$responses["OK_INSERT"], $DBManager->getInsertedId());
		case 'UPDATE':
		case 'DELETE':
			$DBManager->reply(V97DB::$responses["OK_UPDATE"]);
		case 'SELECT':
			if ($result->num_rows > 0) {
				// output data of each row
				$listRes = [];
				while($row = $result->fetch_assoc()) {
					array_push($listRes, $row);
				}
				$DBManager->reply(V97DB::$responses["OK_SELECT"], json_encode($listRes));
			} else {
				$DBManager->reply(V97DB::$responses["INF_EMPTY_RES"]);
			}
	}
}else{
	$DBManager->reply(V97DB::$responses["OK_UPDATE"]);
}
}catch(Exception $e) {
	var_dump($e);
	die($e->getMessage());
}

?>