<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends ifx_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->PageHeader('Dashboard');
		
		$Games = new mGame();
		
		foreach($Games->get_list(1) as $Game)
		{
			//Get latest matches
			$Matches = new mMatch();
			$Matches->db->where('game_id', $Game->id());
			$Matches->db->limit(10);
			$Matches->db->order_by('date', 'desc');
			$MatchDetails[] = array(0=>$Game, 1=>$Matches->get());
		}
		
		$this->html->data('MatchGroup', $MatchDetails);
		
		//Get league tables
		$Leagues = array();
		$i = 0;
		
		foreach($Games->get_list(1) as $Game)
		{
			$League = new mvw_League_Table();
			$Table = $League->league_table($Game->id());
			if(count($Table) > 0){
				$Leagues[$i][0] = $Game;
				$Leagues[$i][1] = $Table;
				$i++;
			}
		}
		$this->html->data('Tables', $Leagues);
		
		$Super = new mvw_Super_League();
		$this->html->data('SuperLeague', $Super->get());
		
		$this->display('dashboard');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */