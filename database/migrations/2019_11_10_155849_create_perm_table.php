<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->comment('父级权限id');
            $table->string('name', 100)->comment('权限名称');
            $table->string('slug', 100)->nullable()->comment('权限标识');
            $table->string('uri',100)->comment('接口uri');
            $table->string('method', 10)->comment('请求方式');
            $table->string('description', 100)->comment('权限描述');
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('perms');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perms');
    }
}
