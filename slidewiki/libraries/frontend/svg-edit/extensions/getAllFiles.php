<?php
function getFileList($dir) 
{
    $list = array();
    $d = dir($dir);
    while (($e = $d->read()) !== false)
        
      $list[] = $e;
            
    return $list;
}
echo json_encode(getFileList('figures/'));
?>

