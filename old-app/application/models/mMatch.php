<?php
	class mMatch extends mObject{
		var $has_one = array('game', 
								'p1'=>'player|p1_id', 
								'p2'=>'player|p2_id');
		
		var $labels = array('p1_score'=>'Player 1 Score',
							'p2_score'=>'Player 2 Score',
							'game_id'=>'Game',
							'p1_id'=>'Player 1',
							'p2_id'=>'Player 2');
							
		var $rules = array('p1_score'=>array('required'),
							'p2_score'=>array('required'),
							'p1_id'=>array('required'),
							'p2_id'=>array('required'),
							'game_id'=>array('required')//,
							//'date'=>array('required')
							);
		function save($Relationship = null){
			//$this->date = date('Y-m-d');
			return parent::save($Relationship);
		}
		
		function get($FromView = null){
			$this->db->order_by('match_id', 'ASC');
			return parent::get($FromView);
		}
		
		function _get_date($Value){
			return date('j\-m\-Y', strtotime($Value));
		}
	}
?>