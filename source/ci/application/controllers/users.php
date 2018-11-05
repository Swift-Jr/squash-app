<?php

class Users extends ifx_REST_Controller
{
    public function post_create()
    {
        $firstname = $this->data['firstname'];
        $lastname = $this->data['lastname'];
        $email = $this->data['email'];
        $password = $this->data['password'];
        $token = $this->data['token'];

        $Invite = new mInvite();
        $Invite->token = $token;
        $Invite->load();

        if (empty($firstname) ||
        empty($lastname) ||
        empty($email) ||
        empty($password)) {
            $this->response(["error"=>"All fields must be complete"], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        //check unique email
        if (mUser::emailExists($email) or mInvite::emailExists($email) && !$Invite->is_loaded()) {
            return $this->response(["error"=>"Yikes! Looks like that address is already in use"], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        //check password require
        if (!mUser::passwordIsStrong($password)) {
            return $this->response(["error"=>"Password must be at least 6 characters"], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        //create?
        try {
            $User = new mUser();
            $User->firstname = $firstname;
            $User->lastname = $lastname;
            $User->set_password($password);
            $User->email = $email;

            if ($User->save()) {
                if ($Invite->is_loaded()) {
                    $User->save($Invite->club);

                    /*$Notification = new mNotification();
                    $Notification->message = "He accepted";
                    $Invite->by->save($Notification);*/

                    $Invite->delete();
                }
                return $this->response($User->_data, ifx_REST_Controller::HTTP_CREATED);
            } else {
                return $this->response(["error"=>$User->_validation->all()], ifx_REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            return $this->response(["error"=>$e->getMessage()], ifx_REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function post_authenticate()
    {
        $email = $this->data['email'];
        $password = $this->data['password'];

        if (empty($email) || empty($password)) {
            return $this->response([], ifx_REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (mUser::emailIsUnique($email)) {
            $Response['error'] = "Dont think I've seen you here before. Sure thats your email?";
            return $this->response($Response, ifx_REST_Controller::HTTP_UNAUTHORIZED);
        }

        $User = new mUser();
        $User->email = $email;
        $User->load();

        if ($User->verifyPassword($password) === false) {
            $Response['error'] = "Thats not the right password, punk.";
            return $this->response($Response, ifx_REST_Controller::HTTP_UNAUTHORIZED);
        }

        $Data = [
            'user_id'=>$User->id(),
            'email'=>$User->email,
            'firstname'=>$User->firstname,
            'lastname'=>$User->lastname
        ];

        $Token = JWT::createToken($Data);

        $Response = (object)[];
        $Response->user = $Data;
        $Response->token = (string) $Token;

        return $this->response($Response, ifx_REST_Controller::HTTP_ACCEPTED);
    }

    public function post_recover()
    {
        $email = $this->data['email'];

        if (!mUser::emailExists($email)) {
            $Response['error'] = "Dont think I've seen you here before. Sure thats your email?";
            return $this->response($Response, ifx_REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (!($Recovery = mRecoveryToken::createRecoveryToken($email))) {
            $this->config->load('email', true);
            $Mail = new CI_Email($this->config->config['email']);

            $Mail->from('admin@battre.infizi.com', 'Battre');
            $Mail->set_mailtype('html');
            $Mail->to($email);
            $Mail->subject("Lost your password? We got you");
            $Data = [
              'recoveryUrl' => base_url('/account/recover/'.$Recovery->token)
            ];
            $Body = $this->load->view('emails/forgot_password.php', $Data, true);
            $Mail->message($Body);

            $Mail->send();

            return $this->response([], ifx_REST_Controller::HTTP_CREATED);
        }

        $Response['error'] = "This.... was not supposed to happen";
        return $this->response($Response, ifx_REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function post_complete_recover()
    {
        $token = $this->data['token'];
        $password = $this->data['password'];

        if (mRecoveryToken::recoveryTokenExists($token)) {
            if (empty($password)) {
                return $this->response(['isValid'=>true], ifx_REST_Controller::HTTP_OK);
            }
        } else {
            return $this->response(['isValid'=>false, 'error'=>'That password reset link is not valid'], ifx_REST_Controller::HTTP_UNAUTHORIZED);
        }

        $Token = new mRecoveryToken();
        $Token->token = $token;
        $Token->load();

        $User = $Token->user;

        if (!$User) {
            return $this->response(['error'=>"Hmm. That token doesn't have a valid user"], ifx_REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $User->set_password($password);

        if ($User->save()) {
            $Token->delete();
            return $this->response([], ifx_REST_Controller::HTTP_ACCEPTED);
        } else {
            return $this->response(['error'=>$User->_validation->all()], ifx_REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
