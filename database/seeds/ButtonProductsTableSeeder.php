<?php

use Illuminate\Database\Seeder;

class ButtonProductsTableSeeder extends Seeder
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
        $j                      = 0;
        $button_of_buttonport_1 = \App\Device::where([['collect_id', 3], ['io_center_id', 1], ['parent_id', 2]])->get();
        foreach ($button_of_buttonport_1 as $button) {
            \App\ButtonProduct::create([
                'dis_id'        => 1,
                'button_id'     => $button->id,
                'product_id'    => 1, // Cocacola
                'total_quantum' => 0,
                'count'         => ++$j,
                'created_by'    => 1,
                'updated_by'    => 1,
                'created_date'  => date("Y-m-d H:i:s"),
                'updated_date'  => date("Y-m-d H:i:s"),
                'vsys_date'     => date("Y-m-d H:i:s"),
                'active'        => true
            ]);
        }
    }
}
