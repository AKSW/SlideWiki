<?php

class Url extends Model {
	public $id;
	public $short;
	public $url;
	public $stamped;
	public $user_id=0;
	
	private function initConnection() {
		// connect to db
		if ($this->connect ( DB_DSN, DB_USER, DB_PASSWORD ) == 0)
			die ( "Could not connect to db" );
	}
	private function generate_short_url($numAlpha = 4) {
		$this->initConnection ();
		$listAlpha = 'abcdefghijklmnopqrstuvwxyz0123456789';
		return str_shuffle ( substr ( str_shuffle ( $listAlpha ), 0, $numAlpha ) );
	}
	function addget_short_url($in) {
		$this->initConnection ();
		//delete urls older than 5 days
		$this->dbQuery ( 'DELETE FROM short_urls where stamped < DATE_SUB(NOW() , INTERVAL 5 DAY);');
		$out = $this->generate_short_url ();
		$result = $this->dbQuery ( 'SELECT * FROM short_urls WHERE short =:short', array ('short' => $out ) );
		while($tmp=count($result)){
			$out = $this->generate_short_url ();
			$result = $this->dbQuery ( 'SELECT * FROM short_urls WHERE short =:short', array ('short' => $out ) );
		}
		$this->dbInsert('short_urls',array('short'=>$out,'url'=>$in,'user_id'=>$this->user_id));
		$this->short = $out;
		$this->url = $in;
		return $out;
	}
	function get_short_url($out) {
		$this->initConnection ();
		$result = $this->dbGetRow ( 'SELECT * FROM short_urls WHERE short =:short', array ('short' => $out ) );
		return  $result ['url'];
	}
}
	
