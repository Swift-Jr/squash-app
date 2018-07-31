<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Play extends ifx_Controller {
	
	protected $Player1 = null;
	protected $Player2 = null;
	protected $GameID = null;
	
	public function __construct(){
		parent::__construct();
		$this->persist($this->Player1);
		$this->persist($this->Player2);
		$this->persist($this->GameID);
	}
	
	public function index()
	{		
		//Select a game
		if(is_null($this->GameID))
		{
			return $this->game();	
		}
		if(is_null($this->Player1) OR is_null($this->Player2))
		//Select some players
		{
			return $this->players();	
		}
		if( ! is_null($this->Player1) AND ! is_null($this->Player2) AND ! is_null($this->GameID))
		{
			return $this->match();
		}
	}
	
	public function reset(){
		$this->Player1 = null;
		$this->GameID = null;
		$this->Player2 = null;
		return $this->index();
	}
	
	public function game($GameID = null){
		$this->PageHeader('Select Game');
		if( ! is_null($GameID))
		{
			$this->GameID = $GameID;
			return $this->index();
		}
		
		$Games = new mGame();
		$this->html->data('Games', $Games->get_list(1));
		return $this->display('play/game_list');
	}
	
	public function players($PlayerID = null)
	{
		$this->PageHeader('Select Players');
		
		if($PlayerID == 'reset')
		{
			$this->Player1 = null;
			$this->Player2 = null;
		}
		elseif(is_null($this->Player1) AND ! is_null($PlayerID))
		{
			$this->Player1 = $PlayerID;
		}
		elseif(is_null($this->Player2) AND ! is_null($PlayerID) AND $PlayerID != $this->Player1)
		{
			$this->Player2 = $PlayerID;
			return $this->match();
		}
		if(! is_null($this->Player1) AND ! is_null($this->Player2))
		{
			return $this->index();
		}
		
		if(is_null($this->Player1))
		{
			$this->html->data('PlayerNum', '1');
		}else
		{
			$this->html->data('PlayerNum', '2');
			$P1 = new mPlayer(($this->Player1));
			$this->html->data('Player1', $P1->name);
		}
		//echo 'is'.$this->GameID;
		$Game = new mGame($this->GameID);
		$this->html->data('Game', $Game);
		
		$Players = new mPlayer();
		$this->html->data('Players', $Players->get_list($this->Player1));
		$this->display('play/player_list');
	}
	
	public function match(){
		$this->PageHeader('Scores');
		if(is_null($this->Player1) OR is_null($this->Player2) OR is_null($this->GameID))
		//Do we have enough to go
		{
			return $this->index();	
		}
		
		//Have we got posted match details?
		if($this->input->post('submit'))
		{
			$Match = new mMatch();
			$Match->game_id = $this->GameID;
			$Match->p1_id = $this->Player1;
			$Match->p2_id = $this->Player2;
			$Match->p1_score = $this->input->post('player_1_score');
			$Match->p2_score = $this->input->post('player_2_score');
			if($Match->save() == true)
			{
				$this->Player1 = null;
				$this->Player2 = null;
				$this->html->info('Match Saved!');
				return $this->reset();
			}else{
				$this->html->error('Match not saved');
			}
		}
		
		$this->load->helper('form');
		
		//Get Stuff
		$Game = new mGame(($this->GameID));
		$this->html->data('Game', $Game);
		
		$Player1 = new mPlayer(($this->Player1));
		$this->html->data('Player1', $Player1);
		
		$Player2 = new mPlayer(($this->Player2));
		$this->html->data('Player2', $Player2);
		
		$this->display('play/match');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */