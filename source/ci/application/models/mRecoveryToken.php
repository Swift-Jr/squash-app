<?php

  class mRecoveryToken extends ifx_Model{
    public $has_one = ['user'];

    static function createRecoveryToken($email){
      static::deleteRecoveryToken($email);

      $User = new mUser();
      $User->email = $email;
      if(!$User->load()){
        throw new Exception("Unable to recover user. Unknown address provided");
      }

      $RecoveryToken = new self();
      $RecoveryToken->token = substr(md5(time()),-6, 6);

      if($RecoveryToken->save($User)){
        //send an email
        return true;
      }else{
        throw new Exception("Unable to create recovery token");
      }
    }

    static function deleteRecoveryToken($email){
      $User = new mUser();
      $User->email = $email;
      if(!$User->load()){
        throw new Exception("Unable to delete tokens. Unknown address provided");
      }

      foreach($User->RecoveryToken as $Token){
        $Token ->delete();
      }
    }

    static function recoveryTokenExists($token){
      $RecoveryToken = new self();
      $RecoveryToken->token = $token;
      return $RecoveryToken->count() == 1;
    }
  }
