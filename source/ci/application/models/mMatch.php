<?php

class mMatch extends ifx_Model
{
    public $has_one = [
      'player1'=>['user', 'player1_id'],
      'player2'=>['user', 'player2_id'],
      'league'
    ];

    public function toJson()
    {
        $Match = (object)[];
        $Match->id = $this->id();
        $Match->date = $this->date;
        $Match->league_id = $this->league->id();
        $Match->player1 = $this->player1->toJson();
        $Match->player2 = $this->player2->toJson();
        $Match->player1Score = $this->player1_score;
        $Match->player2Score = $this->player2_score;

        return $Match;
    }
}
