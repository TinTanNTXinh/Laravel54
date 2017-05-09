<?php

use Illuminate\Database\Seeder;

class UserRolesTableSeeder extends Seeder
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
        // Thuoc Viet Admin
        \App\UserRole::create([
            'user_id'      => 4,
            'role_id'      => 15,
            'created_by'   => 1,
            'updated_by'   => 0,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'active'       => true
        ]);

        // Thuoc Viet Input
        \App\UserRole::create([
            'user_id'      => 5,
            'role_id'      => 17,
            'created_by'   => 1,
            'updated_by'   => 0,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'active'       => true
        ]);

        // Thuoc Viet Input
        \App\UserRole::create([
            'user_id'      => 6,
            'role_id'      => 17,
            'created_by'   => 1,
            'updated_by'   => 0,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'active'       => true
        ]);

        // Nhan Ai Admin 1
        \App\UserRole::create([
            'user_id'      => 7,
            'role_id'      => 16,
            'created_by'   => 1,
            'updated_by'   => 0,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'active'       => true
        ]);

        // Nhan Ai Admin 2
        \App\UserRole::create([
            'user_id'      => 8,
            'role_id'      => 16,
            'created_by'   => 1,
            'updated_by'   => 0,
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
            'active'       => true
        ]);

        // Add role Product for All user
        $user_ids = [4, 5, 6, 7, 8];
        foreach ($user_ids as $user_id) {
            \App\UserRole::create([
                'user_id'      => $user_id,
                'role_id'      => 6,
                'created_by'   => 1,
                'updated_by'   => 0,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_date' => date('Y-m-d H:i:s'),
                'active'       => true
            ]);
        }
    }
}
