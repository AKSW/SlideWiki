<?php

class Stream extends Model {
    public $activities = array();
    public $month;
    
    private function initConnection() {
        // connect to db
        if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
                die ( "Could not connect to db" );
    }
    public function getMainPageStream($limit = 5){       
        $this->initConnection();
        $result = array();
        $result = $this->dbQuery('SELECT * FROM deck WHERE 1');        
        if (count($result)){
            $filter_array = array('1','1','1','1');
            $today = date('Y-m-d H:m:s');           
            $yesterday = strtotime($today.' -1 day');
            $yesterday = date('Y-m-d H:m:s',$yesterday);            
            foreach($result as $row){
                $this->createForDeck($row['id'], $yesterday, $filter_array, $today);
            }
            if (count($this->activities)){
                $this->sort();
                $activities = array();
                $i = 0;            
                while($i < $limit && $i < count($this->activities)){
                    $activities[] = $this->activities[$i];
                    $i++;
                }
                $this->activities = $activities;
            }
        }        
    } 
    public function getUserNotifications($user_id, $notification_interval){
        $user = new User();
        $user->id = $user_id;
        $followings = array();
        $end = date('Y-m-d H:i:s');
//        $end = date('Y-m-d H:i:s', strtotime($end.' +1 hour'));
        switch ($notification_interval) {
            case 'hourly':
                $start_str = strtotime($end.' -1 hour'); //last weak 
                $start = date('Y-m-d H:i:s', $start_str);
                $filter_array_user = array('1','1','1','1','1','1','1');
                $filter_array_deck = array('1','1','1','1');
                $filter_array_slide = array('1','1','1','1');
                break;
            case 'daily':
                $start_str = strtotime($end.' -1 day'); //last weak 
                $start = date('Y-m-d H:i:s', $start_str);
                $filter_array_user = array('1','1','0','1','1','1','1');
                $filter_array_deck = array('1','1','1','1');
                $filter_array_slide = array('1','1','1','1');
                break;
            case 'weekly':
                $start_str = strtotime($end.' -7 day'); //last weak 
                $start = date('Y-m-d H:i:s', $start_str);
                $filter_array_user = array('1','1','0','1','1','1','1');
                $filter_array_deck = array('1','1','1','1');
                $filter_array_slide = array('1','1','1','1');
                break;
            default:
                break;
        }
        
        $followings = $user->getFollowedProfiles();
        if (count($followings)){            
            foreach($followings as $following){
                $this->createForUser($following->id, $start, $filter_array_user, $end);
            }
        }
        $followings = $user->getFollowedDecks();
        if (count($followings)){            
            foreach($followings as $following){                
                $this->createForDeck($following->deck_id, $start, $filter_array_deck, $end);
            }
        }
        $followings = $user->getFollowedSlides();
        if (count($followings)){            
            foreach($followings as $following){
                $this->createForSlide($following->id, $start, $filter_array_slide, $end);
            }
        }        
    }
    public function searchStream($mode,$filter,$keywords,$user_id = false){
        switch ($mode){
            case 'my' : 
                if ($user_id){
                    if ($filter == false) {
                        $filter_array = array('0','1','1','1','1','1','1'); 
                    }else{
                        $filter_array = explode(',',$filter);
                        $filter_array[0] = '0';
                    }
                    $this->createForUser($user_id, false, $filter_array, false, $keywords);
                }
            break;
        }
    }
    public function getUserNews($user_id, $type, $month, $filter, $facet, $portion){
        if (!$month){
            $month = date("Y-m");
            $month.='-01';
        }        
        $end = date($month); //1st day of current month
        $start = $end;
        switch ($portion){
            default :                
                break;
            case '1' :                
                $end_str = strtotime($end.' +1 month'); //last day of current month
                $end = date('Y-m-d',$end_str); //last day of month 
                $start_str = strtotime($end.' -7 day'); //last weak 
                $start = date('Y-m-d', $start_str);
                break;
            case '2' :
                $end_str = strtotime($end.' +1 month -7 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($end.' -7 day');
                $start = date('Y-m-d', $start_str);
                break;
            case '3' :
                $end_str = strtotime($end.'+1 month -14 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($end.' -7 day');
                $start = date('Y-m-d', $start_str);
                break;
            case '4' :
                $end_str = strtotime($end.'+1 month -21 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($month);
                $start = date('Y-m-d', $start_str);
                break;
        }
         
        $user = new User();
        $user->id = $user_id;
        $followings = array();
        
        //users
        switch ($type){
            case 'users' :
                if ($filter == false) {
                    $filter_array = array('1','1','0','1','1','1','1'); 
                }else{
                    $filter_array = explode(',',$filter);
                }
                if ($facet == false){
                    $followings = $user->getFollowedProfiles();
                    if (count($followings)){            
                        foreach($followings as $following){
                            $this->createForUser($following->id, $start, $filter_array, $end);
                        }
                    }
                }else{
                    $facet_array = explode(',', $facet);
                    foreach ($facet_array as $followed_id){
                        $this->createForUser($followed_id, $start, $filter_array, $end);
                    }
                }                
                $this->getSelfRegistration($user_id, $start, $end );
                
                break;
            case 'decks' :
                if ($filter == false) {
                    $filter_array = array('1','1','1','1'); 
                }else{
                    $filter_array = explode(',',$filter);
                }
                if ($facet == false){
                    $followings = $user->getFollowedDecks();
                    if (count($followings)){            
                        foreach($followings as $following){
                            $this->createForDeck($following->deck_id, $start, $filter_array, $end);
                        }
                    }
                }else{
                    $facet_array = explode(',', $facet);
                    foreach ($facet_array as $followed_id){
                        $this->createForDeck($followed_id, $start, $filter_array, $end);
                    }
                } 
                $this->getSelfRegistration($user_id, $start, $end);
                break;
            case 'slides' :
                if ($filter == false) {
                    $filter_array = array('1','1','1','1'); 
                }else{
                    $filter_array = explode(',',$filter);
                }
                if ($facet == false){
                    $followings = $user->getFollowedSlides();
                    if (count($followings)){            
                        foreach($followings as $following){
                            $this->createForSlide($following->id, $start, $filter_array, $end);
                        }
                    }
                }else{
                    $facet_array = explode(',', $facet);
                    foreach ($facet_array as $followed_id){
                        //echo 'id: '.$followed_id.'<br>';
                        $this->createForSlide($followed_id, $start, $filter_array, $end);
                    }
                } 
                $this->getSelfRegistration($user_id, $start, $end);
                break;
        }
    }
    public function getShortDeckStream($deck_revision_id){
        $end = date('Y-m-d H:i:s');
        $start_str = strtotime($end.' -1 year'); 
        $start = date('Y-m-d H:i:s', $start_str);
        
        $deck = new Deck();
        $deck->id = $deck_revision_id;
        $deck->deck_id = $deck->getBasicID();
        $deck->content = $deck->fetchDeckContent();        
        $filter_array = array('1','1','1','1');
        $this->createForDeck($deck->deck_id, $start, $filter_array, $end);
        foreach($deck->content as $item){
            if (get_class($item) == 'Slide'){                
                $this->createForSlide($item->id, $start, $filter_array, $end);
            }
        }
    }
    
    public function getFullDeckStream($deck_revision_id, $month, $filter = false, $portion){
        if (!$month){
            $month = date("Y-m");
            $month.='-01';
        }
        $end = date($month); //1st day of current month
        $start = $end;
        switch ($portion){
            default :                
                break;
            case '1' :                
                $end_str = strtotime($end.' +1 month '); //last day of current month
                $end = date('Y-m-d',$end_str); //last day of month 
                $start_str = strtotime($end.' -7 day'); //last weak 
                $start = date('Y-m-d', $start_str);
                break;
            case '2' :
                $end_str = strtotime($end.' +1 month -7 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($end.' -7 day');
                $start = date('Y-m-d', $start_str);
                
                break;
            case '3' :
                $end_str = strtotime($end.'+1 month -14 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($end.' -7 day');
                $start = date('Y-m-d', $start_str);
                break;
            case '4' :
                $end_str = strtotime($end.'+1 month -21 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($month);
                $start = date('Y-m-d', $start_str);
                break;
        }
        if ($filter == false) {
            $filter_array = array('1','1','1','1'); 
        }else{
            $filter_array = explode(',',$filter);
        }
        $filter_array[0] = '1';
        $deck = new Deck();
        $deck->id = $deck_revision_id;
        $deck->deck_id = $deck->getBasicID();
        $deck->content = $deck->fetchDeckContentLite();
        $this->createForDeck($deck->deck_id, $start, $filter_array, $end);
        foreach ($deck->content as $item){
            if (get_class($item) == 'Deck'){
                if ($filter == false) {
                    $filter_array = array('1','1','1','1'); 
                }else{
                    $filter_array = explode(',',$filter);
                }
                $filter_array[0] = '1';
                $this->getFullDeckStream($item->id, $month, $filter, $portion);
            }else{
                if ($filter == false) {
                    $filter_array = array('1','1','1','1'); 
                }else{
                    $filter_array = explode(',',$filter);
                }
                $this->getSlideStream($item->id, $month, $filter, $portion);
            }
        }      
    }
    public function getSlideStream($slide_revision_id, $month, $filter = false, $portion){
        if (!$month){
            $month = date("Y-m");
            $month.='-01';
        }
        $end = date($month); //1st day of current month
        $start = $end;
        switch ($portion){
            default :                
                break;
            case '1' :                
                $end_str = strtotime($end.' +1 month '); //last day of current month
                $end = date('Y-m-d',$end_str); //last day of month 
                $start_str = strtotime($end.' -7 day'); //last weak 
                $start = date('Y-m-d', $start_str);
                break;
            case '2' :
                $end_str = strtotime($end.' +1 month -7 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($end.' -7 day');
                $start = date('Y-m-d', $start_str);
                
                break;
            case '3' :
                $end_str = strtotime($end.'+1 month -14 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($end.' -7 day');
                $start = date('Y-m-d', $start_str);
                break;
            case '4' :
                $end_str = strtotime($end.'+1 month -21 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($month);
                $start = date('Y-m-d', $start_str);
                break;
        }
        if ($filter == false) {
            $filter_array = array('0','1','1','1'); 
        }else{
            $filter_array = explode(',',$filter);
        } 
        $this->createForSlide($slide_revision_id, $start, $filter_array, $end);
    }
    public function getUserStream($user_id, $month, $filter = false, $portion){
        if (!$month){
            $month = date("Y-m");
            $month.='-01';
        }
        if (!$month){
            $month = date("Y-m");
            $month.='-01';
        }
        $end = date($month); //1st day of current month
        $start = $end;
        switch ($portion){
            default :                
                break;
            case '1' :                
                $end_str = strtotime($end.' +1 month '); //last day of current month
                $end = date('Y-m-d',$end_str); //last day of month 
                $start_str = strtotime($end.' -7 day'); //last weak 
                $start = date('Y-m-d', $start_str);
                break;
            case '2' :
                $end_str = strtotime($end.' +1 month -7 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($end.' -7 day');
                $start = date('Y-m-d', $start_str);
                
                break;
            case '3' :
                $end_str = strtotime($end.'+1 month -14 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($end.' -7 day');
                $start = date('Y-m-d', $start_str);
                break;
            case '4' :
                $end_str = strtotime($end.'+1 month -21 day');
                $end = date('Y-m-d',$end_str);
                $start_str = strtotime($month);
                $start = date('Y-m-d', $start_str);
                break;
        }
        if ($filter == false) {
            $filter_array = array('1','1','0','1','1','1','1'); 
        }else{
            $filter_array = explode(',',$filter);
        } 
        $this->createForUser($user_id, $start, $filter_array, $end);
    }
    public function createForUser($user_id, $start, $filter_array, $end, $keywords = ''){        
              
        $this->getUserRegistration($user_id, $start, $end);  
        if ($filter_array[0] == '1'){
            $this->getFollowDeck($user_id, false, $start, $end, $keywords);
            $this->getFollowSlide($user_id, false, $start, $end, $keywords);
            $this->getFollowUser($user_id, $start, $end, $keywords);
        }
        if ($filter_array[1] == '1'){
            $this->getCreateDeck($user_id, false, $start, $end, $keywords);            
        }
        if ($filter_array[2] == '1'){
            $this->getCreateSlide($user_id, false, $start, $end, $keywords);
        }
        if ($filter_array[3] == '1'){
            $this->getTranslateSlideRevision($user_id, false, $start, $end, $keywords);
        }
        if($filter_array[4] == '1'){
            $this->getCommentSlideRevision($user_id, false, $start, $end, $keywords);
            $this->getCommentDeckRevision($user_id, false, $start, $end, $keywords);
        }   
        if ($filter_array[5] == '1'){
            $this->getCreateQuestion($user_id, false, $start, $end, $keywords);
            $this->getCreateQuestionRevision($user_id, $start, $end, $keywords);
        }
        if ($filter_array[6] == '1'){
            $this->getAnswerTest($user_id, false, $start, $end, $keywords);
            //$this->getCreateTest($user_id, false, $month);
        }      
            
    }
    public function createForSlide($slide_revision_id, $start, $filter_array, $end){
        $slide = new Slide();
        $slide->id = $slide_revision_id;
        $slide_id = $slide->getBasicId();
        if ($filter_array[0] == '1'){
            $this->getCreateSlide(false, $slide_id, $start,$end);
        }
        if ($filter_array[1] == '1'){
            $this->getFollowSlide(false, $slide_id, $start,$end);
        }        
        if ($filter_array[2] == '1'){
            $this->getCommentSlideRevision(false, $slide_id, $start,$end);
        }        
    }
    public function createForDeck($deck_id, $start, $filter_array, $end){
        
        if ($filter_array[0] == '1'){
            $this->getCreateDeck(false, $deck_id, $start, $end);
        }
        if ($filter_array[1] == '1'){
            $this->getFollowDeck(false, $deck_id, $start, $end);
        }        
        if ($filter_array[2] == '1'){
            $this->getCommentDeckRevision(false, $deck_id, $start, $end);
        }
        if ($filter_array[3] == '1'){
            $this->getTranslateDeck(false, $deck_id, $start, $end);
        }
//        if ($filter_array[2] == '1'){
//            $this->getAnswerTest(false, $deck_id, $month);
//        }
    }
    public function getSelfRegistration($user_id, $start, $end){
        $this->initConnection();
        if ($start && $end){
            $row = $this->dbGetRow('SELECT * FROM users WHERE id=:id AND registered >= "' . $start . '" AND registered < "' . $end . '"',array('id' => $user_id));
        }else{
            $row = $this->dbGetRow('SELECT * FROM users WHERE id=:id',array('id' => $user_id));
        }
        if (count($row)){
            $user = new User;
            $user->id = $row['id'];
            $user->username = $row['username'];
            $object = NULL;
            $registration = new Activity($row['registered'], $user, 'self_registered_in', $object);
            $this->activities[] = $registration;
        }        
    }
    public function getUserRegistration($user_id, $start, $end){
        $this->initConnection();
        $row = $this->dbGetRow('SELECT * FROM users WHERE id=:id AND registered >= "' . $start . '" AND registered < "' . $end . '"',array('id' => $user_id));
        if (count($row)){
            $user = new User;
            $user->id = $row['id'];
            $user->username = $row['username'];
            $object = NULL;
            $registration = new Activity($row['registered'], $user, 'registered_in', $object);
            $this->activities[] = $registration;
        }        
    }
    public function getFollowSlide($user_id = false, $slide_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $slide_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                $result = $this->dbQuery('SELECT * FROM subscription WHERE user_id=:user_id AND item_type="slide" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){
                        $slide = new Slide();
                        $slide->slide_id = $row['item_id'];                        
                        $slide->createFromIDLite($slide->getLastRevisionID());
                        $slide->title = $slide->getTitle();
                        if ($slide->title=="") $slide->title="Untitled";
                        $activity = new Activity($row['timestamp'], $user, 'followed_slide', $slide);
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $result = $this->dbQuery('SELECT * FROM subscription WHERE item_id=:slide_id AND item_type="slide" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('slide_id' => $slide_id));
                if (count($result)){
                    $slide = new Slide();
                    $slide->slide_id = $slide_id;
                    $slide_revision_id = $slide->getLastRevisionID();
                    $slide->createFromIDLite($slide_revision_id);
                    $slide->title = $slide->getTitle();
                    if ($slide->title=="") $slide->title="Untitled";
                    foreach($result as $row){
                        $user = new User();
                        $user->id = $row['user_id'];
                        $user->getUsername();
                        $activity = new Activity($row['timestamp'], $user, 'followed_slide', $slide);
                        $activities[] = $activity;
                    }
                }
            }            
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }        
    }
    public function getFollowDeck($user_id = false, $deck_id = false, $start, $end, $keywords = ''){        
        if ($user_id != false || $deck_id != false){            
            $this->initConnection();
            $activities = array();
            $result = array();           
            if ($user_id){ 
                $result = $this->dbQuery('SELECT * FROM subscription WHERE user_id=:user_id AND item_type="deck" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    $user = new User();
                    $user->id = $user_id;
                    $user->getUsername();
                    foreach($result as $row){
                        $deck = new Deck();
                        $deck->deck_id = $row['item_id'];
                        $deck->id = $deck->getLastRevisionID();
                        $deck->title = $deck->getTitle();                        
                        if ($deck->title=="") $deck->title="Untitled";
                        $deck->slug_title = $deck->sluggify($deck->title);
                        $activity = new Activity($row['timestamp'], $user, 'followed_deck', $deck );
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $result = $this->dbQuery('SELECT * FROM subscription WHERE item_id=:deck_id AND item_type="deck" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('deck_id' => $deck_id));
                if (count($result)){
                    $deck = new Deck();
                    $deck->deck_id = $deck_id;
                    $deck->id = $deck->getLastRevisionID();
                    $deck->title = $deck->getTitle();                    
                    if ($deck->title=="") $deck->title="Untitled";
                    $deck->slug_title = $deck->sluggify($deck->title);
                    foreach($result as $row){
                        $user = new User();
                        $user->id = $row['user_id'];
                        $user->getUsername();
                        $activity = new Activity($row['timestamp'], $user, 'followed_deck', $deck);
                        $activities[] = $activity;
                    }
                }
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }        
    }
    
    public function getFollowUser($user_id, $start, $end, $keywords = ''){
        $this->initConnection();
        $activities = array();
        $result = array();
        $user = new User();
        $user->id = $user_id;
        $user->getUsername();
        $result = $this->dbQuery('SELECT * FROM subscription WHERE user_id=:user_id AND item_type="user" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
        if (count($result)){
            foreach($result as $row){
                $item = new User();
                $item->id = $row['item_id'];
                $item->getUsername();
                $activity = new Activity($row['timestamp'], $user, 'followed_user', $item);
                $activities[] = $activity;
            }                    
        }
        if (strlen($keywords))
            $result = $this->dbQuery('SELECT * FROM subscription INNER JOIN users ON subscription.user_id = users.id WHERE subscription.item_id=:user_id AND subscription.item_type="user" AND MATCH (users.username) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
        else
            $result = $this->dbQuery('SELECT * FROM subscription WHERE item_id=:user_id AND item_type="user" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
        if (count($result)){
            foreach($result as $row){
                $item = new User();
                $item->id = $row['user_id'];
                $item->getUsername();
                $activity = new Activity($row['timestamp'], $item, 'followed_user', $user);
                $activities[] = $activity;
            }                    
        }
        if (count($activities)){
            foreach ($activities as $activity){
                $this->activities[] = $activity; 
            }
        }
    }
    public function getTranslateDeck($user_id = false, $deck_id = false, $start, $end, $keywords=''){
        if ($user_id != false || $deck_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($deck_id){
                $deck = new Deck();
                $deck->deck_id = $deck_id;
                $deck->id = $deck->getLastRevisionID();
                $deck->title = $deck->getTitle();
                $deck->language = $deck->getLanguage();
                if ($deck->title=="") $deck->title="Untitled";
                $deck->slug_title = $deck->sluggify($deck->title);
                $result = $this->dbQuery('SELECT * FROM deck WHERE translated_from=:deck_id AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('deck_id' => $deck_id));
                if (count($result)){
                    foreach ($result as $row){
                        $user = new User();
                        $user->id = $row['user_id'];
                        $user->getUsername();
                        $translation = new Deck();
                        $translation->deck_id = $row['id'];
                        $translation->id = $translation->getLastRevisionID();
                        $translation->title = $translation->getTitle();
                        if ($translation->title=='') $translation->title='Untitled';
                        $translation->slug_title = $translation->sluggify($translation->title);
                        $translation->language = $translation->getLanguage();                        
                        $activity = new Activity( $row['timestamp'], $user, 'translated_deck', $translation, $deck);
                        $activities[] = $activity;
                    }
                }
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }            
    }
    public function getCreateDeck($user_id = false, $deck_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $deck_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT * FROM deck_revision WHERE user_id=:user_id AND MATCH (deck_revision.title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT * FROM deck_revision WHERE user_id=:user_id AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){
                        $deck = new Deck();
                        $deck->id = $row['id'];
                        $deck->deck_id = $row['deck_id'];
                        $deck->title = $deck->getTitle(); 
                        $deck->language = $deck->getLanguage();
                        if ($deck->title=="") $deck->title="Untitled";
                        $deck->slug_title = $deck->sluggify($deck->title);
                        if (!$row['translated_from_revision']){
                            if ($row['based_on']){
                                $based_on = new Deck();
                                $based_on->id = $row['based_on'];
                                $based_on->title = $based_on->getTitle();
                                if ($based_on->title=="") $based_on->title="Untitled";
                                $based_on->slug_title = $based_on->sluggify($based_on->title);
                                $activity = new Activity($row['timestamp'], $user, 'created_deck_revision', $deck, $based_on);
                            }else{
                                $activity = new Activity($row['timestamp'], $user, 'created_deck', $deck);
                            }                             
                        }else{
                            $based_on = new Deck();
                            $based_on->id = $row['translated_from_revision'];
                            $based_on->title = $based_on->getTitle();
                            if ($based_on->title=="") $based_on->title="Untitled";
                            $based_on->slug_title = $based_on->sluggify($based_on->title);
                            $activity = new Activity($row['timestamp'], $user, 'translated_deck', $deck, $based_on);
                        }
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $result = $this->dbQuery('SELECT * FROM deck_revision WHERE deck_id=:deck_id AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('deck_id' => $deck_id));
                if (count($result)){
                    foreach($result as $row){
                        $deck = new Deck();
                        $deck->deck_id = $deck_id;
                        $deck->id = $row['id'];
                        $deck->title = $deck->getTitle();
                        $deck->language = $deck->getLanguage();
                        if ($deck->title=="") $deck->title="Untitled";
                        $deck->slug_title = $deck->sluggify($deck->title);
                        $user = new User();
                        $user->id = $row['user_id'];
                        $user->getUsername();
                        if ($row['translated_from_revision']){
                            $based_on = new Deck();
                            $based_on->id = $row['translated_from_revision'];
                            $based_on->deck_id = $based_on->getBasicID();
                            $based_on->language = $based_on->getLanguage();
                            $based_on->title = $based_on->getTitle();
                            $based_on->slug_title = $based_on->sluggify($based_on->title);
                            $activity = new Activity($row['timestamp'], $user, 'translated_deck_from', $deck, $based_on);
                        }else{
                            if (!$row['based_on']){
                                $activity = new Activity($row['timestamp'], $user, 'created_deck', $deck); 
                            }else{
                                $based_on = new Deck();
                                $based_on->id = $row['based_on'];
                                $based_on->deck_id = $based_on->getBasicID();
                                $based_on->language = $based_on->getLanguage();
                                $based_on->title = $based_on->getTitle();
                                $based_on->slug_title = $based_on->sluggify($based_on->title);
                                $activity = new Activity($row['timestamp'], $user, 'created_deck_revision', $deck, $based_on); 
                            }
                        }                                                                                        
                        $activities[] = $activity;
                    }
                }                
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }               
            }
        }            
    }
   public function getTranslateSlide($user_id = false, $slide_revision_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $slide_revision_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT * FROM slide_revision WHERE user_id=:user_id AND translated_from_revision IS NOT NULL AND MATCH (slide_revision.content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT * FROM slide_revision WHERE user_id=:user_id AND translated_from_revision IS NOT NULL AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){
                        $based_on = new Slide();
                        $based_on->id =$row['translated_from_revision'];
                        $based_on->slide_id = $based_on->getBasicId();
                        $based_on->language = $based_on->getLanguage();
                        $slide = new Slide();
                        $slide->createFromIDLite($row['id']);
                        if ($slide->title=="") $slide->title="Untitled";
                        $slide->language = $slide->getLanguage();
                        $activity = new Activity($row['timestamp'], $user, 'translated_slide', $slide, $based_on);                            
                        $activities[] = $activity;
                    }                        
                }                    
            }
        }else{
            $slide = new Slide();                
            $slide->createFromIDLite($slide_revision_id);
            $slide->title = $slide->getTitle();
            if ($slide->title=="") $slide->title="Untitled";
            $result = $this->dbQuery('SELECT * FROM slide_revision WHERE translated_from_revision=:slide_revision_id AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('slide_revision_id' => $slide_revision_id));
            if (count($result)){
                foreach ($result as $row){                        
                    $translation = new Slide();
                    $translation->id = $row['id'];
                    $translation->slide_id = $translation->getBasicID();
                    if ($translation->slide_id != $slide->slide_id){
                        $user = new User();
                        $user->id = $row['user_id'];
                        $user->getUsername();
                        $translation->createFromIDLite($translation->id);
                        $translation->title = $translation->getTitle();
                        if ($translation->title=='') $translation->title='Untitled';
                        $translation->language = $translation->getLanguage();
                        $activity = new Activity($translation->revisionTime, $user, 'translated_slide', $slide, $translation);
                        $activities[] = $activity;
                    }
                }
            }
        }
        if (count($activities)){
            foreach ($activities as $activity){
                $this->activities[] = $activity; 
            }
        }                    
    }
    public function getCreateSlide($user_id = false, $slide_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $slide_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT * FROM slide_revision WHERE user_id=:user_id AND based_on IS NULL AND translated_from_revision IS NULL AND MATCH (slide_revision.content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT * FROM slide_revision WHERE user_id=:user_id AND based_on IS NULL AND translated_from_revision IS NULL AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){                        
                        $slide = new Slide();
                        $slide->id = $row['id'];
                        $slide->slide_id = $slide->getBasicId();
                        $slide->id = $slide->getLastRevisionID();
                        $slide->createFromIDLite($slide->id);
                        $slide->title = $slide->getTitle();
                        if ($slide->title=="") $slide->title="Untitled";
                        $activity = new Activity($row['timestamp'], $user, 'created_slide', $slide);
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $result = $this->dbQuery('SELECT * FROM slide_revision WHERE slide=:slide_id AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('slide_id' => $slide_id));
                if (count($result)){
                    foreach($result as $row){                        
                        $slide = new Slide();
                        $slide->createFromIDLite($row['id']);                        
                        $slide->title = $slide->getTitle();
                        if ($slide->title=="") $slide->title="Untitled";
                        $user = new User();
                        $user->id = $row['user_id'];
                        $user->getUsername();
                        if ($row['translated_from_revision']){
                            $based_on = new Slide();
                            $based_on->id = $row['translated_from_revision'];
                            $based_on->slide_id = $based_on->getBasicId();
                            $based_on->language = $based_on->getLanguage();
                            $activity = new Activity($row['timestamp'], $user, 'translated_slide_from', $slide, $based_on);
                        }else{
                            if(!$row['based_on']){
                                $activity = new Activity($row['timestamp'], $user, 'created_slide', $slide); 
                            }else{
                                $based_on = new Slide();
                                $based_on->id = $row['based_on'];
                                $based_on->slide_id = $based_on->getBasicId();
                                $based_on->translator_id = $based_on->getTranslatorId();
                                if ($based_on->translator_id == $row['translator_id']){
                                    $activity = new Activity($row['timestamp'], $user, 'created_slide_revision', $slide);
                                }else{
                                    $activity = new Activity($row['timestamp'], $user, 'translated_slide_revision', $slide);
                                }
                            }                                                        
                        }                        
                        $activities[] = $activity;
                    }                    
                }                 
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }            
    }
    public function getCreateSlideRevision($user_id = false, $start, $end, $keywords = ''){
        if ($user_id != false){
            $user = new User();
            $user->id = $user_id;
            $user->getUsername();
            $this->initConnection();
            $activities = array();
            $result = array(); 
            if (strlen($keywords))
                $result = $this->dbQuery('SELECT * FROM slide_revision WHERE user_id=:user_id AND based_on IS NOT NULL AND translated_from_revision IS NULL AND MATCH (slide_revision.content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
            else
                $result = $this->dbQuery('SELECT * FROM slide_revision WHERE user_id=:user_id AND based_on IS NOT NULL AND translated_from_revision IS NULL AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
            if (count($result)){
                foreach($result as $row){
                    $slide = new Slide();
                    $slide->createFromIDLite($row['id']);
                    $slide->title = $slide->getTitle();
                    if ($slide->title=="") $slide->title="Untitled";
                    $slide->translator_id = $row['translator_id'];
                    $based_on = new Slide();
                    $based_on->id = $row['based_on'];
                    $based_on->translator_id = $based_on->getTranslatorId();
                    if ($slide->translator_id == $based_on->translator_id){
                        $activity = new Activity($row['timestamp'], $user, 'created_slide_revision', $slide);                            
                        $activities[] = $activity;
                    }
                }                    
            }            
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }            
    }
    public function getTranslateSlideRevision($user_id = false, $slide_revision_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $slide_revision_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT * FROM slide_revision WHERE user_id=:user_id AND based_on IS NOT NULL AND translated_from_revision IS NULL AND MATCH (slide_revision.content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT * FROM slide_revision WHERE user_id=:user_id AND based_on IS NOT NULL AND translated_from_revision IS NULL AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){
                        $slide = new Slide();
                        $slide->createFromIDLite($row['id']);
                        $slide->title = $slide->getTitle();
                        if ($slide->title=="") $slide->title="Untitled";
                        $slide->translator_id = $row['translator_id'];
                        $based_on = new Slide();
                        $based_on->id = $row['based_on'];
                        $based_on->translator_id = $based_on->getTranslatorId();
                        if ($slide->translator_id != $based_on->translator_id){
                            $activity = new Activity($row['timestamp'], $user, 'translated_slide_revision', $slide);                            
                            $activities[] = $activity;
                        }
                    }                    
                }
                if (count($activities)){
                    foreach ($activities as $activity){
                        $this->activities[] = $activity; 
                    }
                }
            }            
        }
    }
    public function getCommentSlideRevision($user_id = false, $slide_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $slide_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT * FROM comment INNER JOIN slide_revision ON comment.item_id = slide_revision.id WHERE comment.user_id=:user_id AND comment.item_type="slide" AND MATCH (slide_revision.content) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT * FROM comment WHERE user_id=:user_id AND item_type="slide" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){
                        $slide = new Slide();
                        $slide->createFromIDLite($row['item_id']);
                        $slide->title = $slide->getTitle();
                        if ($slide->title=="") $slide->title="Untitled";
                        $activity = new Activity($row['timestamp'], $user, 'commented_slide_revision', $slide);                            
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $result = $this->dbQuery('SELECT * FROM `comment` WHERE `item_id` IN (SELECT id FROM slide_revision WHERE slide=:slide_id) AND item_type="slide" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('slide_id' => $slide_id));
                if (count($result)){                    
                    foreach ($result as $row){
                        $slide = new Slide();
                        $slide->createFromIDLite($row['item_id']);
                        $slide->title = $slide->getTitle();
                        if ($slide->title == "") $slide->title='Untitled';
                        $user = new User();
                        $user->id = $row['user_id'];
                        $user->getUsername();
                        $activity = new Activity($row['timestamp'], $user, 'commented_slide_revision', $slide);                           
                        $activities[] = $activity;
                    }                    
                }               
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }
    }
    public function getCommentDeckRevision($user_id = false, $deck_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $deck_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT * FROM comment INNER JOIN deck_revision ON comment.item_id = deck_revision.id WHERE comment.user_id=:user_id AND comment.item_type="deck" AND MATCH (deck_revision.title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT * FROM comment WHERE user_id=:user_id AND item_type="deck" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){                    
                    foreach($result as $row){
                        $deck = new Deck();
                        $deck->id = $row['item_id'];
                        $deck->title = $deck->getTitle();
                        if ($deck->title=="") $deck->title="Untitled";
                        $deck->slug_title = $deck->sluggify($deck->title);
                        $activity = new Activity($row['timestamp'], $user, 'commented_deck_revision', $deck);                            
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $result = $this->dbQuery('SELECT * FROM `comment` WHERE `item_id` IN (SELECT id FROM deck_revision WHERE deck_id=:deck_id) AND item_type="deck" AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('deck_id' => $deck_id));
                if (count($result)){                    
                    foreach ($result as $row){
                        $deck = new Deck();
                        $deck->id = $row['item_id'];
                        $deck->title = $deck->getTitle();
                        if ($deck->title == "") $deck->title='Untitled';
                        $deck->slug_title = $deck->sluggify($deck->title);
                        $user = new User();
                        $user->id = $row['user_id'];
                        $user->getUsername();
                        $activity = new Activity($row['timestamp'], $user, 'commented_deck_revision', $deck);                           
                        $activities[] = $activity;
                    }                    
                }                
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }
    }
    public function getCreateQuestion($user_id = false, $question_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $question_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT * FROM questions WHERE user_id=:user_id AND based_on IS NULL AND MATCH (question) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT * FROM questions WHERE user_id=:user_id AND based_on IS NULL AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){
                        $question = new Question();
                        $question->question = $row['question'];
                        $text = new Slide();
                        $text->slide_id = $row['item_id'];
                        $text->createFromIDLite($text->getLastRevisionID());
                        $text->title = $text->getTitle();
                        if ($text->title=="") $text->title="Untitled";                        
                        $activity = new Activity($row['timestamp'], $user, 'created_question', $question, $text);                            
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $row = $this->dbGetRow('SELECT * FROM questions WHERE id=:question_id AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('question_id' => $question_id));
                if ($row['based_on']){
                    $activity = new Activity($row['timestamp'], $row['user_id'], 'created_question_revision', $question_id);                           
                } else{
                    $activity = new Activity($row['timestamp'], $row['user_id'], 'created_question', $question_id);
                } 
                $activities[] = $activity;
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }
    }
    public function getCreateQuestionRevision($user_id = false, $start, $end, $keywords = ''){
        if ($user_id){
            $user = new User();
            $user->id = $user_id;
            $user->getUsername();
            $this->initConnection();
            $activities = array();
            $result = array();
            if (strlen($keywords))
                $result = $this->dbQuery('SELECT * FROM questions WHERE user_id=:user_id AND based_on IS NOT NULL AND MATCH (question) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
            else
                $result = $this->dbQuery('SELECT * FROM questions WHERE user_id=:user_id AND based_on IS NOT NULL AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
            if (count($result)){
                foreach($result as $row){
                    $question = new Question();
                    $question->question = $row['question'];
                    $text = new Slide();
                    $text->slide_id = $row['item_id'];
                    $text->createFromIDLite($text->getLastRevisionID());
                    $text->title = $text->getTitle();
                    if ($text->title=="") $text->title="Untitled";
                    $activity = new Activity($row['timestamp'], $user, 'created_question', $question, $text);                            
                    $activities[] = $activity;
                }                    
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            } 
        }               
    }
   public function getCreateTest($user_id = false, $test_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $slide_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT * FROM user_tests WHERE user_id=:user_id AND MATCH (title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE)',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT * FROM user_tests WHERE user_id=:user_id AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){
                        $activity = new Activity($row['timestamp'], $user_id, 'created_test', $row['id']); 
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $row = $this->dbGetRow('SELECT * FROM user_tests WHERE id=:test_id AND timestamp >= "' . $start . '" AND timestamp < "' . $end . '"',array('test_id' => $test_id));
                $activity = new Activity($row['timestamp'], $row['user_id'], 'created_test', $test_id);
                $activities[] = $activity;                 
            }
            if (count($activities)){
                foreach ($activities as $activity){
                    $this->activities[] = $activity; 
                }
            }
        }            
    }
    public function getAnswerTest($user_id = false, $deck_revision_id = false, $start, $end, $keywords = ''){
        if ($user_id != false || $deck_revision_id != false){
            $this->initConnection();
            $activities = array();
            $result = array();
            if ($user_id){
                $user = new User();
                $user->id = $user_id;
                $user->getUsername();
                if (strlen($keywords))
                    $result = $this->dbQuery('SELECT max(testing.max_points) AS max_p, testing.item_id, testing.timestamp, wiki_app FROM testing INNER JOIN deck_revision ON testing.item_id = deck_revision.id WHERE testing.user_id=:user_id AND testing.type="auto" AND testing.max_points IS NOT NULL AND MATCH (deck_revision.title) AGAINST (\'' . $keywords . '*\' IN BOOLEAN MODE) GROUP BY testing.attempt_id',array('user_id' => $user_id));
                else
                    $result = $this->dbQuery('SELECT max(max_points) AS max_p, item_id, timestamp, wiki_app FROM testing WHERE user_id=:user_id AND type="auto" AND max_points IS NOT NULL AND DATE(timestamp) >= DATE("' . $start . '") AND DATE(timestamp) < DATE("' . $end . '") GROUP BY attempt_id',array('user_id' => $user_id));
                if (count($result)){
                    foreach($result as $row){
                        $test = new Deck();
                        $test->id = $row['item_id'];
                        $test->title = $test->getTitle();
                        if ($test->title=="") $test->title="Untitled";
                        $test->slug_title = $test->sluggify($test->title);
                        $text = Array();
                        $text['wiki_app'] = $row['wiki_app'];
                        $text['max_points'] = $row['max_p'];
                        $text['score'] = round($row['wiki_app'] / $row['max_p'] * 100 , 2).'%';
                        $activity = new Activity($row['timestamp'], $user, 'answered_test', $test, $text);                            
                        $activities[] = $activity;
                    }                    
                }
            }else{
                $result = $this->dbQuery('SELECT * FROM testing WHERE item_id=:deck_revision_id AND DATE(timestamp) >= DATE("' . $start . '") AND DATE(timestamp) < DATE("' . $end . '")',array('deck_revision_id' => $deck_revision_id));
                $test = new Deck();
                $test->id = $deck_revision_id;
                $test->title = $test->getTitle();
                if ($test->title=="") $test->title="Untitled";
                $test->slug_title = $test->sluggify($test->title);
                if (count($result)){                    
                    foreach ($result as $row){
                        $activity = new Activity($row['timestamp'], $row['user_id'], 'answered_test', $test, 'with points '.$row['wiki_app'].'/'.$row['max_points']);                         
                        $activities[] = $activity;
                    }                    
                }                
            }
            if (count($activities)){
                foreach ($activities as $activity){
                   $this->activities[] = $activity; 
                }
            }
        }
    }
    
    public function sort(){
        $array = $this->activities;
        foreach ($array as $key => $activity) {
            $timestamp[$key]  = $activity->timestamp;            
        }        
        array_multisort($timestamp, SORT_DESC, $array);
        $this->activities = $array;
    }
    
    //combine the same activities not in use
    public function collapse(){
        $array = $this->activities;
        $new_array = $array;
        $length = count($array) - 1;
        $i = 0;
        while ($i < $length - 1){
            while (($array[$i]->subject !== $array[$i + 1]->subject || $array[$i]->type !== $array[$i + 1]->type) && $i < $length - 1) {
                $new_array[] = $array[$i];
                $i++;
            }
            $j = 1;
            while($array[$i]->subject === $array[$i + 1]->subject && $array[$i]->type === $array[$i + 1]->type && $i < $length - 1){
                $i++;
                $j++;
            }
            if ($array[$i]->subject === $array[$i - 1]->subject && $array[$i]->type === $array[$i - 1]->type){
                $array[$i]->type = 'collapsed_'.$array[$i]->type;
                $array[$i]->text->j = $j;
                $new_array[] = $array[$i];
                $i++;
            }            
        }
        $this->activities = $new_array;
    }
    
}
?>
