<?php

class Me extends authenticated_REST_Controller
{
    public function get_index()
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $Response = (object)[];
        $Response->user = $User->toJson();
        $Response->clubs = $this->_getClubsFromUser($User);
        //$Response->leagues = $this->_getLeaguesFromUser($User);

        return $this->response($Response, ifx_REST_Controller::HTTP_OK);
    }

    public function get_clubs()
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $Response = (object)[];
        $Response->clubs = $this->_getClubsFromUser($User);

        return $this->response($Response, ifx_REST_Controller::HTTP_OK);
    }

    public function get_leagues($ClubID = null)
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $Response = (object)[];
        $Response->leagues = $this->_getLeaguesFromUser($User, $ClubID);

        return $this->response($Response, ifx_REST_Controller::HTTP_OK);
    }

    public function get_matches($ClubID = null, $LeagueID = null)
    {
        $User = new mUser($this->token->getClaim('user_id'));

        $Response = (object)[];
        $Response->matches = $this->_getMatchesFromUser($User, $ClubID, $LeagueID);

        return $this->response($Response, ifx_REST_Controller::HTTP_OK);
    }

    public function _getClubsFromUser(mUser $User)
    {
        $Clubs = [];
        $this->db->order_by('clubs.club_id');
        foreach ($User->clubs as $Club) {
            array_push($Clubs, $Club->toJson());
        }

        return $Clubs;
    }

    public function _getLeaguesFromUser(mUser $User, $ClubID = null)
    {
        $Leagues = [];
        foreach ($User->clubs as $Club) {
            if (!is_null($ClubID)) {
                if ($Club->id() !== (int) $ClubID) {
                    continue;
                }
            }
            foreach ($Club->leagues as $League) {
                array_push($Leagues, $League->toJson());
            }
        }

        return $Leagues;
    }

    public function _getMatchesFromUser(mUser $User, $ClubID = null, $LeagueID = null)
    {
        $Matches = [];
        foreach ($User->clubs as $Club) {
            if (!is_null($ClubID)) {
                if ($Club->id() !== (int) $ClubID) {
                    continue;
                }
            }
            foreach ($Club->leagues as $League) {
                if (!is_null($LeagueID)) {
                    if ($League->id() !== (int) $LeagueID) {
                        continue;
                    }
                }
                foreach ($League->matches as $Match) {
                    array_push($Matches, $Match->toJson());
                }
            }
        }

        return $Matches;
    }
}
