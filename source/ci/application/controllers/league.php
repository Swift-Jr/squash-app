<?php

class League extends authenticated_REST_Controller
{
    public function post_create()
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $Name = $this->data['name'];
        $Shortname = $this->data['shortname'];
        $ClubID = $this->data['club_id'];

        $Club = new mClub($ClubID);

        if (!$Club->is_loaded()) {
            $this->response(['error'=>"The selected club is not valid"], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        $League = new mLeague();
        $League->name = $Name;
        $League->shortname = $Shortname;
        $League->club = $Club;
        $League->owner = $User;

        if ($League->alreadyExists()) {
            $this->response(['error'=>"You cant use that name, its already taken!"], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($League->save()) {
            $Result = $League->toJson();
            $this->response(['league'=>$Result], ifx_REST_Controller::HTTP_ACCEPTED);
        } else {
            $Errors = $League->_validation->all();
        }

        $this->response(['error'=>$Errors], ifx_REST_Controller::HTTP_BAD_REQUEST);
    }

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
}
