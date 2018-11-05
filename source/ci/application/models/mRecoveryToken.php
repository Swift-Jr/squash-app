<?php

  class mRecoveryToken extends ifx_Model
  {
      public $has_one = ['user'];

      public static function createRecoveryToken($email)
      {
          static::deleteRecoveryToken($email);

          $User = new mUser();
          $User->email = $email;
          if (!$User->load()) {
              throw new Exception("Unable to recover user. Unknown address provided");
          }

          $RecoveryToken = new self();

          if ($RecoveryToken->save($User)) {
              //send an email
              return $RecoveryToken;
          } else {
              throw new Exception("Unable to create recovery token");
          }
      }

      public static function deleteRecoveryToken($email)
      {
          $User = new mUser();
          $User->email = $email;
          if (!$User->load()) {
              throw new Exception("Unable to delete tokens. Unknown address provided");
          }

          foreach ($User->RecoveryToken as $Token) {
              $Token ->delete();
          }
      }

      public function before_save()
      {
          if (empty($this->token)) {
              $this->token = substr(md5(time()), -6, 6);
          }
      }

      public static function recoveryTokenExists($token)
      {
          $RecoveryToken = new self();
          $RecoveryToken->token = $token;
          return $RecoveryToken->count() == 1;
      }

      public function __set_token($token)
      {
          return strtoupper($token);
      }
  }
