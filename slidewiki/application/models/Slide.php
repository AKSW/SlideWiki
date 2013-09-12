<?php
require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'phpQuery' . DS .'phpQuery.php');
class Slide extends Model {
	public $item_name = "slide";
	//revision id
	public $id;
	//slide id
	public $slide_id;
	public $description; // contains original source of slide
	//the initial owner of a slide serie
	public $initiator;
	public $title;
        //sluggified title for uri
        public $slug_title;
	//contains the latest revision id
	public $last_revision_id;
	public $revisionTime;
	//a lightweight version of content with thumbnail of images
	public $thumbnailContent;
	public $content;
	//position relative to container deck
	public $position = 0;
	//position from the root container deck
	public $absolute_position = 0;
	public $owner;
	public $note;
	public $popularity;
	//revision comment
	public $comment;
	//discussion comments
	public $comments;
	public $user;
	public $contributors;
	//subscribers
	public $subscribers;
	public $subscriptions;
	//revision list
	public $revisions;
	//usage list
	public $usage;
	public $questions;
	public $identicals;
        public $language;
        public $translated_from;
        public $translated_from_revision;
        public $translation_status="original";
        //who removed the google banner
        public $translator_id;
	//parent deck (obj: deck->id, deck->title, deck->slug_title)	
	public $deck;
	//a flag indicating edit identical slides as well
	public $cascade_edit=0;
	public $is_followed_by_current_user = 0;
	//indicates whether user is in the editor list of a teh slide deck
	public $is_editable_by_current_user = 0;
        public $parent_language;
        public $link;
	private $max_title_length = 30;
	private $tree_icon = 'static/img/file.png';
	
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	
	public function createFromID($id) {
		$this->initConnection ();
		// query here
		$res = $this->dbGetRow ( 'SELECT * FROM slide_revision WHERE id=:id', array ('id' => $id ) );
		
		$this->id = $id;
		$this->slide_id = $res ['slide'];
		$this->description=$this->getDescription();
		$this->initiator=$this->getInitiator();
		$this->revisionTime = $res ['timestamp'];
		$this->content = $res ['content'];
		$this->note = $res ['note'];
		$this->comment = $res ['comment'];
		$this->popularity = $res ['popularity'];
		$user = new User ();
		$user->createFromID ( $res ['user_id'] );
		$this->user = $user;
		$this->owner = $this->getOwner ();
		$this->last_revision_id = $this->getLastRevisionID ();
		$this->subscribers = $this->getSubscribers ();
		$this->subscriptions = $this->getSubscriptions ();
                $this->translated_from = $this->getTranslatedFrom();
                $this->translated_from_revision = $this->getTranslatedFromRevision();
                $this->translation_status = $res['translation_status'];
                $this->title = $this->getTitle ();
                $this->slug_title = $this->sluggify($this->title);
		$this->body = $this->getBody ();
		$this->position = $this->getPosition ();                
	
	}
	public function createFromIDLite($id) {
		$this->initConnection ();
		// query here
		$res = $this->dbGetRow ( 'SELECT * FROM slide_revision WHERE id=:id', array ('id' => $id ) );	
		$this->id = $id;
		$this->slide_id = $res ['slide'];
		$this->revisionTime = $res ['timestamp'];
		$this->content = $res ['content'];
		$this->note = $res ['note'];
		$this->comment = $res ['comment'];
		$this->popularity = $res ['popularity'];
                $this->translation_status = $res['translation_status'];
		$this->title = $this->getTitle ();
                $this->slug_title = $this->sluggify($this->title);
		$user = new User ();
		$user->createFromID ( $res ['user_id'] );
		$this->user = $user;
		$this->owner = $this->user; //owner of slide rev	
	}
        public function getTranslatorId(){
            $res = $this->dbGetRow ( 'SELECT * FROM slide_revision WHERE id=:id', array ('id' => $this->id ) );
            return $res['translator_id'];
        }
	public function create() {           
		$this->slide_id = $this->dbInsert ( 'slide', array ('user_id' => $this->user->id, 'language' =>  $this->language['id'].'-'.$this->language['name']) );
		$this->id = $this->commit ();
		return $this->id;
	}
	// do not create a new revision if $as_new_revision=0
	//$cascade: update only the current slide or all the identical slides
	public function save($cascade = 0, $as_new_revision = 1) {
		if ($as_new_revision == 0) {
			return $this->dbQuery ( "UPDATE slide_revision SET content=:content,comment=:comment, translation_status=:translation_status, note=:note WHERE id=:id", array ('id' => $this->id, 'note' => $this->note, 'comment' => $this->comment,'translation_status' =>$this->translation_status, 'content' => $this->content) );
		} else {
			//commit slide as a new revision
			$old_id = $this->id;
			$rev = $this->commit ( $this->id );
			// mail to the users
			$old_deck_id = $this->deck->id;
			//TODO: it only works within one deck, if same slides are in different decks we need to recursively update them
			if (! $cascade) {
				$a = $this->dbQuery ( 'UPDATE deck_content SET item_id=:rev WHERE deck_revision_id=:deck AND item_id=:id AND item_type<> "deck" AND position=:position', array ("rev" => $rev, "deck" => $old_deck_id, "id" => $old_id, "position" => $this->position ) );
			} else {
				$this->dbQuery ( 'UPDATE deck_content SET item_id=:rev WHERE deck_revision_id=:deck AND item_id=:id AND item_type<> "deck"', array ("rev" => $rev, "deck" => $old_deck_id, "id" => $old_id ) );
			}
			$this->id = $rev;
			return $rev;
		}
	}
	public function getOwner() {
		$res = $this->dbGetCol ( 'SELECT slide.user_id FROM slide_revision, slide WHERE slide_revision.slide=slide.id AND slide_revision.id=:id', array ('id' => $this->id ) );
		$user = new User ();
		$user->createFromID ( $res ['0'] );
		return $user;
	}
	public function getOwnerID() {
		$res = $this->dbGetCol ( 'SELECT slide.user_id FROM slide_revision, slide WHERE slide_revision.slide=slide.id AND slide_revision.id=:id', array ('id' => $this->id ) );
		return $res ['0'];
	}	
	//initiator is the first owner of the slide
	public function getInitiator() {
		$res = $this->dbGetCol ( 'SELECT user_id FROM slide WHERE id=:id', array ('id' => $this->slide_id ) );
		$user = new User ();
		$user->createFromID ( $res [0] );
		return $user;
	}	
	public function getDescription() {
		$res = $this->dbGetRow('SELECT * FROM slide WHERE id=:id', array ('id' => $this->slide_id ));
		$this->description = $res['description'];
		return $res['description'];
	}	
	public function getContributors() {
		$c = $this->dbGetRow ( 'SELECT * FROM slide_revision INNER JOIN users ON(slide_revision.user_id=users.id) WHERE slide_revision.id=:id', array ('id' => $this->id ) );
		if (count($c)) {
			if ($c['based_on']) {
				$temp = new Slide ();
				$temp->id = $c['based_on'];
				return array_unique ( array_merge ( $temp->getContributors (), array ($c ['user_id'] . '|' . $c ['username'] ) ) );
			}
			return array ($c ['user_id'] . '|' . $c ['username'] );
		} else
			return array();
	}
	public function getIdenticals($container_deck) {
		$matches = array ();
		$deck = new Deck ();
		$deck->createFromID ( $container_deck );
		$slides = $deck->getSlides ();
		foreach ( $slides as $item ) {
			if (($item->id == $this->id) && (($item->position != $this->position) || ($item->deck->id != $this->deck->id)))
				$matches [] = array ('parent' => $item->deck->id, 'position' => $item->position );
		}
		return $matches;
	
	}
	//for search
	public function searchInContent($term) {
		$res=$this->dbQuery ( 'SELECT * FROM slide_revision WHERE slide_revision.content RLIKE :term', array ('term' => '[[:<:]]'.$term.'[[:>:]]' ) );
		return $res;
	}
	public function getSlideContent() {
		$res = $this->dbGetRow ( 'SELECT content FROM slide_revision WHERE id=:id', array ('id' => $this->id ) );
		$content = $res ['content'];
		$this->content = $content;
		return $content;
	}
	public function commit($based_on = NULL) {
		$this->id = $this->dbInsert ( 'slide_revision', array ('slide' => $this->slide_id, 'content' => $this->content, 'user_id' => $this->user->id, 'translation_status' =>$this->translation_status, 'comment' => $this->comment, 'based_on' => $based_on, 'note' => $this->note, 'translator_id' => $this->translator_id ) );
		if($based_on){
			//delete first new slides NULL value on based_on
			//only first slide has
			$res = $this->dbGetRow ( 'SELECT * FROM slide_revision WHERE id=:basedid',array('basedid'=>$based_on) );
			if(!$res['based_on'] && $res['content']=='<h2>new slide</h2>'){
				$this->dbQuery ('DELETE FROM slide_revision WHERE id=:basedid',array('basedid'=>$based_on));
				$this->dbQuery ('UPDATE slide_revision SET based_on=NULL WHERE id=:newid',array('newid'=>$this->id));
			}
		}	
		return $this->id;
	} 
        public function getLanguage(){
            $this->initConnection ();
            $language=array();
            $language['id'] = '';
            $language['name'] = '';
            $default_language = $this->dbGetOne('SELECT language FROM slide WHERE id=:slide_id',array('slide_id' => $this->slide_id));
            if ($default_language){
                $language_arr = array();
                $language_arr = explode('-',$default_language);
                if (count($language_arr) == 2){
                    $language = array();
                    $language['id'] = $language_arr[0];
                    $language['name'] = $language_arr[1];
                }
            }          
            return $language;  
        }
        public function getParentLanguage(){
            $this->initConnection();
            $parent_language_string = $this->dbGetOne('SELECT language FROM slide WHERE id=:parent_id',array('parent_id' => $this->translated_from));
            $result = array();
            $parent_language_array = explode('-', $parent_language_string);
            $result['id'] = '';
            $result['name'] = '';
            if ($parent_language_array[0] != ''){
                $result['id'] = $parent_language_array[0];
                $result['name'] = $parent_language_array[1];
            }
            return $result;
            
        }
        public function getLinkInDeck($deck_rev_id){
            $this->initConnection();
            $deck = new Deck();
            $deck->createFromID($deck_rev_id);
            $slide_prop = array();
            $slide_prop = $deck->getSlidePropertiesById($this->id);
            $this->link = './deck/'.$deck_rev_id . '_' . $deck->slug_title . '#'.$slide_prop['link'];
        }
        public function setLanguageFull($language){
            $this->initConnection();
            $this->dbQuery('UPDATE slide SET language=:language WHERE id=:slide_id',array('language' => $language, 'slide_id' => $this->slide_id));
        }
        public function symbolsToLatex(){
            $result_text = preg_replace("!\\\\\[(.*?)\\\\\]!si","<latex>\\1</latex>",$this->content);
            $this->content = $result_text;
        }
        public function latexToSymbols(){
            $result_text = preg_replace("!<latex>(.*?)</latex>!si","\[\\1\]",$this->content);
            $this->content = $result_text;
        }
        //creating an array with text fragments that shouldn't be translated
        public function filterTags(){
            $tags = array('pre','latex','.no-translate','.code');
            $content = phpQuery::newDocument($this->content, $contentType = null);
            $result_array = array();
            foreach($tags as $tag){
                if (pq($tag)){
                    $result_array[$tag] = array();
                    foreach (pq($tag) as $finding){
                        $result_array[$tag][] = pq($finding)->text();
                        pq($finding)->text('');
                    }
                }               
            }
            $this->content = $content->html();
            return $result_array;
        }
        //adding text fragments back to the "holes"
        public function addTagsBack($content,$replace_array){
            $tags = array('.code','.no-translate','latex','pre');
            $new_content = phpQuery::newDocument($content, $contentType = null);
            foreach($tags as $tag){
                $i=0;
                if(count($replace_array[$tag])){
                    foreach(pq($tag) as $hole){
                        pq($hole)->text($replace_array[$tag][$i]);
                        $i++;
                    }
                }                              
            }
            return $new_content;
        }
        public function getAllTranslations(){
            $this->initConnection();
            $result = array();
            $node = array();
            $language_ids = array();
            
            $node['id'] = $this->slide_id;
            $node['language'] = $this->getLanguage();
            if (!isset($node['language']['id'])){
                $node['language']['id'] == 'xh';
                $node['language']['name'] == 'undefined';
            }
            
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
                $parent_slide = new Slide();
                $parent_slide->slide_id = $parent;
                $parent_slide->language = $parent_slide->getLanguage();
                $parent_slide->translated_from = $parent_slide->getTranslatedFrom();
                if (!in_array($parent_slide->language['id'], $language_ids)){
                    $parent_arr = array();
                    $parent_arr['id'] = $parent;
                    $parent_arr['language'] = $parent_slide->language;
                    $result[] = $parent_arr;
                    $language_ids [] = $parent_arr['language']['id'];
                }                
                foreach($parent_slide->getTranslatedTo(true) as $node){
                    if (!in_array($node['id'], $visited) && !in_array($node['language']['id'],$language_ids)){
                        $visited[] = $node['id'];
                        $result[] = $node;
                        $language_ids[] = $node['language']['id'];
                    }
                }
                $parent = $parent_slide->translated_from;
            }            
            return $result;
        }
        
        public function getTranslatedTo($flag = false){            
            static $result_children = array();
            static $language_ids = array();
            if ($flag){
                $result_children = array();
                $language_ids = array();
            }
            $children = $this->dbQuery('SELECT id, language FROM slide WHERE translated_from=:id', array('id' => $this->slide_id));
            if(count($children)){
                foreach ($children as $slide){
                    $child_obj = new Slide();
                    $child_obj->slide_id = $slide['id'];
                    $language_array = explode('-',$slide['language']);
                    $language['id'] = $language_array[0];
                    $language['name'] = $language_array[1];
                    if (!in_array($language['id'],$language_ids)){
                        $child = array();
                        $child['id'] = $slide['id'];
                        $child['language']['id'] = $language['id'];
                        $child['language']['name'] = $language['name'];
                        $language_ids [] = $language['id'];
                        $result_children[] = $child;
                    }                
                    $child_obj->getTranslatedTo();
                }
            }
            return $result_children;
        }
