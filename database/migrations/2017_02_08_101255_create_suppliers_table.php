<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique()->comment('Mã');
            $table->string('name', 100)->comment('Tên');
            $table->text('address')->nullable()->comment('Địa chỉ');
            $table->string('ward_code')->nullable()->comment('Phường, xã');
            $table->string('district_code')->nullable()->comment('Quận, huyện');
            $table->string('city_code')->nullable()->comment('Tỉnh, thành phố');
            $table->string('phone')->nullable()->comment('Điện thoại');
            $table->string('email')->nullable()->comment('Email');
            $table->string('fax')->nullable()->comment('fax');
            $table->text('note')->nullable()->comment('Ghi chú');
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
        Schema::dropIfExists('suppliers');
    }
}
