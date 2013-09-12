<?php

class Activity extends Model {
    public $timestamp;
    public $subject;
    public $type;
    public $object = array();
    public $text;
    
    private function initConnection() {
        // connect to db
        if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
                die ( "Could not connect to db" );
    }
    
   function __construct($timestamp, $subject, $type, $object, $text = false) {
        $this->timestamp = $timestamp;
        $this->subject = $subject;
        $this->type = $type;
        $this->object = $object;
        if ($text!=false){
            $this->text = $text;
        }
    }
}
?>
