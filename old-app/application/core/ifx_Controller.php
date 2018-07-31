<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
	/*
		PERSISTING
		Within a controllers construct variables can be set to persist
		until next load, or persist for the lifetime of the load, Consider:
		
		SomeClass extends Base_Controller{
			var $FilterValues
			
			__construct(){
				$this->persist_once($this->FilterValues)
			}
		}
		
		While the user is within this controller, the variable will be auto-populated with
		the value from last page load
		
		POSTBACK
		Using postback/GUID/controller/function it is possible to remove ID's from URLS. The GUID
		is available as $this->GUID but will not appear in the URL and is persisted as above
	*/
	
	/*
		CLASS AUTOLOADING
	*/
	function __autoload($class) 
	{
	 	log_message('info', 'Auto Loading Class:'.$class);
		if (file_exists(APPPATH."models/".($class).EXT)) {
			//A model has been requested
		    if(!class_exists('CI_Model',FALSE)){
		    	//Check CI_Model is loaded
		        if (file_exists(BASEPATH."core/Model".EXT)) {
		            require_once(BASEPATH."core/Model".EXT);
		            log_message('info', 'Loaded Class:'.$class);
		        }else{
		            log_message('error', 'File does not exist: '.BASEPATH."libraries/Model".EXT);    
		        }
		        //Check if ifx_Model exists
		        if (file_exists(APPPATH."core/ifx_Model".EXT)) {
		            require_once(APPPATH."core/ifx_Model".EXT);
		            log_message('info', 'Loaded Class:'.$class);
		        }    
		    }
		    //Load the requested Model 
		    require_once(APPPATH."models/".($class).EXT);
		    log_message('info', 'Loaded Class:'.$class);
		    return;  
		}elseif (file_exists(APPPATH."libraries/".($class).EXT)) {
		    //Have a go at loading straight
		    require_once(APPPATH."libraries/".($class).EXT);
		    return; 
		}elseif (file_exists(APPPATH."libraries/".strtolower($class).EXT)) {
		    //Have a go at loading using CI
		    $CI =& get_instance();
		    $CI->load->library($class);  
		    require_once(APPPATH."libraries/".strtolower($class).EXT);
		    return;  
		}elseif (file_exists(BASEPATH."libraries/".(ltrim($class, 'CI_')).EXT)) {
		    //Have a go at loading using CI
		    $CI =& get_instance();
		    $CI->load->library(ltrim($class, 'CI_'));  
		    require_once(BASEPATH."libraries/".(ltrim($class, 'CI_')).EXT);
		    return; 
		}
		log_message('info', 'Auto Loading Failed:'.$class);
	}
	
	class ifx_Controller extends CI_Controller{
		const CONFIG_NAME = 'ifx_settings';
		
        protected $GUID;
        private $Tabs;
        private $TabID;

        private $_ifxObjects = array();

        private $postback;
        private $clearPostback = FALSE;
        
        public $PreviousPage = null;
        public $PreviousPostback = null;

        /**
        * @var Html
        */
        public $html;
        
        /**
        * put your comment there...
        * 
        * @var CI_Input
        */
        public $input;
        
        private $Persisted_Once = array();
        private $Persisted = array();
        //init
        function __construct(){
            parent::__construct();
            //Load Required Libraries
            $this->load->library('session');
            //session_start();
            $this->load->library('html');
            
            $this->config->load('ifx_settings', TRUE);
            
            if($this->config->item('site_name', self::CONFIG_NAME) !== FALSE)
            {
				$this->html->title($this->config->item('site_name', self::CONFIG_NAME), FALSE);
            }
            
            //Get the postback name of the active controller
            $this->postback = strtolower(get_class($this));
            //See if there is a GUID stored
            $Value = $this->session->flashdata($this->postback);
            if(!empty($Value)){
            	//OK there is, now let's keep ahold of this unless the user navigates away'
                $this->session->keep_flashdata($this->postback);
                $this->GUID = $Value;
            }
            
            //First time anything is loaded, remember a short history                       
            if($this->session->userdata('_ifx_ThisPage') == false){
                $this->session->set_userdata('_ifx_ThisPage', $_SERVER["REQUEST_URI"]);    
                $this->session->set_userdata('_ifx_LastPage', $_SERVER["REQUEST_URI"]);    
                $this->session->set_userdata('_ifx_ThisPostback', array($_SERVER["REQUEST_URI"]));    
            }
            
            $PostbackPages = $this->session->userdata('_ifx_ThisPostback');
            
            if($this->uri->segment(2) != 'postback' && $this->session->userdata('_ifx_ThisPage') != $_SERVER["REQUEST_URI"]){
                //Remember this new page and the last page accessed
                $this->session->set_userdata('_ifx_LastPage', $this->session->userdata('_ifx_ThisPage'));
                $this->session->set_userdata('_ifx_ThisPage', $_SERVER["REQUEST_URI"]);
                
            }elseif($this->uri->segment(2) == 'postback' && $PostbackPages[0] != $_SERVER["REQUEST_URI"]){
                $NewPostbackPages[0] = $_SERVER["REQUEST_URI"];
                foreach($PostbackPages as $Page){
                    $NewPostbackPages[] = $Page;    
                }
                $this->session->set_userdata('_ifx_ThisPostback', $NewPostbackPages);
            }
            $this->PreviousPage = $this->session->userdata('_ifx_LastPage');
            $this->PreviousPostback = $this->session->userdata('_ifx_ThisPostback');
            
            //Anything persisted once?
            $this->Persisted_Once =  $this->session->flashdata('ifx_Persist_Once_'.get_class($this));
            if(is_array($this->Persisted_Once)){
                foreach($this->Persisted_Once as $Variable=>$Value){
                    $this->$Variable = $Value;
                }
            }
            $this->Persisted_Once = array();

            //Anything persisted?
            $this->Persisted =  $this->session->userdata('ifx_Persist_'.get_class($this));
            if(is_array($this->Persisted)){
                foreach($this->Persisted as $Variable=>$Value){
                    $this->$Variable = $Value;
                }
            }
                
            //if(isset($_SESSION['ifx_Persist_Once'])) unset($_SESSION['ifx_Persist_Once']);
        }
        
        function __destruct(){
        	$this->session->set_userdata('ifx_Persist_'.get_class($this), $this->Persisted);
        	$this->session->set_flashdata('ifx_Persist_Once_'.get_class($this), $this->Persisted_Once);
			
            //$_SESSION['ifx_Persist'][get_class($this)] = $this->Persisted;
            //$_SESSION['ifx_Persist_Once'][get_class($this)] = $this->Persisted_Once;
            
            foreach($this->_ifxObjects as $GUID => $Obj){
                $_SESSION['ifxObjectCache'][$GUID] = serialize($Obj);
            }    
        }
        
        public function persist_once(&$Persist){
            $PersistValue = $Persist;
            $Persist = rand();
            
            //Get the variable name so we can reset it
            $Class_Vars = get_object_vars($this);

            foreach($Class_Vars as $Var=>$Value){
                if(is_numeric($Value)){
                    if($Value == $Persist) $Variable = $Var;
                }
            }
            
            $this->Persisted_Once[$Variable] = &$this->$Variable;
            
            $Persist = $PersistValue;
                
        }
        
        public function persist(&$Persist){
            $PersistValue = $Persist;
            $Persist = rand();
            
            //Get the variable name so we can reset it
            $Class_Vars = get_object_vars($this);

            foreach($Class_Vars as $Var=>$Value){
                if(is_numeric($Value)){
                    if($Value == $Persist) $Variable = $Var;
                }
            }
            
            $this->Persisted[$Variable] = &$this->$Variable;
            
            $Persist = $PersistValue;    
        }
        /**
        * Redirect to the previous page
        * 
        */
        function previous($Postback = FALSE, $Number = 0){
            if($Postback and array_key_exists($Number, $this->PreviousPostback)) redirect($this->PreviousPostback[$Number]);
            redirect($this->PreviousPage);
        }
        
        /**
        * Postback support for setting an ID, and passing to an event
        *
        * @param GUID The unique GUID/ID of the record
        * @param Method $Target
        */
        function postback($GUID, $Target = null){
            //mimic a postback
            $this->session->set_flashdata($this->postback, $GUID);
            redirect(site_url(array($this->postback, $Target)));
        }
        
        public function getPostback($Postback){
        	if(is_null($Postback)) $Postback = $this->postback;
        	return $this->session->flashdata($Postback);
        }

        function clearPostback(){
        	$this->clearPostback = TRUE;
        }

        /**
        * Return postback guid as a reference
        *
        * @param mixed $ObjectType
        * @return mixed
        */
        protected function &_get_object($ObjectType){
            if(empty($_SESSION['ifxObjectCache'][$this->GUID])){
                $_SESSION['ifxObjectCache'][$this->GUID] = serialize(new $ObjectType($this->GUID));
            }

            if(empty($this->_ifxObjects[$this->GUID])){
                $this->_ifxObjects[$this->GUID] = unserialize($_SESSION['ifxObjectCache'][$this->GUID]);
            }
            return $this->_ifxObjects[$this->GUID];
        }
        
        function PageHeader($Title){
			$this->html->data('PageHeader', $Title);
        }
        
        function display($View){
            $this->load->view('system/html_head');//, data());
            if($this->config->item('common_header',self::CONFIG_NAME) != FALSE){
				$this->load->view($this->config->item('common_header',self::CONFIG_NAME));
            }
            $this->load->view($View);//, data());
            $this->load->view('system/html_foot');//, data());
        }
    }
        
?>