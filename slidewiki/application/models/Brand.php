<?php
	
class Brand extends Model {
	public $deck_id;
	public $description;
	public $image;
	public $url;

	private function initConnection(){
		// connect to db
		if( $this->connect(DB_DSN, DB_USER, DB_PASSWORD) == 0 )
			die ("Could not connect to db");
	}
	
	public function create(){
		$this->initConnection();
		$this->id=$this->dbInsert ( 'brand', array ('deck_revision_id' => $this->deck_id, 'description' => $this->description, 'image' => $this->image, 'url' => $this->url ) );	
	}	

}
	
