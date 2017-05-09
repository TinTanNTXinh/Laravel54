<?php

use Illuminate\Database\Seeder;
use App\Traits\DBHelper;

class GroupRolesTableSeeder extends Seeder
{
    use DBHelper;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array_code = [
            'MD',
            'DLBD',
            'CDTB',
            'BC'
        ];

        $array_name = [
            'Mặc định',
            'Dữ liệu ban đầu',
            'Cài đặt thiết bị',
            'Báo cáo'
        ];

        foreach($array_name as $key => $name){
            \App\GroupRole::create([
                'code'        => $this->generateCode(\App\GroupRole::class, 'GROUP_ROLE'),
                'name'        => $array_name[$key],
                'description' => $array_name[$key],
                'active'      => true
            ]);
        }
    }
}
