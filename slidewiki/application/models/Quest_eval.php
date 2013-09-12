<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Quest_eval
 *
 * @author maayan
 */
class Quest_eval extends Model{
    
    public $attempt;
    public $type;
    public $deck_id;
    public $max_points;
    public $user_id;
    public $mode;
    public $limit;
    public $wiki_app;
    public $dich;
    public $morgan;
    public $ripkey;
    public $mtf;
    public $title;
    
    private function initConnection() {
        // connect to db
        if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
            die ( "Could not connect to db" );
    }
   
    function addNew($attempt,$quest_id,$checked){
        $this->initConnection();
        $this->dbInsert('test_results', array ('quest_id' => $quest_id, 'attempt_id' => $attempt, 'checked' => $checked));
    }
    
    function getLast(){
        $this->initConnection();
        $test_id = $this->dbGetOne( 'SELECT test_id FROM test_results order by test_id DESC');
        return $test_id+1;
    }
    
    function create(){
        $this->initConnection();
        $this->dbInsert('testing', array (
            'attempt_id' => $this->attempt,
            'user_id' => $this->user_id, 
            'item_id' => $this->deck_id, 
            'mode' => $this->mode,
            'limit' => $this->limit,
            'type' => $this->type
       ));      
    }
    
    function finish($attempt, $deck_id){
        $this->initConnection();
        $this->dbQuery ( 'UPDATE testing SET wiki_app=:wiki_app, ripkey=:ripkey, mtf=:mtf, morgan=:morgan, dich=:dich, max_points=:max_points WHERE attempt_id=:attempt AND item_id=:deck_id', array (
            'attempt'=>$attempt,
            'deck_id' => $deck_id,
            'wiki_app' => $this->wiki_app, 
            'ripkey' => $this->ripkey,
            'mtf' => $this->mtf,
            'morgan' => $this->morgan,
            'dich' => $this->dich,
            'max_points' => $this->max_points
        ) );
    }    
    function getLastAttempt(){
        $this->initConnection();
        $last_attempt = 0;
        $last_attempt = $this->dbGetOne('SELECT max(attempt_id) FROM testing');        
        return $last_attempt;
    }
    
