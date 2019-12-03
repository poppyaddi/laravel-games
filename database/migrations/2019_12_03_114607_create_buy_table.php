<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buy', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_num', 50)->comment('订单id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('求购者id');
            $table->unsignedBigInteger('game_id')->nullable()->comment('游戏名称');
            $table->unsignedBigInteger('price_id')->nullable()->comment('面值id');
            $table->integer('unit')->comment('求购数量');
            $table->integer('default_unit')->comment('初始求购数量');
            $table->decimal('unit_price', 10, 2)->default(0)->comment('单位价格');
            $table->decimal('fro_money', 10, 2)->default(0)->comment('冻结金额');
            $table->string('description', 100)->nullable()->comment('备注');
            $table->enum('status', [1, 2, 3, 4])->default(1)->comment('1正常挂单2部分交易3交易完成4交易下架');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('set null');
            $table->foreign('price_id')->references('id')->on('prices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buy');
    }
}
