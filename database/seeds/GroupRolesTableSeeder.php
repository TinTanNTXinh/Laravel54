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
        $array_name = [
            'Mặc định',
            'Dữ liệu ban đầu',
            'Cài đặt thiết bị',
            'Báo cáo'
        ];

        $array_index = [
            1, 2, 3, 4
        ];

        $array_icon_name = [
            '',
            'glyphicon-align-left icon',
            'glyphicon-cog icon',
            'glyphicon-stats icon',
        ];

        foreach($array_name as $key => $name){
            \App\GroupRole::create([
                'code'        => $this->generateCode(\App\GroupRole::class, 'GROUP_ROLE'),
                'name'        => $array_name[$key],
                'description' => $array_name[$key],
                'icon_name'   => $array_icon_name[$key],
                'index'       => $array_index[$key],
                'active'      => true
            ]);
        }
    }
}
