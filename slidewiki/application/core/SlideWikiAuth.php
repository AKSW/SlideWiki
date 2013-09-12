<?php

class SlideWikiAuth{
	private $user;
	
	function __construct($user) {
		// check for login and pass 
		if( (!isset($user->mail) && !isset($user->password)) && !$this::isAuthorized() && !isset($user->fb_id)){
			echo "Auth error! Login or password is missing.";
			return -1;
		}
		
		// initialize a session 
		//if (session_id() == "") session_start();
		
		// create user object
                $this->user = $user;
                
		//$_SESSION['uid'] = $this->user->auth();
	}
	
	public function login(){
		// try to get user id
		$uid = $this->user->auth();
                
                if ($uid!=-1){ //$uid==-1 if there are several users with the same fb_id
                    if( isset($uid) ){ // check if user exists
			$_SESSION['uid'] = $uid;
			return $uid;
                    }else{
			//echo 'login failed';
			unset($_SESSION['uid']);
			return -1;
                    }
                }else{
                    return -2;
                }
	}
        //authorisation without asking a password
        public function loginByID($id){
            $_SESSION['uid'] = $id;
            return $id;
        }
	
//	public function registerUser($username, $fb_id = ''){
//		// try to get user id
//		if($this->user->register($username, $fb_id) !== -1) {
//			$uid = $this->user->auth();
//                        return $uid;
//		} else {
//			return -1;
//		}
//
////		// check if user exists
////		if( $uid !== -1 ){
////			$_SESSION['uid'] = $uid;
////			return $uid;
////		}else{
////			unset($_SESSION['uid']);
////			return -1;
////		}
//	}
        
        public function userdata(){
		if (session_id() == "") @session_start();
		if( !SlideWikiAuth::isAuthorized() ){
			return -1;
		}
		return $this->sw->getUserData($_SESSION['uid']);
	}
	
	public static function logout(){
		// initialize a session 
		if (session_id() == "") @session_start();
		// check
		unset($_SESSION['uid']);
		session_destroy();
	}
	
	public static function isAuthorized(){
		// initialize a session 
		if (session_id() == "") @session_start();
		// check
		return isset($_SESSION['uid']);
	}
	
	public static function getUserId() {
		if(SlideWikiAuth::isAuthorized()) {
			return $_SESSION['uid'];
		} else {
			return 0;
			//return 'Login please.';
		}		
	}
}

?>