<?php
if(!class_exists('Form')){
    class Form extends CI_library{
        private $config;
        
        /**
        * Params array{url:url, error:top|inline|false}
        * 
        * @param mixed $config
        * @return Form
        */
        function __construct($config = array()){
            parent::__construct();
            
            $this->config = $config;
            
            if(empty($config['action'])) $this->config['action'] = '';
            if(empty($config['error'])) $this->config['error'] = 'inline';
            
            $this->ci->load->helper('form');
        }
        
        private function prefix_id($Name){
            return 'field_'.$Name;    
        }
        
        public function open($ID = null){
            $Attr = array('method'=>'post');
            if(!is_null($ID)) $Attr['id'] = $ID;
            echo form_open($this->config['action'], $Attr);
            if($this->config['error'] == 'top') echo validation_errors('<p id="validation_errors">', '</p>');
        }
        
        public function close(){
            echo form_close();
        }
        
        public function form_input($Name, $Title = null, $Note = null, $default = null){
            $this->_input('input', $Name, $Title, $Note, $default);
        }
        
        public function form_password($Name, $Title = null, $Note = null, $default = null, $Refill = false){
            $this->_input('password', $Name, $Title, $Note, $default, $Refill);
        }
         
        public function form_hidden($Name, $Default = NULL){
            echo form_hidden($Name, $this->get_value($Name, $Default));    
        }   
            
        public function form_text($Name, $Title = null, $Note = null, $default = null){
            $this->_input('textarea', $Name, $Title, $Note, $default);
        }
        
        public function form_submit($Title, $Value = 'submit'){
            echo '<div class="form-button">';
            $extra = 'id="'.$this->prefix_id(strtolower($Title)).'"';
            echo form_submit($Value, $Title, $extra);
            echo '</div>';
        }
        
        public function form_button($Name, $Title, $Type = 'submit', $Primary = true){
            if($Primary == true){
                $Btn = 'btn-primary';
            }else{$Btn = NULL;}
            echo '<button type="'.$Type.'" name="'.$Name.'" class="btn '.$Btn.'">'.$Title.'</button>';
        }
        
        public function form_dropdown($Options, $Name, $Title = null, $Note = null, $Default = null){
            //$Options = array_merge_recursive(array(''=>'Please Select'), $Options);
            $Option = array(''=>'Please Select');
            foreach($Options as $K=>$V){
                $Option[$K] = $V;    
            }
            echo '<div class="form-item select-'.$Name.'">';
            $extra = 'id="'.$this->prefix_id($Name).'"';
            if(!empty($Title)) echo $this->form_label($Title, $Name);
            if(!empty($Note)) echo "<p>$Note</p>";
            if($this->config['error'] == 'inline') echo $this->has_error($Name);
            echo '<div class="input-padding">'.form_dropdown($Name, $Option, $this->get_value($Name, $Default), $extra).'</div>';
            echo '</div>';
        }
        
        public function form_checkbox($Name, $Value, $Title = null, $Note = null, $Default = null){
            echo '<div class="form-item checkbox '.$Name.'">';
            $extra = 'id="'.$this->prefix_id($Name).'"';
            if(!empty($Title)) echo $this->form_label($Title, $Name);
            if($this->config['error'] == 'inline') echo $this->has_error($Name);
            if(!empty($Note)) echo "<p>$Note</p>";
            echo '<div class="input-padding">'.form_checkbox($Name, $Value, $this->get_value($Name, $Default), $extra).'</div>';
            echo '</div>';
        }
        
        private function _input($Type, $Name, $Title, $Note, $Default, $Refill = true){
            echo '<div class="form-item '.$Type.'-'.$Name.'">';
            $extra = 'id="'.$this->prefix_id($Name).'"';
            if(!empty($Title)) echo $this->form_label($Title, $Name);
            if(!empty($Note)) echo "<p>$Note</p>";
            $Fn = 'form_'.$Type;
            if($this->config['error'] == 'inline') echo $this->has_error($Name);
            echo '<div class="input-padding">'.$Fn($Name, $this->get_value($Name, $Default, $Refill), $extra).'</div>';
            echo '</div>';
        }
        
        function form_label($Title, $Name = NULL){
            echo form_label($Title, $this->prefix_id($Name));
        }
        
        function get_value($Name, $Default = null, $Refill = true){
            if($Refill == false) return NULL;
            $Value = set_value($Name);
            if(!empty($Value)){
                return $Value;
            }else{
                $Value = $this->ci->input->post($Name);
                if(!empty($Value)){
                    return $Value;
                }else{
                    return $Default;
                }
            }
        }
        
        function has_error($Field){
            //See if there is a form validation error
            $Error = form_error($Field);
            
            if(empty($Error)){
                //Anything sitting in validation?
                /**
                * @var mObject_validation
                */
                if(function_exists('get_mObject_validation')){
                    $ObjValidation = &get_mObject_validation();
                    $Error = $ObjValidation->$Field;
                }
            }else{
                return $Error;
            }
            
            if(!empty($Error)) return $Error;
            
            return FALSE;            
        }
        
        function item($Type){
            $Item = new ifxFormItem($Type);
            return $Item;
        }
    }
    
    class ifxFormItem extends CI_library{
        protected $Type;
        protected $Class;
        protected $ID;
        protected $Name;
        protected $Note;
        protected $Extra;
        protected $Default;
        protected $Label;
        protected $Options;
        protected $Value;
        
        public function __construct($Type = 'text'){
            $this->Type = strtolower($Type);
            return $this;    
        }
        
        public function __toString(){
            $Str = '';
            if($this->Type != 'hidden'){
                $Str = '<div class="form-item'.(is_null($this->Name)?'':' '.$this->Type.'-'.$this->Name).'">';
                if(!is_null($this->Label)){
                    $Str .= '<label for="'.$this->Name.'">'.$this->Label.'</label>';
                }
                if(!is_null($this->Note)) $Str .= '<p>'.$this->Note.'</p>'; 
                //if($this->config['error'] == 'inline') echo $this->has_error($Name);
                $Str .= '<div class="input-padding">';
            } 
            switch($this->Type){
                case 'select':
                case 'button':
                    $Str .= '<'.$this->Type.'';
                    $Str .= $this->get_var('Name');
                    $Str .= $this->get_var('ID');
                    $Str .= ' '.$this->Extra;
                    $Str .= '>';
                    if($this->Type == 'button'){
                        $Str .= $this->Value;    
                    }elseif($this->Type == 'select'){
                        foreach($this->Options as $I=>$V){
                            $Str .= '<option value="'.$I.'"'.($I==$this->get_value()? 'selected':'').'>'.$V.'</option>';
                        }
                    }
                    $Str .= '</'.$this->Type.'>';
                break;
                case 'text':
                case 'hidden':
                case 'password':
                    $Str .= '<input type="'.$this->Type.'"';
                    $Str .= $this->get_var('Name');
                    $Str .= $this->get_var('ID');
                    $Str .= $this->get_var('Value', 'Default');
                    $Str .= ' '.$this->Extra;
                    $Str .= '/>';
                    //$Fn($Name, $this->get_value($Name, $Default, $Refill), $extra);  
                break;
            }
            if($this->Type != 'hidden') $Str .= '</div></div>';
            return $Str;     
        }
        
        protected function get_value(){
            return $this->Default;    
        }
        
        public function iClass($Class){
            $this->Class = $Class;
            return $this;    
        }
        
        protected function get_var($Var, $Name = NULL){
            if(is_null($Name)) $Name = strtolower($Var);
            if(isset($this->$Var)) return ' '.$Name.'="'.$this->$Var.'"';
        }    
        
        public function Name($Name){
            $this->Name = $Name;
            $this->DefaultValue(set_value($this->Name));
            return $this;    
        }
        
        public function DefaultValue($Value){
            if(empty($this->Default)){
				$this->Default = $Value;	
            }
            return $this;    
        }
        
        public function Value($Value){
            $this->Value = $Value;
            return $this;    
        }    
        
        public function ID($ID){
            $this->ID = $ID;
            return $this;    
        }    
        
        public function Note($Note){
            $this->Note = $Note;
            return $this;    
        }    
        
        public function Options($Options){
            $this->Options = $Options;
            return $this;    
        }    
        
        public function Extra($Extra){
            $this->Extra = $Extra;
            return $this;    
        }
        public function Label($Label){
            $this->Label = $Label;
            return $this;    
        }    
    }  
}
?>