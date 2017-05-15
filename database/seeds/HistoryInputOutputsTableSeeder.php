<?php

use Illuminate\Database\Seeder;

class HistoryInputOutputsTableSeeder extends Seeder
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
            \App\HistoryInputOutput::create([
                'dis_id'            => 1,
                'io_center_id'      => 1,
                'button_id'         => $button->id,
                'product_id'        => 1, // Cocacola
                'button_product_id' => ++$j,
                'status'            => 'IN',
                'quantum_in'        => 0,
                'quantum_out'       => 0,
                'quantum_remain'    => 0,
                'sum_in'            => 0,
                'sum_out'           => 0,
                'product_price'     => 0,
                'total_pay'         => 0,
                'count'             => $j,
                'created_by'        => 1,
                'updated_by'        => 0,
                'user_input_id'     => 4, // ThuocVietAdmin
                'user_output_id'    => 0,
                'created_date'      => date("Y-m-d H:i:s"),
                'updated_date'      => null,
                'vsys_date'         => date("Y-m-d H:i:s"),
                'isDefault'         => true,
                'adjust_by'         => 0,
                'active'            => true
            ]);
        }
    }
}
