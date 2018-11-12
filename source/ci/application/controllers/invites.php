<?php

class Invites extends authenticated_REST_Controller
{
    public function __construct()
    {
        parent::__construct(['get_invite']);
    }

    public function post_create()
    {
        $User = new mUser($this->token->getClaim('user_id'));
        $Invites = $this->data['invites'];
        $ClubId = $this->data['club_id'];

        $Club = new mClub($ClubId);

        if (!$Club->is_loaded()) {
            $this->response(['error'=>"The selected club is not valid"], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->config->load('email', true);
        $Mail = new CI_Email($this->config->config['email']);

        $Mail->from('admin@battre.infizi.com', 'Battre');
        $Mail->set_mailtype('html');
        $Sent = 0;

        foreach ($Invites as $email) {
            if (mUser::emailExists($email)) {
                $ExistingUser = new mUser();
                $ExistingUser->email = $email;
                $ExistingUser->load();
                $ExistingUser->save($Club);

                continue;
            }

            if (mInvite::emailExists($email)) {
                continue;
            }

            $Invite = new mInvite();
            $Invite->email = $email;
            $Invite->club = $Club;

            if (!$User->save($Invite)) {
                $Errors = $NewClub->_validation->all();
                $this->response(['error'=>$Errors], ifx_REST_Controller::HTTP_BAD_REQUEST);
            }

            $Mail->to($email);
            $Mail->subject("You're invited to play $User->firstname on Battre");
            $Data = [
                'invitedBy' => $User->firstname,
                'clubName' => $Club->name,
                'inviteUrl' => (APP_URL.'/account/invite/'.$Invite->token)
            ];
            $Body = $this->load->view('emails/invite.php', $Data, true);
            $Mail->message($Body);

            if (!$Mail->send()) {
                $this->response(['error'=>'Unable to send invite emails right now'], ifx_REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                $Sent++;
                $SentInvites[] = $Invite->toJson();
            }
        }

        $this->response(['sent'=>$Sent, 'invites'=>$SentInvites], ifx_REST_Controller::HTTP_ACCEPTED);
    }

    public function get_get_invite($Token)
    {
        $Invite = new mInvite();
        $Invite->token = $Token;

        if (!$Invite->load()) {
            $this->response(['error'=>'Thats links not valid'], ifx_REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->response(['invite'=>$Invite->toJson()], ifx_REST_Controller::HTTP_OK);
    }

    public function get_all()
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $Invites = [];

        foreach ($User->invites as $Invite) {
            $Invites[] = $Invite->toJson();
        }

        $this->response(['invites'=>$Invites], ifx_REST_Controller::HTTP_OK);
    }
}
