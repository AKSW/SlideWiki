<?php

class Question extends Model {
	
	public $id;
	public $user;
	public $item_id;
        public $item_owner;
	public $question;
	public $answers;
	public $based_on;
	public $mark;
	public $timestamp;
	public $guessing;
	public $difficulty;
	public $diff_count;
	public $module;
	public $diff_flag = 0;
        public $slide_revision;
        public $checked;

	
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	public function create() {
		$this->initConnection();                
		$id = $this->dbInsert('questions',array('item_id'=>$this->item_id,'based_on'=>$this->based_on,'mark'=>$this->mark,'difficulty'=>$this->difficulty,'diff_count'=>$this->diff_count,'user_id'=>$this->user->id,'question'=>addslashes($this->question)));
		return $id;
	}
	public function createFromID($id) {
		$this->initConnection ();
		$res = $this->dbGetRow ( 'SELECT * FROM questions WHERE id=:id', array ('id' => $id ) );
		$this->id = $id;
		$user = new User();
		$user->createFromID($res ['user_id']);
		$this->user = $user;
		$this->item_id = $res ['item_id'];
		$this->question = stripslashes($res ['question']);
		$this->answers = $this->getAnswers();
		$this->based_on = $res['based_on'];
		$this->mark = $res['mark'];
                $difficulty = $res['difficulty'];
//                if ($res['diff_count'] == NULL || $res['diff_count']<=0) $difficulty = $res['difficulty'];
//                else {
//                    $difficulty = $res['diff_count'];
//                    $this->diff_flag = 1;
//                }
		$this->difficulty = $difficulty;
		$this->diff_count = $res['diff_count'];
		$this->timestamp = $res['timestamp'];
		$this->guessing = $this->guessCount();		
	}
        public function copy($user_id){
           $this->question = str_replace("\n"," ", $this->question);
           $this->question = str_replace("<br>"," ", $this->question);
           $id = $this->dbInsert('questions',array('item_id'=>$this->item_id,'based_on'=>$this->based_on,'mark'=>$this->mark,'difficulty'=>$this->difficulty,'diff_count'=>0,'user_id'=>$user_id,'question'=>$this->question));
           $copy = new Question();
           $copy->id = $id;
           foreach ($this->answers as $answer){
               $copy->addAnswer(stripslashes($answer['answer']), stripslashes($answer['explanation']), $answer['is_right']);
           }
           return $id;
        }
        public function delete(){
            $this->dbQuery('DELETE FROM questions WHERE id=:id',array('id'=>$this->id));
            $this->dbQuery('DELETE FROM answers WHERE question_id=:id',array('id'=>$this->id));
            $children = $this->getAllChildren();
            foreach ($children as $question){
                $this->dbQuery('DELETE FROM questions WHERE id=:id',array('id'=>$question->id));
                $this->dbQuery('DELETE FROM answers WHERE question_id=:id',array('id'=>$question->id));
            }
        }
        public function getFirstRevision(){
            $this->initConnection();
            if ($this->based_on > 0){
                $question = new Question();
                $question->createFromID($this->based_on);
                $res = $question->getFirstRevision();
            }else $res = $this;
            return $res;
        }
        public function getFirstChildren(){
            $this->initConnection();
            $res = array();
            foreach ($this->dbQuery('SELECT id FROM questions WHERE based_on=:id', array('id'=>$this->id))as $row){
                $res [] = $row['id'];
            }
            return $res;
        }
        public function getAllRevisions() {
            $this->initConnection();
            $result = array();
            $first = new Question();
            $first = $this->getFirstRevision();
            $sort_numcie = array();
            foreach($first->answers as $index=>$answer) {
                $sort_numcie[] = $answer['is_right'];
            }
            array_multisort($sort_numcie, SORT_DESC, $first->answers);
            $result [] = $first;
            $result = array_merge($result, $first->getAllChildren());
            $sort_numcie = array();
            foreach($result as $index=>$question) {
                $sort_numcie[] = $question->timestamp;
            }
            array_multisort($sort_numcie, SORT_DESC, $result);                    
            return $result;
        }
        public function getAllChildren(){
            static $result = array();
            if (count($this->getFirstChildren())){
                foreach ($this->getFirstChildren() as $next){
                    $question = new Question();
                    $question->createFromID($next);
                    $sort_numcie = array();
                    foreach($question->answers as $index=>$answer) {
                        $sort_numcie[] = $answer['is_right'];
                    }
                    array_multisort($sort_numcie, SORT_DESC, $question->answers);
                    $result [] = $question;
                    $question->getAllChildren();
                }
            }
            return $result;
        }
        public function useQuestRevision(){
            $this->initConnection();            
            $difficulty = $this->dbGetOne ( 'SELECT difficulty FROM questions WHERE id=:id', array ('id' => $this->id ) );
            $this->dbQuery('DELETE FROM questions WHERE id=:id',array('id'=>$this->id));
            $this->difficulty = $difficulty;
            $id = $this->dbInsert('questions',array('id'=>$this->id,'item_id'=>$this->item_id,'based_on'=>$this->based_on,'mark'=>$this->mark,'difficulty'=>$this->difficulty,'diff_count'=>$this->diff_count,'user_id'=>$this->user->id,'question'=>  addslashes($this->question)));
//            $this->dbQuery('INSERT INTO questions (id, item_id, based_on, mark, difficulty, diff_count, user_id, question) VALUES ('.$this->id .','.$this->item_id.','.$this->based_on.','.$this->mark.','.$this->difficulty.','.$this->diff_count.','.$this->user->id.','.htmlentities($this->question, ENT_QUOTES).')');
            return $this->id;
        }
        public function edit() {
		$this->initConnection ();
                $this->question = str_replace("\n"," ", $this->question);
                $this->question = str_replace("<br>"," ", $this->question);
               	$this->dbQuery('UPDATE questions SET question=:question, mark=:mark, difficulty=:difficulty, diff_count=:diff_count WHERE id=:id', array ('id' => $this->id, 'mark' => $this->mark, 'question' => addslashes($this->question), 'difficulty' => $this->difficulty, 'diff_count' => $this->diff_count));
		
	}
	public function getOwnerID() {
		$this->initConnection ();
		return $this->dbGetOne('SELECT user_id FROM questions WHERE id=' . $this->id);
	}
        public function getItemType() {
		$this->initConnection ();
		return $this->dbGetOne('SELECT item_type FROM questions WHERE id=' . $this->id);
	}
        public function getLastRevision(){	
		$timestamp = $this->timestamp;
		$last_id = $this->id;
		foreach ($this->dbQuery( 'SELECT * FROM questions WHERE based_on=:id', array ('id' => $last_id ) ) as $question_row) { 
			if ($timestamp < $question_row['timestamp']) {
				$question = new Question();
				$question->createFromID($question_row['id']);
				$last_id = $question->getLastRevision();
			}
		}		
		return $last_id;
	}
	public function addAnswer($answer,$explanation,$is_right){
            $answer = str_replace("\n"," ", $answer);
            $explanation = str_replace("\n"," ", $explanation);
            $answer = str_replace("<br>"," ", $answer);
            $explanation = str_replace("<br>"," ", $explanation);
            $res = $this->dbInsert('answers',array('question_id'=>$this->id,'answer'=>addslashes($answer), 'is_right'=>$is_right, 'explanation'=>addslashes($explanation)));
            $this->guessUpdate();
            return $res;
	}
	public function getAnswer($newAnswerId){
		$res = $this->dbGetRow ( 'SELECT * FROM answers WHERE id=:id', array ( 'id' => $newAnswerId ) );
                $res['answer'] = stripslashes($res['answer']);
                $res['explanation'] = stripslashes($res['explanation']);
		return $res;
	}
        public function getAnswers() {
		$answers = array ();
		foreach ( $this->dbQuery ( 'SELECT id, answer, explanation, is_right FROM answers WHERE question_id=' . $this->id ) as $row ){
                    $row['answer'] = stripslashes($row['answer']);
                    $row['explanation'] = stripslashes($row['explanation']);	
                    $answers[] = $row;
                }
		return $answers;
	}
	public function getRightAnswers() {
		$answers = array ();
		foreach ( $this->dbQuery ( 'SELECT id, answer, explanation, is_right FROM answers WHERE question_id=' . $this->id . ' AND is_right="yes"' ) as $row ){
                    $row['answer'] = stripslashes($row['answer']);
                    $row['explanation'] = stripslashes($row['explanation']);
                    $answers[] = $row;
                        
		}
		return $answers;
	}
        public function getDistractors(){
		$answers = array ();
		foreach ( $this->dbQuery ( 'SELECT id, answer, explanation, is_right FROM answers WHERE question_id=' . $this->id . ' AND is_right="no"' ) as $row ){
                    $row['answer'] = stripslashes($row['answer']);
                    $row['explanation'] = stripslashes($row['explanation']);
                    $answers[] = $row;
                        
		}
		return $answers;
	}
        public function editAnswer($answerId, $answer, $explanation, $is_right){
            $answer = str_replace("\n"," ", $answer);
            $explanation = str_replace("\n"," ", $explanation);
            $answer = str_replace("<br>"," ", $answer);
            $explanation = str_replace("<br>"," ", $explanation);
            $this->dbQuery( 'UPDATE answers SET answer=:answer, explanation=:explanation, is_right=:is_right WHERE id=:id ', array ( 
                'answer' => addslashes($answer),
		'explanation' => addslashes($explanation),
		'is_right' => $is_right,
		'id' => $answerId
            ) );
            $this->guessUpdate();
            return $this->getAnswer($answerId);
	}
	public function removeAnswer($answerId){
		$this->dbQuery( "DELETE FROM answers WHERE id=:id", array ( 'id' => $answerId ) );
		$this->guessUpdate();
	}
        //get QuestID by answerID
        public function getID($answerId){
		return $this->dbGetOne('SELECT question_id FROM answers WHERE id=:id LIMIT 1', array ('id' => $answerId ));
	}
        public function addExplanation($answerId, $explanation){
            $this->dbQuery("UPDATE answers SET explanation='" . addslashes($explanation) . "' WHERE id=" . $answerId);
            $explanation = str_replace("\n"," ", $explanation);
            $explanation = str_replace("<br>"," ", $explanation);
	}
	public function getExplanation($answerId){
		$res = $this->dbGetOne( 'SELECT explanation FROM answers WHERE id=:id LIMIT 1', array ('id' => $answerId ) );
                $res = $res;
                return $res;
	}
	public function guessCount() {		
		$count_right = $this->dbGetOne('SELECT count(id) FROM answers WHERE is_right="yes" AND question_id=:id', array('id'=>$this->id));
		$count_false = $this->dbGetOne('SELECT count(id) FROM answers WHERE is_right="no" AND question_id=:id', array('id'=>$this->id));
		if ($count_right > 0 && $count_false > 0){
			$guessing = $count_right/($count_right+$count_false);
		}else $guessing = 0;
		return $guessing;
	}
	public function guessUpdate() {		
		$guessing = $this->guessCount();
		$this->dbQuery("UPDATE questions SET guessing=:guessing WHERE id=:id", array('guessing'=>$guessing,'id'=>$this->id));
		$this->guessing = $guessing;
	}
	public function markDoubtful($user_id, $comment){
		$this->dbQuery("UPDATE questions SET mark='doubtful' WHERE id=" . $this->id);
		$this->dbInsert('doubtful', array('question_id' => $this->id, 'user_id' => $user_id, 'comment' => $comment));
	}	
	public function unMarkDoubtful(){
		$this->dbQuery("UPDATE questions SET mark='accepted' WHERE id=" . $this->id);
		$this->dbQuery('DELETE FROM doubtful WHERE question_id = ' . $this->id);
	}	
	//update count of correct/all_answers
        public function editCorrect($correct){            
            $this->dbQuery('UPDATE questions SET all_answers = all_answers+1 WHERE id=' . $this->id);
            $incorrect = 1-$correct;
            $this->dbQuery('UPDATE questions SET incorrect = incorrect+' . $incorrect . ' WHERE id=' . $this->id);
            if ($this->dbGetOne('SELECT all_answers FROM questions WHERE id=' . $this->id)>10){
                $this->dbQuery('UPDATE questions SET diff_count=incorrect/all_answers*4+1 WHERE id=' . $this->id);
            }
        }
        public function reAssign($new_slide_id){
            $slide = new Slide();
            $slide->id = $new_slide_id;
            $new_basic_id = $slide->getBasicId();
            $this->dbQuery('UPDATE questions SET item_id = '.$new_basic_id.' WHERE id=' . $this->id);
        }
        
}
	
