<?php

use Illuminate\Database\Seeder;

class UserCardMoneysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_cards = \App\UserCard::whereActive(true)->get();
        foreach ($user_cards as $user_card) {
            \App\UserCardMoney::create([
                'io_center_id' => 0,
                'device_id'    => 0,
                'user_card_id' => $user_card->id,
                'status'       => 'DPS',
                'money'        => 0,
                'count'        => 0,
                'created_by'   => 1,
                'updated_by'   => 0,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => null,
                'vsys_date'    => date('Y-m-d H:i:s'),
                'active'       => true
            ]);
        }
    }
}
