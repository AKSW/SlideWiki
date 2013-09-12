<?php

    class FooterParagraphs extends Paragraphs {
        private $_paragraph_list;
        private $_paragraphs;

        function __construct($slide_xml, $paragraphs_list) {
            parent::__construct($slide_xml, $paragraphs_list);
        }

        #override
        function formatParagraphs($paragraphs) {
            #return $this->formatPlain($paragraphs);
            return '';
        }

    }

?>
