<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perm extends Model
{
    //
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at', 'slug'];
    protected $appends = ['key'];

    public function parent()
    {
        return $this->hasOne(get_class($this),$this->getKeyName(),  'parent_id');
    }

    protected function getKeyAttribute(){
        return (string)$this->id;
    }
}
