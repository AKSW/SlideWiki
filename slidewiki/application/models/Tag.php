<?php
	
class Tag extends Model {
	public $item_type;
	public $item_id;
	public $tag;

	
	private function initConnection(){
		// connect to db
		if( $this->connect(DB_DSN, DB_USER, DB_PASSWORD) == 0 )
			die ("Could not connect to db");
	}
	
	public function save(){
		$this->initConnection();
		$this->tag = preg_replace("|[^a-zA-Z0-9]|i","",$this->tag);
		$this->dbInsert('tag',array('item_type'=>$this->item_type,'item_id'=>$this->item_id,'tag'=>htmlentities($this->tag)));
	}	
	public function delete(){
		$this->initConnection();
		$this->dbQuery('DELETE FROM tag WHERE item_type=:item_type AND item_id=:item_id AND tag=:tag', array('item_type'=>$this->item_type,'item_id'=>$this->item_id,'tag'=>$this->tag));
	}	
	public function deleteAllItemTags() {
		$this->initConnection();
		$this->dbQuery('DELETE FROM tag WHERE item_type=:item_type AND item_id=:item_id',array('item_type'=>$this->item_type,'item_id'=>$this->item_id));
	}	
	public function getAll(){

	}
	
	function getTagCloudData() {
        
        $query = $this->dbQuery("SELECT `tag`, COUNT(`item_id`)  AS freq FROM `tag` GROUP BY `tag`");
        foreach($query as $key => $tag){
            if ($tag['tag'] == '' || $tag['freq'] == 1){
                unset($query[$key]);
            }
        }
	return $query;       
    }
}
	
