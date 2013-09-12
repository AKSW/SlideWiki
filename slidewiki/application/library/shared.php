<?php

/** Check if environment is development and display errors **/
function setReporting() {
	if (DEVELOPMENT_ENVIRONMENT == true) {
		error_reporting(E_ALL);
		ini_set('display_errors','On');
	} else {
		error_reporting(E_ALL);
		ini_set('display_errors','Off');
		ini_set('log_errors', 'On');
		ini_set('error_log', ROOT.DS.'tmp'.DS.'logs'.DS.'error.log');
	}
}

/** Check for Magic Quotes and remove them **/
function stripSlashesDeep($value) {
	$value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);
	return $value;
}
function removeMagicQuotes() {
	if ( get_magic_quotes_gpc() ) {
		$_GET    = stripSlashesDeep($_GET   );
		$_POST   = stripSlashesDeep($_POST  );
		$_COOKIE = stripSlashesDeep($_COOKIE);
	}
}

/** Check register globals and remove them **/
function unregisterGlobals() {
    if (ini_get('register_globals')) {
        $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
        foreach ($array as $value) {
            foreach ($GLOBALS[$value] as $key => $var) {
                if ($var === $GLOBALS[$key]) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
}

/** Main Call Function **/
function callHook() {
	global $url;

	$urlArray = array();
	$urlArray = explode("/",$url);
	
	$controller = $urlArray[0];
	array_shift($urlArray);
	$action = $urlArray[0];
	array_shift($urlArray);
	$queryString = $urlArray;

	$controllerName = $controller;
	$controller = ucwords($controller);
	
	$model = rtrim($controller, 's');
	$controller .= 'Controller';
	$dispatch = new $controller($model,$controllerName,$action);

	if ((int)method_exists($controller, $action)) {
		call_user_func_array(array($dispatch,$action),$queryString);
	} else {
		/* TODO: Error Generation Code Here */
	}
}

/** Autoload any classes that are required **/
function __autoload($className) {
	if (file_exists(ROOT . DS . 'application' . DS . 'library' . DS . $className . '.class.php')) {
		require_once(ROOT . DS . 'application' . DS . 'library' . DS . $className . '.class.php');
	} else if (file_exists(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php')) {
		require_once(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php');
	} else if (file_exists(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php')) {
		require_once(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php');
	} else {
	    if ($className != strtolower($className))
	    {
	        __autoload(strtolower($className));
	    }
	    else
	    {
    		/* Error Generation Code Here */
	    }
	}
}

// basic stuff here
setReporting();
removeMagicQuotes();
unregisterGlobals();
callHook();
