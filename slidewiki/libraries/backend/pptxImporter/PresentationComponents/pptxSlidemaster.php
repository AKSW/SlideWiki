<?php
	require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'PresentationComponents'. DS .'PresentationComponent.php';

	class pptxSlidemaster extends PresentationComponent {
	
		// slidemaster specific parameters
		private $_rels_path;
		public $_title_style;
		public $_body_style;
		public $_other_style;
		
		public function __construct($slidemaster_path) {
			$this->_slidemaster_xml = $this->importSlidemasterXml($slidemaster_path); 
			$this->_rels_path = $this->getRelsPath($slidemaster_path);
			$this->_rels = $this->importRels($this->_rels_path);
			
			// importing styles
			$this->_title_style = $this->importTitleStyle($this->_slidemaster_xml);
			$this->_body_style = $this->importBodyStyle($this->_slidemaster_xml);
			$this->_other_style = $this->importOtherStyle($this->_slidemaster_xml);
			
			// getting number of shape tree objects
			$this->_numberof_shapes = $this->importNumberOfShapes($this->_slidemaster_xml);
			$this->_numberof_pics = $this->importNumberOfPics($this->_slidemaster_xml);
			$this->_numberof_gr_frames = $this->importNumberOfGrFrames($this->_slidemaster_xml);
			
			// getting shape tree objects
			if($this->_numberof_shapes != 0) {
				$this->_shapes = $this->importShapes($this->_slidemaster_xml, $this->_numberof_shapes);
			}
		}
		
		function importTitleStyle($slidemaster_xml) {
			$slidemaster_xml_root = $slidemaster_xml->documentElement;
			
			$query = 'p:txStyles/p:titleStyle';
			$p_title_style = Util::evaluateQueryOn($slidemaster_xml, $slidemaster_xml_root, $query); 
			$p_title_style = $p_title_style->item(0);
			
			$title_style = Util::getStyleList($p_title_style);
			
			return $title_style;
		}
		
		function importBodyStyle($slidemaster_xml) {
			$slidemaster_xml_root = $slidemaster_xml->documentElement;
			
			$query = 'p:txStyles/p:bodyStyle';
			$p_body_style = Util::evaluateQueryOn($slidemaster_xml, $slidemaster_xml_root, $query); 
			$p_body_style = $p_body_style->item(0);
			
			$body_style = Util::getStyleList($p_body_style);
			
			return $body_style;
		}
		
		function importOtherStyle($slidemaster_xml) {
			$slidemaster_xml_root = $slidemaster_xml->documentElement;
			
			$query = 'p:txStyles/p:otherStyle';
			$p_other_style = Util::evaluateQueryOn($slidemaster_xml, $slidemaster_xml_root, $query); 
			$p_other_style = $p_other_style->item(0);
			
			$other_style = Util::getStyleList($p_other_style);
			
			return $other_style;
		}
		
		function importSlidemasterXml($slidemaster_path) {
			$slidemaster_xml = Util::readFileToDOMDocument($slidemaster_path);
			return $slidemaster_xml;
		}
		
		function getRelsPath($layout_path) {
			$rels_path = 'ppt/slideMasters/_rels/' . substr($layout_path, 17) . '.rels';
			return $rels_path;
		}
		
	}
?>
