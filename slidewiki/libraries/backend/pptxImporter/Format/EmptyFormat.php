<?php

    class EmptyFormat {
        private $_string;

        function __construct($string='') {
            $this->_string = $string;
        }

        function toString() {
            return $this->string;
        }

        public function __toString() {
            return $this->_string;
        }

    }

?>
