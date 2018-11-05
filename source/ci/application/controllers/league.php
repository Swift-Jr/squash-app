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
}
