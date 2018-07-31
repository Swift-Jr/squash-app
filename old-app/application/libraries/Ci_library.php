<?php
    class CI_Library{
        static protected  $ci;
        
        public function __construct(){
            $this->ci =& get_instance();
        }
    }
?>
