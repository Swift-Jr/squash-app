<?php

  class Scorecard extends authenticated_REST_Controller
  {
      public function get_headtohead($Player1, $Player2)
      {
          $Match = new mMatch();
          $Match->db->group_start()
            ->where('match.player1_id', $Player1)
            ->where('match.player2_id', $Player2)
            ->group_end()->group_start()
            ->or_where('match.player1_id', $Player2)
            ->or_where('match.player2_id', $Player1)
            ->group_end();

          $Result = (object)[
            'gamesPlayed' => 0,
            'p1gamesWon' => 0,
            'p2gamesWon' => 0,
            'p1pointsWon' => 0,
            'p2pointsWon' => 0,
            'p1pointsLost' => 0,
            'p2pointsLost' => 0
          ];

          foreach ($Match->fetch() as $Game) {
              $Result->gamesPlayed++;

              if ($Game->player1_id == $Player1) {
                  if ($Game->player1_score > $Game->player2_score) {
                      $Result->p1gamesWon++;
                  } else {
                      $Result->p2gamesWon++;
                  }
                  $Result->p1pointsWon += $Game->player1_score;
                  $Result->p2pointsWon += $Game->player2_score;
                  $Result->p1pointsLost += $Game->player2_score;
                  $Result->p2pointsLost += $Game->player1_score;
              } elseif ($Game->player1_id == $Player2) {
                  if ($Game->player1_score > $Game->player2_score) {
                      $Result->p2gamesWon++;
                  } else {
                      $Result->p1gamesWon++;
                  }
                  $Result->p2pointsWon += $Game->player1_score;
                  $Result->p1pointsWon += $Game->player2_score;
                  $Result->p2pointsLost += $Game->player2_score;
                  $Result->p1pointsLost += $Game->player1_score;
              }
          }

          $this->response($Result, ifx_REST_Controller::HTTP_OK);
      }
  }
