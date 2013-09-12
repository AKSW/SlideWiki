<?php
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'PresentationComponents' . DS . 'pptxSlide.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'utils' . DS . 'util.php';
require_once ROOT . DS . 'application' . DS . 'config' . DS . 'config.php';

class pptxPresentation {
	
	// general presentation properties
	private $_title = '';
	private $_canvas; // cx, cy
	

	// number of presentation components
	private $_numberof_slides = 0;
	private $_numberof_layouts = 0;
	private $_numberof_slidemasters = 0;
	
	// presentation components
	public $_slides = array();
	private $_layouts = array ();
	private $_slidemasters = array ();
	
	// SlideWiki specific
	public $_slide_db_ids = array ();
	
	public function __construct($pptx_uri, $import_with_style) {
		
		// send .pptx file path to the util class
		Util::$_pptx_uri = $pptx_uri;
		Util::$_import_with_style = $import_with_style;
		// presentation general properties
		$this->_title = $this->importTitle ();
		$this->_canvas = $this->importCanvas ();
		Util::$_canvas = $this->_canvas;
		
		// counting number of presentation components
		$this->_numberof_slides = $this->importNumberOfSlides ();
		$this->_numberof_layouts = $this->importNumberOfLayouts ();
		$this->_numberof_slidemasters = $this->importNumberOfSlidemasters ( $pptx_uri );
		
		// checking for existency and
		// importing presentation components
		$this->_slides = $this->importSlides ( $this->_numberof_slides);
	
		// test section
		//var_dump($this->_numberof_slides);
		//var_dump($this->coincidenceTest($this->_slides));
	}
	
	/*
		 *  General presentation properties
		 */
	
	function importTitle() {
		$core_xml = Util::readFileToDOMDocument ( Util::$CORE_XML );
		$core_xml_root = $core_xml->documentElement;
		$title = Util::evaluateQueryOn ( $core_xml, $core_xml_root, 'string(dc:title)' );
		return $title;
	}
	
	function importCanvas() {
		
		$presentation_xml = Util::readFileToDOMDocument ( Util::$PRESENTATION_XML );
		$presentation_xml_root = $presentation_xml->documentElement;
		
		$canvas ['cx'] = Util::evaluateQueryOn ( $presentation_xml, $presentation_xml_root, 'string(p:sldSz/@cx)' );
		
		$canvas ['cy'] = Util::evaluateQueryOn ( $presentation_xml, $presentation_xml_root, 'string(p:sldSz/@cy)' );
		return $canvas;
	}
	
	/*
		 *  Functions for counting numbers of presentation components
		 */
	
	function importNumberOfSlides() {
		$app_xml = Util::readFileToDOMDocument ( Util::$APP_XML );
		$app_xml_root = $app_xml->documentElement;
		
		$tag = 'Slides';
		$numberof_slides = ( int ) $app_xml_root->getElementsByTagName ( $tag )->item ( 0 )->nodeValue;
		
		return $numberof_slides;
	}
	
	function importNumberOfLayouts() {
		$numberof_layouts = Util::countFilesIn ( 'ppt/slideLayouts/', 'slideLayout' );
		return $numberof_layouts;
	}
	
	function importNumberOfSlidemasters() {
		$numberof_slidemasters = Util::countFilesIn ( 'ppt/slideMasters/', 'slideMaster' );
		return $numberof_slidemasters;
	}
	
	/*
		 *  Importing functions
		 */
	
	function importSlides($numberof_slides) {
		
		$slides = array ();
		for($slide_num = 1; $slide_num <= $numberof_slides; $slide_num ++) {
			
			$slide_xml = Util::readFileToDOMDocument ( Util::$SLIDE_PATH . 'slide' . $slide_num . '.xml' );
			array_push ( $slides, new pptxSlide ( $slide_xml, $slide_num) );
		}
		return $slides;
	}
	
	function importLayouts($numberof_layouts) {
		$layouts = array ();
		for($layout_num = 1; $layout_num <= $this->_numberof_layouts; $layout_num ++) { // filename count starts with 1;
			

			$layout_xml = Util::readFileToDOMDocument ( Util::$LAYOUT_PATH . 'slideLayout' . $layout_num . '.xml' );
			array_push ( $layouts, new pptxSlide ( $layout_xml, $layout_num ) );
		}
		return $layouts;
	}
	
	function importSlidemasters($pptx_uri) {
	
	}
	
	/*
		 *  API
		 */
	
	public function toString() {
		$string = array ();
		for($i = 0; $i < $this->_numberof_slides; $i ++) {
			array_push ( $string, $this->_slides [$i]->_html_slide );
		}
		$string = implode ( $string );
		
		return $string;
	}
	
	/*
		 *  SlideWiki specific
		 */
	
	public function saveToDB($user_id) {			
		// create a deck with the name of a presentation
		$content = array ();
		$new_deck = new Deck ();
		$user = new User ();
		$user->createFromID ( $user_id );
		$new_deck->user = $user;
		$new_deck->comment = "new deck created!";
		$new_deck->title = $this->_title;
		$deck_rev_id = $new_deck->create ();
		// get each slide and put it in the deck
		for($i = 0; $i < $this->_numberof_slides; $i ++) {
			$new_slide = new Slide ();
			$new_slide->user = $user;
			$new_slide->comment = "new slide created!";
			$slide_content = $this->_slides [$i]->_html_slide;
			$new_slide->content = $slide_content;
			$new_slide->deck = $new_deck->id;
			$new_slide->position = $i + 1;
			$slide_rev_id = $new_slide->create ();
			$content [] = $new_slide;
			// populate media_relations table
			if(sizeof($this->_slides[$i]->_pics) != 0) {
				foreach($this->_slides[$i]->_pics as $pic) {
					$img = new Media();
					$img->addRelationsFor($pic->_db_id, $deck_rev_id, $slide_rev_id);
				}
			}
		}
		$new_deck->addContent ( $content );
		
		
		
		
		return $new_deck->id;
	}
	
	/*
		 *  Testing section
		 */
	
	function coincidenceTest($slides) {
		$count = 0;
		foreach ( $slides as $slide ) {
			if ($slide->_equal) {
				$count ++;
			} else {
				var_dump ( $slide->_slide_num );
			}
		}
		return $count;
	}
}
?>
