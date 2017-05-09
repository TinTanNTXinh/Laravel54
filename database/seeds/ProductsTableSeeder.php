<?php

use Illuminate\Database\Seeder;
use App\Traits\DBHelper;

class ProductsTableSeeder extends Seeder
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
            'Cocacola',
            'Pepsi',
            'Mirinda',
            'Bitis Hunter',
            'Convert',
            'Nike'
        ];

        $array_product_type = [
            1,
            1,
            1,
            3,
            3,
            3
        ];

        foreach ($array_name as $key => $name) {
            \App\Product::create([
                'code'            => $this->generateCode(\App\Product::class, 'PRODUCT'),
                'barcode'         => mt_rand(100000000, 900000000),
                'name'            => $name,
                'description'     => $name,
                'created_date'    => date('Y-m-d H:i:s'),
                'updated_date'    => date('Y-m-d H:i:s'),
                'active'          => true,
                'product_type_id' => $array_product_type[$key],
                'producer_id'     => 2,
                'unit_id'         => 2,
                'is_allowed'      => true,
                'created_by'      => 1,
                'updated_by'      => 0
            ]);
        }
    }
}
