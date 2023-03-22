<?php

    class DbOperations{
        //private $con;
		
		//private $conAndroid;
        //private $conSubDB;
        //private $conForensic;
        private $conPS;
        function __construct(){
			
			require_once dirname(__File__).'/DbConnect.php';
			
			$db = new DbConnect();
			
			//$this->conSubDB = $db->connect();
			//$this->conAndroid = $db->connectAndroid();
            //$this->conForensic = $db->connectForensic();
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


        public function getBlackListData($cnicForSubInfo){
            $result = array();
            
            $value = $cnicForSubInfo;
			$sql = "exec [sp_searchOps_blacklist_searchcnic] @CNIC = ?";
			$params = array($value);
			$getResults= sqlsrv_query($this->conPS, $sql, $params);
			if( $getResults === false) {
			die( print_r( sqlsrv_errors(), true) );
			}
			
			while( $row = sqlsrv_fetch_array($getResults) ) {
			  array_push($result,
			  array('name'=>$row[0],'cnic'=>$row[1],'reference'=>$row[2]));
			}
			
            return $result;
			sqlsrv_free_stmt( $getResults);
			
		}


        // create new user in database
        public function uploadData($ps, 
        $lat, 
        $lng,
        $name, 
        $cnic, 
        $msisdn,
        $image,
        $status,
        $mobId){
           
			$sql = "EXEC [sp_searchOps_searchdata_uploaddata] 
            @PoliceStation = ?, 
            @Lat = ?, 
            @Lng = ?,
            @Name = ?,
            @Cnic = ?,
            @Msisdn = ?,
            @Image = ?,
            @Status = ?,
            @MobileId = ?";
			$params = array($ps, 
            $lat, 
            $lng, 
            $name,
            $cnic, 
            $msisdn, 
            $image,
            $status,
            $mobId);

			$getResults= sqlsrv_query($this->conPS, $sql, $params);
			

			if ($getResults == FALSE){
                die(print_r( sqlsrv_errors(), true));
				return false;
			}else{
				return true;
			}
			sqlsrv_free_stmt($getResults);
            }


            public function getUserByMobileId($mobileId){
                $sql = "EXEC sp_searchops_users_isuserexist @MobileId = ?";
                $params = array($mobileId);
                $stmt= sqlsrv_query($this->conPS, $sql, $params);
                if (sqlsrv_has_rows( $stmt ) === false){
                    echo "Error in retrieveing row count.";
                }
                else{
                    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    return $row;
                }
                
            }

            // create new user in database
        public function createUser($UserName, 
        $MobileId, 
        $Msisdn, 
        $Cnic, 
        $Password,
        $SecEncKey,
        $FCM){
            $password = password_hash($Password, PASSWORD_DEFAULT);
            if(!$this -> isUserExist($MobileId)){
            $description = 'UserName: '.$UserName.' Password: '.$Password;
			
			//$date = date('Y-m-d H:i:s');
			// EXEC the procedure, {call stp_Create_Item (@Item_ID = ?, @Item_Name = ?)} seems to fail with various errors in my experiments
			$sql = "EXEC sp_searchops_users_insertuser 
            @UserName = ?, 
            @Password = ?, 
            @MobileId = ?,
            @Msisdn = ?,
            @Cnic = ?,
            @Description = ?,
            @SecEncKey = ?,
            @FCM = ?";
			$params = array($UserName, 
            $password, 
            $MobileId, 
            $Msisdn, 
            $Cnic, 
            $description,
            $SecEncKey,
            $FCM);
			$getResults= sqlsrv_query($this->conPS, $sql, $params);
			//$rowsAffected = sqlsrv_rows_affected($getResults);
			if ($getResults == FALSE){
				die(print_r( sqlsrv_errors(), true));
				return USER_FAILURE;
			}else{
				return USER_CREATED;
			}
			sqlsrv_free_stmt($getResults);
            }

            return USER_EXIST;
		}


        // check user login
        public function userLogin($username, $mobId, $pass){
			//$valid = false;
            if($this -> isUserExist($mobId)){
                $user = array();
                $user = $this->getUserByMobileId($mobId);
                if(password_verify($pass, $user['Password']) && $user['IsActive'] == true
				|| ($user['Password'] == 'demouser'))	{
					//$valid = true;
					
					/*StoredProcedure to insert user login time to login time table*/
					$sql = "EXEC sp_searchops_userlogintime_insertrecord 
                    @UserName = ?,
                    @UserMobileId = ?,
                    @UserMsisdn = ?,
                    @UserCnic = ?";
					$params = array($username,
                                    $mobId,
                                    $user['Msisdn'],
                                    $user['Cnic']);
					$getResults = sqlsrv_query($this->conPS, $sql, $params);
                    
					if ($getResults == FALSE){
                        die(print_r( sqlsrv_errors(), true));
                        return USER_FAILURE;
                    }else{
                        return USER_AUTHENTICATED;;
                    }
                    sqlsrv_free_stmt($getResults);
                    
            }else{
                return USER_NOT_AUTHENTICATED;
            }		
                
                //sqlsrv_free_stmt($getResults);
        }else{
            return USER_NOT_FOUND;
        }
			//return $valid;
		}

        // check user exist
        private function isUserExist($mobileId){
            $sql = "EXEC sp_searchops_users_isuserexist @MobileId = ?";
			$params = array($mobileId);
	        $stmt= sqlsrv_query($this->conPS, $sql, $params);
            return sqlsrv_has_rows( $stmt );
	       
        }

        public function isAlreadyPresent($cnic, $msisdn){
            $sql = "EXEC sp_searchops_person_ispersonexist @Cnic = ?,
            @Msisdn = ?";
			$params = array($cnic,
            $msisdn);
	        $stmt= sqlsrv_query($this->conPS, $sql, $params);
            return sqlsrv_has_rows( $stmt );
        }

        public function getRoles(){
            $result = array();
            $sql = "EXEC [sp_searchops_roles_getroles]";
	        $getResults= sqlsrv_query($this->conPS, $sql);
			if (sqlsrv_has_rows( $getResults ) === false){
				echo "Error in retrieveing row count.";
			}
			else{
				
			    while( $row = sqlsrv_fetch_array($getResults) ) {
			     array_push($result,
			    array('roleid'=>$row[0],'roletitle'=>$row[1]));
			    }
			}

            return $result;
        }

        public function generateRandomString() {

            $length = 32;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }


        public function activateUser($mobileId, $RoleId){
            $sql = "EXEC [sp_searchops_users_activateuser] @MobileId = ?, @RoleId = ?";
			$params = array($mobileId, $RoleId);
	        $getResults= sqlsrv_query($this->conPS, $sql, $params);
			if ($getResults === false) {
                die( print_r( sqlsrv_errors(), true) );
                return USER_NOT_ACTIVATED;
            }
            else{
                return USER_ACTIVATED;
            }
              
              sqlsrv_free_stmt($getResults);
        }


        public function inActiveUsersList(){
            //if($this->userLogin($username, $mobId, $pass)){
            //$sql = "SELECT [UserName], [isActive] FROM [XMatchUM].[dbo].[Users] where [isActive] = 1";
            $sql = "EXEC [sp_searchops_users_inactiveuserslist]";
            $getResults= sqlsrv_query($this->conPS, $sql);
            if( $getResults === false) {
            die( print_r( sqlsrv_errors(), true) );
            }
            $result = array();
            while( $row = sqlsrv_fetch_array($getResults) ) {
              array_push($result, 
              array('username' =>$row[0]
              , 'msisdn' => $row[1]
              , 'cnic' => $row[2]
              , 'mobId' => $row[3]));
            }
            
            //return json_encode(array("users"=>$result));
            return $result;
            sqlsrv_free_stmt($getResults);
        //}
            
        }


    }
    
?>        