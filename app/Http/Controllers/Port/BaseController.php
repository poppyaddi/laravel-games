<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    //
    public function parent()
    {
        $user_id    = auth('port')
                    ->user()
                    ->user_id;
        $user_info  = User::where('users.id', $user_id)
                    ->with('userinfo')
                    ->first();
        return $user_info;
        /**
         * {
            "id": 9,
            "name": "test1",
            "role_id": 3,
            "status": false,
            "last_login_time": null,
            "last_login_ip": null,
            "userinfo": {
                "id": 2,
                "user_id": 9,
                "nickname": "大头娃娃742202",
                "loginnum": 0,
                "money": "0.00",
                "fro_money": "0.00",
                "expire_time": "2019-12-25",
                "pay_pass": "123456",
                "charge_status": "月租收费",
                "admin": "1",
                "pass_store": "2",
                "save_device": "无需启用",
                "nickname_change_times": 0
            }
          }
         */
    }
}
