<?php

use Illuminate\Database\Seeder;
use App\Traits\DBHelper;

class ProductTypesTableSeeder extends Seeder
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
            'Nước giải khát',
            'Sữa',
            'Giày dép',
            'Quần áo',
            'Trang sức',
            'Thuốc'
        ];

        foreach($array_name as $key => $name){
            \App\ProductType::create([
                'code'        => $this->generateCode(\App\ProductType::class, 'PRODUCT_TYPE'),
                'name'        => $name,
                'description' => $name,
                'active'      => true
            ]);
        }
    }
}
