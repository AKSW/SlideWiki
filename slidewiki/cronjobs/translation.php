<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

// include bootstrap
require_once (ROOT . DS . 'application' . DS . 'config' . DS . 'config.php');
require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'phpQuery' . DS .'phpQuery.php');


class Queue extends Model {
	
    public $language;
    public $id;
    public $user_id;
    public $tasks;
    
    
    function __construct() {
        $this->initConnection();
        
        $tasks = $this->dbquery('SELECT * FROM translation_cronjobs');
        //print_r($tasks);
        foreach ($tasks as $task){           
            $id = $task['revision_id'];
            $language = $task['to_language'];
            $old_deck = new Deck();
            $old_deck->createFromID($id);
            $new_deck = new Deck();
            $new_deck->createFromID($task['future_deck']);
            $new_deck->translation_status = 'translated';
            $new_deck->content = $old_deck->content;
            if ($new_deck->translate_parent($language)){
                $old_deck->removeFromQueue($id,$language);
            }            
        }
    }
    
    private function initConnection() {
        // connect to db
        if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
                die ( "Could not connect to db" );
    }
}

function __autoload($className) {
		if (file_exists(ROOT . DS . 'application' . DS . 'library' . DS . $className . '.class.php')) {
			require_once(ROOT . DS . 'application' . DS . 'library' . DS . $className . '.class.php');
		} else if (file_exists(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php')) {
			require_once(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php');
		} else if (file_exists(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php')) {
			require_once(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php');
		} else {
			if ($className != strtolower($className))
			{
				__autoload(strtolower($className));
			}
			else
			{
				/* Error Generation Code Here */
			}
		}
	}
	
new Queue;
?>
