<?php

class Game extends authenticated_REST_Controller
{
    public function post_save_game()
    {
        $Game = (object)[];

        $Game->league = $this->data['league'];
        $Game->player1 = $this->data['player1'];
        $Game->player2 = $this->data['player2'];
        $Game->player1_score = $this->data['player1score'];
        $Game->player2_score = $this->data['player2score'];

        $League = new mLeague($Game->league);

        if (!$League->is_loaded()) {
            $this->response(['error'=>"The selected league is not valid"], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        $Player1 = new mUser($Game->player1);
        $Player2 = new mUser($Game->player2);

        if (!$Player1->is_loaded() || !$Player2->is_loaded()) {
            $this->response(['error'=>"The selected player(s) are not valid"], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        $Match = new mMatch();
        $Match->league = $League;
        $Match->player1 = $Player1;
        $Match->player2 = $Player2;
        $Match->player1_score = $Game->player1_score;
        $Match->player2_score = $Game->player2_score;

        if ($League->save($Match)) {
            $League = new mLeague($Game->league);
            $Result = $League->toJson();
            $this->response(['league'=>$Result, 'match'=>$Match->toJson()], ifx_REST_Controller::HTTP_ACCEPTED);
        } else {
            $Errors = $League->_validation->all();
        }

        $this->response(['error'=>$Errors], ifx_REST_Controller::HTTP_BAD_REQUEST);
    }

    public function get_all($leagueId = null)
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $this->db->order_by('matches.date', 'DESC');
        if (!is_null($leagueId) && $leagueId !== '*') {
            $this->db->where('matches.league_id', $leagueId);
        }
        $Matches = $User->related('clubs/leagues/matches');

        $ResultMatches = [];

        if (count($Matches)) {
            foreach ($Matches as $Match) {
                $ResultMatches[] = $Match->toJson();
            }

            $this->response(['matches'=>$ResultMatches], ifx_REST_Controller::HTTP_OK);
        }

        $this->response(['matches'=>$ResultMatches], ifx_REST_Controller::HTTP_OK);
    }

    public function post_delete()
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $Game = new mMatch($this->data['id']);

        $League = $Game->league;

        if ($Game->player1->id() == $User->id() || $Game->player2->id() == $User->id() and $Game->delete()) {
            $Games = [];
            foreach ($League->matches as $Match) {
                $Games[] = $Match->toJson();
            }

            $Return = (object)[
                'matches'=> $Games,
                'league'=>$League->toJson()
            ];

            $this->response($Return, ifx_REST_Controller::HTTP_ACCEPTED);
        } else {
            $Errors = $Game->_validation->all();
        }

        $this->response(['error'=>$Errors], ifx_REST_Controller::HTTP_BAD_REQUEST);
    }
}
