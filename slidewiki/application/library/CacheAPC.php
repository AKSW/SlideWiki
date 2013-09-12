<?php

class CacheAPC {

    public $iTtl = 600; // Time To Live
    public $bEnabled = false; // APC enabled?

    // constructor
    function __construct() {
        $this->bEnabled = extension_loaded('apc');
    }

    // get data from memory
    function getData($sKey) {
        $bRes = false;
        $vData = apc_fetch($sKey, $bRes);
        return ($bRes) ? $vData :null;
    }

    // save data to memory
    function setData($sKey, $vData) {
        return apc_store($sKey, $vData, $this->iTtl);
    }

    // delete data from memory
    function delData($sKey) {
        $bRes = false;
        apc_fetch($sKey, $bRes);
        return ($bRes) ? apc_delete($sKey) : true;
    }
}

?>
