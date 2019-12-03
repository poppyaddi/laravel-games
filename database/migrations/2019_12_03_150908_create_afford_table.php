<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('afford', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('预购者id');
            $table->unsignedBigInteger('buy_id')->nullable()->comment('预购表id');
            $table->integer('unit')->default(0)->comment('供货数量');
            $table->decimal('unit_price', 10, 2)->comment('当时的价格');
            $table->unsignedBigInteger('price_id')->nullable()->comment('面值id');
            $table->decimal('fro_buy_money')->default(0)->comment('供货者的冻结金额');
            $table->enum('status', [1, 2])->default(1)->comment('供货状态1未供货2已供货');
            $table->decimal('fro_self_money')->default(0)->comment('冻结供货者的金额');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('buy_id')->references('id')->on('buy')->onDelete('set null');
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
        Schema::dropIfExists('afford');
    }
}
