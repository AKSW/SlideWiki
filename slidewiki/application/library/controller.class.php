<?php

//require_once (ROOT . DS . 'application' . DS . 'core' . DS . 'SlideWiki.php');
require_once (ROOT . DS . 'application' . DS . 'core' . DS . 'SlideWikiAuth.php');


// simple controller class
class Controller {
	
	protected $_model;
	protected $_controller;
	protected $_action;
	protected $_template;
	protected $_user;
	
	protected $_noRender;
	protected $_noHeader;
	protected $_noFooter;
	
	function __construct($model, $controller, $action) {
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_model = $model;
		$user_id = SlideWikiAuth::getUserId ();
		if ($user_id) {
			$user = new User ();
			$user->createFromID ( $user_id );
			$this->_user = array ('is_authorized' => SlideWikiAuth::isAuthorized (), 'id' => $user_id, 'name' => $user->username )// SlideWikiAuth::isAuthorized() ? $sw->getUserData($_SESSION['uid']) : ''
;
		} else {
			$this->_user = array ('is_authorized' => false, 'id' => 0, 'name' => '' );
		}
		
		//$this->$model = new $model;
		

		if (! $this->_noRender) {
			$this->_template = new Template ( $controller, $action, $this->_noHeader, $this->_noFooter );
			
			$this->set ( 'user', $this->_user );
		}
	}
	
	function set($name, $value) {
		$this->_template->set ( $name, $value );
	}
	
	function __destruct() {
		if (! $this->_noRender)
			$this->_template->render ();
	}

}