<?php

use Illuminate\Database\Seeder;

class UserCardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * IOCenter 1
         * */
        // NV Nhap hang 1 Thuoc Viet
        \App\UserCard::create([
            'user_id'      => 5,
            'card_id'      => 8,
            'count'        => 0,
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'vsys_date'    => date("Y-m-d H:i:s"),
            'total_money'  => 0,
            'sum_dps'      => 0,
            'sum_buy'      => 0,
            'active'       => true
        ]);

        // NV Nhap hang 2 Thuoc Viet
        \App\UserCard::create([
            'user_id'      => 6,
            'card_id'      => 9,
            'count'        => 0,
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'vsys_date'    => date("Y-m-d H:i:s"),
            'total_money'  => 0,
            'sum_dps'      => 0,
            'sum_buy'      => 0,
            'active'       => true
        ]);

        // NV Xuat hang Nhan Ai cung la Admin
        \App\UserCard::create([
            'user_id'      => 7,
            'card_id'      => 10,
            'count'        => 0,
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'vsys_date'    => date("Y-m-d H:i:s"),
            'total_money'  => 0,
            'sum_dps'      => 0,
            'sum_buy'      => 0,
            'active'       => true
        ]);

        \App\UserCard::create([
            'user_id'      => 8,
            'card_id'      => 11,
            'count'        => 0,
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'vsys_date'    => date("Y-m-d H:i:s"),
            'total_money'  => 0,
            'sum_dps'      => 0,
            'sum_buy'      => 0,
            'active'       => true
        ]);
    }
}
