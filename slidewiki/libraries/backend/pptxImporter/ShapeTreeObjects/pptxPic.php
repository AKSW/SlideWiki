<?php

	class pptxPic {
		// general properties
		private $_id;
		private $_name;
		private $_rId;
		private $_filename; // new id from the DB table
		private $_img_size; // information got by getimagesize() function
		public $_original_width;
		public $_original_height;
		
		// positioning
		private $_offset = array();
		private $_extent = array();
		
		// picture specific properties
		private $_img_location; // inside the presentation
		private $_file_ext; // jpeg, jpg, bmp etc.
		public $_uri; // on the server
		
		// API
		public $_html_string;
		
		// SlideWiki specific
		private $_uid;
		public $_db_id;
		
		public function __construct($slide_xml, $pic_num, $rels) {
			
			// SlideWiki specific
			$this->_uid = $_SESSION['uid'];
			
			$this->importGeneralProperties($slide_xml, $pic_num);
			$this->_img_location = $this->getLocation($this->_rId, $rels);
			$this->_file_ext = $this->get_file_extension($this->_img_location);
			// save to DB and filesystem routine
				// save to DB without URI field - get the id for filename
				$this->_uri = '';
				$this->_title = '';
				$this->_db_id = $this->saveToDB($this->_uid, $this->_uri, $this->_title);
				// generate URI based on filename and save to disk
				$this->_uri = $this->saveToDisc($this->_img_location, $this->_file_ext, $this->_uid, $this->_db_id);
				// update the DB with the generated URI (actual path to the file)
				$this->updateURIOf($this->_db_id, $this->_uri);
				// extract original size
				$this->_img_size = $this->extractOriginalSizeOf($this->_uri);
				$this->_original_width = $this->_img_size[0];
				$this->_original_height = $this->_img_size[1];
				// save width and height to the DB
				$this->updateSizeOf($this->_db_id, $this->_img_size);
			
			if( !empty($this->_offset) ) {
				$this->_offset = Util::offsetToPercents($this->_offset);
				$this->_extent = Util::extentToPercents($this->_extent);
			} 
			
			$this->_html_string = $this->toString($this->_id, $this->_uri, $this->_offset, $this->_extent);
		}
		
		/*
		 *  Getting general properties
		 */
		
		function importGeneralProperties($slide_xml, $pic_num) {
			$slide_xml_root = $slide_xml->documentElement;
			
			$query = 'p:cSld/p:spTree//p:pic';
			$pics  = Util::evaluateQueryOn($slide_xml, $slide_xml_root, $query); 
			$pic = $pics->item($pic_num);
			
			
			$query = 'string(p:nvPicPr/p:cNvPr/@id)';
			$id = Util::evaluateQueryOn($slide_xml, $pic, $query); 
			
			$query = 'string(p:nvPicPr/p:cNvPr/@name)';
			$id = Util::evaluateQueryOn($slide_xml, $pic, $query); 
			
			$query = 'string(p:nvSpPr/p:cNvPr/@id)';
			$name = Util::evaluateQueryOn($slide_xml, $pic, $query); 
			
			$query = 'string(p:blipFill/a:blip/@r:embed)';
			$rId = Util::evaluateQueryOn($slide_xml, $pic, $query); 
			
			$query = 'string(p:spPr/a:xfrm/a:off/@x)';
			$offset['x'] = Util::evaluateQueryOn($slide_xml, $pic, $query); 
			$query = 'string(p:spPr/a:xfrm/a:off/@y)';
			$offset['y'] = Util::evaluateQueryOn($slide_xml, $pic, $query); 
			
			$query = 'string(p:spPr/a:xfrm/a:ext/@cx)';
			$extent['cx'] = Util::evaluateQueryOn($slide_xml, $pic, $query); 
			$query = 'string(p:spPr/a:xfrm/a:ext/@cy)';
			$extent['cy'] = Util::evaluateQueryOn($slide_xml, $pic, $query); 
		
			// pushing parameters to class attributes
			$this->_id = $id;
			$this->_name = $name;
			$this->_rId = $rId;
			$this->_offset = $offset;
			$this->_extent = $extent;
		}
				
		function getLocation($rId, $rels) {
			// parse rels and look for location
			$location = '';
			//var_dump($rId);
			//var_dump($rels);
			foreach($rels as $rel_num => $attr) {
				foreach($attr as $key => $value) { 
					if( ($key == 'Id') && ($value == $rId)) {
						$location = $attr['Target'];
						$location = 'ppt' . substr($location, 2);
					}
				}
			}
			return $location;
		}
		
		function extractOriginalSizeOf($img_location) {
			return Util::extractOriginalSizeOf($img_location);
		}
		
		/*
		 *  SlideWiki Specific
		 */
		
		function generateURI() {
			$path = './upload/media/images/';
			$uri = $path . $uid . '/' . $id . '.' . $file_ext;
			
			return $uri;
		}
		 
		function saveToDisc($img_location, $file_ext, $uid, $db_id) {
			return Util::saveFileFromLocationToDisc($img_location, $file_ext, $uid, $path = './upload/media/images/', $db_id);
		}
		
		function saveToDB($user_id, $uri, $title) {
			// create image record in the database
			$img = new Media();
			$id = $img->saveImagetoDB($user_id, $uri, $title);
			return $id;
		}
		
		function updateURIOf($db_id, $uri) {
			$img = new Media();
			$img->updateURIOf($db_id, $uri);
		}
		
		function updateSizeOf($db_id, $img_size) {
			$img = new Media();
			$img->updateSizeOf($db_id, $img_size);
		}
		
		/*
		 *  API
		 */
		
		function toString($id, $uri, $offset, $extent) {
			$string = '';
			$string .= '<div ';
			//$string .= 'id="'. $id .'" ';
			if(Util::$_import_with_style) {
				$string .= 'style="';
				
				if(!empty($offset) || !empty($extent)) { 
					$string .= 'position:absolute;';
				}
				
				if($offset['x'] != 0) {
					$string .=  'left:'. $offset['x'] .'%;';
				}
				
				if($offset['y'] != 0) {
					$string .=  'top:'. $offset['y'] .'%;';
				}
				/*
				if($extent['cx'] != 0) {
					$right = 100 - $offset['x'] - $extent['cx'];
					$string .= 'right:'. $right .'%;';
				}
				if($extent['cx'] != 0) {
					$bottom = 100 - $offset['y'] - $extent['cy'];
					$string .= 'bottom:'. $bottom .'%;';
				}*/
				$string .= '"';
			}
			// calculate size of the image
			//Util::$_canvas_width - 600
			//Util::$_canvas_height - 450
			$image_width = Util::$_canvas_width*($extent['cx']/100);
			
			$string .= '>';
			$string .= '<img src="'. $uri .'?filter=Resize-width-'. $image_width .'"/>';
			$string .= '</div>';
			return $string;
		}	
		
		/*
		 *  Auxilary functions
		 */
		 
		function get_file_extension($file_name)
		{
		  $ext=substr(strrchr($file_name,'.'),1);
		  return strtolower($ext)=="jpeg"?"jpg":$ext;
		}
	}
?>