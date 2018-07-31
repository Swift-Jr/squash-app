<?php
	class mPlayer extends mObject{
		var $has_one = array('sport');
		var $has_many = array('match_p1'=>'match|p1_id',
								'match_p2'=>'match|p2_id');
		
		function get_list($Not_Player = NULL)
		{
			if( ! is_null($Not_Player))
			{
				$this->db->where_not_in('player_id', array($Not_Player));
			}
			$this->db->where('is_deleted', 0);
			$this->db->order_by('name');
			return $this->get();
		}
	}
?>