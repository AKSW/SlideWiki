<?php

    class Paragraphs {
        private $_paragraph_list;
        private $_paragraphs;

        function __construct($slide_xml, $paragraphs_list) {
            $this->_paragraphs = $this->parseParagraphs($slide_xml, $paragraphs_list);
        }

        function parseParagraphs($slide_xml, $paragraphs_list) {
            $paragraphs = array();
            if ($paragraphs_list->length != 0) {
	        foreach($paragraphs_list as $paragraph) {
	            array_push($paragraphs, $this->parseParagraph($slide_xml,$paragraph));
                }
            }  
            return $paragraphs;
        }

        public function getParagraphs() {
            return $this->_paragraphs;
        }
        
        function parseParagraph(DOMDocument $slide_xml, DOMElement $paragraph) {
            $runs = array();

            if ($paragraph->hasChildNodes()) {
                    $paragraph_nodes = $paragraph->childNodes; //DOMNodeList
                    $i = 0; // running through the runs here
                    foreach($paragraph_nodes as $paragraph_node) {

                        if($paragraph_node->nodeName == "a:fld") 
                            $runs[$i]['content'] = $this->parseNodeField($slide_xml, $paragraph_node);
                        
                        if($paragraph_node->nodeName == "a:pPr") { 
                            $runs['properties'] = $this->parseNodeProperties($paragraph_node);
                            continue;
                        }

                        if($paragraph_node->nodeName == 'a:r') {
                            list($string, $run_parameters) = $this->parseNodeRun($slide_xml, $paragraph_node);
                            $runs[$i]['content'] = $string;
                            foreach($run_parameters as $parameter_name => $parameter_value) {
                                $runs[$i][$parameter_name] = $parameter_value; 
                            }
                        }

                        // remove empty runs
                        if(empty($runs[$i]['content'])) {
                            unset($runs[$i]);
                        }

                        if(empty($runs['properties']['bullet_type'])) {
                            unset($runs['properties']['bullet_type']);
                        }

                        $i++;
                    }
            } 
            
            return $runs;
	}

        function parseNodeField(DOMDocument $slide_xml, DOMNode $paragraph_node) {
            /**
             * Contain slideNum or datetime
             */
            $string = Util::evaluateQueryOn($slide_xml, $paragraph_node, 'string(a:t)');
            if($paragraph_node->hasAttributes()) {
                $field_type = (string) $paragraph_node->getAttribute('type');
                if(preg_match('/datetime/', $field_type)) {
                    return new DatetimeFormat($string);
                } elseif(preg_match('/slidenum/', $field_type)) {
                    return new SlideNumFormat($string);
                }
            }
        }

        function parseNodeProperties(DOMNode $paragraph_node) {
            /**
             * pPr - text paragraph properties
             */
            $node_properties = array();
            if($paragraph_node->hasAttributes()) {
                $node_properties['lvl'] = (int) $paragraph_node->getAttribute('lvl');
            }						
            $node_properties['bullet_type'] = $this->detectListType($paragraph_node);
            return $node_properties;
        }

        function detectListType(DOMNode $paragraph_node) {
            /**
             * Used by parseNodeProperties
             */
            $bullet_types = array('buNone', 'buAutoNum', 'buChar', 'buBlip');
            $result = array();
            foreach($bullet_types as $bullet_type) {
                if( $paragraph_node->getElementsByTagName($bullet_type)->length != 0)
                    array_push($result, $bullet_type);
            }
            return $result;  
        }

        function parseNodeRun(DOMDocument $slide_xml, DOMNode $paragraph_node) {
            /**
             * Detects run of the text inside the paragraph
             */
            if($paragraph_node->nodeName == 'a:r') {
                $string = Util::evaluateQueryOn($slide_xml, $paragraph_node, 'string(a:t)');
                $run_parameters = $this->parseRunParameters($slide_xml, $paragraph_node);
            } 
            return array($string, $run_parameters);
        }

        function parseRunParameters(DOMDocument $slide_xml, DOMNode $run_node) {
            $parameters_list = Util::evaluateQueryOn($slide_xml, $run_node, 'a:rPr/@*');
            $result = array();
            if ($parameters_list->length != 0) {
                foreach($parameters_list as $parameter) {
                    $result[$parameter->nodeName] = $parameter->nodeValue;
                }
            }
            return $result;
        }

        function parseNodeLinebreak(DOMNode $paragraph_node) {
            if($paragraph_node->nodeName == 'a:br') {
                    return '<br/>';
            } 
        } 

        public function toString($id, $shape_num, $offset, $extent) {
            $string = '';
            $string .= '<div ';
            //$string .= 'id="'. $id .'" ';
            if(Util::$_import_with_style) {
                $string .= $this->dimensionsToString($offset, $extent);
            }
            $string .= '>';
            $string .= $this->paragraphsToString($this->_paragraphs, $shape_num);
            $string .= '</div>';
            return $string;
        }

        function dimensionsToString($offset, $extent) {
            $string = 'style="';
            
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
            return $string;
        }

        public function paragraphsToString($paragraphs) {
            $formatted_runs = $this->formatRuns($paragraphs); 
            $formatted_paragraphs = $this->formatParagraphs($formatted_runs);

            return $formatted_paragraphs;
        }

        function formatRuns($paragraphs) {
            $result = array();
            foreach($paragraphs as $paragraph_number => $run) {
                $paragraph = $paragraphs[$paragraph_number];
                foreach($paragraph as $run_number => $run) {

                    if(!is_int($run_number)) {
                        continue;
                    }

                    $result[$paragraph_number][$run_number]= $run['content'];

                    if($this->isRunParameter('b', $run))
                        $result[$paragraph_number][$run_number] = '<b>' . $run['content'] . '</b>';
                    
                    if($this->isRunParameter('i', $run))
                        $result[$paragraph_number][$run_number] = '<i>' . $run['content'] . '</i>';

                    if($this->isRunParameter('u', $run))
                        $result[$paragraph_number][$run_number] = '<u>' . $run['content'] . '</u>';
                }
                $paragraphs[$paragraph_number]['content'] = implode($result[$paragraph_number]);
            }
            return $paragraphs;
        }

        function isRunParameter($name, $run) {
            /**
             * for b, i, u (bold, italic, underscore)
             */
            if(array_key_exists($name, $run) && (int) $run[$name] == 1) 
                return True;
            else
                return False;
        }

        function formatParagraphs($paragraphs) {
            return $this->formatAsList($paragraphs);
        }
            
        function formatAsList($paragraphs) {
            $last_paragraph = sizeof($paragraphs) - 1;
            if(empty($paragraphs[0]['properties']['lvl']))
                $level_first = 0;
            else
                $level_first = $paragraphs[0]['properties']['lvl'];

            #start list
            $result = '<ul>';
            
            foreach($paragraphs as $paragraph_number => $run) {
                $properties = $paragraphs[$paragraph_number]['properties'];
                $content = $paragraphs[$paragraph_number]['content'];

                if($paragraph_number == 0)
                    $level_previous = $level_first;
                else
                    $level_previous = $level_current;

                if(empty($properties['lvl']))
                    $level_current = 0;
                else
                    $level_current = (int) $properties['lvl'];

                $difference = $level_current - $level_previous;

                if($difference == 0) 
                    $result .= '<li>' . $content . '</li>';

                if($difference > 0)
                    $result .= str_repeat('<ul>', $difference) . '<li>' . $content . '</li>';

                if($difference < 0) {
                    $result .= str_repeat('</ul>', abs($difference)) . '<li>' . $content . '</li>';
                }

                #last paragraph
                if($paragraph_number == $last_paragraph) 
                    $result .= str_repeat('</ul>', $level_current - $level_first);
            }

            #end list
            $result .= '</ul>';

            return $result;
        }

        function formatPlain($paragraphs) {
            return $this->implodeParagraphs($paragraphs); 
        }

        function formatTitle($paragraphs) {
            return '<h2>' . $this->implodeParagraphs($paragraphs) . '</h2>';
        }

        function formatSubtitle($paragraphs) {
            return '<h3>' . $this->implodeParagraphs($paragraphs) . '</h3>';
        }

        function implodeParagraphs($paragraphs) {
            $result = '';
            foreach($paragraphs as $paragraph_number => $run) {
                $result .= $paragraphs[$paragraph_number]['content'] . '<br/>';
            }
            return $result;
        }
    
    }

?>
