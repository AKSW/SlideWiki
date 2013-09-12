<?php
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'utils' . DS . 'util.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Paragraphs' . DS . 'Paragraphs.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Paragraphs' . DS . 'BodyParagraphs.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Paragraphs' . DS . 'FooterParagraphs.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Paragraphs' . DS . 'SubtitleParagraphs.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Paragraphs' . DS . 'TitleParagraphs.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Paragraphs' . DS . 'SlideNumParagraphs.php';

require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Format' . DS . 'EmptyFormat.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Format' . DS . 'DatetimeFormat.php';
require_once ROOT . DS . 'libraries' . DS . 'backend' . DS . 'pptxImporter' . DS . 'Format' . DS . 'SlideNumFormat.php';

    class ParagraphFactory {
        private $_paragraph_list;
        private $_paragraphs;

        function __construct($slide_xml, $shape, $shape_type) {
            $this->_paragraph_list = $this->parseParagraphList($slide_xml, $shape);
            $this->_paragraphs=$this->parseParagraphs($slide_xml, $this->_paragraph_list, $shape_type);
        }

        function parseParagraphList($slide_xml, $shape) {
            return Util::evaluateQueryOn($slide_xml, $shape, 'p:txBody/a:p');
        }

        function parseParagraphs($slide_xml, $paragraphs_list, $shape_type) {
            switch($shape_type) {
                case 'body':
                    $paragraphs = new BodyParagraphs($slide_xml, $paragraphs_list);
                    break;
                case 'ctrTitle':
                case 'title':
                    $paragraphs = new TitleParagraphs($slide_xml, $paragraphs_list);
                    break;
                case 'subTitle':
                    $paragraphs = new SubtitleParagraphs($slide_xml, $paragraphs_list);
                    break;
                case 'sldNum':
                    $paragraphs = new SlideNumParagraphs($slide_xml, $paragraphs_list);
                    break;
                case 'ftr':
                    $paragraphs = new FooterParagraphs($slide_xml, $paragraphs_list);
                    break;
                default:
                    $paragraphs = new Paragraphs($slide_xml, $paragraphs_list);
                    break;
            }
	    return $paragraphs;
        }

        public function getParagraphs() {
            return $this->_paragraphs;
        }
    }
?>
