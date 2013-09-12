<?php
	
class Style extends Model {
	public $id;
	public $owner;
	public $name;
	public $scss_varfunc;
	public $scss;
	public $css;
	public $comment;
	public $based_on;
	private function initConnection(){
		// connect to db
		if( $this->connect(DB_DSN, DB_USER, DB_PASSWORD) == 0 )
			die ("Could not connect to db");
	}
	
	public function createFromID($id){
		$this->initConnection();
		
		// query here
		$res = $this->dbGetRow('SELECT * FROM style WHERE id=:id', array ('id' => $id ));
		$this->id = $res['id'];
		$this->owner = $res['user_id'];
		$this->name = $res['name'];
		$this->scss_varfunc = $res['scss_varfunc'];
		$this->scss = $res['scss'];
		$this->css = $res['css'];
		$this->comment = $res['comment'];
		$this->based_on = $res['based_on'];	
		//var_dump($this); die;
	}
	public function create(){
		$this->initConnection();
		$this->id=$this->dbInsert ( 'style', array ('user_id' => $this->user_id, 'name' => $this->name, 'scss_varfunc' => $this->scss_varfunc, 'scss' => $this->scss, 'css' => $this->css,'based_on' => $this->based_on,'comment' => $this->comment ) );	
	}	
	public function save(){
	$this->dbQuery ( 'UPDATE style SET name=:name,scss_varfunc=:scss_varfunc,scss=:scss,css=:css,comment=:comment WHERE id=:id', array ('id' => $this->id, 'name' => $this->name, 'scss_varfunc' => $this->scss_varfunc, 'scss' => $this->scss, 'css' => $this->css, 'comment' => $this->comment ) );
	}	
	public function getAll() {
		return $this->dbQuery ( 'SELECT id,name FROM style ORDER BY id' );
	}
	public function getCSS($id) {
		header ( 'Content-Type: text/css' );
		return $this->dbGetOne ( 'SELECT css FROM style WHERE id=' . $id );
	}
	public function getSCSS($id) {
		return $this->dbGetOne ( 'SELECT scss FROM style WHERE id=' . $id );
	}
	public function getSCSSVarfunc($id) {
		return $this->dbGetOne ( 'SELECT scss_varfunc FROM style WHERE id=' . $id );
	}
	public function getStyle($id) {
		return $this->dbGetRow ( 'SELECT * FROM style WHERE id=' . $id );
	}
	public function add($name, $scss_varfunc, $scss, $css, $based_on=NULL) {
		$this->dbInsert ( 'style', array ('user_id' => $this->user_id, 'name' => $name,'scss_varfunc' => $scss,'scss_varfunc' => $scss, 'css' => $css,'based_on' => $based_on ) );
	}	
}
	
