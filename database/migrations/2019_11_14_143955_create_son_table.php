<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('主账户id');
            $table->string('name', 50)->unique()->comment('子账户名称');
            $table->string('password', 255)->comment('账户密码');
            $table->enum('type', [1, 2, 3])->comment('账户类型1入库2出库3入库出库');
            $table->enum('status', [1, 2])->default(1)->comment('账户类型1启用2禁用');
            $table->timestamps();
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
        Schema::dropIfExists('sons');
    }
}
