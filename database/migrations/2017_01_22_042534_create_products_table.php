<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('barcode')->nullable()->comment('Mã vạch');
            $table->string('name')->comment('Tên');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->dateTime('created_date')->default(date('Y-m-d H:i:s'))->comment('Ngày tạo');
            $table->dateTime('updated_date')->nullable()->comment('Ngày cập nhật');
            $table->boolean('active')->default(false)->comment('Kích hoạt');
            $table->integer('product_type_id')->unsigned()->default(0)->comment('Loại');
            $table->integer('producer_id')->unsigned()->default(0)->comment('Nhà sản xuất');
            $table->integer('unit_id')->unsigned()->comment('Đơn vị tính');
            $table->boolean('is_allowed')->default(false)->comment('Được cho phép');
            $table->integer('created_by')->unsigned()->comment('Người tạo');
            $table->integer('updated_by')->unsigned()->comment('Người cập nhật');
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
        Schema::dropIfExists('products');
    }
}
