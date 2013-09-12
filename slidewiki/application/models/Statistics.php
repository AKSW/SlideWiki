<?php

class Statistics extends Model {
	public $number_of_slides;
	public $number_of_slide_revisions;
	public $number_of_decks;
	public $number_of_deck_revisions;
	public $number_of_users;
	public $number_of_questions;
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	
	public function calculateAll() {
		$this->number_of_slides=$this->getNumberOfSlides();
		$this->number_of_slide_revisions=$this->getNumberOfSlideRevisions();
		$this->number_of_decks=$this->getNumberOfDecks();
		$this->number_of_deck_revisions=$this->getNumberOfDeckRevisions();
		$this->number_of_users=$this->getNumberOfUsers();
		$this->number_of_questions=$this->getNumberOfQuestions();
	}
	public function getNumberOfSlides(){
		$res = $this->dbGetCol ( 'SELECT count(*) FROM slide' );
		return $res ['0'];
	}
	public function getNumberOfSlideRevisions(){
		$res = $this->dbGetCol ( 'SELECT count(*) FROM slide_revision' );
		return $res ['0'];
	}
	public function getNumberOfDecks(){
		$res = $this->dbGetCol ( 'SELECT count(*) FROM deck' );
		return $res ['0'];
	}
	public function getNumberOfDeckRevisions(){
		$res = $this->dbGetCol ( 'SELECT count(*) FROM deck_revision' );
		return $res ['0'];
	}
	public function getNumberOfUsers(){
		$res = $this->dbGetCol ( 'SELECT count(*) FROM users' );
		return $res ['0'];
	}	
	public function getNumberOfQuestions(){
		$res = $this->dbGetCol ( 'SELECT count(*) FROM questions' );
		return $res ['0'];
	}				
}
