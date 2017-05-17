<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoryInputOutputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_input_outputs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dis_id')->unsigned()->comment('Distributor');
            $table->integer('io_center_id')->unsigned()->comment('Bộ trung tâm');
            $table->integer('button_id')->unsigned()->comment('Mâm');
            $table->integer('product_id')->unsigned()->comment('Sản phẩm');
            $table->integer('button_product_id')->default(0)->unsigned()->comment('Mã phân mâm - sản phẩm');
            $table->enum('status', ['IN', 'OUT'])->comment('Trạng thái: NẠP, BÁN');
            $table->bigInteger('quantum_in')->default(0)->comment('Số lượng nạp');
            $table->bigInteger('quantum_out')->default(0)->comment('Số lượng bán');
            $table->bigInteger('quantum_remain')->default(0)->comment('Số lượng còn lại');
            $table->bigInteger('sum_in')->default(0)->comment('Tổng nhập');
            $table->bigInteger('sum_out')->default(0)->comment('Tổng xuất');
            $table->decimal('product_price', 18, 0)->default(0)->comment('Giá sản phẩm');
            $table->decimal('total_pay', 18, 0)->default(0)->comment('Tổng tiền');
            $table->unsignedBigInteger('count')->default(0)->comment('Biến đếm');
            $table->integer('created_by')->default(0)->unsigned()->comment('Người tạo');
            $table->integer('updated_by')->default(0)->unsigned()->comment('Người cập nhật');
            $table->integer('user_input_id')->default(0)->unsigned()->comment('Nhân viên nạp');
            $table->integer('user_output_id')->default(0)->unsigned()->comment('Nhân viên bán');
            $table->dateTime('created_date')->default(date('Y-m-d H:i:s'))->comment('Ngày tạo');
            $table->dateTime('updated_date')->nullable()->comment('Ngày cập nhật');
            $table->dateTime('vsys_date')->default(date('Y-m-d H:i:s'))->comment('Ngày bộ trung tâm');
            $table->boolean('isDefault')->default(false)->comment('Là dòng dữ liệu mặc định của admin');
            $table->integer('adjust_by')->default(0)->comment('Người điều chỉnh số lượng (nhập xuất cân bằng)');
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
        Schema::dropIfExists('history_input_outputs');
    }
}
