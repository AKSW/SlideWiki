<?php
// simple deck class
class Deck extends Model {
	public $item_name = "deck";
	// revision id in database
	public $id;
	//deck id
	public $deck_id;
	//description of original deck, for instanace about its originator
	public $description;
	public $sources; //original sources of deck slides
	//the initial owner of a deck serie
	public $initiator;
	//aggregration of all slide contributors of the deck
	public $slide_contributors;
	//contains the latest revision id
	public $last_revision_id;
	// title
	public $title;
        //sluggified title for uri
        public $slug_title;
	public $revisionTime;
	// content
	public $content;
	//revision comment
	public $comment;
	//discussion comments
	public $comments;
	//the user which creates the revision
	public $user;
	//containts the size of deck -> number of slides
	public $size;
	// abstract
	public $abstract;
	public $questions;
	// tags
	public $tags;
	//visibility of the deck 0:invisible 1:visible
	public $visibility=0;
	//to get appeared in the featured presentation list
	public $is_featured;		
	//footer text
	public $footer_text;
	// position
	public $position = 0;
	//owner
	public $owner;
	public $popularity;
	// styles
	public $styles;
	// transitions
	public $transitions;	
	//revision list
	public $revisions;
	// subscriptions
	public $subscriptions;
	//usage list
	public $usage;
	//deck editors
	public $editors;
	public $default_theme=2;
	public $default_transition=4;
	public $slides;
	public $number_of_slides;
	//parent deck	
	public $parent_deck;
	//to determine whether we need to update all instances of a deck or not
	public $cascade_update = 0;
	//to determine if we need to save a deck as a new revision
	public $save_as_new_revision = 0;
	public $is_followed_by_current_user = 0;
	private $max_title_length = 30;
	private $tree_icon = 'static/img/folder.png';
        //not to show endless loops in search results
        public $loop = 'no';
        public $language;
        public $translated_from;
        public $translated_from_changed = false;
        public $translated_from_revision;
        public $translation_status;
        public $parent_language;
	
