<?php

    class DatetimeFormat {
        private $_datetime_string;

        function __construct($string) {
            $this->_datetime_string = $string;
        }

        public function toString() {
            return $this->_datetime_string;
        }

        public function __toString() {
            return $this->_datetime_string;
        }

    }

?>
