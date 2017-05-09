<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('Người dùng');
            $table->integer('card_id')->unsigned()->comment('Thẻ');
            $table->decimal('total_money', 18, 0)->default(0)->comment('Tổng tiền');
            $table->decimal('sum_dps', 18, 0)->default(0)->comment('Tổng nạp');
            $table->decimal('sum_buy', 18, 0)->default(0)->comment('Tổng mua');
            $table->unsignedBigInteger('count')->default(0)->comment('Biến đếm');
            $table->integer('created_by')->unsigned()->comment('Người tạo');
            $table->integer('updated_by')->unsigned()->comment('Người cập nhật');
            $table->dateTime('created_date')->default(date('Y-m-d H:i:s'))->comment('Ngày tạo');
            $table->dateTime('updated_date')->nullable()->comment('Ngày cập nhật');
            $table->dateTime('vsys_date')->default(date('Y-m-d H:i:s'))->comment('Ngày bộ trung tâm');
            $table->boolean('active')->default(false)->comment('Kích hoạt');
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
        Schema::dropIfExists('user_cards');
    }
}
