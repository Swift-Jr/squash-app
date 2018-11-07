<?php

class mUser extends ifx_Model
{
    public $has_many = [
        'recoveryToken',
        'clubs'=>'club',
        'invites'=>['invite', 'invited_by']
    ];

    public function toJson()
    {
        return (object)[
        'id'=>$this->id(),
        'user_id'=>$this->id(),
        'email'=>$this->email,
        'firstname'=>$this->firstname,
        'lastname'=>$this->lastname,
        'google_id'=>$this->google_id
      ];
    }

    public function createJWT()
    {
        return JWT::createToken($this->toJson());
    }

    public function set_password($password)
    {
        return $this->password = static::hashPassword($password);
    }

    public static function emailIsUnique($email)
    {
        $Check = new self();
        $Check->email = $email;
        return $Check->count() == 0;
    }

    public static function emailExists($email)
    {
        $Check = new self();
        $Check->email = $email;
        return $Check->count() > 0;
    }

    public static function passwordIsStrong($password)
    {
        return strlen($password) > 5;
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public function __set_email($email)
    {
        return strtolower($email);
    }

    public function __get_firstname($name)
    {
        return ucfirst($name);
    }

    public function __get_lastname($name)
    {
        return ucfirst($name);
    }
}
