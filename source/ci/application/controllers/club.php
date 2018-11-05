<?php

class Club extends authenticated_REST_Controller
{
    public function post_create()
    {
        $User = new mUser($this->token->getClaim('user_id'));
        $Name = $this->data['name'];

        $NewClub = new mClub();
        $NewClub->name = $Name;
        $NewClub->owner_id = $User->id();

        if ($User->save($NewClub)) {
            $Result = $NewClub->toJson();
            $this->response(['club'=>$Result], ifx_REST_Controller::HTTP_ACCEPTED);
        } else {
            $Errors = $NewClub->_validation->all();
        }

        $this->response(['error'=>$Errors], ifx_REST_Controller::HTTP_BAD_REQUEST);
    }
}
