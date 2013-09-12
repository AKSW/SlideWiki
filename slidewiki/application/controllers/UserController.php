<?php


class UserController extends Controller {
    function decks(){
        
    }
        function feed(){
            if (!($this->_user['is_authorized'])){
                header('Location: '.BASE_PATH);
                die();
            }else{                
                $user = new User();
                $id = $_SESSION['uid'];
                $user->createFromID($id);
                $user->subscriptions = $user->getSubscriptions();
                $this->set('profile',$user);
                $this->set('current_user',$id);
                $this->set ( 'page_title', "SlideWiki User - ".$user->username );
            }     
        }       
	function profile() {
            $id = $_GET ['id'];
            $user=new User();
            $user->id = $id;
            $user->getUsername();
            //show error if user does not exist
            if(!isset($user->username)){
                header('Location: '.BASE_PATH.'error/404');
                die();
            }
            $user->createFromIDFull($id);
            $email=$user->getEmail();
            $user->subscriptions = $user->getSubscriptions();
            if($this->_user['id']){
                $current_user=new User();
                $current_user->createFromID($this->_user['id']);
                $current_user->subscriptions = $current_user->getSubscriptions();
                $this->set('isFollowing',$current_user->isFollowing('user', $id));
            }else{
                $this->set('isFollowing',0);
            }
            $this->set('profile',$user);
            $this->set('email',$email);
            $this->set('login_user', $this->_user['id']);
            $this->set ( 'page_title', "SlideWiki User - ".$user->username );
            $this->set ( 'page_description', $user->description );
            $this->set ( 'page_additional_footer','<p><a href="user/alldecks/'.$user->id.'">( "'.$user->username .'"  plain decks )</a></p>');
	}
	
	function profileEdit() {	
		// Authorize user
		
		$id = $_GET ['id'];
		if ($this->_user['is_authorized']) {
			if(!($this->_user['id'] == $id)) {
				$this->set('authorized', false);
				die('You are not allowed to edit this profile!');
			} else {
				$this->set('authorized', true);
			}
		} else {
			$this->set('authorized', false);
			die('You are not allowed to edit this profile!');
		}                
		
		// get all necessary user data
		$user=new User();
		$user->createFromID($id);
		$email=$user->getEmail();
                $language = $user->getDefaultLanguage();
                $this->set('default_language',$language);
		// notification_interval is rewritten if any POST data is present
		$notification_interval = $user->getNotificationInterval();
		$default_theme = $user->getDefaultTheme();
		//if any POST data in - update profile
		$style = new Style ();
		$styles_list = $style->getAll ();
		$this->set ( 'styles', $styles_list );
			
		$this->set('notification_interval', $notification_interval);
		$this->set('default_theme', $default_theme);
		$this->set('profile',$user);
		$this->set('email',$email);
		$this->set ( 'page_title', "Edit User - ".$user->username );
		
	}
	function confirmEditorship(){
		$user_id = $_GET ['user'];
		$deck_id=$_GET ['deck'];
		$deck_obj = new Deck ();
		$deck_obj->createFromIDLite ( $deck_id );		
		if ($this->_user['is_authorized'] && ($this->_user['id'])==$deck_obj->owner->id) {
			$user = new User ();
		    $user->createFromID ( $user_id );
		   //assign to current deck
			$deck_obj->addUser ( $user, 'editor' );
		   //assign to all subdecks of owner
		    $deck_obj->assignEditorToSubdecks ( $user );
			$response = 'User has been added as editor to the deck #' . $deck_id . ': <a href="deck/' . $deck_id. '_' . $deck_obj->slug_title . '">' . $deck_obj->title . '</a>';
			$type = "success";
		}else{
			$response="You should login to the system with an appropriate username in order to confirm the request!";
			$type="warning";
		}
		$this->set('response',$response);
		$this->set('type',$type);
	}
	function scores() {
	
		//authorize user
		$id = $_GET ['id'];
		if ($this->_user['is_authorized']) {
			if(!($this->_user['id'] == $id)) {
				$this->set('authorized', false);
				die('You are not allowed to view this page!');
			} else {
				$this->set('authorized', true);
			}
		} else {
			$this->set('authorized', false);
			die('You are not allowed to view this page!');
		}
		
		$testsArray = array();
		$testsTable = array();
                $listsArray = array();
		$listsTable = array();
		$timestampRow = array();
		$user = new User();
		$user->createFromID($id);
		foreach ($user->getTests() as $testRow){
			$test = new Test();
			$test->createFromItem($testRow['item_id']);
                        $type = $test->type;
			$timestampRow = $test->getLast($id, $type);
			$test->getMaxForUser($id,$type);
			$testsArray['title'] = $test->title;
			$testsArray['max_score'] = $test->max_for_user*100;
			$testsArray['timestamp'] = $timestampRow['timestamp'];
			$testsArray['count'] = $test->getCountForUser($id,$type);
			$testsArray['item_id'] = $testRow['item_id'];
			$testsTable[] = $testsArray;			
		}
                foreach ($user->getLists() as $listRow){
			$list = new QList();
                        $list->createFromID($listRow['item_id']);
                        $type=$list->type;
			$timestampRow = $list->getLast($id,$type);
			$maxForUser = $list->getMaxForUser($id,$type);
			$listsArray['title'] = $listRow['title'];
			$listsArray['max_score'] = $maxForUser*100;
			$listsArray['timestamp'] = $timestampRow['timestamp'];
			$listsArray['count'] = $list->getCountForUser($id,$type);
			$listsArray['item_id'] = $listRow['item_id'];
			$listsTable[] = $listsArray;			
		}
		$this->set("testsTable", $testsTable);
                $this->set("listsTable", $listsTable);
		$this->set('user_obj', $user);		
	}
        function tests(){
            $user_id = $_GET['id'];
            $user = new User();
            $user->createFromID($user_id);
            $lists_array = array();
            foreach ($user->getOwnLists() as $list_row) {
               $list = new QList();
               $list->createFromID($list_row['id']);
               $lists_array[] = $list;               
            }
            $this->set('lists_array',$lists_array);
        }
        function getAllDeckContributions(){
        	$user_id = $_GET['id'];
        	$user = new User();
            $user->createFromID($user_id);
            $user->email=$user->getEmail();
            $editoreddecks=$user->getEditorDecks();
            $owndecks=array();
            foreach( $user->getContributedDecks() as $c){
               	$n=new Deck();
            	$n->createFromIDLite($c);
            	$owndecks[]=$n;         	
            }
            $this->set('editoreddecks',$editoreddecks);
            $this->set('owndecks',$owndecks);
            $this->set('profile',$user);
            $this->set('page_title',$user->username.' - List of all contributed decks');
        }
}