        public function __toString()
        {
            return $this->id;
        }
        
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	//input: deck_revision_id
	public function createFromID($id) {
		$this->initConnection ();
		
		// query here
		$res = $this->dbGetRow ( 'SELECT * FROM deck_revision WHERE id=:id', array ('id' => $id ) );
		$this->id = $id;
		$this->deck_id = $res ['deck_id'];
		$this->description=$this->getDescription();
		$this->initiator=$this->getInitiator();
		$this->title = trim($res ['title'])==''?'Untitled':$res ['title'];
                $this->slug_title = $this->sluggify($this->title);
		$this->popularity = $res ['popularity'];
		$this->default_theme = $res ['default_theme'];
		$this->default_transition = $res ['default_transition'];
		$this->revisionTime = $res ['timestamp'];
		$this->comment = $res ['comment'];
		$user = new User ();
		$user->createFromID ( $res ['user_id'] );
		$this->user = $user;
		$this->abstract = $res ['abstract'];
		$this->is_featured= $res ['is_featured'];
		$this->visibility= $res ['visibility'];
                $language = array();
                $language = $this->getLanguage();
                $this->language = $language;
                $this->translated_from = $this->getTranslatedFrom();
                $this->translated_from_revision = $this->getTranslatedFromRevision();
                $this->footer_text = isset($res ['footer_text'])?$res ['footer_text']:'';
		$this->content = $this->fetchDeckContent ();
                $this->tags = join ( ',', $this->getTags ( $id ) );
		$this->owner = $this->user; //owner of deck rev
		$this->last_revision_id = $this->getLastRevisionID ();
		$this->subscriptions = $this->getSubscriptions ();
	}
	public function createFromIDLite($id) {
		$this->initConnection ();
		
		// query here
		$res = $this->dbGetRow ( 'SELECT * FROM deck_revision WHERE id=:id', array ('id' => $id ) );		
		$this->id = $id;
		$this->deck_id = $res ['deck_id'];
		$this->title = trim($res ['title'])==''?'Untitled':$res ['title'];
                $this->slug_title = $this->sluggify($this->title);
		$this->popularity = $res ['popularity'];
		$this->abstract = $res ['abstract'];
		$this->is_featured= $res ['is_featured'];
		$this->visibility= $res ['visibility'];
                $language = array();
                $language = $this->getLanguage();
                $this->language = $language;
                $this->translated_from = $this->getTranslatedFrom();
                $this->translated_from_revision = $this->getTranslatedFromRevision();
		$this->default_theme = $res ['default_theme'];
		$this->default_transition = $res ['default_transition'];		
		$this->revisionTime = $res ['timestamp'];
		$user = new User ();
		$user->createFromID ( $res ['user_id'] );
		$this->user = $user;
		$this->owner = $this->user; //owner of deck rev	
		$this->initiator=$this->getInitiator();	
	}	
	public function create($based_on = NULL) {
		$this->deck_id = $this->dbInsert ( 'deck', array ('user_id' => $this->user->id,'language' => $this->language["id"].'-'.$this->language["name"],'translated_from' =>$this->translated_from, 'translated_from_revision' =>$this->translated_from_revision ) );
		$this->owner=$this->user;
		$this->initiator=$this->user;
		$this->commit ($based_on);
		//adds an empty slide to deck
		$slide = new Slide ();
		$slide->user = $this->user;
		$slide->content = "<h2>new slide</h2>";
                $slide->language = $this->language;
                $slide->translation_status = 'original';
		$slide->create ();
		$this->addContent ( array ($slide ) );
		return $this->deck_id;
	}
	public function getPreviousRevisionID() {
		$prev_id = $this->dbGetOne ( 'SELECT id 
									FROM deck_revision 
									WHERE deck_id=:deck_id AND id < :id 
									ORDER BY timestamp 
									DESC LIMIT 1', 
									array ('deck_id' => $this->deck_id, 'id' => $this->id) );
		return $prev_id;
	}	
	public function commit($based_on = NULL) {
		if (empty ( $this->default_theme )) {
			$this->default_theme = 2;
		}
		if (empty ( $this->default_transition )) {
			$this->default_transition = 4;
		}
		$old_id=$this->id; 
		$old_owner_id=  $this->owner->id;           
		$this->id = $this->dbInsert ( 'deck_revision', array ('deck_id' => $this->deck_id, 'title' => $this->title, 'user_id' => $this->user->id, 'abstract' => $this->abstract, 'footer_text' => $this->footer_text, 'comment' => $this->comment, 'visibility' => $this->visibility, 'based_on' => $based_on, 'default_theme' => $this->default_theme, 'default_transition'=>$this->default_transition ) );
		if (count ( $this->content ))
			$this->addContent ( $this->content );
		//copy the user groups into the new deck revision
		$groups = $this->dbQuery ( 'SELECT * FROM user_group WHERE deck_revision_id=:id', array ('id' => $old_id) );
		if (! empty ( $groups )) {
			foreach ( $groups as $group ) {
				$this->dbInsert ( 'user_group', array('deck_revision_id'=>$this->id, 'user_id'=>$group ['user_id'], 'category'=>$group ['category']));
			}
		}	
		//add owner to the editor list as well
		if($this->user->id!=$old_owner_id)
			$this->dbInsert ( 'user_group', array('deck_revision_id'=>$this->id, 'user_id'=>$old_owner_id, 'category'=>'editor'));
		$user = new User ();
                $user->id = $this->user->id;
                $user->getUsername();
		$this->revisionTime = $this->getRevisionTime();
                
                $activity = new Activity($this->revisionTime, $user, 'created_deck_revision', $this);
                
		return $this->id;
	}	
	//content: an array including slide or deck objects
	public function addContent($content) {
		if(!count($content))
			return 0;
		foreach ( $content as $element ) {
			if ($element->position) {
				$this->dbQuery ( 'UPDATE deck_content SET position=position+1 WHERE deck_revision_id=:id AND position>=:position ORDER BY position DESC', array ('id' => $this->id, 'position' => $element->position ) );
			} else {
				$element->position = $this->dbGetOne ( 'SELECT MAX(position) FROM deck_content WHERE deck_revision_id=' . $this->id );
				//for the first child position is 1 but for others is ++
				if (! $element->position)
					$element->position = 1;
				else
					$element->position = $element->position +1;
			}
			$this->content [] = $element;
			$this->dbInsert ( 'deck_content', array ('deck_revision_id' => $this->id, 'item_type' => $element->item_name, 'item_id' => $element->id, 'position' => $element->position ) );
		}

	}
        public function getRevisionTime(){
            $this->initConnection();
            $res = $this->dbGetOne('SELECT timestamp FROM deck_revision WHERE id=:id',array('id' => $this->id));
            return $res;
        }
        public function translateContent($user_id, $language){           
            if (!count($this->content)){                
                return false;
            }else{
                $new_content = array();
                foreach($this->content as $element){                    
                    if ($element->item_name == 'deck'){
                        echo 'deck '.$element->id;
                        $new_element = new Deck();
                        $element->visibility = 0; 
                        $element->user->id = $user_id;  
                        $new_element_id = $element->translate($language);
                        $new_element->createFromID($new_element_id); 
                        $new_content[] = $new_element;
                        echo ' done;';
                    }else{
                        echo 'slide '.$element->id;
                        $new_element = new Slide();
                        $element->user->id = $user_id;
                        $new_element_id = $element->translate($language);
                        $new_element->createFromID($new_element_id); 
                        $new_content[] = $new_element;
                        echo ' done;';
                    }                       
                }                
                $this->content = $new_content;
                $this->dbQuery("DELETE FROM deck_content WHERE deck_revision_id=:id",array('id'=>$this->id));
                $this->addContent($new_content);                
            } 
            return true;
        }
        public function setLanguage($language){
            $this->initConnection();
            $this->dbQuery('UPDATE deck SET language=:language WHERE id=:deck_id',array('language' => $language, 'deck_id' => $this->deck_id));
        }
        public function getTranslationStatus(){
            $this->translation_status = $this->dbGetOne('SELECT translation_status FROM deck_revision WHERE id=:id',array('id'=>$this->id));
            if ($this->translation_status){
                return $this->translation_status;
            }
            foreach($this->getSlidesLite() as $slide){
                if ($slide->translation_status=='google'){
                    return 'translated';
                }
            }
            return 'original';
        }
        public function getParentLanguage(){
            $this->initConnection();
            $result = array();
            $parent_id = $this->dbGetOne('SELECT translated_from_revision FROM deck_revision WHERE id=:id',array('id' => $this->id));
            if ($parent_id){
                $based_on = new Deck();
                $based_on->id = $parent_id;
                $based_on->deck_id = $based_on->getBasicID();
                $result = $based_on->getLanguage();
            }
            return $result;
            
        }
        public function getFutureId($language){
            if (empty ( $this->default_theme )) {
                    $this->default_theme = 2;
                }
                if (empty ( $this->default_transition )) {
                    $this->default_transition = 4;
                }
                $source_arr = $this->getLanguage();
                $source = $source_arr['id'];
                $this->setLanguageFull($source.'-'.$source_arr['name']);
                $target_arr = explode('-',$language);
                $target = $target_arr[0];
                $translated_from_revision = $this->id;
                $translations_array = array();
//              //check if translation already exists
                $translations_array = $this->getAllTranslations();
                $translated_from = $this->deck_id;                
                $continue = true;
                if(count($translations_array)){
                    $continue = false;
                    foreach($translations_array as $translation){
                        if ($translation['language']['id'] == $target){   //if there is a translation to the same language 
                            $new_deck = new Deck();
                            $new_deck->createFromIDLite($translation['last']);//to copy the title, abstract etc.
                            $deck_id = $translation['id'];                                
                            $based_on = $translation['last'];
                            $title = $new_deck->title;                            
                            $abstract = $new_deck->abstract;
                            $footer_text = $new_deck->footer_text;
                            $this->id = $this->dbInsert ( 'deck_revision', array ('deck_id' => $deck_id, 'title' => $title, 'user_id' => $this->user->id, 'abstract' => $abstract, 'footer_text' => $footer_text, 'comment' => $this->comment, 'visibility' => $this->visibility, 'based_on' => $based_on, 'default_theme' => $this->default_theme, 'default_transition'=>$this->default_transition,'translation_status'=>'in_progress','translated_from_revision'=>$translated_from_revision ) );
                            $groups = $this->dbQuery ( 'SELECT * FROM user_group WHERE deck_revision_id=:id', array ('id' => $based_on) );
                            if (! empty ( $groups )) {
                                    foreach ( $groups as $group ) {
                                            $this->dbInsert ( 'user_group', array('deck_revision_id'=>$this->id, 'user_id'=>$group ['user_id'], 'category'=>$group ['category']));
                                    }
                            }
                            $this->translateOneSlide($this->user->id,$language);
                            $this->dbQuery('UPDATE translation_cronjobs SET future_deck=:future_deck WHERE revision_id=:revision_id AND to_language=:language', array('future_deck'=>$this->id,'language'=>$language,'revision_id'=>$translated_from_revision));
                            return $this->id;                           
                        }else{ //there is no translations on the same language
                            $continue = true;
                        }
                    }                     
                }
                if($continue){
                    $translator = new LanguageTranslator('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
                    $abstract = ''; 
                    $title = '';
                    $footer_text = '';  
                    $q = $this->title;
                    $title = $translator->translate($q,$target,$source);                
                    $q = $this->abstract;
                    if ($q){
                        $abstract = $translator->translate($q,$target,$source);
                    }                
                    $q = $this->footer_text;
                    if ($q){
                        $footer_text = $translator->translate($q,$target,$source);
                    }

                    $based_on = $this->id;
                    $this->deck_id = $this->dbInsert ( 'deck', array ('user_id' => $this->user->id,'language' => $language,'translated_from' =>$translated_from, 'translated_from_revision' => $translated_from_revision ) );

                    $this->id = $this->dbInsert ( 'deck_revision', array ('deck_id' => $this->deck_id, 'title' => $title, 'user_id' => $this->user->id, 'abstract' => $abstract, 'footer_text' => $footer_text, 'comment' => $this->comment, 'visibility' => $this->visibility, 'based_on' => NULL, 'default_theme' => $this->default_theme, 'default_transition'=>$this->default_transition,'translation_status'=>'in_progress','translated_from_revision'=>$translated_from_revision ) );
                        //copy the user groups into the new deck revision
                    $groups = $this->dbQuery ( 'SELECT * FROM user_group WHERE deck_revision_id=:id', array ('id' => $translated_from_revision) );
                    if (! empty ( $groups )) {
                            foreach ( $groups as $group ) {
                                    $this->dbInsert ( 'user_group', array('deck_revision_id'=>$this->id, 'user_id'=>$group ['user_id'], 'category'=>$group ['category']));
                            }
                    }
                    $this->translateOneSlide($this->user->id,$language);
                    $this->dbQuery('UPDATE translation_cronjobs SET future_deck=:future_deck WHERE revision_id=:revision_id AND to_language=:language', array('future_deck'=>$this->id,'language'=>$language,'revision_id'=>$translated_from_revision));
                    $this->title = $title;
                    $this->slug_title = $this->sluggify($this->title);
                    return $this;
                }
        }
        public function getFirstSlide(){
            if (!count($this->content))
                return 0;
            $content = array();
            $content = $this->content;            
            $i=0;            
            if ($content[$i]->item_name=='slide'){
                return $content[$i];
            }else{
                $new_element = new Deck();
                $new_element->createFromID($content[0]->id);
                return ($new_element->getFirstSlide());
            }
            return 0;
        }
        public function translateOneSlide($user_id,$language){            
            $first_slide = $this->getFirstSlide();
            if (!$first_slide)
                return 0;
            $new_content = array();
            $slide = new Slide(); 
            $first_slide->user->id = $user_id;
            $new_slide_rev_id = $first_slide->translate($language);
            $slide = new Slide();
            $slide->createFromID($new_slide_rev_id);
            $slide->user->id = $user_id;
            $new_content[] = $slide; 
            $this->addContent($new_content);
        }
        public function translate_parent($language){
            $bool = $this->translateContent($this->user->id, $language);
            if ($bool){ 
                $this->dbQuery('UPDATE deck_revision SET translation_status = NULL WHERE id=:id',array('id'=>$this->id));
                return $this->id;
            }
            return false;
         }
         public function noNeedOfRevision($translation){
            $new_deck = new Deck();
            $new_deck->createFromIDLite($translation['last']) ;
            $new_editors = $new_deck->getEditors();
            if ($this->user->id == $new_deck->owner->id){
                return true;
            }
            foreach ($new_editors as $editor) {
                if ($this->user->id == $editor->id){ 
                    return true;
                }
            }
            return false;
         }
         public function checkTranslationsLoop($target){
             $parent_id = $this->getTranslatedFrom();
             if ($parent_id){
                $parent = new Deck();
                $parent->deck_id = $parent_id;
                $parent->language = $parent->getLanguage();
                if ($parent->language['id'] == $target){
                    return false;
                }else{
                    $parent->checkTranslationsLoop($target);
                } 
             }else{
                return true;
             } 
             return true;
         }
         public function translate($language){
                if (empty ( $this->default_theme )) {
                    $this->default_theme = 2;
                }
                if (empty ( $this->default_transition )) {
                    $this->default_transition = 4;
                }
                $source_arr = $this->getLanguage();
                if (is_array($source_arr)&& isset($source_arr['id'])){
                    $source = $source_arr['id'];
                }             
                $target_arr = explode('-',$language);
                $target = $target_arr[0];
                $translations_array = array();
                $translated_from_revision = $this->id;
//                //check if translation already exists
                $translations_array = $this->getAllTranslations();
                $continue = true;
                if(count($translations_array)){ //if there are other translations
                    $continue = false;
                    foreach($translations_array as $translation){
                        if ($translation['language']['id'] == $target){   //if there is a translation to the same language 
                            $this->removeFromQueue($this->id,$language);                   
                            $new_deck = new Deck();
                            $new_deck->createFromIDLite($translation['last']);//to copy the title, abstract etc.
                            $deck_id = $translation['id'];                                
                            $based_on = $translation['last'];
                            $title = $new_deck->title;
                            $abstract = $new_deck->abstract;
                            $footer_text = $new_deck->footer_text;
                            $this->id = $this->dbInsert ( 'deck_revision', array ('deck_id' => $deck_id, 'title' => $title, 'user_id' => $this->user->id, 'abstract' => $abstract, 'footer_text' => $footer_text, 'comment' => $this->comment, 'visibility' => $this->visibility, 'based_on' => $based_on, 'default_theme' => $this->default_theme, 'default_transition'=>$this->default_transition,'translation_status'=>'in_progress','translated_from_revision'=>$translated_from_revision ) );
                            $groups = $this->dbQuery ( 'SELECT * FROM user_group WHERE deck_revision_id=:id', array ('id' => $based_on) );
                            if (! empty ( $groups )) {
                                    foreach ( $groups as $group ) {
                                            $this->dbInsert ( 'user_group', array('deck_revision_id'=>$this->id, 'user_id'=>$group ['user_id'], 'category'=>$group ['category']));
                                    }
                            }
                            $this->translateContent($this->user->id, $language);
                            $this->dbQuery('UPDATE deck_revision SET translation_status = NULL WHERE id=:id',array('id'=>$this->id));
                            $this->dbQuery('UPDATE translation_cronjobs SET future_deck=:future_deck WHERE revision_id=:revision_id AND to_language=:language', array('future_deck'=>$this->id,'language'=>$language,'revision_id'=>$translated_from_revision));
                            return $this->id;
                        }else{ //there is no translations on the same language
                            $continue = true;
                        }
                    }                    
                }
                if ($continue){ //create new deck                   
                    $this->language = $language;
                    $this->translated_from = $this->deck_id;
                    $this->translated_from_revision = $this->id;
                    
                    $translator = new LanguageTranslator('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
                    $abstract = ''; 
                    $title = '';
                    $footer_text = '';  
                    $q = $this->title;
                    $title = $translator->translate($q,$target,$source);
                    if ($title != -1){
                        $q = $this->abstract;
                        if ($q){
                            $abstract = $translator->translate($q,$target,$source);
                        }                
                        $q = $this->footer_text;
                        if ($q){
                            $footer_text = $translator->translate($q,$target,$source);
                        }
                        $based_on = $this->id;
                        $this->deck_id = $this->dbInsert ( 'deck', array ('user_id' => $this->user->id,'language' => $this->language,'translated_from' =>$this->translated_from, 'translated_from_revision' => $this->translated_from_revision ) );                    
                        $this->id = $this->dbInsert ( 'deck_revision', array ('deck_id' => $this->deck_id, 'title' => $title, 'user_id' => $this->user->id, 'abstract' => $abstract, 'footer_text' => $footer_text, 'comment' => $this->comment, 'visibility' => $this->visibility, 'based_on' => NULL, 'default_theme' => $this->default_theme, 'default_transition'=>$this->default_transition,'translation_status'=>'in_progress','translated_from_revision'=>$translated_from_revision ) );

                            //copy the user groups into the new deck revision
                        $groups = $this->dbQuery ( 'SELECT * FROM user_group WHERE deck_revision_id=:id', array ('id' => $this->translated_from_revision) );
                        if (! empty ( $groups )) {
                                foreach ( $groups as $group ) {
                                        $this->dbInsert ( 'user_group', array('deck_revision_id'=>$this->id, 'user_id'=>$group ['user_id'], 'category'=>$group ['category']));
                                }
                        }                    
                        $this->translateContent($this->user->id, $language);
                        $this->dbQuery('UPDATE translation_cronjobs SET future_deck=:future_deck WHERE revision_id=:revision_id AND to_language=:language', array('future_deck'=>$this->id,'language'=>$language,'revision_id'=>$this->translated_from_revision));
                        $this->dbQuery('UPDATE deck_revision SET translation_status = NULL WHERE id=:id',array('id'=>$this->id));
                        return $this->id;
                    }else{
                        return -1;
                    }                    
                }
         }
        public function remove_progress_revision(){
            $this->dbQuery("DELETE FROM deck_revision WHERE id=:id",array('id'=>$this->id));
        }
        public function removeFromQueue($id,$language){
            $this->dbQuery('DELETE FROM translation_cronjobs WHERE revision_id=:id AND to_language=:language', array('id'=>$id, 'language'=>$language));
        }
        public function addToQueue($language){
            $tasks = array();
            $ids = array();
            $tasks = $this->dbQuery('SELECT * FROM translation_cronjobs');
            foreach ($tasks as $task){
                $ids[] = $task['revision_id'].$task['to_language'];
            }
            $new_task = $this->id.$language;
            if (!in_array($new_task, $ids)){
                $this->dbInsert('translation_cronjobs', array('revision_id'=>$this->id,'to_language'=>$language ));
                return true;
            } else {
                return false;
            }      
        }
        public function getTranslatedTo($flag = false){
            static $result = array();
            static $language_ids = array();
            if ($flag){
                $result = array();
                $language_ids = array();
            }
            $children = $this->dbQuery('SELECT id, language FROM deck WHERE translated_from=:id ORDER BY timestamp DESC', array('id' => $this->deck_id));
            if(count($children)){
                foreach ($children as $deck){
                    $child_obj = new Deck();
                    $child_obj->deck_id = $deck['id'];
                    $language_array = explode('-',$deck['language']);
                    $language['id'] = $language_array[0];
                    $language['name'] = $language_array[1];
                    if (!in_array($language['id'],$language_ids)){
                        $child = array();
                        $child['id'] = $deck['id'];
                        $child['language']['id'] = $language['id'];
                        $child['language']['name'] = $language['name'];                        
                        $child['last'] = $child_obj->getLastRevisionID();
                        $deck_for_title = new Deck();
                        $deck_for_title->id = $child['last'];
                        $deck_for_title->title = $deck_for_title->getTitle();                        
                        $child['slug_title'] = $child_obj->sluggify($deck_for_title->title);
                        $language_ids [] = $language['id'];
                        $result[] = $child;
                    }                
                    $child_obj->getTranslatedTo();
                }
            }
            return $result;
        }
        //check if the deck contains the slide revision (subdecks are considered)
        public function isSlideIn($slide_id){
            $this->initConnection();
            $slides = $this->getSlidesIds();
            if (in_array($slide_id, $slides)){
                return true;
            }else{
                return false;
            }
        }
        public function getTranslatedFromChanged(){            
            $deck = new Deck();
            $parent_id = $this->getTranslatedFromRevision();
            if ($parent_id){
                $deck->createFromIDLite($parent_id);
                if ($deck->id != $deck->getLastRevisionID()){
                    $this->translated_from_changed = true;
                }
            }
        }
        public function getAllTranslations($slide_rev_id = 0){
            $this->initConnection();
            $result = array(); 
            $complete_result = array();
            $language_ids = array();
            $node = array();
            $slide_translations = array();
            $node['id'] = $this->deck_id;
            $node['language'] = $this->getLanguage();
            $node['last'] = $this->id;
            $node['slug_title'] = $this->slug_title;
            $result = $this->getTranslatedTo(true);
            foreach ($result as $lang){
                $language_ids [] = $lang['language']['id'];
            }
            if (!in_array($node['language']['id'],$language_ids)){
                $language_ids [] = $node['language']['id'];
                $result[] = $node;
            }            
            $visited = array();            
            foreach ($result as $node){
                $visited[] = $node['id'];
            }
            $parent = $this->translated_from;
            while ($parent){
                $parent_deck = new Deck();
                $parent_deck->id = $this->getTranslatedFromRevision();
                $parent_deck->deck_id = $parent;
                $parent_deck->language = $parent_deck->getLanguage();
                $parent_deck->translated_from = $parent_deck->getTranslatedFrom();
                $parent_deck->title = $parent_deck->getTitle();
                $parent_deck->slug_title = $parent_deck->sluggify($parent_deck->title);
                
                if (!in_array($parent_deck->language['id'], $language_ids)){
                    $parent_arr = array();
                    $parent_arr['id'] = $parent;
                    $parent_arr['language'] = $parent_deck->language;
                    $parent_arr['first'] = $parent_deck->id;
                    $parent_arr['last'] = $parent_deck->getLastRevisionID();
                    $parent_arr['slug_title'] = $parent_deck->slug_title;
                    $result[] = $parent_arr;
                    $language_ids [] = $parent_arr['language']['id'];
                }                
                foreach($parent_deck->getTranslatedTo(true) as $node){
                    if (!in_array($node['id'], $visited) && !in_array($node['language']['id'],$language_ids)){
                        $visited[] = $node['id'];
                        $result[] = $node;
                        $language_ids[] = $node['language']['id'];
                    }
                }
                $parent = $parent_deck->translated_from;
            }
            if ($slide_rev_id){
                $slide = new Slide();
                $slide->createFromID($slide_rev_id);
                $slide_translations = $slide->getAllTranslations();
                foreach ($result as $node){
                    
                    $language = $node['language']['id'];
                    $deck = new Deck();
                    $deck->createFromID($node['last']);
                    
                    foreach ($slide_translations as $slide){
                        if ($slide['language']['id'] == $language){
                            $slide_all_revisions = new Slide();
                            $slide_all_revisions->slide_id = $slide['id'];
                            $all_revisions = array();
                            $all_revisions = $slide_all_revisions->getThisSlideRevisions();
                            foreach ($all_revisions as $slide_rev){
                                if ($deck->isSlideIn($slide_rev['id'])){
                                    $node['slide'] = $deck->getSlidePropertiesById($slide_rev['id']);
                                    $node['slideLink'] = $node['slide']['link'];
                                }
                            }
                            $complete_result[] = $node;
                        }
                    }
                }
            }else {
                foreach ($result as $node){
                    $deck = new Deck();
                    $node['slide'] = $deck->getSlidePropertiesById(0);
                    $complete_result[] = $node;
                }                
            }
            
            return $complete_result;
        }
        //check if the deck contains the slide revision (subdecks are not considered)
        public function isSlideInThis($slide_rev){
            $this->initConnection();
            //$slide_id = $this->dbQuery("SELECT slide_revision.id FROM deck_content JOIN slide_revision ON deck_content.item_id = slide_revision.id WHERE deck_content.deck_revision_id = " . $this->id . " AND deck_content.item_type = 'slide'	AND slide_revision.slide = " . $slide_basic
            $result = false;
            if (count($this->content)){
                foreach ( $this->content as $index => $element ) {
                    if (get_class ( $element ) == "Slide") {
                            if ($element->id == $slide_rev){
                                $result = true;
                            }
                    } 
                }
            }            
            return $result;
        }
        //get slide properties for the deck-revision tree by slide revision id (even if the container deck is not known)
        public function getSlidePropertiesById($slide_rev){            
            //TODO : rewrite not to check all the children, if the slide was found
            $result = array();
            $result['slide_id'] = 0;
            $result['deck_id'] = 0;
            $result['position'] = 0;
            $result['link'] = '';
            if ($slide_rev){
                $this->initConnection();
                $children = array();
                $childrenArr = array();
                $children [] = $this->id;
                $childrenArr = explode ( '.', $this->getChildren () );
                foreach ( $childrenArr as $child_id ) {
                        if ($child_id > '')
                                $children [] = $child_id;
                }
                foreach ($children as $child_id){
                    $deck = new Deck();
                    $deck->createFromID($child_id);
                    if ($deck->isSlideInThis($slide_rev)){
                        $result['slide_id'] = $slide_rev;
                        $result['deck_id'] = $deck->id;
                        $slide_position = $this->dbGetOne('SELECT position FROM deck_content WHERE deck_revision_id=:deck_id AND item_type="slide" AND item_id=:slide_id LIMIT 1',array('deck_id'=>$deck->id,'slide_id'=>$slide_rev));
                        $result['position'] = $slide_position;
                        $result['link'] = 'tree-' . $result['deck_id'] . '-slide-' . $result['slide_id'] . '-' . $result['position'] . '-view';
                        return $result;
                    }
                }
            }
            return $result;
        }
        public function setLanguageFull($language){
            $this->initConnection();
            $this->setLanguage($language);
            foreach ($this->content as $element){
                $language_existed = $element->getLanguage();
                if (!$language_existed['id']){
                    $element->setLanguageFull($language);
                }
            }
        }
        public function getLanguage(){
            $this->initConnection ();
            $language=array();
            $language['id']='';
            $language['name']='';
            $default_language = $this->dbGetOne('SELECT language FROM deck WHERE id=:deck_id',array('deck_id' => $this->deck_id));
            if ($default_language){
                $language_arr = array();
                $language_arr = explode('-',$default_language);
                $language = array();
                $language['id'] = $language_arr[0];
                $language['name'] = $language_arr[1];
            }           
            return $language;            
        }
        
        public function getTranslatedFrom(){
            $this->initConnection();
            $translated_from = $this->dbGetOne('SELECT translated_from FROM deck WHERE id=:deck_id',array('deck_id' => $this->deck_id));
            return $translated_from;
        }
        public function getTranslatedFromRevision(){
            $this->initConnection();
            $translated_from = $this->dbGetOne('SELECT translated_from_revision FROM deck_revision WHERE id=:id',array('id' => $this->id));
            return $translated_from;
        }
	public function addUser($user,$category){
		$groups = $this->dbQuery ( 'SELECT * FROM user_group WHERE user_id=:user_id AND deck_revision_id=:id AND category=:category', array ('user_id'=>$user->id, 'id' => $this->id, 'category'=>$category) );
		if (empty ( $groups )) {
			$this->dbInsert ( 'user_group', array('deck_revision_id'=>$this->id, 'user_id'=>$user->id, 'category'=>$category));
		}		
	}
	public function removeUser($user,$category){
		$groups = $this->dbQuery ( 'DELETE FROM user_group WHERE user_id=:user_id AND deck_revision_id=:id AND category=:category', array ('user_id'=>$user->id, 'id' => $this->id, 'category'=>$category) );	
	}
	public function replaceWith($newdeck) {
		$this->dbQuery ( 'UPDATE  deck_content SET item_id=' . $newdeck . ' WHERE deck_revision_id=' . $this->parent_deck . ' AND position=' . $this->position );
	}
	//gets a new item (slide/deck) and replaces it with new one
	public function replaceContentWith($newId, $oldId, $type) {
		$this->dbQuery ( 'UPDATE  deck_content SET item_id=:new_item_id WHERE deck_revision_id=:deck_revision_id AND item_id=:old_item_id AND item_type=:type', array ('deck_revision_id' => $this->id, 'new_item_id' => $newId, 'old_item_id' => $oldId, 'type' => $type ) );
	}
	public function replaceContentByPosition($newId, $oldId, $type,$oldPosition) {
		$this->dbQuery ( 'UPDATE  deck_content SET item_id=:new_item_id WHERE deck_revision_id=:deck_revision_id AND item_id=:old_item_id AND position=:position AND item_type=:type', array ('deck_revision_id' => $this->id, 'new_item_id' => $newId, 'old_item_id' => $oldId, 'type' => $type, 'position' => $oldPosition ) );
	}
	//returns the usage of the deck
	public function getUsage() {
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT deck_revision.id, title, deck_revision.user_id, username
			FROM deck_content INNER JOIN deck_revision ON(deck_revision_id=deck_revision.id) INNER JOIN users ON(deck_revision.user_id=users.id)
			WHERE item_type="deck" AND item_id=' . $this->id . ' GROUP BY deck_revision.id ORDER BY deck_revision.timestamp DESC' ) as $row ) {
			$deck = new Deck ();
			$deck->createFromIDLite ( $row ['id'] );
			$res [] = $deck;
		}
		return $res;
	}
	public function getDescription() {
		$res = $this->dbGetRow('SELECT * FROM deck WHERE id=:id', array ('id' => $this->deck_id ));
		$this->description = $res['description'];
		return $res['description'];
	}
	public function getOriginalOwnerID() {
		$res = $this->dbGetRow('SELECT * FROM deck WHERE id=:id', array ('id' => $this->deck_id ));
		return $res['user_id'];
	}
	public function setDescription($desc) {
		$this->dbQuery ( 'UPDATE deck SET description=:desc WHERE id=:id', array ('id' => $this->deck_id, 'desc' =>$desc ) );
	}
	//returns the usage of the deck removing the usage by the owner of the deck + editors
	public function getUsageExceptUser($user_id, $ignore_owner=0) {
		$user = new User ();
		$user->createFromID($user_id);
		if(!$ignore_owner){
			$ignore_list= array($user_id);
		}else{
			//ignore the usage by owner 
			$ignore_list= array($user_id, $this->owner->id);
		}	
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT deck_revision.id, title, deck_revision.user_id, username
			FROM deck_content INNER JOIN deck_revision ON(deck_revision_id=deck_revision.id) INNER JOIN users ON(deck_revision.user_id=users.id)
			WHERE item_type="deck" AND item_id=' . $this->id . ' GROUP BY deck_revision.id ORDER BY deck_revision.timestamp DESC' ) as $row ) {
			$deck = new Deck (); 
			$deck->createFromIDLite ( $row ['id'] );
			if(!in_array($deck->owner->id, $ignore_list) && !$user->isMemberOfGroup($deck->id, 'editor')){
				$res [] = $deck;
			}	
		}
		return $res;
	}	
	public function getRevisions() {
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT deck_revision.id, timestamp, user_id, email, translated_from_revision FROM deck_revision INNER JOIN users ON(user_id=users.id) WHERE deck_id=' . $this->deck_id . ' ORDER BY timestamp DESC' ) as $row ) {
			$deck = new Deck ();
			$deck->createFromIDLite ( $row ['id'] );
                        $deck->parent_language = $deck->getParentLanguage();
                        $deck->getTranslatedFromChanged();
			$res [] = $deck;
		}
		return $res;
	}
	public function getLastRevisionID($user_id=NULL) {
            if($user_id)
                    return $this->dbGetOne ( 'SELECT id FROM deck_revision WHERE deck_id=:deck_id AND user_id=:user_id ORDER BY timestamp DESC LIMIT 1', array ('deck_id' => $this->deck_id, 'user_id'=>$user_id ) );
            else
                    return $this->dbGetOne ( 'SELECT id FROM deck_revision WHERE deck_id=:deck_id ORDER BY timestamp DESC LIMIT 1', array ('deck_id' => $this->deck_id ) );
	}
	public function getComments() {
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT c.id,title,c.timestamp,text FROM comment c INNER JOIN users u ON(user_id=u.id) WHERE item_type="deck" AND item_id=' . $this->id . ' ORDER BY timestamp DESC' ) as $row ) {
			$comment = new Comment ();
			$comment->createFromID ( $row ['id'] );
			$res [] = $comment;
		}
		return $res;
	}
	public function getAllRevComments() {
		$res = array ();
		$revisions=$this->getRevisions();
		foreach ($revisions as $rev)
			foreach ( $this->dbQuery ( 'SELECT c.id,title,c.timestamp,text FROM comment c INNER JOIN users u ON(user_id=u.id) WHERE item_type="deck" AND item_id=' . $rev->id . ' ORDER BY timestamp DESC' ) as $row ) {
				$comment = new Comment ();
				$comment->createFromID ( $row ['id'] );
				$res [] = $comment;
			}
		return $res;
	}	
	public function getSubdeckComments() {
		$res = array ();
		if(!$this->content){
			$this->content=$this->fetchDeckContentLite();
		}
		foreach ($this->content as $e){
			if(count($com=$e->getAllRevComments()))
				$res = array_merge ( $res, $com );			
			if($e->item_name == 'deck'){
				if(count($com=$e->getSubdeckComments()))
					$res = array_merge ( $res, $com );
			}
		}
		return $res;
	}	
	function getPosition() {
		if($this->parent_deck)
			return $this->dbGetOne ( 'SELECT position FROM deck_content WHERE item_type="deck" AND item_id=:item_id AND deck_revision_id=:deck_id ORDER BY item_id DESC LIMIT 1', array ('item_id' => $this->id,'deck_id'=>$this->parent_deck ) );
		else 
			return $this->dbGetOne ( 'SELECT position FROM deck_content WHERE item_type="deck" AND item_id=:item_id ORDER BY item_id DESC LIMIT 1', array ('item_id' => $this->id ) );	
	}
	//used for linking to the parent deck, goes thorough parent decks while usage is 1
	public function getLastOuterParent($deck_id) {
		$d=new Deck();
		$d->id=$deck_id;
		$usage=$d->getUsage();
		if(is_array($usage) && count($usage)==1){
			return $this->getLastOuterParent($usage[0]->id);
		}else{
			return $deck_id;
		}
	}	
	public function save() {
		return $this->dbQuery ( "UPDATE deck_revision SET title=:title,comment=:comment, abstract=:abstract, footer_text=:footer_text, visibility=:visibility, default_theme=:default_theme, default_transition=:default_transition WHERE id=:id", array ('id' => $this->id, 'title' => $this->title, 'comment' => $this->comment, 'abstract' => $this->abstract,'footer_text'=>$this->footer_text, 'visibility'=>$this->visibility, 'default_theme' => $this->default_theme, 'default_transition'=>$this->default_transition ) );
	}
	public function setTitle($title) {
		$this->title=$title;
		return $this->dbQuery ( "UPDATE deck_revision SET title=:title WHERE id=:id", array ('id' => $this->id, 'title' => $title ) );		
	}	
	public function getOwner() {
		return $this->owner;
	}
	public function getOwnerID(){
		$res = $this->dbGetRow ( 'SELECT * FROM deck_revision WHERE id=:id', array ('id' => $this->id ) );
		return $res['user_id'];
	}
	public function getEditors() {
		$editors = array ();
		$results = $this->dbQuery ( 'SELECT * FROM user_group WHERE deck_revision_id=:id AND category = "editor"', array ('id' => $this->id) );
		if (! empty ( $results )) {
			foreach ( $results as $result ) {
				$user=new User();
				$user->createFromID($result ['user_id']);
				array_push ($editors,$user);
			}
		}
		$this->editors=$editors;
		return $editors;		
	}
	public function getInitiator() {
		$res = $this->dbGetCol ( 'SELECT user_id FROM deck WHERE id=:id', array ('id' => $this->deck_id ) );
		$user = new User ();
		$user->createFromID ( $res [0] );
		return $user;
	}
	public function getNumberOfSlides() {
        if (!(count($this->content))) {
			$this->content=$this->fetchDeckContentLite();
        }
        $i=0;
		$slides = array ();
		
		foreach ( $this->content as $index => $element ) {
			if (get_class ( $element ) == "Slide") {
			$i++;
			} else if (get_class ( $element ) == "Deck") {
				$i = $i+ $element->getNumberOfSlides ();
			} else {
				die ( "some strange stuff in content: " . print_r ( $element ) );
			}
		}
		$this->size=$i;
		return $i;
	}
	public function getDirectSlides() {
		$this->content = $this->fetchDeckContent ();
		$slides = array ();
		
		foreach ( $this->content as $index => $element ) {
			if (get_class ( $element ) == "Slide" ) {
				$element->thumbnailContent = $element->getThumbnailContent ();
				$slides [] = $element;
			} 
		}	
		return $slides;	
	}
	//this gets all the related resources of a slide
	public function getSlidesFull() {
		$this->content = $this->fetchDeckContent ();
		$slides = array ();
		
		foreach ( $this->content as $index => $element ) {
			if (get_class ( $element ) == "Slide") {
				$element->contributors = $element->getContributors ();
				$element->thumbnailContent = $element->getThumbnailContent ();
				//check if it is followed by current user
				$element->is_followed_by_current_user = $this->user->isFollowing ( 'slide', $element->slide_id );
				$slides [] = $element;
			} else if (get_class ( $element ) == "Deck") {
				$slides = array_merge ( $slides, $element->getSlidesFull () );
			} else {
				die ( "some strange stuff in content: " . print_r ( $element ) );
			}
		}
		return $slides;
	}
	public function getSlides($empty_content=0) {
        if (!(count($this->content))) {
			$this->content = $this->fetchDeckContent ();
            }
		$slides = array ();
		foreach ( $this->content as $index => $element ) {
			if (get_class ( $element ) == "Slide") {
				$element->thumbnailContent = $element->getThumbnailContent ();
				$slides [] = $element;
			} else if (get_class ( $element ) == "Deck") {
				$slides = array_merge ( $slides, $element->getSlides () );
			} else {
				die ( "some strange stuff in content: " . print_r ( $element ) );
			}
		}
		//will empty the deck content for performance optimization
		if($empty_content)
			$this->content=array();
		return $slides;
	}
	public function getSlidesLite($empty_content=0) {
        if (!(count($this->content))) {
			$this->content = $this->fetchDeckContentLite ();
            }
		$slides = array ();
		foreach ( $this->content as $index => $element ) {
			if (get_class ( $element ) == "Slide") {
                                
				$element->thumbnailContent = $element->getThumbnailContent ();
				$slides [] = $element;
			} else if (get_class ( $element ) == "Deck") {
				$slides = array_merge ( $slides, $element->getSlidesLite (1) );
			} else {
				die ( "some strange stuff in content: " . print_r ( $element ) );
			}
		}
		//will empty the deck content for performanice optimization
		if($empty_content)
			$this->content=array();
		return $slides;
	}
        public function getSlidesIds(){
            if (!(count($this->content))) {
			$this->content = $this->fetchDeckContentLite ();
            }
		$slides = array ();
		foreach ( $this->content as $index => $element ) {
			if (get_class ( $element ) == "Slide") {
                            $slides [] = $element->id;
			} else if (get_class ( $element ) == "Deck") {
                            $slides = array_merge ( $slides, $element->getSlidesIds());
			} else {
                            die ( "some strange stuff in content: " . print_r ( $element ) );
			}
		}
		return $slides;
        }
	public function getSlidesByRange($from,$to) {
		$slides = $output= array ();
		$slides=$this->getSlidesLite();
		for ( $i=$from;$i<=$to;$i++ ) {
			if($i<count($slides)){
				$slides[$i]->owner = $slides[$i]->getOwner ();
				$slides[$i]->last_revision_id = $slides[$i]->getLastRevisionID ();
				$slides[$i]->subscribers = $slides[$i]->getSubscribers ();
				$slides[$i]->subscriptions = $slides[$i]->getSubscriptions ();
				$slides[$i]->title = $slides[$i]->getTitle ();
                                $slides[$i]->slug_title = $this->sluggify($slides[$i]->title);
				$slides[$i]->body = $slides[$i]->getBody ();
				$slides[$i]->position = $slides[$i]->getPosition ();				
				$slides[$i]->absolute_position=$i;
				$slides[$i]->contributors = $slides[$i]->getContributors ();
				$slides[$i]->description = $slides[$i]->getDescription ();
				$slides[$i]->initiator = $slides[$i]->getInitiator ();
                                $slides[$i]->translator = $slides[$i]->getTranslator();
                                $slides[$i]->language = $slides[$i]->getLanguage();
				$slides[$i]->is_followed_by_current_user = $this->user->isFollowing ( 'slide', $slides[$i]->slide_id );
				$slides[$i]->user=$this->user;
				$slides[$i]->is_editable_by_current_user=($slides[$i]->user->isMemberOfGroup($slides[$i]->deck->id, 'editor'))|| ($slides[$i]->user->isOwnerOfDeck($slides[$i]->deck->id));
				$output[]=$slides[$i];
			}
		}
		return $output;
	}

	public function getSlideContributors(){
            $contributors= array ();
            if(!count($this->slides)){
                    $this->slides=$this->getSlidesLite ();
            }
            //$this->slides=$this->getSlidesLite ();
        foreach ($this->slides as $slide){
                $tmp=$slide->getContributors();
                if (count($tmp)){
                    $contributors=array_merge($contributors,$tmp);
                }
                //echo $slide->id;var_dump($tmp);
        }
        $contributors=array_unique($contributors);
        $this->slide_contributors=$contributors;
        return $contributors; 		
	}
	public function getSlidesByRangeLite($from,$to) {
		$slides = $output= array ();
		$slides=$this->getSlidesLite();
		$this->slides=$slides;
		for ( $i=$from;$i<=$to;$i++ ) {
			if($i<count($slides)){
				$slides[$i]->absolute_position=$i;
				$slides[$i]->contributors = $slides[$i]->getContributors ();
				$slides[$i]->is_followed_by_current_user = $slides[$i]->user->isFollowing ( 'slide', $slides[$i]->slide_id );
				$output[]=$slides[$i];
			}
		}
		return $output;
	}	
        public function getFourSlides(){
            
            $slides = array();
            $res = array();
            $slides = $this->dbQuery ( 'SELECT * FROM deck_content WHERE deck_revision_id=:id AND item_type="slide" ORDER BY position LIMIT 4', array ('id' => $this->id ) );
            foreach ($slides as $slide){
                $slide_obj = new Slide();
                $slide_obj->createFromID($slide['item_id']);
                $slide_obj->thumbnailContent = $slide_obj->getThumbnailContent();
                $slide_obj->deck = new Deck();
                $slide_obj->deck->id = $this->id;
                $slide_obj->deck->title = $slide_obj->deck->getTitle();
                $slide_obj->deck->slug_title = $slide_obj->sluggify($slide_obj->deck->title);
                $res [] = $slide_obj;
            }
            return $res;            
        }
	//assign editor to all sub decks belonging to user or in which user has the editor role
	public function assignEditorToSubdecks($user,$editor=0) {
		$this->content = $this->fetchDeckContentLite();
		foreach ( $this->content as $element ) {
			 if (get_class ( $element ) == "Deck") {
			 	//assign editor
			 	if($editor){
			 		if(($element->owner->id==$editor->id)|| $editor->isMemberOfGroup($element->id, 'editor'))
						$element->addUser($user, 'editor');			 		
			 	}else{
			 		if(($element->owner->id==$this->owner->id)|| $this->owner->isMemberOfGroup($element->id, 'editor'))
						$element->addUser($user, 'editor');	
			 	}
				$element->assignEditorToSubdecks ($user,$editor);
			}
		}
	}
	//sets an array of editor users
	public function addEditors($editors) {
		foreach ( $editors as $editor ) {
			$this->addUser($editor, 'editor');
		}
	}	
	//remove editor from all sub decks belonging to user
	public function removeEditorFromSubdecks($user) {
		$this->content = $this->fetchDeckContentLite ();
		foreach ( $this->content as $element ) {
			 if (get_class ( $element ) == "Deck") {
			 	//assign editor
			 	if($element->owner->id==$this->owner->id)
					$element->removeUser($user, 'editor');
				$element->removeEditorFromSubdecks ($user);
			}
		}
	}
	public function getTags($id) {
		$res = array ();
		$tags = $this->dbQuery ( 'SELECT tag FROM tag WHERE item_type="deck" AND item_id=:id', array ('id' => $id ) );
		foreach ( $tags as $item ) {
			$res [] = html_entity_decode ( $item ['tag'] );
		}
		return $res;
	}
