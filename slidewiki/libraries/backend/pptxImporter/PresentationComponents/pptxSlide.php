<?php
	require_once ROOT . DS . 'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'PresentationComponents'. DS .'PresentationComponent.php';
	require_once ROOT . DS . 'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'PresentationComponents'. DS .'pptxLayout.php';
	
	
	class pptxSlide extends PresentationComponent {
	
		// Slide specific properties
		public $_slide_num;
		private $_layout;
		private $_layout_path;
		
		// slide objects
		public $_pics;
		private $_gr_frames;
		
		// public properties for test purposes
		public $_equal = false;
		
		public function __construct($slide_xml, $slide_num) {
			
			// general slide properties
			$this->_slide_num = $slide_num;
			$this->_rels_path = Util::$SLIDE_RELS_PATH . 'slide' . $slide_num . '.xml.rels';
			$this->_rId = $this->importrId($slide_num);
			$this->_id = $this->importId($this->_rId);
			$this->_rels = $this->importRels($this->_rels_path);
			$this->_layout_path = $this->getLayoutPath($this->_rels);
			
			// get layout and default styles for text etc
			$this->_layout = $this->importLayout($this->_layout_path);
			
			// getting number of shape tree objects
			$this->_numberof_shapes = $this->importNumberOfShapes($slide_xml);
			$this->_numberof_pics = $this->importNumberOfPics($slide_xml);
			$this->_numberof_gr_frames = $this->importNumberOfGrFrames($slide_xml);
			
			// getting shape tree objects
			if($this->_numberof_shapes != 0) {
				$this->_shapes = $this->importShapes($slide_xml, $this->_numberof_shapes);
				// no NULLs after comparing with slidemaster
				Util::compareWithSlidemaster($this->_shapes, $this->_layout->_slidemaster);
				
				// only matching cases here
				Util::compareShapeWithShape($this->_shapes, $this->_layout->_shapes);
				Util::applyFormatting($this->_shapes);
				
				//var_dump($this->_shapes); die;
			}
			
			if($this->_numberof_pics != 0) {
				$this->_pics = $this->importPics($slide_xml, $this->_numberof_pics, $this->_rels);
			}
			
			
			if($this->_numberof_gr_frames != 0) {
				$this->_gr_frames = $this->importGrFrames($slide_xml, $this->_numberof_gr_frames, $this->_rels);
			}
			
			
			
			if( (($this->_numberof_shapes != 0) && 
				$this->atLeastAShapeHasText($this->_numberof_shapes, $this->_shapes)) ||
				($this->_numberof_pics != 0) ) {
				$this->_html_slide = @$this->toString($this->_shapes, $this->_numberof_shapes, 
													$this->_pics, $this->_numberof_pics,
													$this->_gr_frames, $this->_numberof_gr_frames);
			} else {
				$this->_html_slide = ' ';
			}
			
		}
		
		function getLayoutPath($rels) {
			$path = '';
			foreach($rels as $rel) {
				if($rel['Type'] == Util::$LAYOUT_TYPE) {
					$path = $rel['Target'];
				}
			}
			$path = 'ppt/' . substr($path, 3);
			return $path;
		}
		
		function importLayout($layout_path) {
			return new pptxLayout($layout_path);
		}
	}
?>
