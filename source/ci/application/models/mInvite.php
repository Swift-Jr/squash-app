<?php

class mInvite extends ifx_Model
{
    public $has_one = [
      'by'=>['user', 'invited_by'],
      'club'
    ];

    public function toJson()
    {
        return (object)[
          'email'=>$this->email,
          'token'=>$this->token,
          'invited_by'=>$this->by->id(),
          'club_id' => $this->club->id()
        ];
    }

    public static function emailExists($email)
    {
        $Check = new self();
        $Check->email = $email;
        return $Check->count() > 0;
    }

    public function before_save()
    {
        if (empty($this->token)) {
            $this->token = substr(md5(time()), -6, 6);
        }
    }

    public function isGarbage()
    {
        $this->ci->load->helper('date');
        if (mysql_to_unix($this->created) < time()-259200) {
            $this->delete();

            return true;
        }

        return false;
    }

    public function __set_email($email)
    {
        return strtolower($email);
    }

    public function __set_token($token)
    {
        return strtoupper($token);
    }
}
