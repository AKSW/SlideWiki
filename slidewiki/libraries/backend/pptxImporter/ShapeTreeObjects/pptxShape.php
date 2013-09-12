<?php
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Paragraphs' . DS . 'ParagraphFactory.php';

	class pptxShape {
	
		// general properties
		public $_id;	// unique id within the presentation
		private $_name; 	// Title, subtitle, content place holder etc.
		public $_shape_num; // shape number within slide
		public $_type; // title or NULL
		public $_idx; // for layout - slide shape matching
                private $_shape; // contain xml element of pptxShape object
                private $_shape_type; // see Util::ST_PLACEHOLDERTYPE for possible shape_types
		
		// Positioning in percents
		public $_offset = array();
		public $_extent = array();
		
		// Text parameters
		public $_paragraphs = array(); // array( "paragraph_number" => {
								//				"text run_number" => {
								//					"italic" => "true|false", 
								//				 	"bold" => "true|false", 
								//				 	"underscore" => "true|false",
								//				 	"font_size" => "pt"
								//				 	"content" => "text" 
								//					}
								//				"paragraph_level" => "int()"
								//				"bullet" => bool()
								//			})
		
		// API
		public $_html_text = '';
								
		public function __construct($slide_xml, $shape_num) {
                        // this shape element
                        $shape = $this->getThisShape($slide_xml, $slide_xml->documentElement, $shape_num);
                        $this->_shape = $shape;
			
			// general properties
			$this->importGeneralProperties($slide_xml, $shape);
			$this->_shape_num = $shape_num;
			
			// Import styles
			$this->_list_style = $this->importListStyle($slide_xml, $shape);
			
			// Import paragraphs
			$this->_paragraphs = $this->importParagraphs($slide_xml, $shape);
			
			// calculate position in percents
			if( !empty($this->_offset)) {
				$this->_offset = Util::offsetToPercents($this->_offset);
				$this->_extent = Util::extentToPercents($this->_extent);
			} 
			
			if( !empty($this->_paragraphs)) {
				$this->_html_string = $this->toString($this->_paragraphs, $this->_id, $this->_shape_num,
				$this->_offset, $this->_extent);
			}
		}
		
		/*
		 *  General properties
		 */

		function importGeneralProperties($slide_xml, $shape) {
			$this->_id = $this->importId($slide_xml, $shape);
			$this->_name = $this->importName($slide_xml, $shape); 
			$this->_type = $this->importType($slide_xml, $shape); 
			$this->_idx = $this->importIdx($slide_xml, $shape); 
			$this->_offset = $this->importOffset($slide_xml, $shape); 
			$this->_extent = $this->importExtent($slide_xml, $shape); 
		}

                function importId($slide_xml, $shape) {
                    return Util::evaluateQueryOn($slide_xml, $shape, 'string(p:nvSpPr/p:cNvPr/@id)'); 
                }

		function importName($slide_xml, $shape) {
		    return Util::evaluateQueryOn($slide_xml, $shape, 'string(p:nvSpPr/p:cNvPr/@name)'); 
                }

                function importType($slide_xml, $shape) {
                    return Util::evaluateQueryOn($slide_xml, $shape, 'string(p:nvSpPr/p:nvPr/p:ph/@type)');
                }

                function importIdx($slide_xml, $shape) {
		    return Util::evaluateQueryOn($slide_xml, $shape, 'string(p:nvSpPr/p:nvPr/p:ph/@idx)'); 
                }

                function importOffset($slide_xml, $shape) {
	            $offset['x'] = Util::evaluateQueryOn($slide_xml, $shape, 'string(p:spPr/a:xfrm/a:off/@x)'); 
	            $offset['y'] = Util::evaluateQueryOn($slide_xml, $shape, 'string(p:spPr/a:xfrm/a:off/@y)');
                    return $offset;
                }

                function importExtent($slide_xml, $shape) {
		    $extent['cx'] = Util::evaluateQueryOn($slide_xml, $shape, 'string(p:spPr/a:xfrm/a:ext/@cx)'); 
		    $extent['cy'] = Util::evaluateQueryOn($slide_xml, $shape, 'string(p:spPr/a:xfrm/a:ext/@cy)'); 
                    return $extent;
                }

		
		/*
		 *  Style import
		 */
		 
		function importListStyle($slide_xml, $shape) {
			$a_lst_style = Util::evaluateQueryOn($slide_xml, $shape, 'p:txBody/a:lstStyle');
			$a_lst_style = $a_lst_style->item(0);
			$shape_style = Util::getStyleList($a_lst_style);
			
			return $shape_style;
		}
		
		/*
		 *  Text import
		 */
		
		function importParagraphs($slide_xml, $shape) {
			$paragraphs_list = Util::evaluateQueryOn($slide_xml, $shape, 'p:txBody/a:p');
			
                        $paragraphs = new ParagraphFactory($slide_xml, $shape, $this->_type);

                        return $paragraphs->getParagraphs();
                }

                function getThisShape($slide_xml,$slide_xml_root,$shape_num) {
                        $query = 'p:cSld/p:spTree//p:sp';
			$shapes = Util::evaluateQueryOn($slide_xml, $slide_xml_root, $query); 
			$shape = $shapes->item($shape_num);
                        return $shape;
                }
		
		/*
		 *  API
		 */

		
		function toString($paragraphs, $id, $shape_num, $offset, $extent) {
                    $string = $paragraphs->toString($id, $shape_num, $offset, $extent); 
                    return $string;
		}		
}
?>
