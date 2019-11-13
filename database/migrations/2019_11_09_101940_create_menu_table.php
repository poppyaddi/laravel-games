<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父级ID');
            $table->string('title', 50)->comment('元素名称');
            $table->string('slug', 50)->commeng('元素标识');
            $table->enum('type', ['m', 'e'])->commeng('资源类型');
            $table->string('path', 50)->nullable()->comment('视图路由');
            $table->string('icon', 50)->nullable()->comment('图标名称');
            $table->unsignedTinyInteger('order')->nullable()->comment('菜单排序');
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
        Schema::dropIfExists('menus');
    }
}
