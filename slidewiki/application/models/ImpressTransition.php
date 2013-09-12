<?php
	
class ImpressTransition extends Model {
	public $css;
	public $slide_position;
	public $deck_id;
	public $user_id;
	private function initConnection(){
		// connect to db
		if( $this->connect(DB_DSN, DB_USER, DB_PASSWORD) == 0 )
			die ("Could not connect to db");
	}
	

	public function create(){
		$this->initConnection();
		$this->id=$this->dbInsert ( 'impress_transition', array ('user_id' => $this->user_id, 'deck_revision_id' => $this->deck_id, 'slide_position' => $this->slide_position, 'css' => $this->css ) );	
	}	
	//input : deck_id and user_id
	
	public function getStylesForUserDeck(){
		$transitions = array ();
		$results=$this->dbQuery ( 'SELECT * FROM impress_transition WHERE user_id=:user_id AND deck_revision_id=:deck_revision_id  ORDER BY slide_position ASC', array ('user_id' => $this->user_id, 'deck_revision_id' => $this->deck_id) );
		if (! empty ( $results )) {
			foreach ( $results as $result ) {
				$t=new ImpressTransition();
				$t->css=$result ['css'];
				$t->slide_position=$result ['slide_position'];
				$t->deck_id=$this->deck_id;
				$t->user_id=$this->user_id;
				array_push ($transitions,$t);
			}
		}
		return $transitions;
	}
	//returns transitions from other users if exists
	public function getAllStylesForDeck(){
		$transitions = array ();
		$results=$this->dbQuery ( 'SELECT * FROM impress_transition WHERE deck_revision_id=:deck_revision_id  GROUP BY user_id', array ('deck_revision_id' => $this->deck_id) );
		if (! empty ( $results )) {
			foreach ( $results as $result ) {
				$t=new User();
				$t->createFromID($result ['user_id']);
				array_push ($transitions,$t);
			}
		}
		return $transitions;
	}	
	public function deleteAllCSS(){
		$transitions = array ();
		$results=$this->dbQuery ( 'DELETE FROM impress_transition WHERE user_id=:user_id AND deck_revision_id=:deck_revision_id  ORDER BY slide_position ASC', array ('user_id' => $this->user_id, 'deck_revision_id' => $this->deck_id) );
	}	
}
	
