<?php

use Illuminate\Database\Seeder;
use App\Traits\DBHelper;

class RolesTableSeeder extends Seeder
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
            'Dashboard',
            'IOCenter',
            'Collection',
            'Device',
            'ProductType',
            'Product',
            'Supplier',
            'Distributor',
            'User',
            'Producer',
            'Unit',
            'ButtonProduct',
            'UserCard',
            'Position',
            'ReportSupplier',
            'ReportDistributor',
            'ReportStaffInput',
            'ReportStaffOutput',
            'ReportVsys',
            'ReportLogging'
        ];

        $array_group_id = [
            1,
            2,
            2,
            2,
            2,
            2,
            2,
            2,
            2,
            2,
            2,
            3,
            3,
            2,
            4,
            4,
            4,
            4,
            4,
            4
        ];

        $array_index = [
            1,  //Dashboard

            10, //IOCenter
            11, //Collection
            12, //Device
            7,  //ProductType
            9,  //Product
            2,  //Supplier
            3,  //Distributor
            5,  //User
            6,  //Producer
            8,  //Unit
            13, //ButtonProduct
            14, //UserCard
            4,  //Position

            15, //ReportSupplier
            16, //ReportDistributor
            17, //ReportStaffInput
            18, //ReportStaffOutput
            19, //ReportVsys
            20, //ReportLogging
        ];

        $array_description = [
            'Trang chủ',
            'Bộ xử lý trung tâm',
            'Loại thiết bị',
            'Thiết bị',
            'Loại sản phẩm',
            'Sản phẩm',
            'Khách hàng',
            'Đại lý',
            'Người dùng',
            'Nhà cung cấp sản phẩm',
            'Đơn vị tính',
            'Box - Sản phẩm',
            'Người dùng - Thẻ',
            'Chức vụ',
            'Báo cáo khách hàng',
            'Báo cáo đại lý',
            'Báo cáo nhân viên nhập',
            'Báo cáo nhân viên xuất',
            'Báo cáo tiền',
            'Báo cáo lỗi',
        ];

        $array_icon_name = [
            'glyphicon-stats icon text-primary-lter',
            'glyphicon-cog icon text-danger-lter',
            'glyphicon-wrench icon text-danger-lter',
            'glyphicon-phone icon text-danger-lter',
            'glyphicon-th-large icon text-info-lter',
            'glyphicon-book icon text-info-lter',
            'glyphicon-plane icon text-success-lter',
            'glyphicon-shopping-cart icon text-success-lter',
            'glyphicon-user icon text-success-lter',
            'glyphicon-home icon text-success-lter',
            'glyphicon-tint icon text-info-lter',
            'glyphicon-inbox icon text-warning-lter',
            'glyphicon-credit-card icon text-warning-lter',
            'glyphicon-eye-close icon text-info-lter',
            'glyphicon-folder-open icon text-info-lter',
            'glyphicon-folder-open icon text-info-lter',
            'glyphicon-user icon text-info-lter',
            'glyphicon-user icon text-info-lter',
            'glyphicon-folder-open icon text-info-lter',
            'glyphicon-exclamation-sign icon text-danger-lter',
        ];

        foreach($array_name as $key => $name){
            $router_link = $array_name[$key] == 'IOCenter' ? 'IoCenter' : $array_name[$key];
            \App\Role::create([
                'code'        => $this->generateCode(\App\Role::class, 'ROLE'),
                'name'        => $array_name[$key],
                'description' => $array_description[$key],
                'router_link' => '/' . strtolower(preg_replace('/\B([A-Z])/', '-$1', $router_link)) . 's',
                'icon_name'   => $array_icon_name[$key],
                'index'       => $array_index[$key],
                'group_role_id' => $array_group_id[$key],
                'active'      => true
            ]);
        }
    }
}
