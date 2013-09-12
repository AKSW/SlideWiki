<?php
class Template {

	protected $variables = array();
	protected $_controller;
	protected $_action;
	protected $_noHeader;
	protected $_noFooter;

	function __construct($controller,$action,$noheader = false,$nofooter = false) {
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_noHeader = $noheader;
		$this->_noFooter = $nofooter;
	}

	/** Set Variables **/
	function set($name,$value) {
		$this->variables[$name] = $value;
	}
	
	function disableHeader(){
		$this->_noHeader = true;
	}
	
	function disableFooter(){
		$this->_noFooter = true;
	}

	/** Display Template **/
    function render() {
		extract($this->variables);
		
		// include specific or default header
		if( !$this->_noHeader ){
			if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php')) {
				include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php');
			} else {
				include (ROOT . DS . 'application' . DS . 'views' . DS . 'header.php');
			}
		}
		
		// include template
        include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php');		 
        
        // include specific or default footer
        if( !$this->_noFooter ){
			if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php')) {
				include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php');
			} else {
				include (ROOT . DS . 'application' . DS . 'views' . DS . 'footer.php');
			}
		}
    }

}