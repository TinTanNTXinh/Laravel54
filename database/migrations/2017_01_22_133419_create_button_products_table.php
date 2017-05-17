<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateButtonProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('button_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dis_id')->unsigned()->comment('Distributor');
            $table->integer('button_id')->unsigned()->comment('Box');
            $table->integer('product_id')->unsigned()->comment('Sản phẩm');
            $table->integer('total_quantum')->default(0)->comment('Số sản phẩm hiện tại');
            $table->unsignedBigInteger('count')->default(0)->comment('Biến đếm');
            $table->integer('created_by')->default(0)->unsigned()->comment('Người tạo');
            $table->integer('updated_by')->default(0)->unsigned()->comment('Nguời cập nhật');
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
        Schema::dropIfExists('button_products');
    }
}
