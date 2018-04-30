<?php

class V97DB{
	
	private $connection = null;
	
	public static $responses = [
		"ERR_WRONG_METHOD" => [
			'code' => 405,
			'message' => "Only POST method ({{replaced}})"
		],
		"ERR_EMPTY_INPUT" => [
			'code' => 400,
			'message' => "No query"
		],
		"ERR_NO_DATA" => [
			'code' => 401,
			'message' => "Missing username and/or password for the {{replaced}} server"
		],
		"ERR_NOT_ALLOWED" => [
			'code' => 401,
			'message' => "This IP ({{replaced}}) is not in whitelist configuration"
		],
		"ERR_NO_DB" => [
			'code' => 400,
			'message' => "Missing header \"db_address\""
		],
		"ERR_SSH_FAIL" => [
			'code' => 400,
			'message' => "SSH connection failed. Please check configuration file."
		],
		"ERR_NO_CONN" => [
			'code' => 400,
			'message' => "Connection failed: {{replaced}}"
		],
		"ERR_WRONG_QUERY" => [
			'code' => 400,
			'message' => "{\"result\":\"KO\",\"error\":\"{{replaced}}\"}",
			'header' => 'Content-Type:application/json'
		],
		"ERR_WRONG_MULTIPLE_TYPE" => [
			'code' => 400,
			'message' => "{\"result\":\"KO\",\"error\":\"Multiple query allowed only for UPDATE or DELETE!\"}",
			'header' => 'Content-Type:application/json'
		],
		"INF_EMPTY_RES" => [
			'code' => 204,
			'message' => "[]",
			'header' => 'Content-Type:application/json'
		],
		"OK_SELECT" => [
			'code' => 200,
			'message' => "{{replaced}}",
			'header' => 'Content-Type:application/json'
		],
		"OK_UPDATE" => [
			'code' => 200,
			'message' => "{\"result\":\"OK\"}",
			'header' => 'Content-Type:application/json'
		],
		"OK_INSERT" => [
			'code' => 200,
			'message' => "{\"result\":\"OK\",\"id\":\"{{replaced}}\"}",
			'header' => 'Content-Type:application/json'
		]
	];
	
	public function reply($response, $message = NULL){
		try{
			if($response!=NULL && $response['code']!=NULL && $response['message']!=NULL){
				http_response_code($response['code']);
				if($response['header']){
					header($response['header']);
				}
				$output = str_replace("{{replaced}}", $message, $response['message']);
				$this->disconnect();
				die($output);
			}else{
				http_response_code(500);
				throw new Exception('RESPONSE ERROR');
			}
		}catch(Exception $e) {
			$this->disconnect();
			die($e->getMessage());
		}
	}
	
	public function connect($servername, $username, $password, $dbname = "", $port = 3306){
		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname, $port);
		// Check connection
		if ($conn->connect_error) {
			return $conn->connect_error;
		}else{
			if($dbname!=""){
				$conn->select_db($dbname);
			}
			$this->connection = $conn;
			return true;
		}
	}
	
	public function openSSHTunnel($servername, $user, $password, $remoteIP, $remotePort, $localPort, $timeout = 20, $out = '/temp/ssh_out.log'){
		$ret = false;
		if($servername!=NULL && trim($servername)!="" &&
				$user!=NULL && trim($user)!="" &&
				$password!=NULL && trim($password)!="" &&
				$remoteIP!=NULL && trim($remoteIP)!="" &&
				$remotePort!=NULL && trim($remotePort)!="" &&
				$localPort!=NULL && trim($localPort)!=""){
			shell_exec('sshpass -p "'.$password.'" ssh -f -oStrictHostKeyChecking=no '.$user.'@'.$servername.' -L '.$localPort.':'.$remoteIP.':'.$remotePort.' sleep '.$timeout.' >> '.$out);
			//TODO add check if tunnel is open (netstat -tunap | grep $localPort)
			$ret = true;
		}
		return $ret;
	}
	
	public function executeQuery($sql){
		return $this->connection->query($sql);
	}
	
	public function getConnectionError(){
		return $this->connection->error;
	}
	
	public function getInsertedId(){
		return $this->connection->insert_id;
	}
	
	public function disconnect(){
		try{
			if($this->connection!=NULL){
				$this->connection->close();
				$this->connection = null;
			}
		}catch(Exception $e) {}
	}
	
}