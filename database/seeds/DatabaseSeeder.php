<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $env = env('APP_PROD', false);
        if ($env) {
            ### DEFAULT SEED
            # Nhóm người dùng
            $this->call(GroupRolesTableSeeder::class);
            $this->call(RolesTableSeeder::class);
            $this->call(PositionsTableSeeder::class);
            $this->call(AdminsTableSeeder::class);
            $this->call(AdminRolesTableSeeder::class);

            # Nhóm thiết bị
            $this->call(CollectionsTableSeeder::class);

            # Nhóm sản phẩm
            $this->call(UnitsTableSeeder::class);

            # Nhóm thiết bị - sản phẩm

            # Nhóm thiết bị - người dùng

            # Nhóm khu vực

            # Nhóm tập tin
            $this->call(AdminFilesTableSeeder::class);

            # Nhóm khách hàng
        } else {
            /*
             * ===========================================
             * */

            ### DEVELOP
            # Nhóm người dùng
            $this->call(GroupRolesTableSeeder::class);
            $this->call(RolesTableSeeder::class);
            $this->call(PositionsTableSeeder::class);
            $this->call(AdminsTableSeeder::class);
            $this->call(UsersTableSeeder::class);
            $this->call(AdminRolesTableSeeder::class);
            $this->call(UserRolesTableSeeder::class);

            # Nhóm thiết bị
            $this->call(CollectionsTableSeeder::class);
            $this->call(IOCentersTableSeeder::class);
            $this->call(DevicesTableSeeder::class);

            # Nhóm sản phẩm
            $this->call(ProductTypesTableSeeder::class);
            $this->call(ProductsTableSeeder::class);
            $this->call(ProductPricesTableSeeder::class);
            $this->call(ProducersTableSeeder::class);
            $this->call(UnitsTableSeeder::class);

            # Nhóm thiết bị - sản phẩm
            $this->call(ButtonProductsTableSeeder::class);
            $this->call(HistoryInputOutputsTableSeeder::class);

            # Nhóm thiết bị - người dùng
            $this->call(UserCardsTableSeeder::class);
            $this->call(UserCardMoneysTableSeeder::class);

            # Nhóm khu vực
            $this->call(CitiesTableSeeder::class);
            $this->call(DistrictsTableSeeder::class);
            $this->call(WardsTableSeeder::class);
            $this->call(NationsTableSeeder::class);

            # Nhóm tập tin
            $this->call(AdminFilesTableSeeder::class);
            $this->call(FilesTableSeeder::class);

            # Nhóm khách hàng
            $this->call(DistributorsTableSeeder::class);
            $this->call(SuppliersTableSeeder::class);
        }
    }
}
