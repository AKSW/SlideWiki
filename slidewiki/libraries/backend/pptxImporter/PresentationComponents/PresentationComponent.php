<?php
	require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'ShapeTreeObjects'. DS .'pptxShape.php';
	require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'ShapeTreeObjects'. DS .'pptxPic.php';
	require_once ROOT . DS .'libraries'. DS .'backend'. DS .'pptxImporter'. DS .'ShapeTreeObjects'. DS .'pptxGrFrame.php';

	class PresentationComponent {
	
		// general properties
		private $_name;
		private $_title;
		private $_rId;
		private $_id;
		private $_rels = array(); 
		
		// Shape Tree Objects counters
		private $_numberof_shapes = 0;
		private $_numberof_pics = 0;
		private $_numberof_gr_frames = 0;
		
		// Shape Tree Objects
		private $_shapes = array(); // pptxShape objects here
		private $_pics = array();
		private $_gr_frames = array();
		
		// API
		public $_html_slide;
		private $_html_slide_textonly;
		
		public function __construct() {
		
		}
		
		/*
		 *  General properties
		 */
		 
		function importrId($slide_num) {
			$presentation_xml_rels = Util::readFileToDOMDocument(Util::$PRESENTATION_XML_RELS);
			$presentation_xml_rels_root = $presentation_xml_rels->documentElement;
			
			$query = '/*/*[@Target = "slides/slide' . (string) $slide_num . '.xml"]';
			
			$rId = Util::evaluateQueryOn(	$presentation_xml_rels,
									$presentation_xml_rels_root, 
									$query);
			
			$rId = $rId->item(0)->getAttribute('Id');
			return $rId;
		}
		
		function importId($rId) { 
			$presentation_xml = Util::readFileToDOMDocument(Util::$PRESENTATION_XML);
			$presentation_xml_root = $presentation_xml->documentElement;
			
			$query = '/*/p:sldIdLst/*[@r:id = "'. $rId .'"]';
			
			$id = Util::evaluateQueryOn(	$presentation_xml,
									$presentation_xml_root, 
									$query);
									
			$id = (int) $id->item(0)->getAttribute('id');
			
			return $id;
		}
		
		function importRels($rels_path) {
			
			$slide_xml_rels = Util::readFileToDOMDocument($rels_path);
			
			$slide_xml_rels_root = $slide_xml_rels->documentElement;
			
			$rels_list = $slide_xml_rels_root->getElementsByTagName('Relationship');
			
			for($i = 0; $i < $rels_list->length; $i++) {
				$rel = $rels_list->item($i);
				foreach($rel->attributes as $name => $value) {
					$rels[$i][$name] = $value->nodeValue;
				}
			}
			return $rels;
		}
		
		function importTitle($html_slide_textonly) {
			// look for <h2> tag
			$pattern = '/<h2>/i';
			preg_match($pattern, $html_slide_textonly, $open_tag, PREG_OFFSET_CAPTURE);
			$pattern = '/<\/h2>/i';
			preg_match($pattern, $html_slide_textonly, $close_tag, PREG_OFFSET_CAPTURE);
			$h2_tag_length = 4;
			if( !empty($open_tag) ) {
				$title_start = $open_tag[0][1] + $h2_tag_length;
				$title_end = $close_tag[0][1] - $open_tag[0][1] - $h2_tag_length;
				return substr($html_slide_textonly, $title_start, $title_end); 
			} else {
				return false;
			}
		}
		
		/*
		 *  Getting number of shape tree objects
		 */
		 
		function importNumberOfShapes($slide_xml) {
			$slide_xml_root = $slide_xml->documentElement;
			
			$query = '//p:sp';
			
			$numberof_shapes = Util::evaluateQueryOn(	$slide_xml,
									$slide_xml_root, 
									$query);
			
			$numberof_shapes = $numberof_shapes->length;
			
			return $numberof_shapes;
		}
		
		function importNumberOfPics($slide_xml) {
			$slide_xml_root = $slide_xml->documentElement;
			
			$query = '//p:pic';
			
			$numberof_pics = Util::evaluateQueryOn(	$slide_xml,
									$slide_xml_root, 
									$query);
			
			$numberof_pics = $numberof_pics->length;			
			return $numberof_pics;
		}
		
		function importNumberOfGrFrames($slide_xml) {
			$slide_xml_root = $slide_xml->documentElement;
			
			$query = '//p:graphicFrame';
			
			$numberof_gr_frames = Util::evaluateQueryOn(	$slide_xml,
									$slide_xml_root, 
									$query);
			
			$numberof_gr_frames = $numberof_gr_frames->length;			
			return $numberof_gr_frames;
		}
		
		/*
		 *  Importing shape tree objects
		 */
		
		function importShapes($slide_xml, $numberof_shapes) {
			$shapes = array();
			// check for the shapes presence here
			for($shape_num = 0; $shape_num < $numberof_shapes; $shape_num++) {
				array_push($shapes, new pptxShape($slide_xml, $shape_num));
			}
			return $shapes;
		}
		
		function importPics($slide_xml, $numberof_pics, $rels) {
			$pics = array();
			for($pic_num = 0; $pic_num < $numberof_pics; $pic_num++) {
				array_push($pics, new pptxPic($slide_xml, $pic_num, $rels));
			}
			return $pics;
		}
		
		function importGrFrames($slide_xml, $numberof_gr_frames, $rels) {
			$gr_frames = array();
			for($gr_frame_num = 0; $gr_frame_num < $numberof_gr_frames; $gr_frame_num++) {
				array_push($gr_frames, new pptxGrFrame($slide_xml, $gr_frame_num, $rels));
			}
			return $gr_frames;
		}
		
		/*
		 *  API
		 */
		
		public function toString($shapes, $numberof_shapes, $pics, $numberof_pics, $gr_frames, $numberof_gr_frames) {
			$string = array();
			// shapes to string
			for($shape_num = 0; $shape_num < $numberof_shapes; $shape_num++) {
				array_push($string, $shapes[$shape_num]->toString($shapes[$shape_num]->_paragraphs, $shapes[$shape_num]->_id, $shapes[$shape_num]->_shape_num, $shapes[$shape_num]->_offset, $shapes[$shape_num]->_extent));
			}
					
			// pics to string
			for($pic_num = 0; $pic_num < $numberof_pics; $pic_num++) {
				array_push($string, $pics[$pic_num]->_html_string);
			}
			
			// tables to string
			for($gr_frame_num = 0; $gr_frame_num < $numberof_gr_frames; $gr_frame_num++) {
				array_push($string, $gr_frames[$gr_frame_num]->_html_string);
			}
			
			$string = implode($string);
			return $string;
		}
		
		/*
		 *  Auxilary functions
		 */
		
		function atLeastAShapeHasText($numberof_shapes, $shapes) {
			for($i = 0; $i < $numberof_shapes; $i++) {
				if( !empty($shapes[$i]->_html_string) ) {
					return true;
				}
			}
			return false;
		}
	}
?>
