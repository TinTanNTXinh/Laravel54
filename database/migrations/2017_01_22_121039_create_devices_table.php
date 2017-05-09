<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('collect_code', ['RFID', 'Cabinet', 'Tray', 'Card', 'CDM'])->comment('Mã bộ sưu tập');
            $table->string('code')->comment('Mã');
            $table->string('name')->comment('Tên');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->integer('quantum_product')->default(0)->unsigned()->comment('Số lượng sản phẩm');
            $table->boolean('active')->default(false)->comment('Kích hoạt');
            $table->integer('collect_id')->default(0)->comment('Bộ sưu tập');
            $table->integer('io_center_id')->default(0)->comment('Bộ trung tâm');
            $table->integer('parent_id')->default(0)->comment('Thiết bị cha');
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
        Schema::dropIfExists('devices');
    }
}
