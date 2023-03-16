<?php

    class DbOperations{
        //private $con;
		
		private $conAndroid;
        private $conSubDB;
        private $conForensic;
        private $conPS;
        function __construct(){
			
			require_once dirname(__File__).'/DbConnect.php';
			
			$db = new DbConnect();
			
			$this->conSubDB = $db->connect();
			$this->conAndroid = $db->connectAndroid();
            $this->conForensic = $db->connectForensic();
            $this->conPS = $db->connectPS();
        }


        public function getPoliceStations(){
            $result = array();
            $sql = "EXEC [sp_searchOps_policestations_getps]";
	        $getResults= sqlsrv_query($this->conPS, $sql);
			if (sqlsrv_has_rows( $getResults ) === false){
				echo "Error in retrieveing row count.";
			}
			else{
				
			    while( $row = sqlsrv_fetch_array($getResults) ) {
			     array_push($result,
			    array('psID'=>$row[0],'psName'=>$row[1]));
			    }
			}

            return $result;
        }


        public function getSearchData(){
            $result = array();
            $sql = "EXEC [sp_searchOps_searchdata_getsearchdata]";
	        $getResults= sqlsrv_query($this->conPS, $sql);
			if (sqlsrv_has_rows( $getResults ) === false){
				echo "Error in retrieveing row count.";
			}
			else{
				
			    while( $row = sqlsrv_fetch_array($getResults) ) {
			     array_push($result,
			    array('ps'=>$row[0],
                'lat'=>$row[1],
                'lng'=>$row[2],
                'cnic'=>$row[3],
                'msisdn'=>$row[4],
                'image'=>$row[5],
                'status'=>$row[6]
            ));
			    }
			}

            return $result;
        }


        // create new user in database
        public function uploadData($ps, 
        $lat, 
        $lng, 
        $cnic, 
        $msisdn,
        $image,
        $status){
           
			$sql = "EXEC [sp_searchOps_searchdata_uploaddata] 
            @PoliceStation = ?, 
            @Lat = ?, 
            @Lng = ?,
            @Cnic = ?,
            @Msisdn = ?,
            @Image = ?,
            @Status = ?";
			$params = array($ps, 
            $lat, 
            $lng, 
            $cnic, 
            $msisdn, 
            $image,
            $status);

			$getResults= sqlsrv_query($this->conPS, $sql, $params);
			

			if ($getResults == FALSE){
				return false;
			}else{
				return true;
			}
			sqlsrv_free_stmt($getResults);
            }
        }
    
?>        