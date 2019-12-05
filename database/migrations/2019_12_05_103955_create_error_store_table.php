<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_store', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('receipt', 10000)->comment('凭证');
            $table->unsignedBigInteger('user_id')->nullable()->comment('子账户id');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('主账户id');
            $table->string('description', 100)->comment('备注');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('sons')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('error_store');
    }
}
