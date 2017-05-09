<?php

use Illuminate\Database\Seeder;
use App\Traits\DBHelper;

class DevicesTableSeeder extends Seeder
{
    use DBHelper;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $class_name = \App\Device::class;

        /*
         * IOCenter 1
         * */
        // Card Reader (RFID)
        // 1
        \App\Device::create([
            'collect_code'    => 'RFID',
            'code'            => $this->generateCode($class_name, 'RFID'),
            'name'            => 'RFID 1',
            'description'     => 'Máy đọc thẻ 1 cho nhà thuốc nhân ái',
            'quantum_product' => 0,
            'active'          => true,
            'collect_id'      => 1,
            'io_center_id'    => 1,
            'parent_id'       => 0
        ]);

        // Button Port (Cabinet)
        // 2
        \App\Device::create([
            'collect_code'    => 'Cabinet',
            'code'            => $this->generateCode($class_name, 'Cabinet'),
            'name'            => 'Cabinet 1',
            'description'     => 'Tủ 1 cho nhà thuốc nhân ái',
            'quantum_product' => 0,
            'active'          => true,
            'collect_id'      => 2,
            'io_center_id'    => 1,
            'parent_id'       => 0
        ]);

        // Button (Tray)
        // 3
        \App\Device::create([
            'collect_code'    => 'Tray',
            'code'            => 1,
            'name'            => 'Tray 1',
            'description'     => 'Khay 1 của tủ 1 cho nhà thuốc nhân ái',
            'quantum_product' => 23,
            'active'          => true,
            'collect_id'      => 3,
            'io_center_id'    => 1,
            'parent_id'       => 2
        ]);
        // 4
        \App\Device::create([
            'collect_code'    => 'Tray',
            'code'            => 2,
            'name'            => 'Tray 2',
            'description'     => 'Khay 2 của tủ 1 cho nhà thuốc nhân ái',
            'quantum_product' => 23,
            'active'          => true,
            'collect_id'      => 3,
            'io_center_id'    => 1,
            'parent_id'       => 2
        ]);
        // 5
        \App\Device::create([
            'collect_code'    => 'Tray',
            'code'            => 3,
            'name'            => 'Tray 3',
            'description'     => 'Khay 3 của tủ 1 cho nhà thuốc nhân ái',
            'quantum_product' => 23,
            'active'          => true,
            'collect_id'      => 3,
            'io_center_id'    => 1,
            'parent_id'       => 2
        ]);
        // 6
        \App\Device::create([
            'collect_code'    => 'Tray',
            'code'            => 4,
            'name'            => 'Tray 4',
            'description'     => 'Khay 4 của tủ 1 cho nhà thuốc nhân ái',
            'quantum_product' => 23,
            'active'          => true,
            'collect_id'      => 3,
            'io_center_id'    => 1,
            'parent_id'       => 2
        ]);
        // 7
        \App\Device::create([
            'collect_code'    => 'Tray',
            'code'            => 5,
            'name'            => 'Tray 5',
            'description'     => 'Khay 5 của tủ 1 cho nhà thuốc nhân ái',
            'quantum_product' => 23,
            'active'          => true,
            'collect_id'      => 3,
            'io_center_id'    => 1,
            'parent_id'       => 2
        ]);

        // Card
        // 8
        \App\Device::create([
            'collect_code'    => 'Card',
            'code'            => '252858287D',
            'name'            => 'Card 1',
            'description'     => 'Card 1 cho nv nhập Thuốc Việt',
            'quantum_product' => 0,
            'active'          => true,
            'collect_id'      => 4,
            'io_center_id'    => 1,
            'parent_id'       => 1
        ]);
        // 9
        \App\Device::create([
            'collect_code'    => 'Card',
            'code'            => '2564582831',
            'name'            => 'Card 2',
            'description'     => 'Card 2 cho nv nhập Thuốc Việt',
            'quantum_product' => 0,
            'active'          => true,
            'collect_id'      => 4,
            'io_center_id'    => 1,
            'parent_id'       => 1
        ]);
        // 10
        \App\Device::create([
            'collect_code'    => 'Card',
            'code'            => '75EC5428E5',
            'name'            => 'Card 3',
            'description'     => 'Card 3 cho nv bán Nhân Ái',
            'quantum_product' => 0,
            'active'          => true,
            'collect_id'      => 4,
            'io_center_id'    => 1,
            'parent_id'       => 1
        ]);
        // 11
        \App\Device::create([
            'collect_code'    => 'Card',
            'code'            => '755C562857',
            'name'            => 'Card 4',
            'description'     => 'Card 4 cho nv bán Nhân Ái',
            'quantum_product' => 0,
            'active'          => true,
            'collect_id'      => 4,
            'io_center_id'    => 1,
            'parent_id'       => 1
        ]);

        // CDM
        // 12
        \App\Device::create([
            'collect_code'    => 'CDM',
            'code'            => $this->generateCode($class_name, 'CDM'),
            'name'            => 'CDM 1',
            'description'     => 'Máy nạp tiền 1 đặt ở Nhà thuốc Nhân Ái',
            'quantum_product' => 0,
            'active'          => true,
            'collect_id'      => 5,
            'io_center_id'    => 1,
            'parent_id'       => 0
        ]);
    }
}
