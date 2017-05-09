<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionVsysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_vsys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('io_center_id')->unsigned();
            $table->integer('card_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->unsignedBigInteger('count')->default(0)->unique();
            $table->dateTime('vsys_date')->default(date('Y-m-d H:i:s'));
            $table->dateTime('created_date')->default(date('Y-m-d H:i:s'));
            $table->dateTime('updated_date')->nullable();
            $table->boolean('active')->default(0);
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
        Schema::dropIfExists('session_vsys');
    }
}