//	public function fetchContentSearch() {
//		$this->initConnection ();
//		
//		$deck = null;
//		$slide = null;
//		$res = array ();
//		
//		// get content
//		$deckContent = $this->dbQuery ( 'SELECT * FROM deck_content WHERE deck_revision_id=:id ORDER BY position', array ('id' => $this->id ) );
//		/*if (! $deckContent)
//			die ( "error querying db for deck content: " . mysql_error () );*/
//		
//		// parse content
//		foreach ( $deckContent as $item ) {
//			// if it's deck
//			if ($item ['item_type'] == 'slide') {
//				$slide = new Slide ();
//				$slide->id = ($item ['item_id']);
//				$slide->position = $item ['position'];
//				$slide->deck = $item ['deck_revision_id'];
//				$res_slides = $this->dbGetRow ( 'SELECT * FROM slide_revision WHERE id=:id', array ('id' => $slide->id ) );
//				$slide->content = $res_slides ['content'];
//				$res [] = $slide;
//			}
//		}
//		return $res;
//	
//	}
	public function fetchDeckContent() {
		$this->initConnection ();
		
		$deck = null;
		$slide = null;
		$res = array ();
		
		// get content
		$deckContent = $this->dbQuery ( 'SELECT * FROM deck_content WHERE deck_revision_id=:id ORDER BY position', array ('id' => $this->id ) );
		/*if (! $deckContent)
			die ( "error querying db for deck content: " . mysql_error () );*/
		
		// parse content
		foreach ( $deckContent as $item ) {
			// if it's deck
			if ($item ['item_type'] == 'deck') {
				$deck = new Deck ();
				$deck->createFromID ( $item ['item_id'] );
				$deck->position = $item ['position'];
				$res [] = $deck;
			} else {
				$slide = new Slide ();
				$slide->createFromID ( $item ['item_id'] );
				$slide->position = $item ['position'];
				$slide->deck = new Deck();
                                $slide->deck->id = $item ['deck_revision_id'];
				$slide->deck->title = $slide->deck->getTitle();
                                $slide->deck->slug_title = $slide->sluggify($slide->deck->title);
				$res [] = $slide;
			}
		}
		return $res;
	}

	public function fetchDeckContentLite() {
		$this->initConnection ();
		$deck = null;
		$slide = null;
		$res = array ();
		
		// get content
		$deckContent = $this->dbQuery ( 'SELECT * FROM deck_content WHERE deck_revision_id=:id ORDER BY position', array ('id' => $this->id ) );
		/*if (! $deckContent)
			die ( "error querying db for deck content: " . mysql_error () );*/
		// parse content
		foreach ( $deckContent as $item ) {
			// if it's deck
			if ($item ['item_type'] == 'deck') {
				$deck = new Deck ();
				$deck->createFromIDLite ( $item ['item_id'] );
				$deck->content = $deck->fetchDeckContentLite ();
				$deck->position = $item ['position'];
				$res [] = $deck;
			} else {
				$slide = new Slide ();
				$slide->createFromIDLite ( $item ['item_id'] );
				$slide->position = $item ['position'];
                                $slide->deck = new Deck();
                                $slide->deck->id = $item ['deck_revision_id'];
				$slide->deck->title = $slide->deck->getTitle();
                                $slide->deck->slug_title = $slide->sluggify($slide->deck->title);
				$res [] = $slide;
			}
		}
		return $res;
	}      
	public function getTitle() {
		$title = $this->dbGetOne('SELECT title FROM deck_revision WHERE id=:id', array ('id' => $this->id ));
                return $title==''?'Untitled':$title;
	}
	public function getIcon() {
		return $this->tree_icon;
	}
	public function shortenTitle($title) {
		if (strlen ( $title ) > $this->max_title_length) {
			return mb_substr ( trim ( $title ), 0, $this->max_title_length, "utf-8" ) . '...';
		} else {
			return $title?$title:'Untitled';
		}
	}
	public function getTree() {
		$questions=$this->getQuestions ();
		$question_no=count($questions['accepted']);
		$output [] = array ("attr" => array ('id' => 'tree-0-deck-' . $this->id . '-1' . '-node' ), 'state' =>'open', 'data' => array ("title" => $this->shortenTitle ( $this->title ), 'icon' => $this->getIcon (), 'attr' => array ('id' => 'tree-0-deck-' . $this->id . '-1', 'class' => 'deck-' . $this->id, 'title'=>htmlspecialchars_decode($this->title).' | '.$this->getNumberOfSlides().' Slide(s) | '.$question_no.' Question(s)', 'href' => '#tree-0-deck-' . $this->id . '-1-view') ), 'children' => $this->getTreeNodes () );
		return $output;
	}
	public function getTreeNodes() {
		static $index_counter=0;
		$output = $childs = array ();
		foreach ( $this->content as $v ) {
			$questions=$v->getQuestions ();
			$question_no=count($questions['accepted']);
			if (get_class ( $v ) == 'Deck'){				
				$childs = $v->getTreeNodes ();
				$output [] = array ("attr" => array ('id' => 'tree-' . $this->id . '-deck-' . $v->id . '-' . $v->position . '-node' ), 'data' => array ("title" => $v->shortenTitle ( $v->title ), 'icon' => $v->getIcon (), 'attr' => array ('id' => 'tree-' . $this->id . '-deck-' . $v->id . '-' . $v->position, 'class' =>   'deck-' . $v->id,  'title'=>htmlspecialchars_decode($v->title).' | '.$v->getNumberOfSlides().' Slide(s) | '.$question_no.' Question(s)', 'href' => '#tree-' . $this->id . '-deck-' . $v->id . '-' . $v->position . '-view' ) ), 'children' => $childs );
			}else{
				$output [] = array ("attr" => array ('id' => 'tree-' . $this->id . '-slide-' . $v->id . '-' . $v->position .'-node' ), 'data' => array ("title" => $v->shortenTitle ( $v->title ), 'icon' => $v->getIcon (), 'attr' => array ('id' => 'tree-' . $this->id . '-slide-' . $v->id . '-' . $v->position, 'class' => 'slide-' . $v->id, 'title'=>htmlspecialchars_decode($v->title).' | '.$question_no.' Question(s) |  Position:'.($index_counter+1), 'href' => '#tree-' . $this->id . '-slide-' . $v->id . '-' . $v->position .'-view' ) ) );
				$index_counter++;
			}
			$childs = array ();
		}
		return $output;
	}
	public function deleteItemFromPosition($position) {
		$this->dbQuery ( 'DELETE FROM deck_content WHERE deck_revision_id=' . $this->id . ' AND position=' . $position );
		$this->dbQuery ( 'UPDATE  deck_content SET position=position-1 WHERE deck_revision_id=' . $this->id . ' AND position>' . $position );
	}
	public function moveItem($item, $target_deck, $new_position) {
		$this->deleteItemFromPosition ( $item->position );
		$item->position = $new_position;
		$content = array ();
		$content [] = $item;
		$target_deck->addContent ( $content );
	}
	public function getItemPosition($item_id, $item_type) {
		return $this->dbGetCol ( 'SELECT position FROM deck_content WHERE deck_revision_id=:id AND item_type=:item_type AND item_id=:item_id', array ('id' => $this->id, 'item_type' => $item_type, 'item_id' => $item_id ) );
	}
	
	public function getSubscriptions() {
		$results = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_id=:item_id && item_type = "deck"', array ('item_id' => $this->id ) );
		
		$subscriptions = array ();
		foreach ( $results as $result ) {
			$res = array ();
			$res ['user_id'] = $result ["user_id"];
			$res ['item_id'] = $result ["item_id"];
			$res ['timestamp'] = $result ["timestamp"];
			array_push ( $subscriptions, $res );
		}
		
		return $subscriptions;
	}
	public function getBrand() {
		$result = $this->dbQuery ( 'SELECT * FROM brand WHERE deck_revision_id=:item_id LIMIT 1', array ('item_id' => $this->id ) );
		$res = array ();
		if(count($result)){
			$res ['image'] = $result [0]["image"];
			$res ['url'] = $result [0]["url"];
			$res ['text'] = $result [0]["description"];
		}
		return $res;
	}	
	public function isDeckInTree($deckId) {
		$this->initConnection ();
		$item = $this->id;
		$deck = $deckId;
		
		if ($item == $deck) {
			return 1;
		
		} else {
			
			$strings_array = $this->dbQuery ( "SELECT `item_id`, `item_type`, `deck_revision_id` FROM `deck_content` WHERE 
			`item_type` = 'deck' AND `deck_revision_id` = " . $deck );
			foreach ( $strings_array as $string ) {
				return $this->isDeckInTree ( $item, $string [item_id] );
			}
		}
		return 0;
	
	}
	
	public function getParents() {
		$this->initConnection ();
		$result_string = '';
		foreach ( ($this->dbQuery ( "SELECT deck_revision_id FROM `deck_content` WHERE `item_type` = 'deck' AND `item_id` = " . $this->id )) as $r ) {
			
			if (isset ( $r ['deck_revision_id'] )) {
				$newDeck = new Deck ();
				$newDeck->id = ($r ['deck_revision_id']);
				$result_string = $result_string . '.' . $r ['deck_revision_id'];
				$result_string = $result_string . '.' . $newDeck->getParents ();
			}
		}
		;
		return $result_string;
	
	}
	
	public function getChildren() {
		$this->initConnection ();
		
		$result_string = '';
		foreach ( ($this->dbQuery ( "SELECT `item_id` FROM `deck_content` WHERE `item_type` = 'deck' AND `deck_revision_id` = " . $this->id )) as $r ) {
			
			if (isset ( $r ['item_id'] )) {
				$newDeck = new Deck ();
				$newDeck->id = ($r ['item_id']);
				$result_string = $result_string . '.' . $r ['item_id'];
				$result_string = $result_string . '.' . $newDeck->getChildren ();
			}
		}
		;
		
		return $result_string;
	
	}
        public function getChildrenForTest() {
            $this->initConnection ();
            $res = array();
            $children = $this->dbQuery ( 'SELECT * FROM deck_content WHERE deck_revision_id=:id AND item_type="deck" ORDER BY position', array ('id' => $this->id ) );
            foreach($children as $deck){
                $deck_obj = new Deck();
                $deck_obj->id = $deck['item_id'];
                $deck_obj->deck_id = $deck_obj->getBasicID();
                $res[] = $deck_obj;
            }
            return $res; 	
 	}
	
	public function parentsChildren($selectedDeckId) {
		$k = 0;
		//form children array
		$children [] = $this->id;
		$childrenArr = explode ( '.', $this->getChildren () );
		foreach ( $childrenArr as $r ) {
			if ($r > '')
				$children [] = $r;
		}
		//form parents array
		$newDeck = new Deck ();
		$newDeck->id = $selectedDeckId;
		$parentsArr = explode ( '.', $newDeck->getParents () );
		$parents = Array ();
		$parents [] = $selectedDeckId;
		foreach ( $parentsArr as $r ) {
			if ($r > '')
				$parents [] = $r;
		}
		//search for the same deckIds and return count of them
		foreach ( $children as $child ) {
			foreach ( $parents as $parent ) {
				if ($child == $parent)
					$k ++;
			}
		}
		return $k;
	}
	
	/*
	 *  For popularity measument
	 */
	
	public function getAllDeckRevisions() {
		$this->initConnection ();
		$deck_revisions = $this->dbQuery ( 'SELECT id, based_on FROM deck_revision' );
		return $deck_revisions;
	}
	public function getRevisionNumber($deck_id) {
		$count=1;
		$result = $this->dbGetCol ( 'SELECT based_on FROM deck_revision WHERE id=:id', array('id'=>$deck_id) );
		if (!$result[0]) {
			return $count;
		}else{
			$count=$count+$this->getRevisionNumber($result[0]);
		}
		return $count;
	}	
	public function getAllDeckSubscriptions() {
		$this->initConnection ();
		$deck_subscriptions = $this->dbQuery ( 'SELECT item_id FROM subscription WHERE item_type=:item_type', array ('item_type' => 'deck' ) );
		return $deck_subscriptions;
	}
	
	public function getAllDeckUsage() {
		$this->initConnection ();
		$deck_usage = $this->dbQuery ( 'SELECT item_id FROM deck_content WHERE item_type=:item_type', array ('item_type' => 'deck' ) );
		return $deck_usage;
	}
	
	public function updatePopularityOf($deck_id, $popularity) {
		$this->dbQuery ( 'UPDATE deck_revision SET popularity=:popularity WHERE id=:deck_id', array ('popularity' => $popularity, 'deck_id' => $deck_id ) );
	}
	//Need for benchmarking
	public function deleteAllDecks() {
		$this->dbQuery ( 'DELETE FROM deck_content WHERE item_type="deck"' );
	}
	//Need for benchmarking
	public function deleteId($id) {
		$this->dbQuery ( 'DELETE FROM deck_revision WHERE id=' . $id );
		$this->dbQuery ( 'DELETE FROM deck_content WHERE deck_revision_id=' . $id );
		$this->dbQuery ( 'DELETE FROM tag WHERE item_type="deck" AND item_id=' . $id );
	}
	//------------------for questions-------------------------
	public function getDirectQuestions() {
		$questions = array ();
		$suggested = array ();
		$accepted = array ();
		$doubtful = array ();		
		$this->slides = $this->getDirectSlides();
		$slide_list=array();
		$id_list = array();
		foreach ($this->slides as $slide_object) {
                    $slide_list = $slide_object->getQuestions();			
                    foreach ($slide_list['accepted'] as $accepted_question){
                        if (!in_array($accepted_question->id, $id_list)){
                            $accepted [] = $accepted_question;
                            $id_list [] = $accepted_question->id;
                        }
                    }
                    foreach ($slide_list['suggested'] as $suggested_question){
                        if (!in_array($suggested_question->id, $id_list)){
                            $suggested [] = $suggested_question;
                            $id_list [] = $suggested_question->id;
                        }
                    }
                    foreach ($slide_list['doubtful'] as $doubtful_question){
                        if (!in_array($doubtful_question->id, $id_list)){
                            $doubtful [] = $doubtful_question;
                            $id_list [] = $doubtful_question->id;
                        }
                    }
		}		
		$questions ['suggested'] = $suggested;
		$questions ['accepted'] = $accepted;
		$questions ['doubtful'] = $doubtful;
		return $questions;
	}
	public function getQuestions() {
		$questions = array ();
		$suggested = array ();
		$accepted = array ();
		$doubtful = array ();		
		$this->slides = $this->getSlidesLite();
		$result_array = array();
		$slide_list=array();
		$id_list = array();
		foreach ($this->slides as $slide_object) {
			$slide_list = $slide_object->getQuestions();			
			foreach ($slide_list['accepted'] as $accepted_question){
				if (!in_array($accepted_question->id, $id_list)){                                    
					$accepted [] = $accepted_question;
					$id_list [] = $accepted_question->id;
				}
			}
			foreach ($slide_list['suggested'] as $suggested_question){
				if (!in_array($suggested_question->id, $id_list)){
					$suggested [] = $suggested_question;
					$id_list [] = $suggested_question->id;
				}
			}
			foreach ($slide_list['doubtful'] as $doubtful_question){
				if (!in_array($doubtful_question->id, $id_list)){
					$doubtful [] = $doubtful_question;
					$id_list [] = $doubtful_question->id;
				}
			}
		}		
		$questions ['suggested'] = $suggested;
		$questions ['accepted'] = $accepted;
		$questions ['doubtful'] = $doubtful;
		return $questions;
	}	
	public function getBasicOwnersId() {
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT id, user_id FROM deck_revision WHERE deck_id=' . $this->deck_id ) as $row ) {
			$res [] = $row ['user_id'];
		}
		return $res;
	}
	
	public function findSlideByBasic($slide_basic) {
		$slide = new Slide();
		$slide_id = $this->dbQuery("SELECT slide_revision.id FROM deck_content JOIN slide_revision ON deck_content.item_id = slide_revision.id WHERE deck_content.deck_revision_id = " . $this->id . " AND deck_content.item_type = 'slide'	AND slide_revision.slide = " . $slide_basic	);
		$slide->createFromID($slide_id['0']['id']);
                $slide->deck = new Deck();
                $slide->deck->id = $slide->getContainerID();
                $slide->deck->title = $slide->deck->getTitle();
                $slide->deck->slug_title = $slide->sluggify($slide->deck->title);
		return $slide;
	}
        
        public function getBasicID(){
            $res = $this->dbGetOne( 'SELECT deck_id FROM deck_revision WHERE id=:id', array ('id' => $this->id ) );
            
            return $res;
        }
        public function getBasicFromID($id){
            $res = $this->dbGetOne( 'SELECT deck_id FROM deck_revision WHERE id=:id', array ('id' => $id ) );
            
            return $res;
        }
	public function searchInSlides($term) {
		$this->content=$this->fetchDeckContentLite();
		$res=$this->dbQuery ( 'SELECT slide_revision.id FROM deck_content,slide_revision WHERE deck_content.item_type="slide" AND deck_content.item_id=slide_revision.id AND deck_content.deck_revision_id=:deck_id AND slide_revision.content RLIKE :term', array ('deck_id' => $this->id, 'term' => '[[:<:]]'.$term.'[[:>:]]' ) );
		foreach ( $this->content as $v ) {
			if (get_class ( $v ) == 'Deck')				
				$res= array_merge($v->searchInSlides($term), $res);
		}
		return $res;
	}
        public function detectLanguage(){
            $q = $this->title;
            if (strlen($this->abstract)){
                $q .= '. ' . $this->abstract; 
            }
            if (strlen($this->footer_text)){
                $q .= '. ' . $this->footer_text; 
            }
            $translator = new LanguageTranslator('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
            $detection = $translator->detect($q);
            $name = $translator->getLanguageName($detection);
            return $detection.'-'.$name;
        }
        
}
