<?php

class Me extends authenticated_REST_Controller
{
    public function get_index()
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $UserData = [
            'user_id'=>$User->id(),
            'email'=>$User->email,
            'firstname'=>$User->firstname,
            'lastname'=>$User->lastname
        ];

        $Response = (object)[];
        $Response->user = $UserData;

        return $this->response($Response, ifx_REST_Controller::HTTP_OK);
    }
}
