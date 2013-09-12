<?php

	class pptxGrFrame {
		// general properties
		private $_type;
		private $_id;
		private $_name;
		private $_grFrame_num;
		
		// positioning
		private $_offset;
		private $_extent;
		
		// constants
		private $TABLE_TYPE = 'http://schemas.openxmlformats.org/drawingml/2006/table';
		private $DIAGRAM_TYPE = 'http://schemas.openxmlformats.org/drawingml/2006/diagram';
		
		// text variables
		private $_paragraphs;
		
		// graphic frame specific
		private $_numberof_cols;
		private $_table = array();	// array( "row_number" => {
									//				"cell" => {
									//					"italic" => "true|false", 
									//				 	"bold" => "true|false", 
									//				 	"underscore" => "true|false",
									//				 	"font_size" => "pt"
									//				 	"content" => "text" 
									//					}
									//				
									//			}) 

		// API
		public $_html_string;
		
		
		public function __construct($slide_xml, $gr_frame_num, $rels) {
			
			// general properties
			$this->_gr_frame_num = $gr_frame_num;
			$this->importGeneralProperties($slide_xml, $gr_frame_num, $rels);
			
			if( !empty($this->_offset) ) {
				$this->_offset = Util::offsetToPercents($this->_offset);
				$this->_extent = Util::extentToPercents($this->_extent);
			}
			
			if(!empty($this->_table)) {
				$string = $this->tableToString($this->_table);
				$this->_html_string = $this->toString($this->_id, $string, $this->_offset, $this->_extent);
			}
			if(!empty($this->_paragraphs)) {
				$string = Util::paragraphsToString($this->_paragraphs);
				$this->_html_string = $this->toString($this->_id, $string, $this->_offset, $this->_extent);
			}
			
		}
		
		function importGeneralProperties($slide_xml, $gr_frame_num, $rels) {
			$slide_xml_root = $slide_xml->documentElement;
			
			$query = 'p:cSld/p:spTree//p:graphicFrame';
			$gr_frames  = Util::evaluateQueryOn($slide_xml, $slide_xml_root, $query);
			$gr_frame = $gr_frames->item($gr_frame_num);
			
			$query = 'string(a:graphic/a:graphicData/@uri)';
			$type = Util::evaluateQueryOn($slide_xml, $gr_frame, $query);
			
			$query = 'string(p:nvGraphicFramePr/p:cNvPr/@id)';
			$id = Util::evaluateQueryOn($slide_xml, $gr_frame, $query);
			
			$query = 'string(p:nvGraphicFramePr/p:cNvPr/@name)';
			$name = Util::evaluateQueryOn($slide_xml, $gr_frame, $query);
			
			$query = 'string(p:xfrm/a:off/@x)';
			$offset['x'] = Util::evaluateQueryOn($slide_xml, $gr_frame, $query);
			
			$query = 'string(p:xfrm/a:off/@y)';
			$offset['y'] = Util::evaluateQueryOn($slide_xml, $gr_frame, $query);
			
			$query = 'string(p:xfrm/a:ext/@cx)';
			$extent['cx'] = Util::evaluateQueryOn($slide_xml, $gr_frame, $query);
			
			$query = 'string(p:xfrm/a:ext/@cy)';
			$extent['cy'] = Util::evaluateQueryOn($slide_xml, $gr_frame, $query);

			// push parameters to class attributes
			$this->_id = $id;
			$this->_name = $name;
			$this->_offset = $offset;
			$this->_extent = $extent;
			
			/*
			 *  Tables and diagrams processing
			 */
			 
			// processing tables
			if($type == $this->TABLE_TYPE) {
				
				$query = 'a:graphic/a:graphicData/a:tbl';
				$tbl_list = Util::evaluateQueryOn($slide_xml, $gr_frame, $query);
				
				$tbl = $tbl_list->item(0);
				
				$query = 'a:tblGrid/a:gridCol';
				$numberof_cols_query = Util::evaluateQueryOn($slide_xml, $tbl, $query);
				
				$query = 'a:tr';
				$rows_query = Util::evaluateQueryOn($slide_xml, $tbl, $query);
				
				$this->_numberof_cols = $numberof_cols_query->length;
				$this->_numberof_rows = $rows_query->length;
				
				$table = array();
				
				for($i = 0; $i < $this->_numberof_rows; $i++) {
					$row = $rows_query->item($i);
					
					// get all cells
					$query = 'a:tc';
					$cells = Util::evaluateQueryOn($slide_xml, $row, $query);
					
					$numberof_cells_in_row = $cells->length;
					
					for($j = 0; $j < $numberof_cells_in_row; $j++) {
						$cell = $cells->item($j);
						
						// get all paragraphs from txBody element
						$query = 'a:txBody/a:p';
						$paragraphs_list = Util::evaluateQueryOn($slide_xml, $cell, $query);
						$paragraphs = array();
						// go through paragraphs
						if ($paragraphs_list->length != 0) {
							foreach($paragraphs_list as $paragraph) {
								array_push($paragraphs, Util::readParagraph($slide_xml, $paragraph));
							}
						} else {}
						
						$table[$i][$j] = Util::paragraphsToString($paragraphs);
					}	
				}
				
				$this->_table = $table;
			}
			
			// processing the diagrams
			if($type == $this->DIAGRAM_TYPE) {
				// get the rId of the data
				$query = 'string(a:graphic/a:graphicData/dgm:relIds/@r:dm)';
				$namespaces[0]['abbr'] = 'dgm';
				$namespaces[0]['uri'] = 'http://schemas.openxmlformats.org/drawingml/2006/diagram';
				$data_rId = Util::evaluateQueryOn($slide_xml, $gr_frame, $query, $namespaces);
				// get the file
				$target = '';
				foreach($rels as $rel) {
					if($rel['Id'] == $data_rId) {
						$target = $rel['Target'];
					}
				}
				$target = 'ppt/' . substr($target, 3);
				
				$dom = Util::readFileToDOMDocument($target);
				
				$dgm_data_model = $dom->documentElement;
				
				$query = 'dgm:ptLst/dgm:pt';
				$points = Util::evaluateQueryOn($dom, $dgm_data_model, $query);
				
				$paragraphs = array();
				for($i = 0; $i < $points->length; $i++) {
					$point = $points->item($i);
					$query = 'dgm:t/a:p';
					$paragraphs_list = Util::evaluateQueryOn($dom, $point, $query);
					// go through paragraphs
					if ($paragraphs_list->length != 0) {
						foreach($paragraphs_list as $paragraph) {
							array_push($paragraphs, Util::readParagraph($dom, $paragraph));
						}
					} else {}
					
				}
				$this->_paragraphs = $paragraphs;
			}
			
		}
		
		function tableToString($table) {
			
			if( !empty($this->_table) ) {
				$string = '<table border="1">';
					
				for($i = 0; $i < $this->_numberof_rows; $i++) {
					$string .= '<tr>';
					foreach($this->_table[$i] as $cell_num => $paragraphs) {
						$string .= '<td>' . $paragraphs . '</td>';
					}
					$string .= '</tr>';
				}
				$string .= '</table>';
			}
			
			return $string;
		}
		
		function toString($id, $str, $offset, $extent) {
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
				if($extent['cx'] != 0) {
					$right = 100 - $offset['x'] - $extent['cx'];
					$string .= 'right:'. $right .'%;';
				}
				if($extent['cx'] != 0) {
					$bottom = 100 - $offset['y'] - $extent['cy'];
					$string .= 'bottom:'. $bottom .'%;';
				}
				$string .= '"';
			}
			$string .= '>';
			$string .= $str;
			$string .= '</div>';
			return $string;
		}
		
	}
?>