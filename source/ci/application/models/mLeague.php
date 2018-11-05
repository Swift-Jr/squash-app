<?php

class mLeague extends ifx_Model
{
    public $has_one = [
      'owner'=>['user', 'owner_id'],
      'club'
    ];

    public $has_many = [
      'matches'=>['match', 'league_id']
    ];

    public function toJson()
    {
        $Object = (object)[];
        $Object->id = $this->id();
        $Object->created = $this->created;
        $Object->owner_id = $this->owner_id;
        $Object->club_id = $this->club_id;
        $Object->name = $this->name;
        $Object->shortname = $this->shortname;
        $Object->results = $this->leagueTable();

        return $Object;
    }

    public function alreadyExists()
    {
        $Check = new self();
        $Check->name = $this->name;
        $Check->shortname = $this->shortname;

        return $Check->load();
    }

    public function leagueTable()
    {
        $PlayerResults = [];

        foreach ($this->matches as $Match) {
            if (!$PlayerResults[$Match->player1->id()]) {
                $PlayerResults[$Match->player1->id()] = new Result($Match->player1->toJson());
            }

            if (!$PlayerResults[$Match->player2->id()]) {
                $PlayerResults[$Match->player2->id()] = new Result($Match->player2->toJson());
            }


            $Player1 =& $PlayerResults[$Match->player1->id()];
            $Player2 =& $PlayerResults[$Match->player2->id()];

            if ($Match->player1_score > $Match->player2_score) {
                $Player1->wonGame($Match->player1_score, $Match->player2_score);
                $Player2->lostGame($Match->player2_score, $Match->player1_score);
            } else {
                $Player1->lostGame($Match->player1_score, $Match->player2_score);
                $Player2->wonGame($Match->player2_score, $Match->player1_score);
            }
        }

        usort($PlayerResults, function ($a, $b) {
            if ($a->scorePoints === $b->scorePoints) {
                if ($a->scoreMargin === $b->scoreMargin) {
                    return $a->scoreDiff < $b->scoreDiff;
                }
                return $a->scoreMargin < $b->scoreMargin;
            }
            return $a->scorePoints < $b->scorePoints;
        });

        for ($Place=0; $Place<count($PlayerResults); $Place++) {
            $PlayerResults[$Place]->place = ($Place + 1);
        }

        return $PlayerResults;
    }
}

class Result
{
    public $player = null;
    public $matchesPlayed = 0;
    public $matchesWon = 0;
    public $matchesLost = 0;
    public $pointsWon = 0;
    public $pointsLost = 0;
    public $scorePoints = 0;
    public $scoreMargin = 0;
    public $scoreDiff = 0;
    public $place = 0;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function playedGame()
    {
        $this->matchesPlayed++;
    }

    public function wonGame($MyScore = 0, $TheirScore = 0, $WinPoints = 2)
    {
        $this->playedGame();
        $this->scoredPoints($MyScore);
        $this->lostPoints($TheirScore);

        $this->matchesWon++;
        $this->scorePoints += $WinPoints;

        if ($MyScore > $TheirScore) {
            $this->scoreDiff += ($MyScore - $TheirScore);
        }
    }

    public function lostGame($MyScore = 0, $TheirScore = 0, $DeucePoints = 1)
    {
        $this->playedGame();
        $this->scoredPoints($MyScore);
        $this->lostPoints($TheirScore);
        $this->matchesLost++;

        if ($MyScore === ($TheirScore - 2)) {
            $this->scoreMargin += $DeucePoints;
        }
    }

    public function scoredPoints(int $points)
    {
        $this->pointsWon += $points;
    }

    public function lostPoints(int $points)
    {
        $this->pointsLost += $points;
    }
}
