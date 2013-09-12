<?php
// simple model 
class Model extends SQLQuery {
	protected $_model;

	function __construct() {

		$this->connect(DB_DSN,DB_USER,DB_PASSWORD);
		$this->_model = get_class($this);
		$this->_table = strtolower($this->_model)."s";
	}

	function __destruct() {
	}
    
//        function sortmddata ( $array , $by , $order , $type ){
//
//            //$array: the array you want to sort
//            //$by: the associative array name that is one level deep
//            ////example: name
//            //$order: ASC or DESC
//            //$type: num or str
//
//                    $sortby = "sort$by" ; //This sets up what you are sorting by
//
//                    $firstval = current ( $array ); //Pulls over the first array
//
//                    $vals = array_keys ( $firstval ); //Grabs the associate Arrays
//
//        foreach ( $vals as $init ){
//        $keyname = "sort$init" ;
//        $ $keyname = array ();
//        }
//            //This was strange because I had problems adding
//            //Multiple arrays into a variable variable
//            //I got it to work by initializing the variable variables as arrays
//            //Before I went any further
//
//        foreach ( $array as $key => $row ) {
//
//        foreach ( $vals as $names ){
//        $keyname = "sort$names" ;
//        $test = array ();
//        $test [ $key ] = $row [ $names ];
//        $ $keyname = array_merge ($ $keyname , $test );
//
//        }
//
//        }
//
//            //This will create dynamic mini arrays so that I can perform
//            //the array multisort with no problem
//            //Notice the temp array... I had to do that because I
//            //cannot assign additional array elements to a
//            //varaiable variable
//
//        if ( $order == "DESC" ){
//        if ( $type == "num" ){
//        array_multisort ($ $sortby , SORT_DESC , SORT_NUMERIC , $array );
//        } else {
//        array_multisort ($ $sortby , SORT_DESC , SORT_STRING , $array );
//        }
//        } else {
//        if ( $type == "num" ){
//        array_multisort ($ $sortby , SORT_ASC , SORT_NUMERIC , $array );
//        } else {
//        array_multisort ($ $sortby , SORT_ASC , SORT_STRING , $array );
//        }
//        }
//
//            //This just goed through and asks the additional arguments
//            //What they are doing and are doing variations of
//            //the multisort
//
//        return $array ;
//        }
        
        function sluggify($url){
	    # Prep string with some basic normalization
	    $url = strtolower($url);
	    $url = strip_tags($url);
	    $url = stripslashes($url);
	    $url = html_entity_decode($url);

	    # Remove quotes (can't, etc.)
	    $url = str_replace('\'', '', $url);

	    # Replace non-alpha numeric with hyphens
	    $match = '/[^a-z0-9]+/';
	    $replace = '-';
	    $url = preg_replace($match, $replace, $url);

	    $url = trim($url, '-');

	    return $url;
    }

}