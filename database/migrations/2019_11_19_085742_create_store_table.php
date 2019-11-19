<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('price')->comment('凭证面值');
            $table->string('identifier', 50)->unique()->comment('凭证订单号');
            $table->text('receipt')->comment('凭证内容');
            $table->text('new_receipt')->nullable()->comment('新凭证');
            $table->string('description', 100)->comment('凭证描述');
            $table->unsignedBigInteger('game_id')->comment('游戏外键');
            $table->unsignedBigInteger('price_id')->comment('面值外键');
            $table->unsignedBigInteger('input_user_id')->nullable()->comment('入库主账号id(外键, delete set null)');
            $table->unsignedBigInteger('owner_user_id')->comment('当前所有人id(可为主账户或子账户)');
            $table->enum('user_type', [1, 2])->default(2)->comment('凭证拥有者账户类型1主账户2子账户');
            $table->enum('status', [1, 2, 3, 4, 5, 6, 7, 8])->comment('凭证状态;1有效2已使用3已过期4使用失败5后台恢复不可交易6手机端已获取7禁止使用8上架中禁止出库');
            $table->dateTime('start_time')->comment('凭证生效时间');
            $table->dateTime('use_time')->nullable()->comment('使用时间');
            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('price_id')->references('id')->on('prices');
            $table->foreign('input_user_id')->references('id')->on('users')->onDelete('set null');

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
        Schema::dropIfExists('stores');
    }
}
