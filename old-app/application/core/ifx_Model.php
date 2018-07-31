<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    class ifx_Model extends CI_Model{

    }
            
    abstract class mObject extends ifx_Model {
        private $error_lang = array(
            'required'=>'{field} is required',
            'unique'=>'The {field} must be unique',
            'currency'=>'{field} must be a valid currency format'
        );
        
        /**
        * @var mObject_Validation
        */
        public $errors;
        
        /**
        * The name of the concrete class that extends this class
        * 
        * @var string
        */
        private $_class;
        /**
        * The name of the primary key for the table of the same name as the concrete class
        * 
        * @var string
        */
        private $_id;
        private $_table;
        private $_data = array();
        
        private $_record;
        private $_affected_field = array();
        private $_isnew = true;
        
        private $_sql = array();
        
        /**
        * @var CI_DB_active_record
        */
        public $db;
        
        private $CI;
                
        //Variabled defined here for consumption by extending object
        /**
        * Overload the field names, thus enabling $Object->normalised_fieldname
        * 
        * const normalised_fieldname = 'some_horrible_db_fieldname';
        */
        
        //Overload getting/setting, by declaring a function and passing data
        /**
        *** Passes in the DB data value, must return actual data value
        *   function _get_field($Data){
        *       return $Data; 
        *   }
        * 
        *** Passes in the user data value, must return DB data value 
        *   function _set_field($Data){
        *       return $Data; 
        *   }
        */
        
        /**
        * A list of any mObjects with a *-1 relationship
        * 
        * @var array
        */
        protected $has_one = array();
        /**
        * A list of any mObjects with a 1-* relationship
        * 
        * @var array
        */
        protected $has_many = array();
        /**
        * A list labels used for over-riding the field names is the case of field validation error messages
        * 
        * @var array('fieldname'=>'label')
        */
        protected $labels = array();
        /**
        * A list of rules in the format [field][validation_fn[variables]] as defined by mObject->rule_*
        * 
        * i.e. $this->rules[field][] = 'numeric'
        *      var $rules = array('field'=>array('numeric'))
        * @var array
        */
        protected $rules = array();
        
        function __construct($Object = null){
            parent::__construct();
            
            //Workaround to access CI DB
            $this->CI =& get_instance();
            $this->CI->load->database();
            $this->db = &$this->CI->db;
            
            $this->errors = &get_mObject_validation();
            
            //Setup
            $this->_class = get_class($this);
            $this->_table = strtolower(ltrim($this->_class, 'm'));
            $this->_id = $this->_table.'_id';
            
            //Load the object, if ID is provided
            if(is_numeric($Object)){
                $this->load($Object);
            }elseif(is_object($Object)){
                $this->load($Object);
            }
            
            //Load Relations
            if(!is_array($this->has_one)) {
                $Str = $this->has_one;
                $this->has_one = array($Str);    
            }
            if(!is_array($this->has_many)){
                $Str = $this->has_many; 
                $this->has_many = array($Str);
            }
            
            return;
            
            if($this->is_loaded()){
                   
                foreach($this->has_one as $Relationship){
                     $this->_load_relationship_one($Relationship);                     
                }
                
                   
                foreach($this->has_many as $Relationship){
                     $this->_load_relationship_many($Relationship);
                }
            }
            
        }
        
        public function __set($name,$value){ 
            if($name == $this->_id) show_error("Access denied __set($name) in ".$trace[0]['file'].': line '.$trace[0]['line']);
            $SetFn = '_set_'.$name;
            if(strlen($value) == 0) $value = NULL;
            if(method_exists($this, $SetFn)){
                $this->_data[$name] = $this->$SetFn($value);    
            }else{
                $this->_data[$name] = $value;  
            }
        }
        
        public function __get($name){
            //ID?
            if(strtolower($name) == 'id') return $this->id();
            
            //if(!$this->is_loaded()) return FALSE;
            
            //Unloaded on demand relationship?
            if(array_key_exists($name, $this->has_one) === TRUE || array_search($name, $this->has_one) !== FALSE AND !array_key_exists($name, $this->_data)){
                $this->_load_relationship_one($name);
            }
            if(array_key_exists($name, $this->has_many) === TRUE || array_search($name, $this->has_many) !== FALSE AND !array_key_exists($name, $this->_data)){
                $this->_load_relationship_many($name);
            }
            
            //Constant set?
            if(defined($this->_class.'::'.$name) > 0){
                $name = constant($this->_class.'::'.$name);
            }
            
            if(array_key_exists($name, $this->_data)){
                $GetFn = '_get_'.$name;
                if(method_exists($this, $GetFn)){
                    return $this->$GetFn($this->_data[$name]);    
                }else{
                    return $this->_data[$name];  
                }    
            }
            
            $trace = debug_backtrace();
            show_error("Undefined property __get($name) in ".$trace[0]['file'].': line '.$trace[0]['line']);
        }
        
        private function _load_relationship_one($Relationship){
            //Is it a magical reference
        	$OriginalRelationship = $Relationship;
        	if(isset($this->has_one[$Relationship])){
        		if(is_string($this->has_one[$Relationship]))
        		{
					list($Relationship,$Field) = explode('|', $this->has_one[$Relationship]);
        		}else{ $Field = FALSE; }
        	}else{ $Field = FALSE; }
        	
        	if($Field === FALSE)
        	{
				$RelatedID = $this->_get_related_id($Relationship);
        	}else
        	{
				if(array_key_exists($Field, $this->_data)){
	                $RelatedID = $this->_data[$Field];
	            }else{
	                show_error('Define 3NF relation ID');
	            }
        	}
        	
            $Obj = 'm'.ucfirst($Relationship);
            $O = new $Obj();
            $this->_get_sub_sql($Relationship);
            $O->load($RelatedID);
            $this->_data[$OriginalRelationship] = $O;   
        }
        
        private function _get_sub_sql($Relationship){
        	$OriginalRelationship = $Relationship;
        	if(array_key_exists($Relationship, $this->has_many))
        	{
				list($Relationship,$Field) = explode('|', $this->has_many[$Relationship]);
        	}
        	if(array_key_exists($Relationship, $this->has_one))
        	{
				list($Relationship,$Field) = explode('|', $this->has_one[$Relationship]);
        	}
            if(array_key_exists($OriginalRelationship, $this->_sql)){
                foreach($this->_sql[$OriginalRelationship] as $Fn=>$Field){
                    if(is_array($Field)){
                        foreach($Field as $Option){
                            list($QField, $QValue, $QEscape) = $Option;
                            $this->db->$Fn($Relationship.'.'.$QField, $QValue, $QEscape);
                        }
                    }else{
                        $this->db->$Fn($Relationship.'.'.$Field);
                    }
                }    
            }    
        }
        
        /**
        * This could be rewritten to use a join to query the data
        * 
        * @param mixed $Relationship
        */
        private function _load_relationship_many($Relationship){
        	//Is it a magical reference
        	$OriginalRelationship = $Relationship;
        	if(isset($this->has_many[$Relationship]))
        	{
        		if(is_string($this->has_many[$Relationship]))
        		{
					list($Relationship,$Field) = explode('|', $this->has_many[$Relationship]);
        		}else{ $Field = FALSE; }
        	}else{ $Field = FALSE; }
        	
            $Obj = 'm'.ucfirst($Relationship);
            if($this->db->field_exists($this->_id, $Relationship) OR $this->db->field_exists($Field, $Relationship)){
                //in table relationship
                $ManyRelations = new $Obj();
                $this->_get_sub_sql($OriginalRelationship);
                if($Field === FALSE)
                {
					$this->db->where($this->_id, $this->id()); 	
                }else{
					$this->db->where($Field, $this->id());
                }
                $this->_data[$OriginalRelationship] = $ManyRelations->get();
            }else{
                //3NF relationship
                //show_error('3NF Relationship not supported');
                
                $O = new $Obj();
                $Table = $this->_3NF_relationship($O);
                $this->db->select($O->_relation_id());
                $this->db->where($this->_id, $this->id());
                $Result = $this->db->get($Table);
                if($Result->num_rows() > 0){
                    $IDs = $Result->result_array();
                    $InArray = array();
                    foreach($IDs as $K=>$ID){
                        $InArray[] = $ID[$O->_relation_id()];    
                    }
                    $this->db->where_in($O->_relation_id(), $InArray);
                    $this->_get_sub_sql($Relationship); 
                    $this->_data[$Relationship] = $O->get();
                }else{
                    $this->_data[$Relationship] = array(new $Obj());
                }                
            }    
        }
        
        private function _3NF_relationship($Relationship){
            if(array_search($Relationship->_table() ,$this->has_one) !== FALSE){
                $Test_Table = $Relationship->_table().'_'.$this->_table;
            }elseif(array_search($Relationship->_table() ,$this->has_many) !== FALSE){
                $Test_Table = $this->_table.'_'.$Relationship->_table(); 
            }else{
                show_error('Relationship not defined between '.$this->_table.' & '.$Relationship->_table());
            }
            if(!$this->db->table_exists($Test_Table)){
                show_error('Relationship table expected ('.$Test_Table.')');
            }
            
            return $Test_Table; 
        }
        
        private function _get_related_id($Relation){
            $Relation = $Relation.'_id';
            if(array_key_exists($Relation, $this->_data)){
                return $this->_data[$Relation];
            }else{
                show_error('Define 3NF relation ID');
            }    
        }
        
        /**
        * Get the table name
        * 
        */
        public function _table(){ return $this->_table;}
        
        public function __isset($name){ return isset($this->_data[$name]);}

        public function __unset($name){ unset($this->_data[$name]);}
        
        /**
        * Return the current ID of the active record
        * 
        */
        final public function id(){ 
            if(array_key_exists($this->_id, $this->_data)) return $this->_data[$this->_id]; 
            return null;
        }
        
        final public function _relation_id(){ return $this->_id;}
        
        final public function is_loaded(){ return array_key_exists($this->_id, $this->_data); }
        
        /**
        * Add a temporary rule to the model
        * 
        * @param mixed Table field
        * @param mixed the requested rule
        */
        final public function rule($Field, $Rule){ $this->rules[$Field][] = $Rule;}
        
        private final function _strip_row(stdClass $DBRow){
            $this->_record = $DBRow;
            $this->_isnew = false;
            
            foreach($DBRow as $Key=>$Value){
                $this->_data[$Key] = $Value;
            }    
        }
        
        private final function _set_row(){
            foreach($this->_data as $Key=>$Value){
                if(!is_object($Value) && !is_array($Value)) {
                    $this->_affected_field[$Key] = $Value;
                    $this->db->set($Key, $Value, !is_numeric($Value));
                }
            }
        }
        
        /**
        * Load record with the ID $ID
        * 
        * @param mixed $ID
        */
        public function load($ID = null){
            if(is_object($ID)){
                $this->_strip_row($ID);
                return true;
            }else{
                //Did the user reauest via a specific ID?
                if(!is_null($ID)) $this->db->where($this->_id, $ID);
                //Was there any fancy sql passed via the related function?                
                foreach($this->_sql as $Table=>$Nothing){
                    $this->_get_sub_sql($Table);                    
                    if($this->db->field_exists($this->_id, $Table)){
                        $this->db->join($Table, $this->_table.'.'.$this->_id.' = '.$Table.'.'.$this->_id, 'inner');
                    }elseif($this->db->field_exists($Table.'_id', $this->_table)){
                        $this->db->join($Table, $this->_table.'.'.$Table.'_id'.' = '.$Table.'.'.$Table.'_id', 'inner');
                    }else{
                        //join table??
                        if(array_search($Table ,$this->has_one) !== FALSE){
                            $Test_Table = $Table.'_'.$this->_table;
                        }elseif(array_search($Table ,$this->has_many) !== FALSE){
                            $Test_Table = $this->_table.'_'.$Table; 
                        }else{
                            show_error('Relationship not defined between '.$this->_table.' & '.$Table);
                        }
                        if(!$this->db->table_exists($Test_Table)){
                            show_error('Relationship table expected ('.$Test_Table.')');
                        }    
                        $this->db->join($Test_Table, $this->_table.'.'.$this->_id.' = '.$Test_Table.'.'.$this->_id, 'inner');
                        $this->db->join($Table, $Test_Table.'.'.$Table.'_id'.' = '.$Table.'.'.$Table.'_id', 'inner');
                    }
                }
                $this->_sql = array();
                //Are there any variabloes set?
                foreach($this->_data as $Field=>$Value){
                    $this->db->where($this->_table().'.'.$Field, $Value);
                }
                $this->_data = array(); 
                $this->db->select($this->_table.'.*'); 
                $this->db->from($this->_table); 
                $Query = $this->db->get();
                if($Query->num_rows() == 1){
                    $this->_strip_row($Query->row());
                    return true;
                }else{
                    return false;
                }   
            }
        }
                   
        /**
        * Save the active record or add a relationship
        * @param mObject $Relationship
        * @return mixed
        */
        public function save($Relationship = null){
            //Run any pre-save defaults - extended by the implementor
            $this->before_save();
            
            //Have we been passed a relationship table?
            if($Relationship instanceof mObject){
                
                //Check that this is a real object, we need it to save the relationship against
                if(!$this->is_loaded()) show_error(get_class($this).' is not loaded');
                
                //Test to see if the relationship is in-table in this table
                $Col = $Relationship->_relation_id();
                if(array_key_exists($Col, $this->_data)){
                    //Make sure the foreign relation is already created
                    if(!$Relationship->is_loaded()){
                        if($Relationship->save() !== TRUE) return FALSE;
                    }
                    //Set this relation column to the foreign ID
                    $this->$Col = $Relationship->id();
                    //Try and save as usual
                    return $this->_save_row();
                
                //Test to see if the relationship is in-table in the foreign table
                }elseif($this->db->field_exists($this->_relation_id(), $Relationship->_table())){
                    $Col = $this->_relation_id();
                    //Set the foreign relation column to this ID 
                    $Relationship->$Col = $this->id();
                    //Save the foreign table
                    return $Relationship->save();
                
                //Test to see if the relationship has a join table
                }else{
                    //Check the relationship has been defined
                    if(array_search($Relationship->_table() ,$this->has_one) !== FALSE){
                        $Test_Table = $Relationship->_table().'_'.$this->_table;
                    }elseif(array_search($Relationship->_table() ,$this->has_many) !== FALSE){
                        $Test_Table = $this->_table.'_'.$Relationship->_table(); 
                    }else{
                        show_error('Relationship not defined between '.$this->_table.' & '.$Relationship->_table());
                    }
                    //Check the table exists
                    if(!$this->db->table_exists($Test_Table)){
                        show_error('Relationship table expected ('.$Test_Table.')');
                    }
                    //Make sure the foreign relation is already created
                    if(!$Relationship->is_loaded()){
                        if($Relationship->save() !== TRUE) return FALSE;
                    }
                    //Insert the record into the join table   
                    $this->db->set($this->_id, $this->id);
                    $this->db->set($Relationship->_relation_id(), $Relationship->id);
                    $this->db->insert($Test_Table);
                    return $this->db->affected_rows();
                }    
            }elseif(!is_null($Relationship)){
                show_error('Invalid Object for Relationship');
            }else{
                return $this->_save_row();    
            }
        }
        
        /* Now Defunked and replaced within $this->save($Relationship)   
        public function prepare_relationship($Relationship = null){
            if($Relationship instanceof mObject){
                //in table relationship?
                $Col = $Relationship->_relation_id();
                if($this->db->field_exists($Col, $Relationship->_table())){
                    $this->$Col = $Relationship->id();
                }else{
                    show_error('Unsupported Relationship');    
                }    
            }elseif(!is_null($Relationship)){
                show_error('Invalid Object for Relationship');
            }
        }
        */
        
        private function _save_row(){
            //Lets try some validation!
            if(!$this->_field_validation()) return FALSE;

            if($this->_isnew){ 
                $this->_set_row(); 
                $this->db->insert($this->_table);
                $this->_data[$this->_id] = $this->db->insert_id();
                //$this->db->flush_cache();
                return !empty($this->_data[$this->_id]);
            }else{ 
                $this->_set_row();
                $this->db->where($this->_id, $this->id());
                $this->db->update($this->_table);
                if($this->_record_is_changed()){
                    return ($this->db->affected_rows() > 0);
                }
                return true;
            }
        }
        
        /**
        * test to see if the fields being updated have been altered 
        * 
        */
        private function _record_is_changed(){
            foreach($this->_affected_field as $Key=>$Value){
                if(!array_key_exists($Key, $this->_record)) show_error("Invalid field '$Key' detected");
                if($this->_record->$Key != $Value){
                    return true;
                }
            }
            return false;    
        }
        
        final public function delete(){
            $this->db->where($this->_id, $this->id());
            $this->db->delete($this->_table);
            return ($this->db->affected_rows() == 1);
        }
        
        public function count(){
            $this->db->from($this->_table);
            return $this->db->count_all_results();        
        }
        
        public function sum($Field){
			$this->db->select_sum($Field);
			$Query = $this->db->get($this->_table());
			$Row = $Query->row();
			return $Row->$Field;
        }
        
        /**
        * Get the result table. Accepts additional queries
        * using CI's active record'
        */
        public function get($FromView = NULL){
            $Query = $this->db->get((is_null($FromView)?$this->_table:$FromView));
            if($Query->num_rows() > 0){
                foreach($Query->result() as $Row){
                    $Obj = $this->_class;
                    $O = new $Obj();
                    $O->load($Row);
                    $Results[] = $O;
                }
                return $Results;
            }
            return array();
        }
        
        /**
        * Add a SQL statement to a relationship
        * 
        * @param mixed $Relationship
        * @param mixed $Query_Type
        * @param mixed $Field
        * @param mixed $Value
        */
        final public function related($Relationship, $Type, $Field, $Value = null, $Escape = false){
            if(!empty($Value)){
                $this->_sql[$Relationship][$Type][] = array($Field, $Value, $Escape);    
            }else{
                $this->_sql[$Relationship][$Type] = $Field;
            }
        }
        
        //VALIDATION RULES

        /**
        * Check the set values using the defined $rules array
        * 
        */
        private function _field_validation(){
            if(is_array($this->rules)){
                foreach($this->rules as $Field => $Rules){
                    foreach($Rules as $Fn){
                        //Have we already got an error?
                        if(isset($this->errors->$Field)) continue;
                        /*Is there no data to validate?
                        if(!isset($this->_data[$Field]) AND $Fn != 'required'){
                              continue;
                        }*/
                        $Vars = explode('[', rtrim($Fn, ']'));
                        $Fn = $Vars[0];
                        $Val = (isset($this->_data[$Field]) ? $this->_data[$Field] : NULL);
                        isset($Vars[1]) ? $Var = $Vars[1] : $Var = null;
                        if(method_exists($this, 'rule_'.$Fn)){
                            $ClFn = 'rule_'.$Fn;
                            switch ($Fn){
                                case 'unique':
                                    if($this->$ClFn($Val, $Field) == FALSE){
                                        $this->errors->$Field = $this->_validation_output($Field, $Fn, $Var);
                                    }      
                                break;
                                case 'required':
                                    if($this->$ClFn($Val) == FALSE){
                                        $this->errors->$Field = $this->_validation_output($Field, $Fn, $Var);
                                    } 
                                break;
                                default:
                                    if($this->$ClFn($Val, $Var) == FALSE){
                                        $this->errors->$Field = $this->_validation_output($Field, $Fn, $Var);
                                    } 
                            }
                            
                        }elseif(function_exists($Fn)){
                            if($Fn($Val, $Var) == FALSE){
                                $this->errors->$Field= $this->_validation_output($Field, $Fn, $Var);
                            }
                        }else{
                            show_error('Unknown validation function "'.$Fn.'" requested for validating: '.$Field);
                        }
                    }
                }
            }
            
            if($this->errors->_has_errors() == FALSE) return TRUE;
            
            return FALSE;
        }
        
        /**
        * Create the validation error
        * 
        */
        private function _validation_output($Fieldname, $Rule, $AdditionalValue = null){
            //Get the template string
            if(isset($this->error_lang[$Rule])){
                $Str = $this->error_lang[$Rule];        
            }else{
                $Str = 'The {field} did not pass validation for '.$Rule;
            }
            //Get the field label
            if(isset($this->labels[$Fieldname])){
                $Label = $this->labels[$Fieldname];
            }else{
                $Label = ucfirst($Fieldname);
            }
            //Fill in the blanks
            $Str = str_replace('{field}', $Label, $Str);    
            $Str = str_replace('{var}', $AdditionalValue, $Str);
            
            return $Str;    
        }
        
        /**
        * Check is the value is unique
        * 
        * @param mixed $Str
        * @return mixed
        */
        public function rule_unique($str, $field){
            $SQL = 'SELECT '.$this->_id.' FROM '.$this->_table().' WHERE '.$field.'='.$this->db->escape($str);
            $Query = $this->db->query($SQL);
            return ($Query->num_rows() == 0);
        }  
        
        /**
         * Required
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_required($str)
        {
            if ( ! is_array($str))
            {
                if($str === "\x0") return TRUE;
                return (trim($str) == '') ? FALSE : TRUE;
            }
            else
            {
                return ( ! empty($str));
            }
        }
        
        /**
         * Valid Date
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_valid_date($str)
        {
            return (strtotime($str) !== FALSE);
        }

        // --------------------------------------------------------------------

        /**
         * Performs a Regular Expression match test.
         *
         * @access    public
         * @param    string
         * @param    regex
         * @return    bool
         */
        public function rule_regex_match($str, $regex)
        {
            if ( ! preg_match($regex, $str))
            {
                return FALSE;
            }

            return  TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Match one field to another
         *
         * @access    public
         * @param    string
         * @param    field
         * @return    bool
         */
        public function rule_matches($str, $field)
        {
            if ( ! isset($_POST[$field]))
            {
                return FALSE;
            }

            $field = $_POST[$field];

            return ($str !== $field) ? FALSE : TRUE;
        }
        
        // --------------------------------------------------------------------

        /**
         * Match one field to another
         *
         * @access    public
         * @param    string
         * @param    field
         * @return    bool
         */
        public function rule_is_unique($str, $field)
        {
            list($table, $field)=explode('.', $field);
            $query = $this->CI->db->limit(1)->get_where($table, array($field => $str));
            
            return $query->num_rows() === 0;
        }

        // --------------------------------------------------------------------

        /**
         * Minimum Length
         *
         * @access    public
         * @param    string
         * @param    value
         * @return    bool
         */
        public function rule_min_length($str, $val)
        {
            if (preg_match("/[^0-9]/", $val))
            {
                return FALSE;
            }

            if (function_exists('mb_strlen'))
            {
                return (mb_strlen($str) < $val) ? FALSE : TRUE;
            }

            return (strlen($str) < $val) ? FALSE : TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Max Length
         *
         * @access    public
         * @param    string
         * @param    value
         * @return    bool
         */
        public function rule_max_length($str, $val)
        {
            if (preg_match("/[^0-9]/", $val))
            {
                return FALSE;
            }

            if (function_exists('mb_strlen'))
            {
                return (mb_strlen($str) > $val) ? FALSE : TRUE;
            }

            return (strlen($str) > $val) ? FALSE : TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Exact Length
         *
         * @access    public
         * @param    string
         * @param    value
         * @return    bool
         */
        public function rule_exact_length($str, $val)
        {
            if (preg_match("/[^0-9]/", $val))
            {
                return FALSE;
            }

            if (function_exists('mb_strlen'))
            {
                return (mb_strlen($str) != $val) ? FALSE : TRUE;
            }

            return (strlen($str) != $val) ? FALSE : TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Valid Email
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_valid_email($str)
        {
            return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Valid Emails
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_valid_emails($str)
        {
            if (strpos($str, ',') === FALSE)
            {
                return $this->valid_email(trim($str));
            }

            foreach (explode(',', $str) as $email)
            {
                if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE)
                {
                    return FALSE;
                }
            }

            return TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Validate IP Address
         *
         * @access    public
         * @param    string
         * @param    string "ipv4" or "ipv6" to validate a specific ip format
         * @return    string
         */
        public function rule_valid_ip($ip, $which = '')
        {
            return $this->CI->input->valid_ip($ip, $which);
        }

        // --------------------------------------------------------------------

        /**
         * Alpha
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_alpha($str)
        {
            return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Alpha-numeric
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_alpha_numeric($str)
        {
            return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Alpha-numeric with underscores and dashes
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_alpha_dash($str)
        {
            return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Numeric
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_numeric($str)
        {
            return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

        }
        
        /**
         * Currency
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_currency($str)
        {
            return (bool)preg_match( '/^[\-+]?[0-9]*(\.)?([0-9]{2})?$/', $str);

        }

        // --------------------------------------------------------------------

        /**
         * Is Numeric
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_is_numeric($str)
        {
            return ( ! is_numeric($str)) ? FALSE : TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Integer
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_integer($str)
        {
            return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
        }

        // --------------------------------------------------------------------

        /**
         * Decimal number
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_decimal($str)
        {
            return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
        }

        // --------------------------------------------------------------------

        /**
         * Greather than
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_greater_than($str, $min)
        {
            if ( ! is_numeric($str))
            {
                return FALSE;
            }
            return $str > $min;
        }

        // --------------------------------------------------------------------

        /**
         * Less than
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_less_than($str, $max)
        {
            if ( ! is_numeric($str))
            {
                return FALSE;
            }
            return $str < $max;
        }

        // --------------------------------------------------------------------

        /**
         * Is a Natural number  (0,1,2,3, etc.)
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_is_natural($str)
        {
            return (bool) preg_match( '/^[0-9]+$/', $str);
        }

        // --------------------------------------------------------------------

        /**
         * Is a Natural number, but not a zero  (1,2,3, etc.)
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_is_natural_no_zero($str)
        {
            if ( ! preg_match( '/^[0-9]+$/', $str))
            {
                return FALSE;
            }

            if ($str == 0)
            {
                return FALSE;
            }

            return TRUE;
        }

        // --------------------------------------------------------------------

        /**
         * Valid Base64
         *
         * Tests a string for characters outside of the Base64 alphabet
         * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
         *
         * @access    public
         * @param    string
         * @return    bool
         */
        public function rule_valid_base64($str)
        {
            return (bool) ! preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
        }
        
        public function before_save(){}
    }
    
    class mObject_Validation{
        private $_Prefix = '<p>';
        private $_Postfix = '</p>';
        
        var $Errors = array();
        
        function __get($Var){
            if(!isset($this->Errors[$Var])) return FALSE;
            return $this->Errors[$Var];
        }   
        
        function __set($Var, $Val){
            $this->Errors[$Var] = $Val;    
        } 
        
        function _has_errors(){
            return (count($this->Errors) > 0);    
        }
        
        function all(){
            if(count($this->Errors) == 0) return FALSE;
            $Output = null;
            foreach($this->Errors as $E){
                $Output .= $this->_Prefix.$E.$this->_Postfix;
            }
            return $Output;
        }
    }
        
    function &get_mObject_validation()
    {
        GLOBAL $_mObject_Validation;
        
        if (!is_object($_mObject_Validation))
        {
            $_mObject_Validation = new mObject_Validation();
        }
        
        return $_mObject_Validation;
    }
    
    /**
    * Find any validation errors
    * 
    * @param mixed $Field
    */
    function validation_error($Field = null)
    {
    	/**
    	* 
    	* @var mObject_Validation
    	*/
        $M =& get_mObject_validation();
        if(empty($Field)){
            return $M->all();
        }
        return $M->$Field; 
    }
?>
