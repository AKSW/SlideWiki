<?php

// define constants
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));



// get requested URI
$url = isset($_GET['url']) ? $_GET['url'] : 'index/view';

 

// include bootstrap
require_once (ROOT . DS . 'application' . DS . 'library' . DS . 'bootstrap.php');



?>

