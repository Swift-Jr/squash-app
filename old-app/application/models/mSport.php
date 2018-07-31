<?php
	class mSport extends mObject{
		var $has_one = array('sport');
		var $has_many = array('match');
		
		function get_list()
		{
			$this->db->order_by('title');
			return $this->get();
		}
	}
?>