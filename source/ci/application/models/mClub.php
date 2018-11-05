<?php

class mClub extends ifx_Model
{
    public $has_one = [
      'owner'=>['user', 'owner_id']
    ];

    public $has_many = [
        'leagues'=>'league',
        'members'=>'user',
        'invites'=>'invite'
    ];

    /*public $rules = [
        'name' => ['min_length[1]', 'max_length[20]']
    ];*/

    public function toJson()
    {
        $Club = (object)[];
        $Club->id = $this->id();
        $Club->name = $this->name;
        /*$Club->leagues = [];

        foreach ($this->leagues as $League) {
            $Club->leagues[] = $League->toJson();
        }*/

        foreach ($this->members as $Member) {
            $Club->members[] = $Member->toJson();
        }

        return $Club;
    }
}
