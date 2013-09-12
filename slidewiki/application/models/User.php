<?php

class User extends Model {
	private $salt = "slidewikisalt";
	
	public $id;
	public $mail;
	public $username;
	private $password;
	public $subscriptions;
	// last revisions of contributed
	// slides
	public $contributed_slides = array();
	// decks
	public $contributed_decks = array();
	// feed page size
	public $feed_page_size = 10;
        //array for each tab independently
	public $numberof_pages;
	      
        public $lists;
	
        //from facebook account
        public $fb_id;
        public $first_name;
        public $middle_name;
        public $last_name;
        public $gender;
        public $locale;
        public $link;
        public $fb_username;
        public $education;
        public $fb_email;
        public $hometown;
        public $location;
        public $languages;
        public $picture;
        public $work;
        public $interests;
        public $birthday;
        public $description;
        public $infodeck;


        function __construct($mail = false, $pwd = false, $username = false, $fb_id = false) {
		if ($mail !== false) {
			$this->mail = $mail;
		}
		if ($pwd !== false ) {
			$this->password = md5 ( $pwd . $this->salt );
		}
		if ($username !== false) {
			$this->username = $username;
		}
                if ($fb_id !== false) {
			$this->fb_id = $fb_id;
		}
	}
	
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
        public function createFromFBId($fb_id){
                $this->initConnection ();
		$res = $this->dbGetRow ( 'SELECT * FROM users WHERE fb_id=:fb_id', array ('fb_id' => $fb_id ) );
		$this->id = $res['id'];
                $this->password = $res ['password'];
                $this->username = $res['username'];
                $this->mail = $res ['email'];
	}
        public function checkFBId($fb_id){
            $this->initConnection();
            $res = $this->dbGetRow ( 'SELECT id FROM users WHERE fb_id=:fb_id', array ('fb_id' => $fb_id ) );
            if (count($res)){
                return true;
            }else{
                return false;
            }
        }
        public function getUsername(){
            $this->initConnection();
            $res = $this->dbGetOne('SELECT username FROM users WHERE id=:id',array('id'=>$this->id));
            $this->username = $res;
        }
	public function createFromID($id) {
		$this->initConnection ();
		$res = $this->dbGetRow ( 'SELECT * FROM users WHERE id=:id', array ('id' => $id ) );
		$this->id = $id;
		$this->username = $res ['username'];
		$this->mail = $res ['email'];
		// hashing e-mail to send to json here
		$this->mail = md5 ( $this->mail . $this->salt );
		$this->password = $res ['password'];
		$this->subscriptions = $this->getSubscriptions();
	}
	public function createFromIDFull($id) {
		$this->initConnection ();
		$res = $this->dbGetRow ( 'SELECT * FROM users WHERE id=:id', array ('id' => $id ) );
		$this->id = $id;
		$this->username = $res ['username'];
		$this->mail = $res ['email'];
		// hashing e-mail to send to json here
		$this->mail = md5 ( $this->mail . $this->salt );
		$this->password = $res ['password'];
		$this->subscriptions = $this->getSubscriptions();
		$this->description = $res ['description'];
		//we can get more info here
		 $this->first_name = $res['first_name'];
         $this->last_name = $res['last_name'];
         $this->gender = $res['gender'];
         $this->locale = $res['locale'];
         $this->hometown = $res['hometown'];
         $this->location = $res['location'];
         $this->languages = $res['languages'];
         $this->interests = $res['interests'];
         $this->picture = $res['picture'];
         $this->birthday = $res['birthday'];
	}	
	public function createFromUsernameOrEmail($userOrMail) {
		$this->initConnection ();
		$res = $this->dbGetRow ( 'SELECT * FROM users WHERE username=:userOrMail OR email=:userOrMail', array ('userOrMail' => $userOrMail ) );
		$this->id = $res ['id'];
		$this->username = $res ['username'];
		$this->mail = $res ['email'];
		// hashing e-mail to send to json here
		$this->mail = md5 ( $this->mail . $this->salt );
		$this->password = $res ['password'];
		//$this->subscriptions = $this->getSubscriptions();
	}
        public function generatePassword($length = 8){
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $numChars = strlen($chars);
            $string = '';
            for ($i = 0; $i < $length; $i++) {
                $string .= substr($chars, rand(1, $numChars) - 1, 1);
            }
            return $string;
        }       
	public function getEmail() {		
		// query here
		$res = $this->dbGetRow ( 'SELECT email FROM users WHERE id=:id', array ('id' => $this->id ) );
		
		return $res ['email'];
	}
        public function getEmailByUsername(){
            $this->initConnection();
            $email = $this->dbGetOne ( 'SELECT email FROM users WHERE username=:username', array ('username' => $this->username ) );
            return $email;
        }
        public function passwordRecovery(){
            $this->initConnection();
            $password = $this->generatePassword();
            $this->changePassword($password);
            $this->sendPassword($password);
        }
        public function checkLogin($login){
            $this->initConnection();
            $res = $this->dbQuery('SELECT id FROM users WHERE email=:email',array('email'=>$login));
            if (count($res)) return false; else return true;
        }
        public function getIDByEmail($login){
            $this->initConnection();
            $res = $this->dbGetOne('SELECT id FROM users WHERE email=:email',array('email'=>$login));
            return $res;
        }
        public function addFBID($fb_id){
            $this->initConnection();
            $this->dbQuery('UPDATE users SET fb_id=:fb_id WHERE id=:id',array('fb_id'=>$fb_id,'id'=>$this->id));
        }
        public function checkName($username){
            $this->initConnection();
            $res = $this->dbQuery('SELECT id FROM users WHERE username=:username',array('username'=>$username));
            if (count($res)) return false; else return true;
        }
        public function changePassword($password){
            $this->initConnection();
            $this->dbQuery('UPDATE users SET password=:password WHERE id=:id', array('password'=>md5 ( $password . $this->salt ), 'id' => $this->id));
        }
        public function sendPassword($password){
            $this->initConnection();
            $to      = $this->getEmail();           
            $subject = "SlideWiki password recovery\r\n";
            $message = "The SlideWiki password recovery process was requested from your account.\r\nIf you do not have an idea what the message is about, just ignore it.";
            $message.= "If you have requested the SlideWiki password recovery, do the following:\r\n";
            $message.= "1. use the automatically created password to access your profile\r\n";
            $message.= "2. change your password in your profile.\r\n";
            $message.= "------------------------------------------------------- \r\n";
            $message.= "your password is: ".$password . "\r\n";
            $message.= "This message was produced automatically by the system. Please, do not reply.\r\n";
            $headers = "From: admin@slidewiki.aksw.org" . "\r\n";
            if (mail($to, $subject, $message, $headers)) return 'yes'; else return 'no';
        }
        public function auth() {
		$this->initConnection ();
		if ($this->fb_id){
                    $res = $this->dbGetOne ( 'SELECT id FROM users WHERE fb_id=:fb_id', array ('fb_id' => $this->fb_id ) );
                    if (count($res)>1) return -1;
                }else{
                    $res = $this->dbGetOne ( 'SELECT id FROM users WHERE email=:email AND password=:password LIMIT 1', array ('email' => $this->mail, 'password' => $this->password ) );
                }                
		return $res;
	}
        public function getUsersSameFB($fb_id){
            $this->initConnection();
            $res = $this->dbQuery('SELECT id,username FROM users WHERE fb_id=:fb_id', array ('fb_id' => $fb_id ));
            return $res;
        }
        public function getFollowersProfiles(){
            $this->initConnection();
            $res = array ();
            $res_array = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_type="user" AND item_id=:id',array('id'=>$this->id));
            foreach ( $res_array as $row ) {
                    $user = new User ();
                    $user->id = $row ['user_id'] ;
                    $user->getProfile();
                    $res [] = $user;
            }
            return $res;
            
        }
        public function saveDescription($description){
            $this->initConnection();
            $description = htmlentities(nl2br($description));
            $this->dbQuery('UPDATE users SET description=:description WHERE id=:id',array('description'=>$description,'id'=>$this->id));            
        }
        public function getProfile(){
            $this->initConnection();
            $res = $this->dbGetRow('SELECT * FROM users WHERE id=:id',array('id'=>$this->id));
            $this->username = $res['username'];
            $this->mail = md5($res['email']);
            if ($res['first_name']){
                $this->first_name = $res['first_name'];
            }            
            if ($res['gender']){
                $this->infodeck = $res['infodeck'];
            }
            if($res['description']){
                $this->description = html_entity_decode($res['description']);
            }
            if($res['birthday']){
                $this->birthday = $res['birthday'];
            }
            if($res['picture']){
                $this->picture = $res['picture'];
            }
            if($res['interests']){
                $this->interests = $res['interests'];
            }
            if($res['languages']){
                $this->languages = $res['languages'];
            }
            if($res['last_name']){
                $this->last_name = $res['last_name'];
            }
            if($res['location']){
                $this->location = $res['location'];
            }
            if($res['locale']){
                $this->locale = $res['locale'];
            }
            if($res['hometown']){
                $this->hometown = $res['hometown'];
            }
            if($res['gender']){
               $this->gender = $res['gender']; 
            }
            if($res['infodeck']){
               $this->infodeck = $res['infodeck']; 
            }
        }
        public function saveProfile(){
            $this->initConnection();
            return $this->dbQuery('UPDATE users SET infodeck=:infodeck,birthday=:birthday, first_name=:first_name, gender=:gender, hometown=:hometown, locale=:locale, location=:location, last_name=:last_name, languages=:languages, interests=:interests, picture=:picture WHERE id=:id',array('infodeck'=>$this->infodeck,'birthday'=>$this->birthday,'first_name'=>$this->first_name,'last_name'=>$this->last_name,'gender'=>$this->gender,'locale'=>$this->locale,'hometown'=>$this->hometown,'location'=>$this->location,'languages'=>$this->languages,'interests'=>$this->interests,'picture'=>$this->picture,'id'=>$this->id));
        }
        public function saveSettings(){
            $this->initConnection();
            
        }
        public function saveInfodeck($link){
            $this->initConnection();
            $this->dbQuery('UPDATE users SET infodeck=:infodeck WHERE id=:id',array('infodeck' => $link, 'id' => $this->id));
        }
        public function mergeWithFB($fb_id){
            $this->initConnection();
            $fb_profile = $this->dbGetRow('SELECT * FROM users WHERE fb_id=:fb_id LIMIT 1', array('fb_id' => $fb_id));
            $this->first_name = $fb_profile['first_name'];
            $this->last_name = $fb_profile['last_name'];
            $this->gender = $fb_profile['gender'];
            $this->locale = $fb_profile['locale'];
            $this->hometown = $fb_profile['hometown'];
            $this->location = $fb_profile['location'];
            $this->languages = $fb_profile['languages'];
            $this->interests = $fb_profile['interests'];
            $this->picture = $fb_profile['picture'];
            $this->birthday = $fb_profile['birthday'];
            $this->description = $fb_profile['description'];
            $this->infodeck = $fb_profile['infodeck'];
            $this->addFBID($fb_id);
            $this->saveProfile();           
        }
        public function getFBId(){
            $this->initConnection();
            $user_fb_id = 0;
            $res = $this->dbGetOne('SELECT fb_id FROM users WHERE id=:id',array('id'=>$this->id));
            if ($res){
                $user_fb_id = $res;
            }
            return $user_fb_id;
        }
	public function isMemberOfGroup($deck_id, $category) {
		$result = $this->dbQuery ( 'SELECT * FROM user_group WHERE deck_revision_id=:deck_id AND user_id=:id AND category =:category', array ('deck_id' => $deck_id, 'category'=>$category, 'id'=>$this->id) );
		if (! empty ( $result )) {
			return true;
		}else{
			return false;
		}
	}	
	public function isOwnerOfDeck($deck_id) {
		$result = $this->dbQuery ( 'SELECT * FROM deck_revision WHERE id=:deck_id AND user_id=:id', array ('deck_id' => $deck_id, 'id'=>$this->id) );
		if (! empty ( $result )) {
			return true;
		}else{
			return false;
		}
	}	
	public function register($username = '') {
		$this->initConnection ();
		
		$username = isset ( $this->username ) ? $this->username : $username;
                $fb_id = isset ($this->fb_id) ? $this->fb_id : 0;
		
		// query here
                if (!$fb_id){
                   $res = $this->dbInsert ( 'users', array ('email' => $this->mail, 'password' => $this->password, 'username' => $username ) ); 
                }else{
                    $res = $this->dbInsert ( 'users', array ('email' => $this->mail, 'password' => $this->password, 'username' => $username, 'fb_id' => $this->fb_id ) );
                }
		
		if ($res !== false) {
			return 1;
		} else {
			return - 1;
		}
	}
//        
	public function setPartOffeedSize($tab){
            switch ($tab) {
                case 'contributed_slide' : 
                    $c_slides_ids = $this->dbQuery('SELECT MAX(id) FROM slide_revision WHERE user_id=:user_id GROUP BY slide_revision.slide ORDER BY slide_revision.timestamp', array('user_id' => $this->id));
		
                    foreach($c_slides_ids as $id) {
                        array_push($this->contributed_slides, (int) $id['MAX(id)']);
                    }

                    $numberof_c_slides = sizeof($c_slides_ids);
                    $this->numberof_pages['c_slide'] = (int) ceil($numberof_c_slides / $this->feed_page_size);
                    break;
                case 'contributed_deck' :
                    $c_decks_ids = $this->dbQuery('SELECT MAX(id) FROM deck_revision WHERE user_id=:user_id GROUP BY deck_revision.deck_id ORDER BY deck_revision.timestamp', array('user_id' => $this->id));
			
			foreach($c_decks_ids as $id) {
				array_push($this->contributed_decks, (int) $id['MAX(id)']);
			}			
			$numberof_c_decks = sizeof($c_decks_ids);
                        $this->numberof_pages['c_deck'] = (int) ceil($numberof_c_decks / $this->feed_page_size);
                        break;
                case 'subscribed_slide' :
                    $numberof_s_slides = $this->dbQuery ( 'SELECT COUNT(*) FROM subscription WHERE item_type="slide" AND user_id=:user_id', array('user_id' => $this->id));
			$numberof_s_slides = (int) $numberof_s_slides[0]['COUNT(*)'];
                        $this->numberof_pages['s_slide'] = (int) ceil($numberof_s_slides / $this->feed_page_size);
                        break;
                case 'subscribed_deck' :
                    $numberof_s_decks = $this->dbQuery ( 'SELECT COUNT(*) FROM subscription WHERE item_type="deck" AND user_id=:user_id', array('user_id' => $this->id));
			$numberof_s_decks = (int) $numberof_s_decks[0]['COUNT(*)'];
                        $this->numberof_pages['s_deck'] = (int) ceil($numberof_s_decks / $this->feed_page_size);
                        break;
                case 'subscribed_user' :
                    $numberof_s_users = $this->dbQuery ( 'SELECT COUNT(*) FROM subscription WHERE item_type="user" AND user_id=:user_id', array('user_id' => $this->id));
			$numberof_s_users = (int) $numberof_s_users[0]['COUNT(*)'];
                        $this->numberof_pages['s_user'] = (int) ceil($numberof_s_users / $this->feed_page_size);
                        break;
            }
        }
	public function setFeedSize() {
		// get the last revision ids for this user contributed slides
		
		
            $c_slides_ids = $this->dbQuery('SELECT MAX(id) FROM slide_revision WHERE user_id=:user_id GROUP BY slide_revision.slide ORDER BY slide_revision.timestamp', array('user_id' => $this->id));

            foreach($c_slides_ids as $id) {
                    array_push($this->contributed_slides, (int) $id['MAX(id)']);
            }

            $numberof_c_slides = sizeof($c_slides_ids);



            $c_decks_ids = $this->dbQuery('SELECT MAX(id) FROM deck_revision WHERE user_id=:user_id GROUP BY deck_revision.deck_id ORDER BY deck_revision.timestamp', array('user_id' => $this->id));

            foreach($c_decks_ids as $id) {
                    array_push($this->contributed_decks, (int) $id['MAX(id)']);
            }

            $numberof_c_decks = sizeof($c_decks_ids);



            $numberof_s_slides = $this->dbQuery ( 'SELECT COUNT(*) FROM subscription WHERE item_type="slide" AND user_id=:user_id', array('user_id' => $this->id));
            $numberof_s_slides = (int) $numberof_s_slides[0]['COUNT(*)'];



            $numberof_s_decks = $this->dbQuery ( 'SELECT COUNT(*) FROM subscription WHERE item_type="deck" AND user_id=:user_id', array('user_id' => $this->id));
            $numberof_s_decks = (int) $numberof_s_decks[0]['COUNT(*)'];


            $numberof_s_users = $this->dbQuery ( 'SELECT COUNT(*) FROM subscription WHERE item_type="user" AND user_id=:user_id', array('user_id' => $this->id));
            $numberof_s_users = (int) $numberof_s_users[0]['COUNT(*)'];
		
		
				
		// getting percentage for one feed page
		
//			$this->c_slides_percentage = $numberof_c_slides / $feed_size * 100;
//			$this->c_decks_percentage = $numberof_c_decks / $feed_size * 100;
//			$this->s_slides_percentage = $numberof_s_slides / $feed_size * 100;
//			$this->s_decks_percentage = $numberof_s_decks / $feed_size * 100;
//			$this->s_users_percentage = $numberof_s_users / $feed_size * 100;
		
		// calculating elements per page
		//$this->recalculateElementsPerPage();
		
            $this->numberof_pages['s_user'] = (int) ceil($numberof_s_users / $this->feed_page_size);
            $this->numberof_pages['c_slide'] = (int) ceil($numberof_c_slides / $this->feed_page_size);
            $this->numberof_pages['s_slide'] = (int) ceil($numberof_s_slides / $this->feed_page_size);
            $this->numberof_pages['c_deck'] = (int) ceil($numberof_c_decks / $this->feed_page_size);
            $this->numberof_pages['s_deck'] = (int) ceil($numberof_s_decks / $this->feed_page_size);

           
	}
	
//	function recalculateElementsPerPage() {
//		// setting elements per page size
//		$this->c_slides_per_page = (int) floor($this->feed_page_size*$this->c_slides_percentage / 100);
//		$this->c_decks_per_page = (int) floor($this->feed_page_size*$this->c_decks_percentage / 100);
//		$this->s_slides_per_page = (int) floor($this->feed_page_size*$this->s_slides_percentage / 100);
//		$this->s_decks_per_page = (int) floor($this->feed_page_size*$this->s_decks_percentage / 100);
//		$this->s_users_per_page = (int) floor($this->feed_page_size*$this->s_users_percentage / 100);
//	}
	
