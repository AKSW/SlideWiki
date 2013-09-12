<?php

class Media extends Model {
	
	public $type;
	public $uri;
	public $timestamp;
	public $title;
	
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	
	public function saveImagetoDB($user_id, $uri, $title = NULL) {
		$this->initConnection();
		$image = $this->dbInsert ( 'media', array ('user_id' => $user_id, 'type' => 'img', 'uri' => $uri, 'title' => $title ) );
		return $image;
	}
	
	// get image from the DB for this user
	public function getImagesFor($user_id) {
		$this->initConnection();
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT uri, title, timestamp, original_width, original_height
			FROM media
			WHERE type="img" AND user_id=' . $user_id . ' ORDER BY timestamp DESC' ) as $row ) {
			
			$image = new Media();
			$image->uri = $row['uri'];
			$image->uri = 'http://' . $_SERVER['HTTP_HOST'] . substr($image->uri,1);
			$image->title = $row['title'];
			$image->original_width = $row['original_width'];
			$image->original_height = $row['original_height'];
			$image->timestamp = $row['timestamp'];
			$image->type = "img";
			$res [] = $image;
		}
		return $res;
	}
	
	public function updateURIOf($db_id, $uri) {
		$this->initConnection();
		$data['id'] = $db_id;
		$data['uri'] = $uri;
		$this->dbUpdate( 'media', $data );
	}
	
	public function updateSizeOf($db_id, $img_size) {
		$this->initConnection();
		$data['id'] = $db_id;
		$data['original_width'] = $img_size[0];
		$data['original_height'] = $img_size[1];
		$this->dbUpdate( 'media', $data );
	}
	
	public function addRelationsFor($db_id, $deck_rev_id, $slide_rev_id) {
		$this->initConnection();
		$img_relations = $this->dbInsert ( 'media_relations', array ('media_id' => $db_id, 'slide_rev_id' => $slide_rev_id, 'deck_rev_id' => $deck_rev_id ) );
		return $img_relations;
	}
}
	
