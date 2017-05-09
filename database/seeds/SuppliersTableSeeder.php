<?php

use Illuminate\Database\Seeder;
use App\Traits\DBHelper;

class SuppliersTableSeeder extends Seeder
{
    use DBHelper;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $class_name = \App\Supplier::class;

        // Supplier of IOCenter 1
        $array_name = [
            'Công ty Thuốc Việt',
            'Công ty thuốc Phano',
            'Công ty Tin Tấn'
        ];

        foreach ($array_name as $key => $name) {
            \App\Supplier::create([
                'code'    => $this->generateCode($class_name, 'SUPPLIER'),
                'name'    => $name,
                'address' => '',
                'phone'   => '',
                'email'   => 'nguyentrucxjnh@gmail.com',
                'fax'     => '',
                'note'    => '',
                'active'  => true
            ]);
        }

        // Supplier of IOCenter 2
        $array_name = [
            'Công ty Thuốc Việt 2',
            'Công ty thuốc Phano 2',
            'Công ty Tin Tấn 2'
        ];

        foreach ($array_name as $key => $name) {
            \App\Supplier::create([
                'code'          => $this->generateCode($class_name, 'SUPPLIER'),
                'name'          => $name,
                'address'       => '',
                'ward_code'     => '',
                'district_code' => '',
                'city_code'     => '',
                'phone'         => '',
                'email'         => 'nguyentrucxjnh@gmail.com',
                'fax'           => '',
                'note'          => '',
                'active'        => true
            ]);
        }
    }
}