	public function setFeedPageSize($size) {
		$this->feed_page_size = $size;
		
		$this->recalculateElementsPerPage();
	}
        public function getContributedSlides(){
            $this->initConnection();
            $result = array();
            $c_slides_ids = $this->dbQuery('SELECT MAX(id) FROM slide_revision WHERE user_id=:user_id GROUP BY slide_revision.slide ORDER BY slide_revision.timestamp', array('user_id' => $this->id));
            foreach($c_slides_ids as $id) {
                    array_push($result, (int) $id['MAX(id)']);
            }            
            return $result;
        }
	
//	public function getContributedSlides($page) {
//		$extent = $this->feed_page_size;
//		$offset = $page*$extent;
//		$res = array ();
//		for($i = $offset; $i < $offset + $extent; $i++) {
//			if(array_key_exists($i, $this->contributed_slides)) {
//				$id = $this->contributed_slides[$i];
//				$slide = new Slide ();
//				$slide->createFromID ($id);
//				
//				$last_revision_id=$slide->getLastRevisionID();
//				$slide->createFromIDLite($last_revision_id);
//				$slide->usage = $slide->getUsage ();
//				$res [] = $slide;
//			}
//		}
//		return $res;
//	}
	public function getSubscribedSlides($page) {
		$extent = $this->feed_page_size;
		$offset = $page*$extent;
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT * FROM subscription WHERE item_type="slide" AND user_id=' . $this->id .' LIMIT '.$offset.','.$extent) as $row ) {
			$slide = new Slide ();
			$slide->slide_id= $row ['item_id'] ;
			$last_revision_id=$slide->getLastRevisionID();
			$slide->createFromID($last_revision_id);
			$slide->usage = $slide->getUsage ();
			$res [] = $slide;
		}
		return $res;
	}
        public function getContributedDecks(){
            $this->initConnection();
            $result = array();
            $c_decks_ids = $this->dbQuery('SELECT MAX(id) FROM deck_revision WHERE user_id=:user_id GROUP BY deck_revision.deck_id ORDER BY deck_revision.timestamp DESC', array('user_id' => $this->id));
            foreach($c_decks_ids as $id) {
                    array_push($result, (int) $id['MAX(id)']);
            }
            return $result;
        }
        public function getEditorDecks(){
        	$result = array();
			$e_decks_ids = $this->dbQuery('SELECT DISTINCT * FROM user_group WHERE user_id=:user_id AND category="editor" ORDER BY timestamp DESC', array('user_id' => $this->id));
            foreach($e_decks_ids as $row) {
            	$n=new Deck();
            	$n->createFromIDLite($row['deck_revision_id']);
            	if($n->title)
            		$result[]=$n;  
            }
            return $result;
        }        
