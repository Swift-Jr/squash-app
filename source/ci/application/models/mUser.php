<?php

class mUser extends ifx_Model {
    public $has_many = ['recoveryToken'];

    function set_password($password){
        return $this->password = static::hashPassword($password);
    }

    static function emailIsUnique($email){
      $Check = new self();
      $Check->email = $email;
      return $Check->count() == 0;
    }

    static function emailExists($email){
      $Check = new self();
      $Check->email = $email;
      return $Check->count() > 0;
    }

    static function passwordIsStrong($password){
        return strlen($password) > 5;
    }

    static function hashPassword($password){
        return password_hash($password, PASSWORD_BCRYPT);
    }

    function verifyPassword($password){
        return password_verify($password, $this->password);
    }

}

?>
