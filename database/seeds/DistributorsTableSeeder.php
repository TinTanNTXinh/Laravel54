<?php

use Illuminate\Database\Seeder;
use App\Traits\DBHelper;

class DistributorsTableSeeder extends Seeder
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
            'Nhà thuốc Nhân Ái',
            'Nhà thuốc Kim Long',
            'Nhà thuốc Bảo Châu'
        ];

        $array_sup_id = [
            1, // Supplier of IOCenter 1
            1,
            1
        ];

        foreach ($array_name as $key => $name) {
            \App\Distributor::create([
                'code'          => $this->generateCode(\App\Distributor::class, 'DISTRIBUTOR'),
                'name'          => $name,
                'address'       => '',
                'ward_code'     => '',
                'district_code' => '',
                'city_code'     => '',
                'phone'         => '',
                'email'         => 'nguyentrucxjnh@gmail.com',
                'fax'           => '',
                'note'          => '',
                'active'        => true,
                'sup_id'        => $array_sup_id[$key]
            ]);
        }
    }
}
