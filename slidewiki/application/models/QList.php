<?php

class QList extends Test {
    
    private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
    public function createFromID($id,$limit=0,$mode=1){
        $this->initConnection();
        $this->id = $id;
        $this->item_id = $id;
        $this->title = $this->getTitle();
        $this->questions = $this->getQuestions($limit,$mode);           			
        $this->quest_count = count($this->questions);
        $this->user = $this->getUser();
        foreach ($this->questions as $question){
            $question->module['id']= $this->item_id;
            $question->module['name']=$this->title;
        }
        $this->type = 'list';
    }
    public function getQuestions($limit,$mode){
        $questions = array();
        $result = array();
        foreach($this->dbGetCol('SELECT question_id from test_content WHERE test_id=:test_id ',array('test_id'=>$this->id))as $quest_id){
            $question = new Question();
            $question->createFromID($quest_id);
            $questions[]=$question;
        }
        $this->avg_diff = $this->countAvgDiff($questions);
        $result = $this->testMode($mode,$limit,$questions);
        return $result;
    }
    public function getTitle(){
        $result = $this->dbGetOne('SELECT title FROM user_tests WHERE id=:test_id',array('test_id'=>$this->id));
        return $result;
    }
    public function getUser(){
        $user = new User();
        $id = $this->dbGetOne('SELECT user_id FROM user_tests WHERE id=:id', array('id'=>$this->id));
        $user->createFromID($id);
        return $user;
    }
    public function delete(){
        $this->initConnection();
        $this->dbQuery('DELETE FROM user_tests WHERE id=:id ',array('id'=>$this->id));
        $this->dbQuery('DELETE FROM test_content WHERE test_id=:test_id ', array('test_id'=>$this->id));
    }
    public function deleteFrom($quest_id){
        $this->initConnection();
        $this->dbQuery('DELETE FROM test_content WHERE test_id=:id AND question_id=:quest_id',array('id'=>$this->id, 'quest_id'=>$quest_id));
    }
    public function rename($title){
        $this->initConnection();
        $this->dbQuery('UPDATE user_tests SET title=:title WHERE id=:id ',array('id'=>$this->id, 'title'=>htmlentities($title, ENT_QUOTES)));
    }
    public function addQuestion($quest_id){
        $this->initConnection();
        $this->dbInsert('test_content', array('test_id'=>$this->id,'question_id'=>$quest_id));           
    }
    public function getAll(){
        $this->initConnection();
        $result = array();
        $result= $this->dbQuery('SELECT * FROM user_tests');  
        return $result;
    }
}
?>
