<?php
	include_once dirname(__FILE__) . '/Constants.php';
	class DbConnect{
		private $conn;
		private $connAndroid;
		private $server_nm = ServerName;
		private $connection = array("Database"=>SubDB, "UID"=>UserName, "PWD"=>UserPassword);
		private $connectionPS = array("Database"=>psDB, "UID"=>UserName, "PWD"=>UserPassword);
		private $connectionAndroid = array("Database"=>UsersDB, "UID"=>UserName, "PWD"=>UserPassword);
		private $connectionForensic = array("Database"=>ForensicDB, "UID"=>UserName, "PWD"=>UserPassword);
		function __construct(){
			
		}
		
		function connect(){
			
			$this->conn = sqlsrv_connect($this->server_nm, $this->connection);
			if( $this->conn === false ) {
				die( print_r( sqlsrv_errors(), true));
		   }
		   else
			return $this->conn;
		}

		function connectAndroid(){
			$this->connAndroid = sqlsrv_connect($this->server_nm, $this->connectionAndroid);
			if( $this->connAndroid === false ) {
				die( print_r( sqlsrv_errors(), true));
		   }
		   else
			return $this->connAndroid;
		}

		function connectForensic(){
			$this->connForensic = sqlsrv_connect($this->server_nm, $this->connectionForensic);
			if( $this->connForensic === false ) {
				die( print_r( sqlsrv_errors(), true));
		   }
		   else
			return $this->connForensic;
		}

		function connectPS(){
			$this->connPS = sqlsrv_connect($this->server_nm, $this->connectionPS);
			if( $this->connPS === false ) {
				die( print_r( sqlsrv_errors(), true));
		   }
		   else
			return $this->connPS;
		}
	}
	
?>	