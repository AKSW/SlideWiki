<?php

class Test extends Model {
	
        public $id;
	public $item_id;
	public $questions;
	public $quest_count;
	public $user;
	public $title;
	public $modules;
	public $all_questions;
        public $max_for_user;
        public $avg_diff;
        public $type;
        private $modules_id;
	
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	public function createFromItem($item_id, $limit=0, $mode=1){		
            $this->initConnection();
            $this->item_id = $item_id;            
            $this->title = $this->getTitle();
            $this->questions = $this->getDirectQuestions();            
            $this->modules_id [] = $item_id;
            $this->quest_count = count($this->questions);
            $this->type = 'auto';
            $this->getModules($limit,$mode);
            if ($this->quest_count){
                $this->questions = $this->testMode($mode,$limit,$this->questions);
            }
            $this->quest_count = count($this->questions);
        } 
       //get the questions of the current deck (without subdecks)
        public function getDirectQuestions(){
            $this->initConnection();
            $questions = array();
            $result = array();
            $result = $this->dbQuery( 'SELECT q.id AS id, q.mark AS mark FROM `questions` AS q
                    JOIN `slide_revision` AS s ON q.item_id = s.slide
                    JOIN `deck_content` AS d ON s.id = d.item_id
                    WHERE d.deck_revision_id=:id AND q.mark="accepted" AND d.item_type="slide" AND q.based_on IS NULL', array('id' => $this->item_id));
            if (count($result)){
                foreach ($result as $question_row) {
                    $question = new Question();
                    $question->createFromID($question_row['id']);
                    $lastId = $question->getLastRevision();
                    $lastRevision = new Question();
                    $lastRevision->createFromID($lastId);
                    $questions[] = $lastRevision;
                }
            }            
            return $questions;
        }
        //randomize the questions and pick the necessary quantity in the necessary order
        public function testMode($mode,$limit,$questions){            
            $questions_mode = array();
            $result = array();
            $this->avg_diff = $this->countAvgDiff($questions);
            if ($mode==2) {
                if ($limit>0) {
                    for ($i=0; $i<$limit && $i<count($questions); $i++) {                        
                        $questions_mode [] = $questions[$i];	
                    }
                }else {
                    $questions_mode = $questions;
                }
                $result = $this->applyMode($mode,$questions_mode);
            }else {
                $questions_mode = $this->applyMode($mode,$questions);       
                if ($limit>0) {
                    for ($i=0; $i<$limit && $i<count($questions_mode); $i++) {                        
                        $result [] = $questions_mode[$i];	
                    }
                }else {
                    $result = $questions_mode;
                }
            }
            return $result;
        }
        
        public function applyMode($mode,$questions) {
            $result = array();
            switch($mode){
                case 1: //random
                    shuffle($questions);
                    $result = $questions;
                    break;
                case 2: //increase difficult
                    $sort_numcie = array();
                    foreach($questions as $index=>$question) {
                        $sort_numcie[] = $question->difficulty;
                    }
                    array_multisort($sort_numcie, SORT_ASC, $questions);                        
                    $result = $questions;
                    break;
                case 3: //only difficult
                    foreach($questions as $question){
                        if ($question->difficulty >= $this->avg_diff ){
                            $result[] = $question;
                        }
                    }
                    shuffle($result);
                    break;
                default : 
                    shuffle($questions);
                    $result = $questions;
                    break;
            }
            return $result;
        }
        //count average difficulty of the test
        public function countAvgDiff($questions){
            $diff=0;
            if (count($questions)){
                foreach ($questions as $question){
                    $diff+= $question->difficulty; 
                }
                $average = $diff/count($questions);
            } else $average = '-';
            return $average;
        }
        //get all the submodules as Test() objects, add them into test
        public function getModules($limit,$mode){
            $questions_old = array();
            if (count($this->questions)){
                $questions_old = $this->questions;
            }
            static $id_array = array();
            static $modules_id = array();            
            //add question_id to a list to check duplicates
            if (count($questions_old)) {
                foreach ($questions_old as $question) {                
                    $id_array[] = $question->id;
                }
            }            
            //get subdecks
            $children = array();
            $deck = new Deck();
            $deck->id = $this->item_id;
            $children = $deck->getChildrenForTest();
            if (count($children)){
                foreach ( $children as $deck ) {
                    $new_item_id = $deck->id;                    
                    if (!in_array($deck->deck_id, $modules_id)) {
                        $questions = array();
                        $modules_id [] = $deck->deck_id;
                        $test = new Test();
                        $test->item_id = $new_item_id;
                        $checkQuestions = $test->getDirectQuestions();                    
                        //do not add duplicates
                        if (count($checkQuestions)){
                            foreach($checkQuestions as $question){
                                if (!in_array($question->id, $id_array)){
                                    $test->questions[] = $question;
                                }
                            }
                        }                        
                        //if there are questions create a module and add it to the test
                        $test->title = $test->getTitle();
                        $test->type = 'auto';
                        $test->quest_count = count($test->questions);
                        if ($test->quest_count){
                            $questions = $test->testMode($mode, $limit, $test->questions); 
                        }                                                               
                        $test->questions = $questions;
                        $test->quest_count = count($test->questions);                             
                        $test->getModules($limit,$mode);
                        $this->modules [] = $test;                           
                    }                  
                }
            }
                        
        }
        //get all test questions without module structure
	public function getAllQuestions() {
            static $questions = array();
           
            if (count($this->questions)){
                foreach($this->questions as $question){
                    $question->module['id']= $this->item_id;
                    $question->module['name']=$this->title;                    
                }
                if (count($questions)){
                    $questions = array_merge($this->questions, $questions);
                }else{
                    $questions = $this->questions;
                }
            }            
            if (count($this->modules)) {
                foreach ($this->modules as $module) {
                    $module->getAllQuestions();
                }
            }
            return $questions;
	}
        //insert into db user results
        public function saveForUser($user_id, $wiki_app, $mtf, $ripkey, $dich, $morgan, $max_points, $mode, $limit) {
            $this->initConnection();
            $this->dbInsert('testing', array('title'=>$this->title,'item_id'=>$this->item_id,'mode'=>$mode,'limit'=>$limit,'user_id'=>$user_id,'wiki_app'=>$wiki_app, 'mtf'=>$mtf, 'ripkey'=>$ripkey, 'dich'=>$dich, 'morgan'=>$morgan,'max_points'=>$max_points, 'type'=>$this->type));
	}
        //get the max points for the test for the current user
	public function getMaxForUser($user_id){
            $this->initConnection();
            $res =  $this->dbGetOne("SELECT max(wiki_app/max_points) FROM testing WHERE item_id=:item_id AND type=:type AND user_id=:user_id", array('item_id'=>$this->item_id, 'type'=>$this->type, 'user_id'=>$user_id));
            if (!$res) $res = 0;
            $this->max_for_user = $res;
            if (count($this->modules)){
                foreach ($this->modules as $module) {
                    $module->getMaxForUser($user_id);
                }
            }                            
        }
        //get the number of attemptions for the current user
	public function getCountForUser($user_id){
            $this->initConnection();
            return $this->dbGetOne("SELECT count(*) FROM testing WHERE item_id=:item_id AND type=:type AND user_id=:user_id", array('item_id'=>$this->item_id, 'type'=>$this->type, 'user_id'=>$user_id));
	}
        //get the last attemption for the current user
	public function getLast($user_id){
            $this->initConnection();
            return $this->dbGetRow("SELECT * FROM testing WHERE item_id=:item_id AND user_id=:user_id AND type=:type ORDER BY timestamp DESC LIMIT 1", array('item_id'=>$this->item_id, 'type'=>$this->type, 'user_id'=>$user_id));
	}
        //get title of the deck, test belongs to
	public function getTitle(){
            $this->initConnection();
            return html_entity_decode($this->dbGetOne("SELECT title FROM deck_revision WHERE id=:item_id", array('item_id'=>$this->item_id)));
	}
        public function evaluation($limit, $mode, $user_id){
            $attempt = $this->evaluate($limit, $mode, $user_id);
            if (count($this->modules)){
                foreach ($this->modules as $module){
                    $module->evaluate($limit, $mode, $user_id, $attempt);
                }
            }
            return $attempt;
        }
       //save the test results for the evaluation of the user activity
        public function evaluate($limit, $mode, $user_id, $attempt=0){
            $eval = new Quest_eval;
            $eval->limit = $limit;
            $eval->mode = $mode;
            $eval->user_id = $user_id;
            $eval->type = $this->type;
            $eval->deck_id = $this->item_id;
            if ($attempt == 0){
                $attempt = $eval->getLastAttempt() + 1;
            }
            $eval->attempt = $attempt;
            $eval->create();
            return $attempt;
        }
        
        public function completeRandom(){
            foreach ($this->questions as $question){
                $question->checked = array();
                $max_checked = count($question->answers);
                $checked_number = rand(0,$max_checked);
                $answer_array = array();
                foreach($question->answers as $answer){
                    $answer_array[$answer['id']] = $answer['id'];
                }                
                $i = 0;
                while($i < $checked_number){
                    $checked_answer = array_rand ($answer_array);
                    $question->checked[] = $answer_array[$checked_answer];
                    unset($answer_array[$checked_answer]);
                    $i++;
                }
            }
        }
        
}
	
