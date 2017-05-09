<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCardMoneysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_card_moneys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('io_center_id')->unsigned()->comment('Bộ trung tâm');
            $table->integer('device_id')->unsigned()->comment('Thiết bị');
            $table->integer('user_card_id')->unsigned()->default(0)->comment('Mã phân người dùng - thẻ');
            $table->enum('status', ['DPS', 'WDR', 'BUY'])->comment('Trạng thái: GỬI, RÚT, MUA');
            $table->decimal('money', 18, 0)->default(0)->comment('Số tiền');
            $table->unsignedBigInteger('count')->default(0)->comment('Biến đếm');
            $table->integer('created_by')->unsigned()->default(0)->comment('Người tạo');
            $table->integer('updated_by')->unsigned()->default(0)->comment('Người cập nhật');
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
        Schema::dropIfExists('user_card_moneys');
    }
}