    function rank(){
        $item_id = $this->deck_id;  
        $model_array = array('wiki_app','mtf','morgan','ripkey','dich');
        $res = array();        
        foreach($model_array as $model){
            $rank = 1;
            $percentage = $model.'/max_points';
            $result = $this->dbQuery('SELECT attempt_id,' . $percentage .' AS points  FROM testing WHERE item_id=:item_id AND max_points > 0 ORDER BY ' . $percentage . ' DESC', array('item_id' => $item_id));
            for ($i=0; $i<count($result); $i++){                                
                $attempt_id = $result[$i]['attempt_id'];
                if ($i > 0){
                    $points = $result[$i-1]['points'];                    
                    if ($points-$result[$i]['points']){
                        $rank++;                  
                    } 
                }
                $res[$attempt_id][$model]['points'] = round($result[$i]['points'],3);
                $res[$attempt_id][$model]['rank'] = $rank;
            }
        }
        return $res;       
    }
    function getEmptyAttempt(){
        $attempt_id = $this->dbGetOne('SELECT attempt_id FROM testing WHERE user_id=0 ORDER BY attempt_id');
        return $attempt_id;
    }
    function getEmptyAttempts(){
        $result = $this->dbQuery('SELECT attempt_id FROM testing WHERE user_id=0');
        $res_array = array();
        foreach ($result as $att){
            $res_array[] = $att['attempt_id'];
        }
        return $res_array;
    }
    function getAttemptDecks(){
        $result = array();
        $result = $this->dbQuery('SELECT item_id FROM testing WHERE attempt_id=:attempt_id', array('attempt_id'=> $this->attempt));
        return $result;
    }
    function createTestFromBD($deck_id){

        $this->initConnection();        
        $questions_bd = array();
        $questions_bd_array = array();
        $questions = array();
        $questions_bd = $this->dbQuery('SELECT quest_id FROM test_results WHERE attempt_id=:attempt_id', array('attempt_id' => $this->attempt));
        if (count($questions_bd)){
            $questions_bd_array = array();
            $deck = new Deck();
            $deck->createFromIDLite ( $deck_id );
            $questions = array();
            $questions = $deck->getQuestions ();  
            $result = array();
            foreach ($questions_bd as $question_id){
                $questions_bd_array [] = $question_id['quest_id'];
            }
            foreach($questions['accepted'] as $question){
                if (in_array($question->id, $questions_bd_array)){
                    $result [] = $question;
                }
            }
            $this->max_points = 0;
            $this->wiki_app = 0;
            $this->morgan = 0;
            $this->ripkey = 0;
            $this->mtf = 0;
            $this->dich = 0;
            if (count($result)){                
                foreach ($result as $question){
                    $checked_string = $this->dbGetOne('SELECT checked FROM test_results WHERE attempt_id=:attempt_id AND quest_id=:quest_id', array('attempt_id' => $this->attempt, 'quest_id' =>$question->id));

                    $checked_res = array();
                    $checked = array();
                    $checked = explode(',',$checked_string);
                    $checked_res = array();
                    foreach ($checked as $answer){
                        if ($answer != '') {
                            $checked_res [] = $answer;
                        }
                    }
                    $this->countAllModels($question, $checked_res);
                }
                $this->dbQuery('UPDATE testing SET user_id=100500 WHERE item_id=:item_id AND attempt_id=:attempt', array('item_id'=>$deck_id, 'attempt' =>$this->attempt));
                $this->finish($this->attempt, $deck_id);
            }else{
                $this->dbQuery('DELETE FROM testing WHERE attempt_id=:attempt_id AND item_id=:item_id', array('attempt_id' => $this->attempt, 'item_id'=>$deck_id));
            }  
        }else {
           $this->dbQuery('DELETE FROM testing WHERE attempt_id=:attempt_id AND item_id=:item_id', array('attempt_id' => $this->attempt, 'item_id'=>$deck_id));
        }
        
    }
    
    function countAllModels($question, $checked){
        $difficulty = $question->difficulty;
        $count_correct = 0;
        $all = count($question->getAnswers());
        $count_correct_points = 0;
        $count_correct = 0;
        $mtf_points = 0;
        $ripkey_result = false;
        $morgan_points = 0;
        $dich_points = $difficulty;
        $penalty = 0;
        echo $question->id;
        print_r($checked);
        echo '<br>';
        if (count($checked)){            
            $correct_answers = $question->getRightAnswers();
            $correct = count($correct_answers);
            foreach ($correct_answers as $answer){
                if (in_array($answer['id'], $checked)){
                    $count_correct_points+= $difficulty/$correct;                    
                    $mtf_points+= $difficulty/$all;
                }
                if (!in_array($answer['id'], $checked)){  
                    $dich_points =  0;
                }
                if (count($checked) > $correct){                
                    $dich_points = 0;
                    $ripkey_result = false ;
                    $penalty = $difficulty*((count($checked) - $correct)/($all - $correct));
                }else{
                    $ripkey_result = true;
                    $penalty = 0;
                }
                $count_correct = $count_correct_points;
                $morgan_points = $count_correct;         
            }
            $distractors = $question->getDistractors();
            foreach ($distractors as $distractor){
                if (in_array($distractor['id'], $checked)){                     
                    $morgan_points-= $difficulty/count($distractors);	
                }
                if (!in_array($distractor['id'], $checked)){
                    $mtf_points+= $difficulty/$all;
                }
            }
            
            if ($mtf_points > 0)
            $this->mtf += $mtf_points;
            if ($ripkey_result) {
                $this->ripkey += $count_correct;
            }
            if ($morgan_points > 0 ){
                $this->morgan += $morgan_points;         
            }         
            $this->dich += $dich_points;
            if ($count_correct > $penalty){
                $this->wiki_app += ($count_correct - $penalty);
            }
            
        }            
        $this->max_points += $difficulty;
        
    }
    
}

?>
