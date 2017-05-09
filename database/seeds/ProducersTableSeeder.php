<?php

use Illuminate\Database\Seeder;
use App\Traits\DBHelper;

class ProducersTableSeeder extends Seeder
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
            'Tân Hiệp Phát',
            'Guardian',
            'Gucci',
            'Bitis'
        ];

        foreach($array_name as $name) {
            \App\Producer::create([
                'code'    => $this->generateCode(\App\Producer::class, 'PRODUCER'),
                'name'    => $name,
                'address' => '',
                'phone'   => '',
                'email'   => 'company@company.com',
                'fax'     => '',
                'note'    => '',
                'active'  => true
            ]);
        }
    }
}
