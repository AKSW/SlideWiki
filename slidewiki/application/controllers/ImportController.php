<?php

require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS . 'pptxPresentation.php';
require_once ROOT . DS .'libraries'. DS .'backend'. DS .'htmlImporter'. DS . 'htmlPresentation.php';
require_once ROOT . DS .'libraries'. DS .'backend'. DS .'texImporter'. DS . 'texPresentation.php';
require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'utils'. DS .'tbszip.php';

class ImportController extends Controller {
	function uploadOOXML() {
		$this->set ( 'page_title', "SlideWiki - Import .pptx" );
	}
	function handleOOXMLUpload() {
	
		if (isset($_FILES['uploaded']))
		{
			$target = './upload/pptx/' . $_SESSION['uid'] . '/'; 
			if(!file_exists($target)) {
				mkdir($target);
			}
			$target = $target . basename( $_FILES['uploaded']['name']);

			$pptxType = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
			
			if($_FILES['uploaded']['type'] != $pptxType) {
				$message = '<div id="error-type">File type not supported! Please, upload .pptx files.</div>';
				// die();
			}
			elseif ($_FILES['uploaded']['error'] === UPLOAD_ERR_OK)
			{
				if(move_uploaded_file($_FILES['uploaded']['tmp_name'], $target)) {

					if(isset($_POST['import_with_style'])) {
						$import_with_style = $_POST['import_with_style'];
					} else {
						$import_with_style = false;
					}
					
					// quick fix for cheking file formatting
					$zip = new clsTbsZip();
					$zip->Open($target);
					if($zip->FileExists('docProps/core.xml')) {
						$proper_pptx = true;
					} else {
						$proper_pptx = false;
					}						
					$zip->Close();
					
					if($proper_pptx) {
						$pr = new pptxPresentation($target, $import_with_style);
						$deck_num = $pr->saveToDB($this->_user['id']);
					} else {
						$message = '<div id="error-type">Not proper .pptx format! Open Office .pptx files are not supported yet.</div>';
					}
								
				} else {
					$message = 'Sorry, there was a problem uploading your file.';
				}
			} else {
				$message = $this->file_upload_error_message($_FILES['uploaded']['error']); 
			}	
		}
		else
		{
			$message = '<div id="error-type">There was no file to upload.</div>';
		}
		
		if(empty($message)) {
			$this->set('deck_num', $deck_num);
		} else {
			$this->set('message', $message);
		}
	}

	function uploadHTML() {
		$this->set ( 'page_title', "SlideWiki - Import deck.js HTML" );
	}
	function handleHTMLUpload() {
		if (isset($_FILES['uploaded']))	{
			$target = './upload/html/' . $_SESSION['uid'] . '/'; 
			if(!file_exists($target)) {
				mkdir($target);
			}
			$target = $target . basename( $_FILES['uploaded']['name']);

			$type = $_FILES['uploaded']['type'];
			//$htmlType = 'text/html';
			//var_dump($_FILES['uploaded']); die;
			
			if ($_FILES['uploaded']['error'] === UPLOAD_ERR_OK) {
				if(move_uploaded_file($_FILES['uploaded']['tmp_name'], $target)) {
					
					// html parser here
					$presentation = new htmlPresentation($target, $this->_user['id'], $type);
					
				} else {
					$message = 'Sorry, there was a problem uploading your file.';
				}
			} else {
				$message = $this->file_upload_error_message($_FILES['uploaded']['error']); 
			}	
		}
		else
		{
			$message = '<div id="error-type">There was no file to upload.</div>';
		}
		
		if(empty($message)) {
                    $deck = new Deck();
			$this->set('presentation', $presentation);
			header ( 'Location:deck/' . $presentation->createddeckid . $deck->sluggify($presentation->createddecktitle) );
		} else {
			$this->set('message', $message);
		}
	}
	
	function uploadLATEX() {

	}
	function handleLATEXUpload() {
		if (isset($_FILES['uploaded']))	{
			$target = './upload/tex/' . $_SESSION['uid'] . '/'; 
			if(!file_exists($target)) {
				mkdir($target);
			}
			$target = $target . basename( $_FILES['uploaded']['name']);
			
			$type = $_FILES['uploaded']['type'];
			//'application/octet-stream'
			//'text/x-tex'
			//'application/zip'
			
			var_dump($type); die;
			
			if ($_FILES['uploaded']['error'] === UPLOAD_ERR_OK) {
				if(move_uploaded_file($_FILES['uploaded']['tmp_name'], $target)) {
					
					// tex parser here
					$pr = new texPresentation($target, $this->_user['id'], $type);
					$deck_num = $pr->_deck_num;
								
				} else {
					$message = 'Sorry, there was a problem uploading your file.';
				}
			} else {
				$message = $this->file_upload_error_message($_FILES['uploaded']['error']); 
			}	
		}
		else
		{
			$message = '<div id="error-type">There was no file to upload.</div>';
		}
		
		if(empty($message)) {
			$this->set('deck_num', $deck_num);
		} else {
			$this->set('message', $message);
		}
	}

	// Auxilary functions
	function file_upload_error_message($error_code) {
		switch ($error_code) { 
			case UPLOAD_ERR_INI_SIZE: 
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini. ' . 'Please contact the administrator of the site.'; 
			case UPLOAD_ERR_FORM_SIZE: 
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ' . 'Please contact the administrator of the site.'; 
			case UPLOAD_ERR_PARTIAL: 
				return 'The uploaded file was only partially uploaded. ' . 'Please contact the administrator of the site.'; 
			case UPLOAD_ERR_NO_FILE: 
				return 'No file was uploaded. ' . 'Please contact the administrator of the site.'; 
			case UPLOAD_ERR_NO_TMP_DIR: 
					return 'Missing a temporary folder. ' . 'Please contact the administrator of the site.'; 
			case UPLOAD_ERR_CANT_WRITE: 
				return 'Failed to write file to disk. ' . 'Please contact the administrator of the site.'; 
			case UPLOAD_ERR_EXTENSION: 
				return 'File upload stopped by extension. ' . 'Please contact the administrator of the site.'; 
			default: 
				return 'Unknown upload error'; 
	} 
}
}
