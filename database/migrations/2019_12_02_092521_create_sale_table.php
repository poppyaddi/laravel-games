<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_num', 50)->comment('订单号');
            $table->unsignedBigInteger('user_id')->nullable()->comment('出售者id');
            $table->unsignedBigInteger('game_id')->nullable()->comment('游戏id');
            $table->unsignedBigInteger('price_id')->nullable()->comment('面值id');
            $table->string('store_id', 10000)->comment('凭证id');
            $table->integer('unit')->default(0)->comment('凭证个数');
            $table->decimal('unit_price', 10, 2)->comment('凭证单价');
            $table->enum('status', [1, 2, 3, 4])->comment('订单状态1正常挂单2部分交易3交易完成4交易过期5订单下架');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('game_id')->references('id')->on('games')->onDelete('set null');
            $table->foreign('price_id')->references('id')->on('prices')->onDelete('set null');
            $table->string('description', 300)->nullable()->comment('订单备注');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
