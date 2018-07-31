<?php
	class Players extends ifx_Controller{
		function index(){
			$this->PageHeader('Manage Player\'s');
			$Players = new mPlayer();
			$this->html->data('Players', $Players->get_list());
			return $this->display('players/list');
		}
		
		function create(){
			$this->PageHeader('Create Player');
			$this->load->helper('form');
			
			if($this->input->post('submit') !== FALSE)
			{
				$Player = new mPlayer();
				$Player->name = $this->input->post('player_name', FALSE);
				
				if($Player->save() == true){
					$this->html->info('New player created');
					return $this->index();
				}else{
					$this->html->error('Unable to create new player', FALSE);
				}
			}
			
			$this->display('players/new');
		}
		
		function delete(){			
			$this->PageHeader('Delete Player');
			$Player = new mPlayer($this->GUID);
			if(!$Player->is_loaded()) return $this->index();
			$this->html->data('Player', $Player);
			$this->display('players/delete');
		}
		
		function confirm_delete(){
			$Player = new mPlayer($this->GUID);
			if(!$Player->is_loaded()) return $this->index();
			
			//Are there any links?
			if(count($Player->match_p1) == 0 AND count($Player->match_p2) == 0)
			//Just delete it
			{
				if($Player->delete() == true)
				{
					$this->html->info('Player Deleted', FALSE);
				}else{
					$this->html->error('Unable to Delete Player', FALSE);
				}
			}else
			//Mark deleted
			{
				$Player->is_deleted = 1;
				if($Player->save() == true)
				{
					$this->html->info('Player Deleted', FALSE);
				}else{
					$this->html->error('Unabled to Delete Player', FALSE);
				}
			}
						
			return $this->index();
		}
		
		function cancel_delete(){
			$this->clearPostback();
			$this->index();
		}
		
		function stats($ID){
			$this->PageHeader('Player Stats');
			//Get some stats
			//Home many games?
			$Player = new mPlayer($ID);
			
			$this->html->data('Player', $Player);
			
			$Games = count($Player->match_p1) + count($Player->match_p2);
			
			$this->html->data('Games', $Games);
			
			//How many Wins?
			$Player = new mPlayer($ID);
			$Player->related('match_p1', 'where', 'p1_score >', 'p2_score', FALSE);
			$Wins = count($Player->match_p1);
			
			//$Player = new mPlayer($ID);
			$Player->related('match_p2', 'where', 'p2_score >', 'p1_score', FALSE);
			$Wins += count($Player->match_p2);
			
			$this->html->data('Wins', $Wins);
			
			//Losses
			$Losses = $Games-$Wins;
			$this->html->data('Losses', $Losses);
			
			//How Many point for
			$Match = new mMatch();
			$Match->db->where('p1_id', $ID);
			$PointsFor = $Match->sum('p1_score');
			$Match->db->where('p2_id', $ID);
			$PointsFor += $Match->sum('p2_score');
			
			$this->html->data('For', $PointsFor);
			
			//Points Against
			//$Match = new mMatch();
			$Match->db->where('p1_id', $ID);
			$PointsAgainst = $Match->sum('p2_score');
			$Match->db->where('p2_id', $ID);
			$PointsAgainst += $Match->sum('p1_score');
			
			$this->html->data('Against', $PointsAgainst);
			
			$this->html->data('WinRatio', $Wins/$Games);
			$this->html->data('ScoreRatio', $PointsFor/$PointsAgainst);
			
			$this->display('players/stats');
		}
	}
?>
