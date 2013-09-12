<?php
	include_once ROOT . DS .'libraries'. DS .'backend'. DS .'simple_html_dom'. DS .'simple_html_dom.php';
	include_once ROOT . DS .'libraries'. DS .'backend'. DS .'manipulation_lib'. DS .'manipulation_lib.php';
	include_once ROOT . DS .'libraries'. DS .'backend'. DS .'SlideCompare'. DS .'SlideCompare.php';	
	
	class htmlPresentation {
		public $createddeckid;
                public $createddecktitle;
		public $_deck_num;
		public $_compare_table;
		
		/*
		 * The path to uploaded file
		 */
		private $_file_uri;
		
		/*
		 * An array with three keys:
		 * full_filename = index.html
		 * filename_ext = html
		 * short_filename = index
		 */
		private $_filename;
		
		/*
		 * User ID for the current user - the one who uploaded the file
		 */
		private $_user_id;
		
		/*
		 * File type can be text/html, application/zip or application/x-zip-compressed
		 */
		private $_file_type;
		
		/*
		 * User path for the upload folder, e.g.
		 * /upload/html/$user_id/
		 */
		private $_user_path;
		
		/*
		 * DD-MM-YYYY_UNIQUEID
		 * Folder name for the files, i.e.
		 * /upload/html/$user_id/$folder_name
		 * /upload/media/$user_id/$folder_name
		 */
		private $_folder_name;
		
		/*
		 * $file_uri can point to the index.html or archive file, containing index.html
		 * in the root of the archive structure.
		 */
	
		public function __construct($file_uri, $user_id, $file_type) {
			
			$this->_user_id = $user_id;
			$this->_file_type = $file_type;
			$this->_file_uri = $file_uri;
			$this->_folder_name = ManipulationLib::getFormattedDate() . "_" . ManipulationLib::generateRandomName();	
			$this->_user_path = "." . DS . "upload" . DS . "html" . DS . $this->_user_id;
				//chdir("./upload/html/$this->_user_id/");
			$this->_filename = ManipulationLib::getFileName($this->_file_uri);

			// Copy the file to the user location
			// return the path for the index.html
			$this->_short_index_html_path = $this->getIndexPath($this->_file_type, $this->_filename, $this->_user_path, $this->_folder_name);
			
			// Parse index.html file for the slides and meta-data
			$index_path = $this->_user_path . DS . $this->_short_index_html_path;
			
			// Getting the metadata from the presentation
			// $this->title
			// $this->deck_id - the container deck_id, deck can contain other decks 
			// $this->deck_revision_id
			$meta_data = $this->getMetaData($index_path);
			foreach($meta_data as $key => $value) {
				$this->$key = $value;
			}
			
			// Getting slides' information
			$this->_slides = $this->parseHtmlFile($index_path, $this->_folder_name, $this->_user_id, $this->_user_path);
			
			//var_dump($this->_slides); die;
			if(!count($this->_slides)) die;
			// create slides objects
			// compare them to the previous slides' revisions
			$user = new User();
			$user->createFromID($user_id);
			$new_deck = new Deck ();
			$new_deck->user=$user;
			$new_deck->title=$user->username." - new deck";
			$new_deck->create();
			$new_deck->deleteItemFromPosition(1);
			$slides_obj= array();
			foreach($this->_slides as $key => $slide) {
				//$old_slide = new Slide();
				//$old_slide->createFromID($slide['slide_rev_id']);
				$new_slide = new Slide ();
				$user = new User();
				$user->createFromID($user_id);
				$new_slide->user = $user;
				$new_slide->comment = "imported";
				$slide_content = $slide['content'];
				$new_slide->content = $slide_content;
				$new_slide->deck = $new_deck->id;
				// handle position!
				//$new_slide->position = $slide['position_in_deck'];
				$new_slide->create ();				
				$slides_obj[]=$new_slide;
				// compare slides!
				//$slide_identical = SlideCompare::compareSlideToSlide($old_slide, $new_slide);
				
				//$compare_table[$key]['old_slide'] = $old_slide;
				//$compare_table[$key]['new_slide'] = $new_slide;
				//$compare_table[$key]['slide_identical'] = $slide_identical;
			}
			//var_dump($slides_obj);
			$new_deck->addContent($slides_obj);
			$this->createddeckid=$new_deck->id;
                        $this->createddecktitle = $new_deck->title;
			//$this->_compare_table = $compare_table;
		}
		
		
		/*
		 * Copy uploaded file to the user dir
		 * Returns index.html path
		 */
		private function getIndexPath($file_type, $filename, $user_path, $folder_name) {
			// working in the user directory
			chdir($user_path);
				
			$index_path = NULL;
			
			switch($file_type) {
				case 'text/html':
					
					// Create a dir with unique ID
					if(!file_exists($folder_name)) {
						mkdir("./" . $folder_name);
					}
					
					// move index.html to the dir
					rename($filename['full_filename'], $folder_name . DS . $filename['full_filename']);
					
					$index_path = $folder_name . DS . $filename['full_filename'];
						
					chdir($filename['short_filename']);
					//$meta_data = $this->getMetaData($filename['full_filename']);
					//$slides = $this->parseHtmlFile($filename['full_filename']);
					break;
			
					// .zip archive
				case 'application/zip':
				case 'application/x-zip-compressed':
					if(ManipulationLib::unpackZipArchiveTo($filename['short_filename'], $filename['filename_ext'], $folder_name)) {
						$index_path = $folder_name . DS . "index.html";
					}
						
					//$meta_data = $this->getMetaData("index" . "." . "html");
					//$slides = $this->parseHtmlFile("index" . "." . "html");
						
					break;
			}
			
			
			// going back to the root dir
			chdir("../../..");		

			return $index_path;
		}
		
		private function getMetaData($index_path) {
			$html = file_get_html($index_path);
			// Extracting the meta data
			$meta_data = array();
			
			$title = implode($this->extractElement($html, 'title'));
			$meta_data['title'] = $title;
			
			$deck_id;
			$deck_revision_id;
			foreach($html->find('meta') as $element) { 
				switch($element->name) {
					case 'deck_id':
						$deck_id = (int) $element->content;
						break;
					case 'deck_revision_id':
						$deck_revision_id = (int) $element->content;
						break;
					default:
						break;
				}
			}
			$meta_data['deck_id'] = $deck_id;
			$meta_data['deck_revision_id'] = $deck_revision_id;
			
			return $meta_data;
		}
		
		private function parseHtmlFile($index_path, $folder_name, $user_id, $user_path) {
			$html = file_get_html($index_path);
			
			// saving images to the /upload/media/$user_id/$folder_name
			// renaming
			$this->saveImg($html, $folder_name, $user_id, $user_path);
						
			
			// grabbing text from slides
			$slides_dom = $html->find('.deck-container .slide');
			$slides = array();
			
			// dumping DOM objects to string
			foreach($slides_dom as $slide_dom) {
				//$slide_params = explode("-",$slide_dom->id);
				//$obj['deck_rev_id'] = $slide_params[1];
				//$obj['obj_type'] = $slide_params[2];
				//$obj['slide_rev_id'] = $slide_params[3];
				//$obj['position_in_deck'] = $slide_params[4];
				//skip nested class slides
				if($slide_dom->parent()->class=='deck-container'){
					$obj['content'] = $slide_dom->innertext();
					array_push($slides, $obj);
				}
			}
			
			return $slides;
		}
		
		private function saveImg($html, $folder_name, $user_id, $user_path) {
			// go to index.html folder
			chdir($user_path . DS . $folder_name);
			
			// look for all img elements
			foreach($html->find('img') as $element) {
				// if picture is local
				if(file_exists($element->src)) {
					// this_presentation_dir/user_id/html -> you are in upload folder
					$filename = ManipulationLib::getFileName($element->src);
                                        $title = $filename['short_filename'];
                                        $media = new Media();
                                        $new_id = $media->saveImagetoDB($this->_user_id, '', $title);
                                        $new_file_name = $new_id.'.'.$filename['filename_ext'];
					rename($element->src, "../../../media/images/$this->_user_id/" .  $new_file_name);
					$element->src = "./upload/media/images/$this->_user_id/" .  $new_file_name;
                                        $img_size = Util::extractOriginalSizeOf($element->src);
                                        $media->updateSizeOf($new_id, $img_size);
                                        $media->updateURIOf($new_id, $element->src);
				}
			}
			
			chdir("../../..");
		}
		
		/*
		 *  Provide a string to the $elements variable
		 *  i.e. if you are looking for tag <title> pass "title"
		 *  string to the function, e.g. extractElement("title");
		 *  by default returns array with elements without outer tags
		 */
		
		private function extractElement($html, $elements, $without_tags = true) {
			$dom_objects = $html->find($elements);
			
			$objects = array();
			
			// dumping DOM objects to string
			foreach($dom_objects as $dom_object) {
				if($without_tags) {
					array_push($objects, $dom_object->innertext());
				} else {
					array_push($objects, $dom_object->outertext());
				}				
			}
			
			return $objects;
		}
		
		public function saveToDB($user_id, $slides, $meta_data) {			
			// create a deck with the name of a presentation
			$content = array ();
			$new_deck = new Deck ();
			$user = new User ();
			$user->createFromID ( $user_id );
			$new_deck->user = $user;
			$new_deck->comment = "Deck imported from HTML file!";
			$new_deck->title = $meta_data['title'];
			$new_deck->create ($meta_data['deck_revision_id']);
			// get each slide and put it in the deck
			foreach($slides as $index => $slide) {
				$new_slide = new Slide ();
				$new_slide->user = $user;
				$new_slide->comment = "new slide created!";
				$slide_content = $slide;
				$new_slide->content = $slide_content;
				$new_slide->deck = $new_deck->id;
				$new_slide->position = $index + 1;
				$new_slide->create ();
				$content [] = $new_slide;
			}
			$new_deck->addContent ( $content );
			//$new_deck->commit();
			
			// save image to server
			// create image entries in the DB table
			return $new_deck->id;
		}
	}
?>