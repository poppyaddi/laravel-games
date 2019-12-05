<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawFeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw_fee', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('money_id')->nullable()->comment('提现表id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('用户id');
            $table->decimal('money')->comment('提现手续费');
            $table->string('description', 100)->comment('备注');
            $table->timestamps();
            $table->foreign('money_id')->references('id')->on('money')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdraw_fee');
    }
}