//        public function translateQuestions($questions, $language){
//            
//            if (count($questions)){
//                if (count($questions['accepted'])){
//                    foreach($questions['accepted'] as $question){
//                        echo $question->question.'<br>';
//                        $answers = array();
//                        $answers = $question->getAnswers();
//                        $q = $question->question;
//                        $question->question = $translator->translate($q, $target, $source);
//                        $question->item_id = $this->slide_id;                    
//                        $question->based_on = NULL;
//                        $question->user = $this->user;
//                        $question->id = $question->create();
//                        if (count($answers)){
//                            foreach ($answers as $answer){                            
//                                $q = $answer['answer'];
//                                $answer['answer'] = $translator->translate($q, $target, $source);
//                                $q = $answer['explanation'];
//                                $answer['explanation'] = $translator->translate($q, $target, $source);
//                                $question->addAnswer($answer['answer'], $answer['explanation'], $answer['is_right']);
//                            }                        
//                        }
//                    }
//                }                
//            }
//        }
        public function getTranslationStatus(){
            $this->initConnection();
            return $this->dbGetOne('SELECT translation_status FROM slide_revision WHERE id=:id',array('id' => $this->id));
        }
        
        public function translateQuestions($old_slide_id, $new_slide_id, $source_arr, $language){
            $questions_array = array();
            $questions = array();
            $translator = new LanguageTranslator('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
            $questions_array = $this->dbQuery ( 'SELECT *
			FROM questions INNER JOIN users on questions.user_id = users.id
			WHERE questions.based_on IS NULL AND questions.mark = "accepted" AND questions.item_id=' . $old_slide_id);
            if (count($questions_array)){
                foreach ( $questions_array  as $row ) {			
                    $question = new Question();
                    $question->createFromID($row['0']);
                    $lastId = $question->getLastRevision();
                    $lastRevision = new Question();
                    $lastRevision->createFromID($lastId);
                    $questions[] = $lastRevision;			
                }
            }
            if (count($questions)){
                $source = $source_arr['id'];
                $target_arr = explode('-',$language);
                $target = $target_arr[0];
                foreach($questions as $question){
                    $answers = array();
                    $answers = $question->getAnswers();
                    $q = $question->question;
                    $question->question = $translator->translate($q, $target, $source);
                    $question->item_id = $new_slide_id;                    
                    $question->based_on = NULL;
                    $question->user->id = $this->user->id;
                    $question->id = $question->create();
                    if (count($answers)){
                        foreach ($answers as $answer){                            
                            $q = $answer['answer'];
                            $answer['answer'] = $translator->translate($q, $target, $source);
                            $q = $answer['explanation'];
                            if ($q != ''){
                                $answer['explanation'] = $translator->translate($q, $target, $source);
                            }                                
                            $question->addAnswer($answer['answer'], $answer['explanation'], $answer['is_right']);
                        }                        
                    }
                }
            }
        }
        public function translate($language) { 
            
            
            $source_arr = $this->getLanguage();
            if (is_array($source_arr) && isset($source_arr['id'])){
                $source = $source_arr['id'];
            }            
            $target_arr = explode('-',$language);
            $target = $target_arr[0];
            $translations = array();
            $translations = $this->getAllTranslations();
            
            $translator = new LanguageTranslator('AIzaSyBlwXdmxJZ__ZNScwe4zq5r3qh3ebXb26k');
            if (count($translations)){
                
                foreach($translations as $node){
                    if ($node['language']['id'] == $target){
                        $slide = new Slide();
                        $slide->slide_id = $node['id'];
                        $slide->createFromIDLite($slide->getLastRevisionID());
                        return $slide->id;

                    }
                }
            }
           
                $old_slide_id = $this->slide_id;//for questions
               
                $this->symbolsToLatex();
                $replace_array = array();
                $replace_array = $this->filterTags();
                $q = $this->content;            
                $content='';
                $translation = $translator->translate($q,$target,$source);
                if ($translation != -1){
                    $tags_back = $this->addTagsBack($translation,$replace_array);
                    $content = '<div lang="'.$target.'-x-mtfrom-'.$source.'">'.$tags_back.'</div>';
                    $this->language = $language;
                    $this->translated_from = $this->slide_id;
                    $translated_from_revision = $this->id;
                    $this->content = $content;
                    $this->description = $this->getDescription();
                    $this->latexToSymbols();
                    $based_on = NULL; 

                    $this->slide_id = $this->dbInsert ('slide', array ('user_id' => $this->user->id, 'description' =>$this->description, 'language' => $this->language,'translated_from' =>$this->translated_from, 'translated_from_revision' => $translated_from_revision ) );
                    $new_slide_id = $this->slide_id; //for questions

                    $this->translateQuestions($old_slide_id, $new_slide_id, $source_arr, $language);

                    $this->based_on = NULL;
                    $this->id = $this->dbInsert ( 'slide_revision', array ('slide' => $this->slide_id, 'content' => $this->content, 'user_id' => $this->user->id, 'comment' => $this->comment, 'based_on' => $based_on, 'note' => $this->note, 'translation_status' => 'google', 'translated_from_revision' => $translated_from_revision) );

                    return $this->id;                     
                }else{
                    return -1;
                }
        }
        public function getTranslatedFrom(){
            $this->initConnection();
            $translated_from = $this->dbGetOne('SELECT translated_from FROM slide WHERE id=:slide_id',array('slide_id' => $this->slide_id));
            return $translated_from;
        }
        public function getTranslatedFromRevision(){
            $this->initConnection();
            $translated_from = $this->dbGetOne('SELECT translated_from_revision FROM slide_revision WHERE id=:id',array('id' => $this->id));
            return $translated_from;
        }
        public function removeGoogle(){
            $this->initConnection();
            $this->dbQuery('UPDATE slide_revision SET translation_status=:translation_status, translator_id=:translator_id WHERE id=:id',array('translation_status' => 'original', 'translator_id' =>$this->translator_id, 'id' => $this->id));
        }
        public function getTranslator(){
            $this->initConnection();
            $translator_id = $this->dbGetOne('SELECT translator_id FROM slide_revision WHERE id=:id', array('id' => $this->id));
            if ($translator_id){
                $translator = new User();
                $translator->createFromID($translator_id);
                return $translator;
            }
            return NULL;
        }         
	public function getUsage() {
		$res = array ();
		$query = $this->dbQuery( 'SELECT deck_content.item_id, deck_content.deck_revision_id FROM deck_content WHERE item_type="slide" AND item_id = :item_id', array('item_id' => $this->id));
		# deck_revision.id - revision id of the deck
		# deck_revision.user_id - owner of this deck revision
		# deck_revision.title - title of this deck revision
		# users.username - username of the owner
		/*$this->dbQuery ( 'SELECT deck_revision.id, deck_revision.title, deck_revision.user_id, users.username
                        FROM deck_content INNER JOIN deck_revision ON(deck_revision_id=deck_revision.id) INNER JOIN users ON(deck_revision.user_id=users.id)
                        WHERE item_type="slide" AND item_id=' . $this->id . ' GROUP BY deck_revision.id ORDER BY deck_revision.timestamp DESC' );*/
		foreach ( $query as $row ) {
			$deck = new Deck ();
			$deck->createFromID ( $row ['deck_revision_id'] );
			array_push ( $res, $deck );
		}
		if(empty($res)) {
			$res = "Not used!";
		}
		return $res;
	}
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
		$query = $this->dbQuery( 'SELECT deck_content.item_id, deck_content.deck_revision_id FROM deck_content WHERE item_type="slide" AND item_id = :item_id', array('item_id' => $this->id));
		# deck_revision.id - revision id of the deck
		# deck_revision.user_id - owner of this deck revision
		# deck_revision.title - title of this deck revision
		# users.username - username of the owner
		/*$this->dbQuery ( 'SELECT deck_revision.id, deck_revision.title, deck_revision.user_id, users.username
                        FROM deck_content INNER JOIN deck_revision ON(deck_revision_id=deck_revision.id) INNER JOIN users ON(deck_revision.user_id=users.id)
                        WHERE item_type="slide" AND item_id=' . $this->id . ' GROUP BY deck_revision.id ORDER BY deck_revision.timestamp DESC' );*/
		foreach ( $query as $row ) {
			$deck = new Deck ();
			$deck->createFromIDLite ( $row ['deck_revision_id'] );
			if(!in_array($deck->owner->id, $ignore_list)){
				array_push ( $res, $deck );
			}	
		}
		if(empty($res)) {
			$res = "Not used!";
		}
		return $res;
	}	
	
	public function getRevisions() {
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT slide_revision.id, timestamp, content, comment, user_id,  username FROM slide_revision INNER JOIN users ON(user_id=users.id) WHERE slide=' . $this->slide_id . ' ORDER BY timestamp DESC' ) as $row ) {
			$slide = new Slide ();
			$slide->createFromID ( $row ['id'] );
			$slide->content=$slide->getThumbnailContent();                        
			$res [] = $slide;
		}
		return $res;
	}
	public function getLastRevisionID($user_id=NULL) {
		if($user_id)
			return $this->dbGetOne ( 'SELECT id FROM slide_revision WHERE slide=:slide_id AND user_id=:user_id ORDER BY timestamp DESC LIMIT 1', array ('slide_id' => $this->slide_id ,'user_id'=>$user_id ) );
		else 
			return $this->dbGetOne ( 'SELECT id FROM slide_revision WHERE slide=:slide_id ORDER BY timestamp DESC LIMIT 1', array ('slide_id' => $this->slide_id ) );
	}
	public function getPreviousRevisionID() {
		#$this->id; //current revision id
		#$this->slide_id; // current slide id
		$prev_id = $this->dbGetOne ( 'SELECT id 
									FROM slide_revision 
									WHERE slide=:slide_id AND id < :id 
									ORDER BY timestamp 
									DESC LIMIT 1', 
									array ('slide_id' => $this->slide_id, 'id' => $this->id) );
		return $prev_id;
	}
	public function getLastDeck($id) {
		$deck_id = $this->dbGetOne ( 'SELECT deck_revision_id FROM deck_content WHERE item_id=:id LIMIT 1', array ('id' => $id ) );
                $deck = new Deck();
                $deck->id = $deck_id;
                $deck->title = $deck->getTitle();
                $deck->slug_title = $deck->sluggify($deck->title);
                return $deck;
	}
	public function getComments() {
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT c.id,title,c.timestamp,text FROM comment c INNER JOIN users u ON(user_id=u.id) WHERE item_type="slide" AND item_id=' . $this->id . ' ORDER BY timestamp DESC' ) as $row ) {
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
			foreach ( $this->dbQuery ( 'SELECT c.id,title,c.timestamp,text FROM comment c INNER JOIN users u ON(user_id=u.id) WHERE item_type="slide" AND item_id=' . $rev->id . ' ORDER BY timestamp DESC' ) as $row ) {
				$comment = new Comment ();
				$comment->createFromID ( $row ['id'] );
				$res [] = $comment;
			}
		return $res;
	}
	public function replaceWith($newslide) {
		$this->dbQuery ( 'UPDATE  deck_content SET item_id=' . $newslide . ' WHERE deck_revision_id=' . $this->deck->id . ' AND position=' . $this->position );
	}
	public function getTitle() {
		preg_match ( "/<h2(.*?)>(.*?)<\/( *?)h2>/", $this->content, $matches );
		if (count ( $matches ))
			return  strip_tags ( $matches [0] ) ;
		else
			return "Untitled";
	}
	public function setTitle($title) {
		$content = $this->content;
		$content = preg_replace ( "/<h2(.*?)>(.*?)<\/( *?)h2>/", "<h2>" . $title . "</h2>", $content, 1, $count );
		if (! $count) {
			$content = "<h2>" . $title . "</h2>" . $content;
		}
		$this->content=$content;
		$this->dbQuery ( 'UPDATE slide_revision SET content="' . $content . '",comment="Title changed to: ' . $title . '" WHERE id=' . $this->id );
	}
	public function getBody() {
		$content = $this->content;
		$body = preg_replace ( "/<h2(.*?)>(.*?)<\/( *?)h2>/", "", $content, 1);
		//$body=utf8_encode($body);
		return $body;
	}	
	public function replaceContentWithNewTitle($title){
		$content = $this->content;
		$content = preg_replace ( "/<h2(.*?)>(.*?)<\/h2>/", "<h2>" . $title . "</h2>", $content, 1, $count );
		if (! $count) {
			$content = "<h2>" . $title . "</h2>" . $content;
		}
		$this->content=$content;		
	}
	public function shortenTitle($title) {
		if (strlen ( $title ) > $this->max_title_length) {
			return mb_substr ( trim ( $title ), 0, $this->max_title_length, "utf-8") . '...';
		} else {
			return $title?$title:'Untitled';
		}
	}
	public function getThumbnailContent() {
		$pattern = array ();
		$patterns [0] = '/\.jpg\s*\"/';
		$patterns [1] = '/\.jpeg\s*\"/';
		$patterns [2] = '/\.gif\s*\"/';
		$patterns [3] = '/\.png\s*\"/';
		$replacements = array ();
		$replacements [0] = '.jpg?filter=Resize-width-150"';
		$replacements [1] = '.jpeg?filter=Resize-width-150"';
		$replacements [2] = '.gif?filter=Resize-width-150"';
		$replacements [3] = '.png?filter=Resize-width-150"';
		$html = preg_replace ( $patterns, $replacements, $this->content );
		return $html;
	}
	public function getIcon() {
		return $this->tree_icon;
	}
	public function getSubscribers() {
		$subscribers = array ();
		$results = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_id=:item_id && item_type = "slide"', array ('item_id' => $this->id ) );
		if (! empty ( $results )) {
			foreach ( $results as $result ) {
				array_push ( $subscribers, $result ['user_id'] );
			}
		}
		return $subscribers;
	}
	
	public function getSubscriptions() {
		$results = $this->dbQuery ( 'SELECT * FROM subscription WHERE item_id=:item_id && item_type = "slide"', array ('item_id' => $this->id ) );
		
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
	
	
	public function getPosition() {
		if($this->deck)
			return $this->dbGetOne ( 'SELECT position FROM deck_content WHERE item_type="slide" AND item_id=:item_id AND deck_revision_id=:deck_id ORDER BY item_id DESC LIMIT 1', array ('item_id' => $this->id,'deck_id'=>$this->deck->id ) );
		else 
			return $this->dbGetOne ( 'SELECT position FROM deck_content WHERE item_type="slide" AND item_id=:item_id ORDER BY item_id DESC LIMIT 1', array ('item_id' => $this->id ) );	
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
	/*
	 *  For popularity measument
	 */
	public function getAllSlideRevisions() {
		$this->initConnection();
		$slides_revisions = $this->dbQuery('SELECT id, based_on FROM slide_revision', array());
		return $slides_revisions;
	}
        public function getThisSlideRevisions(){
                $this->initConnection();
		$slides_revisions = $this->dbQuery('SELECT id, based_on FROM slide_revision WHERE slide=:slide_id', array('slide_id'=>$this->slide_id));
		return $slides_revisions;
        }
        public function getAllRevisions(){
                $this->initConnection();
		$slides_revisions = $this->dbQuery('SELECT * FROM slide_revision WHERE slide=:basic_id', array('basic_id'=>$this->slide_id));
		return $slides_revisions;
        }
	public function setDescription($desc) {
		$this->dbQuery ( 'UPDATE slide SET description=:desc WHERE id=:id', array ('id' => $this->slide_id, 'desc' =>$desc ) );
	}	
	public function getAllSlidesInDecks() {
		$this->initConnection();
		$slides_in_decks = $this->dbQuery('SELECT item_id, deck_revision_id FROM deck_content WHERE item_type=:item_type', array('item_type' => 'slide'));
		return $slides_in_decks;
	}
	
	public function updatePopularityOf($slide_id, $popularity) {
		$this->initConnection();		
		$this->dbQuery('UPDATE slide_revision SET popularity=:popularity WHERE id=:slide_id', array('popularity' => $popularity, 'slide_id' => $slide_id));
	}
	
	public function deleteId($id) {
		$this->dbQuery ( 'DELETE FROM slide_revision WHERE id='.$id );
	
	}
	
	//-----------------------for questions-----------------
	
	public function getBasicId() {
		
		$basic_id =  $this->dbGetOne ( 'SELECT slide FROM slide_revision WHERE id='. $this->id );                
		return $basic_id;
	}
	
	public function getQuestions() {
		$questions = array ();
		$suggested = array ();
		$accepted = array ();
		$doubtful = array ();
		$basic_id = $this->getBasicId ();
                
                if($basic_id == NULL) {
                    return array (
                        "accepted" => array(),
                        "suggested" => array(),
                        "doubtful" => array()
                    ); 
                }

                $array = array();
                $owner_id = $this->getOwnerID();
                $array = $this->dbQuery ( 'SELECT *
			FROM questions INNER JOIN users on questions.user_id = users.id
			WHERE questions.based_on IS NULL AND questions.mark = "suggested" AND questions.item_id=' . $basic_id);
                if (count($array)){
                    foreach ( $array as $row ) {
			$question = new Question();
			$question->createFromID($row['0']);
			$lastId = $question->getLastRevision();
			$lastRevision = new Question();
			$lastRevision->createFromID($lastId);
                        $lastRevision->slide_revision = $this->id;
                        $lastRevision->item_owner = $owner_id;
			$suggested [] = $lastRevision;			
		}
                }
		$array = $this->dbQuery ( 'SELECT *
			FROM questions INNER JOIN users on questions.user_id = users.id
			WHERE questions.based_on IS NULL AND questions.mark = "accepted" AND questions.item_id=' . $basic_id);
                if (count($array)){
                    foreach ( $array  as $row ) {			
			$question = new Question();
			$question->createFromID($row['0']);
                        $lastId = $question->getLastRevision();
			$lastRevision = new Question();
			$lastRevision->createFromID($lastId);
                        $lastRevision->slide_revision = $this->id;
                        $lastRevision->item_owner = $owner_id;
			$accepted [] = $lastRevision;			
		}
                }
		$array = $this->dbQuery ( 'SELECT *
			FROM questions INNER JOIN users on questions.user_id = users.id
			WHERE questions.based_on IS NULL AND questions.mark = "doubtful" AND questions.item_id=' . $basic_id );
                if (count($array)){
                    foreach ( $array as $row ) {
			$question = new Question();
			$question->createFromID($row['0']);
			$lastId = $question->getLastRevision();
			$lastRevision = new Question();
			$lastRevision->createFromID($lastId);
                        $$lastRevision->item_owner = $owner_id;
			$doubtful [] = $lastRevision;
		}
                }
		
		$questions ['suggested'] = $suggested;
		$questions ['accepted'] = $accepted;
		$questions ['doubtful'] = $doubtful;
		return $questions;
	}
	
	public function getBasicOwnersId() {
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT id, user_id FROM slide_revision WHERE slide=' . $this->slide_id  ) as $row ) {			
			$res [] = $row ['user_id'];
		}
		return $res;
	}
	
	public function getContainerID() {
		return $this->dbGetOne ( 'SELECT deck_revision_id FROM deck_content WHERE item_type="slide" AND item_id=:item_id ORDER BY item_id DESC LIMIT 1', array ('item_id' => $this->id ) );	
	}
}
	
