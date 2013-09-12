<?php

class UserGroup extends Model {
	public $users;
	public $category;
	public $deck;

	public function getAll($deck_id,$category) {
		$users = array ();
		$results = $this->dbQuery ( 'SELECT * FROM user_group WHERE deck_revision_id=:id AND category =:category', array ('id' => $deck_id, 'category'=>$category) );
		if (! empty ( $results )) {
			foreach ( $results as $result ) {
				$user=new User();
				$user->createFromID($result ['user_id']);
				array_push ($users,$user);
			}
		}
		$this->users=$users;
		return $users;	
	}	
}
	
