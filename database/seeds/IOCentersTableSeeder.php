<?php

use Illuminate\Database\Seeder;

class IOCentersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array_name = [
            'IO Easy Center 1',
            'IO Easy Center 2',
            'IO Easy Center 3'
        ];

        $array_code = [
            'IO53A096',
            'IO53A097',
            'IO53A098'
        ];;

        foreach($array_name as $key => $name){
            \App\IOCenter::create([
                'code'        => $array_code[$key],
                'name'        => $name,
                'description' => null,
                'created_date'=> date('Y-m-d H:i:s'),
                'updated_date'=> date('Y-m-d H:i:s'),
                'active'      => true,
                'dis_id'      => ++$key
            ]);
        }
    }
}
