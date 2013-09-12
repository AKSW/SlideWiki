<?php
	require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'PresentationComponents'. DS .'PresentationComponent.php';
	require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'PresentationComponents'. DS .'pptxSlidemaster.php';

	class pptxLayout extends PresentationComponent {
	
		// layout specific parameters
		private $_rels_path;
		private $_layout_xml;
		public $_slidemaster;
		
		public function __construct($layout_path) {
			$this->_layout_xml = $this->importLayoutXml($layout_path); 
			$this->_rels_path = $this->getRelsPath($layout_path);
			$this->_rels = $this->importRels($this->_rels_path);
			
			$this->_slidemaster_path = $this->getSlidemasterPath($this->_rels);
			$this->_slidemaster = $this->importSlidemaster($this->_slidemaster_path);
			
			$this->_name = $this->importLayoutName($this->_layout_xml);
			
			// getting number of shape tree objects
			$this->_numberof_shapes = $this->importNumberOfShapes($this->_layout_xml);
			$this->_numberof_pics = $this->importNumberOfPics($this->_layout_xml);
			$this->_numberof_gr_frames = $this->importNumberOfGrFrames($this->_layout_xml);
			
			// getting shape tree objects
			if($this->_numberof_shapes != 0) {
				$this->_shapes = $this->importShapes($this->_layout_xml, $this->_numberof_shapes);
				// fill all gaps in variables (no NULL after this
				$this->_shapes = Util::compareWithSlidemaster($this->_shapes, $this->_slidemaster);
				// apply only on match
				$this->_shapes = Util::applyFormatting($this->_shapes);
			}
			
			if($this->_numberof_pics != 0) {
				$this->_pics = $this->importPics($this->_layout_xml, $this->_numberof_pics, $this->_rels);
			}
			
			
			if($this->_numberof_gr_frames != 0) {
				$this->_gr_frames = $this->importGrFrames($this->_layout_xml, $this->_numberof_gr_frames, $this->_rels);
			}
		}
		
		function importLayoutXml($layout_path) {
			$layout_xml = Util::readFileToDOMDocument($layout_path);
			return $layout_xml;
		}
		
		function importLayoutName($layout_xml) {
			$layout_xml_root = $layout_xml->documentElement;
			
			$query = 'string(p:cSld/@name)';
			
			$name = Util::evaluateQueryOn($layout_xml,
													$layout_xml_root, 
													$query);
													
			return $name;
		}
		
		function getRelsPath($layout_path) {
			$rels_path = 'ppt/slideLayouts/_rels/' . substr($layout_path, 17) . '.rels';
			return $rels_path;
		}
		
		function getSlidemasterPath($rels) {
			$slidemaster_path = '';
			foreach($rels as $rel) {
				if($rel['Type'] == Util::$SLIDEMASTER_TYPE) {
					$slidemaster_path = $rel['Target'];
				}
			}
			$slidemaster_path = 'ppt/' . substr($slidemaster_path,3);
			return $slidemaster_path;
		}
		
		function importSlidemaster($slidemaster_path) {
			return new pptxSlidemaster($slidemaster_path);
		}
		
	}
?>
