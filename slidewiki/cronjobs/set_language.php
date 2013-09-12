<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

// include bootstrap
require_once (ROOT . DS . 'application' . DS . 'config' . DS . 'config.php');
require_once (ROOT . DS . 'libraries' . DS . 'backend' . DS . 'phpQuery' . DS .'phpQuery.php');


class setLanguage extends Model {
	
    public $language;
    public $id;
    public $user_id;
    public $decks;
    
    
    function __construct() {
        $this->initConnection();
        $decks = $this->dbquery('SELECT * FROM deck WHERE language IS NULL OR language="" OR language="-"');
        echo 'decks:' . count($decks);
        foreach ($decks as $task){
            $deck = new Deck();
            $deck->deck_id = $task['id'];
            $deck->createFromID($deck->getLastRevisionID());
            $language = $deck->detectLanguage();
            $deck->setLanguageFull($language);
        }
        $slides = $this->dbquery('SELECT * FROM slide WHERE language IS NULL OR language="" OR language="-"');
        echo 'slides:' . count($slides);
        $decks = array();
        foreach ($slides as $task){            
            $slide = new Slide();
            $slide->slide_id = $task['id'];
            $id = $slide->getLastRevisionID();
            $decks_array = $this->dbGetOne('SELECT deck_revision_id FROM deck_content WHERE item_id =:id AND item_type="slide" LIMIT 1',array('id' => $id));
            if ($decks_array){
                if (!in_array($decks_array, $decks))
                $decks[] = $decks_array;
            }
       }
       echo 'decks:' . count($decks) . '<br>';
        foreach ($decks as $task){
            echo $task;
            $deck = new Deck();
            $deck->createFromID($task);
            $language = $deck->detectLanguage();
            $deck->setLanguageFull($language);
           echo ' done ';
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
	
new setLanguage;
?>

