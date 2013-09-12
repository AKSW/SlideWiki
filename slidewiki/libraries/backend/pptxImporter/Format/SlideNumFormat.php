<?php

    class SlideNumFormat {
        private $_slidenum_string;

        function __construct($string) {
            $this->_slidenum_string = $string;
        }

        public function toString() {
            return $this->_slidenum_string;
        }

        public function __toString() {
            return $this->_slidenum_string;
        }

    }

?>
