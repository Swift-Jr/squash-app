<?php
	class mvw_League_Table extends mObject{
		var $has_one = array('player', 'game');
		var $has_many = array('match');
		
		function league_table($Game, $Year = null, $Month = null)
		{
			if(is_null($Year) AND is_null($Month))
			{
				$Year = date('Y');
				$Month = date('n');
			}
			$this->db->where('year', $Year);
			$this->db->where('month', $Month);
			$this->db->where('game_id', $Game);
			$this->db->order_by('points', 'desc');
			$this->db->order_by('difference', 'desc');
			$this->db->order_by('for', 'desc');
			$this->db->order_by('against', 'asc');
			return $this->get();
		}
	}
?>
