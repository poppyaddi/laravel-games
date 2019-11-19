<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('gold', 50)->unique()->comment('面值名称(游戏币)');
            $table->string('title', 50)->unique()->comment('面值唯一标识');
            $table->decimal('money')->comment('面值价格');
            $table->unsignedBigInteger('game_id')->comment('游戏id');
            $table->enum('status', [1, 2])->default(1)->comment('1启用2禁用');
            $table->timestamps();
            $table->foreign('game_id')->references('id')->on('games');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prices');
    }
}