//	public function getContributedDecks($page) {
//		$extent = $this->feed_page_size;
//		$offset = $page*$extent;
//		$res = array ();
//		for($i = $offset; $i < $offset + $extent; $i++) {
//			if(array_key_exists($i, $this->contributed_decks)) {
//				$id = $this->contributed_decks[$i];                                
//				$deck = new Deck ();
//                $deck->id = $id ;
//				$deck->deck_id = $deck->getBasicID();
//                $last = $deck->getLastRevisionID();
//                $deck->createFromIDLite($last);
//				$res [] = $deck;
//			}
//		}
//		return $res;
//	}
        public function getFollowedProfiles(){
            $this->initConnection();
            $res = array ();
            $res_array = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_type="user" AND user_id=:id',array('id'=>$this->id));
            foreach ( $res_array as $row ) {
                    $user = new User ();
                    $user->id = $row ['item_id'] ;
                    $user->getProfile();
                    $res [] = $user;
            }
            return $res;
        }
        public function getFollowedDecks(){
            $this->initConnection();
            $res = array ();
            $res_array = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_type="deck" AND user_id=:id',array('id'=>$this->id));
            foreach ( $res_array as $row ) {
                $deck = new Deck ();
                $deck->deck_id = $row ['item_id'] ;
                $deck->id = $deck->getLastRevisionID();
                $title = $deck->getTitle();
                $deck->title = $deck->shortenTitle($title);                
                $res [] = $deck;
            }
            return $res;
        }
        public function getFollowedSlides(){
            $this->initConnection();
            $res = array ();
            $res_array = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_type="slide" AND user_id=:id',array('id'=>$this->id));
            foreach ( $res_array as $row ) {
                $slide = new Slide ();
                $slide->slide_id = $row ['item_id'] ;
                $slide->createFromIDLite($slide->getLastRevisionID());
                $title = $slide->getTitle();
                $slide->title = $slide->shortenTitle($title);                
                $res [] = $slide;
            }
            return $res;
        }
	public function getSubscribedDecks($page) {
		$extent = $this->feed_page_size;
		$offset = $page*$extent;
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT * FROM subscription WHERE item_type="deck" AND user_id=' . $this->id . ' LIMIT '.$offset.','.$extent) as $row ) {
			$deck = new Deck ();
			$deck->deck_id = $row ['item_id'];
                        $last = $deck->getLastRevisionID();
                        $deck->createFromID($last);                      
			$res [] = $deck;
		}
		return $res;
	}
	public function getSubscribedUsers($page) {
		$extent = $this->feed_page_size;
		$offset = $page*$extent;
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT * FROM subscription WHERE item_type="user" AND user_id=' . $this->id .' LIMIT '.$offset.','.$extent) as $row ) {
			$user = new User ();
			$user->createFromID ( $row ['item_id'] );
			$res [] = $user;
		}
		return $res;
	}
	public function follow($item_type, $item_id, $unfollow = 0) {
		$this->initConnection ();
		if (! $unfollow)
			return $this->dbInsert ( 'subscription', array ('item_type' => $item_type, 'item_id' => $item_id, 'user_id' => $this->id ) );
		else
			$this->dbQuery ( 'DELETE FROM subscription WHERE item_type=:item_type AND item_id=:item_id AND user_id=:user_id', array ('item_type' => $item_type, 'item_id' => $item_id, 'user_id' => $this->id ) );
	
	}
	public function isFollowing($item_type, $id) {
		if ($this->dbGetOne ( 'SELECT user_id FROM subscription WHERE user_id=:user_id AND item_type=:item_type AND item_id=:item_id', array ('item_id' => $id, 'user_id' => $this->id, 'item_type' => $item_type ) ))
			return 1;
		else
			return 0;
	}
	
	public function getSubscribersOf($item_type, $item_id) {
		$this->initConnection ();
		$subscribers = array ();
		$parameters = array ('item_id' => $item_id, 'item_type' => $item_type );
		
		$results = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_id=:item_id AND item_type=:item_type', $parameters );
		
		if (! empty ( $results )) {
			foreach ( $results as $result ) {
				array_push ( $subscribers, $result ['user_id'] );
			}
		}
		return $subscribers;
	}
	

	
	public function getSubscriptions() {
		$results = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_id=:item_id && item_type = "user"', array ('item_id' => $this->id ) );
		
		$subscriptions = array();
		foreach($results as $result) {
			$res = array();
			$res['user_id'] = $result["user_id"];
			$res['item_id'] = $result["item_id"];
			$res['timestamp'] = $result["timestamp"];
			array_push($subscriptions, $res);
		}
		
		return $subscriptions;
	}
	
	
	public function getPreferenceValue($preference) {
		$results = $this->dbQuery ( 'SELECT value FROM preference WHERE user_id=:id AND preference=:preference', array ('id' => $this->id, 'preference' => $preference));
		if(!empty($results)) {
			$preference_value = $results[0]['value'];
		} else {
			$preference_value = NULL;
		}
		
		return $preference_value;
	}
	
	public function setPreferenceValue($preference, $value, $password) {
		$this->initConnection ();
		if(md5 ( $password . $this->salt ) == $this->password) {
			// check if row exists
			$exists = $this->dbQuery('SELECT COUNT(value) FROM preference WHERE user_id=:id AND preference=:preference', array('id' => $this->id, 'preference' => $preference));
			if($exists[0]['COUNT(value)'] > 0) {
				$exists = true;
			} else {
				$exists = false;
			}
			if($exists) {
				$this->dbQuery('UPDATE preference SET value=:value WHERE user_id=:id AND preference=:preference', array ('id' => $this->id, 'preference' => $preference, 'value' => $value));
			} else {
				$this->dbInsert ( 'preference', array ('user_id' => $this->id, 'preference' => $preference, 'value' => $value));
			}
			return 0;
		} else {
			return -1;
		}		
	}
	
	public function setPreferenceValueNoPass($preference, $value) {
		$this->initConnection ();
		
		$exists = $this->dbQuery('SELECT COUNT(value) FROM preference WHERE user_id=:id AND preference=:preference', array('id' => $this->id, 'preference' => $preference));
		if($exists[0]['COUNT(value)'] > 0) {
			$exists = true;
		} else {
			$exists = false;
		}
		if($exists) {
			$this->dbQuery('UPDATE preference SET value=:value WHERE user_id=:id AND preference=:preference', array ('id' => $this->id, 'preference' => $preference, 'value' => $value));
		} else {
			$this->dbInsert ( 'preference', array ('user_id' => $this->id, 'preference' => $preference, 'value' => $value));
		}
		
		return 0;	
	}
	public function getDefaultLanguage(){
                $this->initConnection ();
                $default_language = $this->dbGetOne('SELECT value FROM preference WHERE user_id=:id AND preference="default_language"',array('id' => $this->id));
                $language_arr = array();
                $language_arr = explode('-',$default_language);
                $language = array();
                $language['id'] = $language_arr[0];
                $language['name'] = $language_arr[1];
                return $language;
        }
	function getNotificationInterval() {
		$notification_interval = $this->getPreferenceValue('notification_interval');
		if(empty($notification_interval)) {
			$notification_interval = 'OFF';
		}
		
		return $notification_interval;
	}
	
	function getDefaultStyle() {
		$results = $this->dbQuery ( 'SELECT default_theme FROM users WHERE id=:id', array ('id' => $this->id));
		$default_theme = $results[0]['default_theme'];
		
		return $default_theme;
	}
	
	function updateNotificationInterval($notification_interval) {
		return $this->setPreferenceValueNoPass('notification_interval',$notification_interval);
	}
	
	public function getDefaultTheme() {
		$default_theme = $this->getPreferenceValue('default_theme');
		if(empty($default_theme)) {
			$default_theme = '1';
		}
		
		return $default_theme;
	}
	
	public function setDefaultTheme($default_theme) {
		return $this->setPreferenceValueNoPass('default_theme', $default_theme);
	}
        
        public function setDefaultLanguage($default_language) {
		return $this->setPreferenceValueNoPass('default_language', $default_language);
	}
	
	public function getShowContributedSlides() {
		$show_contributed_slides = $this->getPreferenceValue('show_contributed_slides');
		if(empty($show_contributed_slides)) {
			$show_contributed_slides = true;
		}
		
		return $show_contributed_slides;
	}
	
	public function getShowContributedDecks() {
		$show_contributed_decks = $this->getPreferenceValue('show_contributed_decks');
		if(empty($show_contributed_decks)) {
			$show_contributed_decks = true;
		}
		
		return $show_contributed_decks;
	}
	
	public function getShowSubscribedSlides() {
		$show_subscribed_slides = $this->getPreferenceValue('show_subscribed_slides');
		if(empty($show_subscribed_slides)) {
			$show_subscribed_slides = true;
		}
		
		return $show_subscribed_slides;
	}
	
	public function getShowSubscribedDecks() {
		$show_subscribed_decks = $this->getPreferenceValue('show_subscribed_decks');
		if(empty($show_subscribed_decks)) {
			$show_subscribed_decks = true;
		}
		
		return $show_subscribed_decks;
	}
	
	public function getShowSubscribedUsers() {
		$show_subscribed_users = $this->getPreferenceValue('show_subscribed_users');
		if(empty($show_subscribed_users)) {
			$show_subscribed_users = true;
		}
		
		return $show_subscribed_users;
	}
	
	public function setShowContributedSlides($show_contributed_slides) {
		// need to add password control here!
		$this->setPreferenceValueNoPass('show_contributed_slides',$show_contributed_slides);
	}
	
	public function setShowContributedDecks($show_contributed_decks) {
		// need to add password control here!
		$this->setPreferenceValueNoPass('show_contributed_decks',$show_contributed_decks);
	}
	
	public function setShowSubscribedSlides($show_subscribed_slides) {
		// need to add password control here!
		$this->setPreferenceValueNoPass('show_subscribed_slides',$show_subscribed_slides);
	}
	
	public function setShowSubscribedDecks($show_subscribed_decks) {
		// need to add password control here!
		$this->setPreferenceValueNoPass('show_subscribed_decks',$show_subscribed_decks);
	}
	
	public function setShowSubscribedUsers($show_subscribed_users) {
		// need to add password control here!
		$this->setPreferenceValueNoPass('show_subscribed_users',$show_subscribed_users);
	}
	
	public function setPassword($old_password, $new_password, $confirm_new_password) {
		$this->initConnection ();
		if (!empty($new_password)) {
			if($new_password != $confirm_new_password) {
				return -2; // password mismatch!
			}
			if(md5( $old_password . $this->salt ) == $this->password) {
                            $new_pass = md5 ( $new_password . $this->salt );
                            $this->dbQuery('UPDATE users SET password=:password WHERE id=:id', array('password' => $new_pass, 'id' => $this->id));
                            return 0;
			} else {
				return -1; // old password not correct!
			} 
		} else return 0;
	}
/*====================== Questions staff ==============================*/        
	public function getTests(){
		$this->initConnection ();
		$result = array();
		$result = $this->dbQuery("SELECT * FROM testing WHERE user_id=:user_id AND type='auto' GROUP by item_id", array('user_id'=>$this->id));
		return $result;
	}
        public function getOwnLists(){
            $this->initConnection ();
		$result = array();
		$result = $this->dbQuery("SELECT * FROM user_tests WHERE user_id=:user_id", array('user_id'=>$this->id));
		return $result;
        }
        public function getLists(){
            $this->initConnection ();
            $result = array();
            $result = $this->dbQuery("SELECT * FROM testing WHERE user_id=:user_id AND type='list' GROUP by item_id", array('user_id'=>$this->id));
            return $result;
        }
        public function addList($title){
            $this->initConnection ();
            return ($this->dbInsert('user_tests', array('user_id'=>$this->id,'title'=>$title)));
        }

}
	
