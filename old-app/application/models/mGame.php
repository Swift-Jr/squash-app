<?php
	class mGame extends mObject{
		var $has_one = array('sport');
		var $has_many = array('match');
									
		var $rules = array('title'=>array('required'));
		
		function save($Relationship = null){
			$this->sport_id = 1; //Squash
			return parent::save($Relationship);
		}
		
		function get_list($SportID)
		{
			$this->db->where('sport_id', $SportID);
			$this->db->where('is_deleted', 0);
			$this->db->order_by('title');
			return $this->get();
		}
	}
?>