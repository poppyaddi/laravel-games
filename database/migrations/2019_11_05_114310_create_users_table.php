<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->comment('账户名称')->unique();
            $table->string('password')->comment('用户密码');
            $table->integer('role_id')->unsigned()->comment('角色id');
            $table->enum('status', [1, 2])->default('1')->comment('用户状态, 1启用2禁用');
            $table->datetime('last_login_time')->nullable()->comment('上次登录时间');
            $table->string('last_login_ip')->nullable()->comment('登陆ip');
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('roles');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
