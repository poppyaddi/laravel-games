<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户id');
            $table->decimal('money', 10, 2)->comment('充值或提现金额');
            $table->enum('type', [1, 2, 3])->default(1)->comment('1支付宝2微信3其他');
            $table->string('real_name', 20)->comment('真实姓名');
            $table->string('account', 50)->comment('打款账户');
            $table->string('fro_money')->nullable()->comment('提现时冻结的金额');
            $table->enum('status', [1, 2, 3])->comment('申请状态1未审核2审核通过3审核拒绝');
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
        Schema::dropIfExists('money');
    }
}
