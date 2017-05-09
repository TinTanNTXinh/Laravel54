<?php

use Illuminate\Database\Seeder;

class ProductPricesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = \App\Product::all();
        foreach($products as $product){
            \App\ProductPrice::create([
                'product_id' => $product->id,
                'price_input' => 10000,
                'price_output' => 12000,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => null,
                'active' => true
            ]);
        }
        
    }
}
