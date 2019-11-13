<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    //
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    protected $appends = ['key'];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne(get_class($this), $this->getKeyName(), 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(get_class($this), 'parent_id', $this->getKeyName());
    }

    public function getTypeAttribute($value)
    {
        switch ($value){
            case 'm':
                return '菜单';
            case 'e':
                return '页面元素';
        }
    }

    public function setTypeAttribute($value)
    {
        switch ($value){
            case '菜单':
                return 'm';
                break;
            case '页面元素':
                return 'e';
        }
    }

    protected function getKeyAttribute(){
        return (string)$this->id;
    }

    public static function handleType($data)
    {
        # 修改器要有break
        switch($data['type']){
            case '菜单':
                $data['type'] = 'm';
                break;
            case '页面元素':
                $data['type'] =  'e';
        }
        return $data;
    }

    public static function get_all_menus($data)
    {
        $parent_id = self::whereIn('id', $data)->where('type', 'm')->pluck('parent_id')->toArray();
        $menu_ids = array_merge($data, $parent_id);
        # 先转为证书
        $temp = array_map(function($value){
            return (int) $value;
        }, $menu_ids);
        $temp = array_filter($temp, function($value){
            return (int) $value > 0;
        });
        return array_unique($temp);
    }


}
