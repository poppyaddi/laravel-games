<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserinfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userinfo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户id');
            $table->string('nickname', 50)->comment('用户昵称');
            $table->tinyInteger('loginnum')->default(0)->comment('登陆次数');
            $table->decimal('money')->default(0)->comment('用户余额');
            $table->decimal('fro_money')->default(0)->comment('冻结金额');
            $table->dateTime('expire_time')->comment('过期时间');
            $table->string('pay_pass', 255)->comment('支付密码');
            $table->enum('charge_status', [1, 2])->default(1)->comment('收费方式, 1月租2出库');
            $table->enum('admin', [1, 2])->default(1)->comment('子账户是否有权上传游戏包名;1有2无');
            $table->enum('pass_store', [1, 2])->default(1)->comment('出库跳过使用过的凭证1跳过2不跳过');
            $table->enum('save_device', [1, 2])->default(2)->comment('首次登陆是否校验;1校验2不校验');
            $table->tinyInteger('nickname_change_times')->default(0)->comment('昵称修改次数');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userinfo');
    }
}
