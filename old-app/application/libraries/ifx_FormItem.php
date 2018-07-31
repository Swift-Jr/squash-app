<?php
	class ifx_FormItem extends CI_library{
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
                $Str = '<div class="form-group form-item'.(is_null($this->Name)?'':' '.$this->Type.'-'.$this->Name).'">';
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
                    $Str .= $this->get_var('Class');
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
                    $Str .= $this->get_var('Class');
                    $Str .= $this->get_var('Default', 'value');
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
            if(is_null($Name))
            {
				$Name = strtolower($Var);
            }
            if(isset($this->$Var))
            {
				return ' '.$Name.'="'.$this->$Var.'"';
            }
            return;
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
?>
