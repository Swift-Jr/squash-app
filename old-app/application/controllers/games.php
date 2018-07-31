<?php
	class Games extends ifx_Controller{
		function index(){
			$this->PageHeader('Manage Game\'s');
			$Games = new mGame();
			$this->html->data('Games', $Games->get_list(1));
			return $this->display('games/list');
		}
		
		function create(){
			$this->PageHeader('Create Game');
			$this->load->helper('form');
			
			if($this->input->post('submit') !== FALSE)
			{
				$Game = new mGame();
				$Game->title = $this->input->post('game_title', FALSE);
				
				if($Game->save() == true){
					$this->html->info('New game created');
					return $this->index();
				}else{
					$this->html->error('Unable to create new game', FALSE);
				}
			}
			
			$this->display('games/new');
		}
		
		function delete(){			
			$this->PageHeader('Delete Game');
			$Game = new mGame($this->GUID);
			if(!$Game->is_loaded()) return $this->index();
			$this->html->data('Game', $Game);
			$this->display('games/delete');
		}
		
		function confirm_delete(){
			$Game = new mGame($this->GUID);
			if(!$Game->is_loaded()) return $this->index();
			
			//Are there any links?
			if(count($Game->match) == 0)
			//Just delete it
			{
				if($Game->delete() == true)
				{
					$this->html->info('Game Deleted', FALSE);
				}else{
					$this->html->error('Unabled to Delete Game', FALSE);
				}
			}else
			//Mark deleted
			{
				$Game->is_deleted = 1;
				if($Game->save() == true)
				{
					$this->html->info('Game Deleted', FALSE);
				}else{
					$this->html->error('Unabled to Delete Game', FALSE);
				}
			}
						
			return $this->index();
		}
		
		function cancel_delete(){
			$this->clearPostback();
			$this->index();
		}
	}
?>
