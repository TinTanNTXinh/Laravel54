<?php

use Illuminate\Database\Seeder;

class CollectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array_name = [
            'Máy đọc thẻ',
            'Tủ',
            'Box',
            'Thẻ',
            'Máy nạp tiền'
        ];

        $array_code = [
            'RFID', //'CardReader',
            'Cabinet', //'ButtonPort',
            'Tray', //'Button',
            'Card',
            'CDM' // Cash Deposit Machine
        ];

        foreach($array_name as $key => $name){
            \App\Collection::create([
                'code'        => $array_code[$key],
                'name'        => $name,
                'description' => $name,
                'active'      => true
            ]);
        }
    }
}
