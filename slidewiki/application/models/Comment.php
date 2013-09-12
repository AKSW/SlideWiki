<?php

class Comment extends Model {
	
	public $id;
	public $user;
	public $item_id;
	public $item_type;
	public $title;
	public $text;
	public $creationTime;
	public $replies;
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	public function createFromID($id) {
		$this->initConnection ();
		$res = $this->dbGetRow ( 'SELECT * FROM comment WHERE id=:id', array ('id' => $id ) );
		$this->id = $id;
		$user = new User ();
		$user->createFromID ( $res ['user_id'] );
		$this->user = $user;
		$this->item_id = $res ['item_id'];
		$this->item_type = $res ['item_type'];
		$this->title = html_entity_decode($res ['title']);
		$this->text = nl2br(htmlspecialchars(html_entity_decode($res ['text'])));
		$this->creationTime = $res ['timestamp'];
		$this->replies=$this->getReplies();
	}
	public function getReplies() {
		$res = array ();
		foreach ( $this->dbQuery ( 'SELECT c.id,title,c.timestamp,text FROM comment c INNER JOIN users u ON(user_id=u.id) WHERE item_type="comment" AND item_id=' . $this->id  . ' ORDER BY timestamp DESC' ) as $row ){
			$comment = new Comment ();
			$comment->createFromID ( $row ['id'] );
			$res [] = $comment;
		}
		return $res;
	}
	public function create() {
		$this->initConnection();
		return $this->dbInsert('comment',array('item_type'=>$this->item_type,'item_id'=>$this->item_id,'user_id'=>$this->user->id,'title'=>htmlentities($this->title),'text'=>htmlentities($this->text)));
	}
}
	
