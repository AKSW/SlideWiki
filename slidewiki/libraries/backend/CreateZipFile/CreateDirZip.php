<?php
include_once('CreateZipFile.inc.php');

class createDirZip extends createZipFile {
 
	function get_files_from_folder($directory, $put_into) {
		if ($handle = opendir($directory)) {
			while (false !== ($file = readdir($handle))) {
				if (is_file($directory.$file)) {
					$fileContents = file_get_contents($directory.$file);
					$this->addFile($fileContents, $put_into.$file);
				} elseif ($file != '.' and $file != '..' and is_dir($directory.$file)) {
					$this->addDirectory($put_into.$file.'/');
					$this->get_files_from_folder($directory.$file.'/', $put_into.$file.'/');
				}
			}
		}
		closedir($handle);
	}
}


?>