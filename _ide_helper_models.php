<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\District
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $type Loại
 * @property string $city_code Thành phố
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\District whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\District whereCityCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\District whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\District whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\District whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\District whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\District whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\District whereUpdatedAt($value)
 */
	class District extends \Eloquent {}
}

namespace App{
/**
 * App\Supplier
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $address Địa chỉ
 * @property string $ward_code Phường, xã
 * @property string $district_code Quận, huyện
 * @property string $city_code Tỉnh, thành phố
 * @property string $phone Điện thoại
 * @property string $email Email
 * @property string $fax fax
 * @property string $note Ghi chú
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereAddress($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereCityCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereDistrictCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereFax($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereNote($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier wherePhone($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Supplier whereWardCode($value)
 */
	class Supplier extends \Eloquent {}
}

namespace App{
/**
 * App\City
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $type Loại
 * @property string $nation_code Quốc gia
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\City whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\City whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\City whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\City whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\City whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\City whereNationCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\City whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\City whereUpdatedAt($value)
 */
	class City extends \Eloquent {}
}

namespace App{
/**
 * App\Ward
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $type Loại
 * @property string $district_code Quận
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Ward whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ward whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ward whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ward whereDistrictCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ward whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ward whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ward whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Ward whereUpdatedAt($value)
 */
	class Ward extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $code Mã
 * @property string $fullname Họ tên
 * @property string $username Tài khoản
 * @property string $password Mật khẩu
 * @property string $address Địa chỉ
 * @property string $phone Điện thoại
 * @property string $birthday Ngày sinh
 * @property string $sex Giới tính
 * @property string $email Email
 * @property string $note Ghi chú
 * @property int $created_by Người tạo
 * @property int $updated_by Người sửa
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property bool $active Kích hoạt
 * @property int $position_id Chức vụ
 * @property string $dis_or_sup System, Distributor hay Supplier
 * @property int $dis_or_sup_id Mã System, Distributor hay Supplier
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Query\Builder|\App\User whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereAddress($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereBirthday($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereDisOrSup($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereDisOrSupId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereFullname($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereNote($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User wherePhone($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User wherePositionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereSex($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereUpdatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereUsername($value)
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\UserCardMoney
 *
 * @property int $id
 * @property int $io_center_id Bộ trung tâm
 * @property int $device_id Thiết bị
 * @property int $user_card_id Mã phân người dùng - thẻ
 * @property string $status Trạng thái: GỬI, RÚT, MUA
 * @property float $money Số tiền
 * @property int $count Biến đếm
 * @property int $created_by Người tạo
 * @property int $updated_by Người cập nhật
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property string $vsys_date Ngày bộ trung tâm
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereCount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereDeviceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereIoCenterId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereMoney($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereUpdatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereUserCardId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCardMoney whereVsysDate($value)
 */
	class UserCardMoney extends \Eloquent {}
}

namespace App{
/**
 * App\Unit
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $description Mô tả
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Unit whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Unit whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Unit whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Unit whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Unit whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Unit whereUpdatedAt($value)
 */
	class Unit extends \Eloquent {}
}

namespace App{
/**
 * App\Nation
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Nation whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Nation whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Nation whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Nation whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Nation whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Nation whereUpdatedAt($value)
 */
	class Nation extends \Eloquent {}
}

namespace App{
/**
 * App\GroupRole
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $description Mô tả
 * @property string $icon_name icon cho aside
 * @property int $index vị trí thứ tự
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereIconName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereIndex($value)
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\GroupRole whereUpdatedAt($value)
 */
	class GroupRole extends \Eloquent {}
}

namespace App{
/**
 * App\Collection
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $description Mô tả
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Collection whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Collection whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Collection whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Collection whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Collection whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Collection whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Collection whereUpdatedAt($value)
 */
	class Collection extends \Eloquent {}
}

namespace App{
/**
 * App\ButtonProduct
 *
 * @property int $id
 * @property int $dis_id Distributor
 * @property int $button_id Box
 * @property int $product_id Sản phẩm
 * @property int $total_quantum Số sản phẩm hiện tại
 * @property int $count Biến đếm
 * @property int $created_by Người tạo
 * @property int $updated_by Nguời cập nhật
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property string $vsys_date Ngày bộ trung tâm
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereButtonId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereCount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereDisId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereProductId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereTotalQuantum($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereUpdatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ButtonProduct whereVsysDate($value)
 */
	class ButtonProduct extends \Eloquent {}
}

namespace App{
/**
 * App\HistoryInputOutput
 *
 * @property int $id
 * @property int $dis_id Distributor
 * @property int $io_center_id Bộ trung tâm
 * @property int $button_id Mâm
 * @property int $product_id Sản phẩm
 * @property int $button_product_id Mã phân mâm - sản phẩm
 * @property string $status Trạng thái: NẠP, BÁN
 * @property int $quantum_in Số lượng nạp
 * @property int $quantum_out Số lượng bán
 * @property int $quantum_remain Số lượng còn lại
 * @property int $sum_in Tổng nhập
 * @property int $sum_out Tổng xuất
 * @property float $product_price Giá sản phẩm
 * @property float $total_pay Tổng tiền
 * @property int $count Biến đếm
 * @property int $created_by Người tạo
 * @property int $updated_by Người cập nhật
 * @property int $user_input_id Nhân viên nạp
 * @property int $user_output_id Nhân viên bán
 * @property \Carbon\Carbon $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property string $vsys_date Ngày bộ trung tâm
 * @property bool $isDefault Là dòng dữ liệu mặc định của admin
 * @property int $adjust_by Người điều chỉnh số lượng (nhập xuất cân bằng)
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereAdjustBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereButtonId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereButtonProductId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereCount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereDisId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereIoCenterId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereIsDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereProductId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereProductPrice($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereQuantumIn($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereQuantumOut($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereQuantumRemain($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereSumIn($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereSumOut($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereTotalPay($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereUpdatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereUserInputId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereUserOutputId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\HistoryInputOutput whereVsysDate($value)
 */
	class HistoryInputOutput extends \Eloquent {}
}

namespace App{
/**
 * App\Position
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $description Mô tả
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Position whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Position whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Position whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Position whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Position whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Position whereUpdatedAt($value)
 */
	class Position extends \Eloquent {}
}

namespace App{
/**
 * App\SessionVsys
 *
 * @property int $id
 * @property int $io_center_id
 * @property int $card_id
 * @property int $user_id
 * @property int $count
 * @property string $vsys_date
 * @property string $created_date
 * @property string $updated_date
 * @property bool $active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereCardId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereCount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereIoCenterId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereUpdatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SessionVsys whereVsysDate($value)
 */
	class SessionVsys extends \Eloquent {}
}

namespace App{
/**
 * App\UserRole
 *
 * @property int $id
 * @property int $user_id Nguời dùng
 * @property int $role_id Quyền
 * @property int $created_by Người tạo
 * @property int $updated_by Người cập nhật
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereRoleId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereUpdatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserRole whereUserId($value)
 */
	class UserRole extends \Eloquent {}
}

namespace App{
/**
 * App\Role
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $description Mô tả
 * @property string $router_link router link cho angular
 * @property string $icon_name icon cho aside
 * @property int $index vị trí thứ tự
 * @property int $group_role_id Nhóm quyền
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereGroupRoleId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereIconName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereIndex($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereRouterLink($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Role whereUpdatedAt($value)
 */
	class Role extends \Eloquent {}
}

namespace App{
/**
 * App\Distributor
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $address Địa chỉ
 * @property string $ward_code Phường, xã
 * @property string $district_code Quận, huyện
 * @property string $city_code Tỉnh, thành phố
 * @property string $phone Điện thoại
 * @property string $email Email
 * @property string $fax fax
 * @property string $note Ghi chú
 * @property bool $active Kích hoạt
 * @property int $sup_id Supplier
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereAddress($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereCityCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereDistrictCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereFax($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereNote($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor wherePhone($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereSupId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Distributor whereWardCode($value)
 */
	class Distributor extends \Eloquent {}
}

namespace App{
/**
 * App\IOCenter
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $description Mô tả
 * @property int $count Biến đếm
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property bool $active Kích hoạt
 * @property int $dis_id Distributor
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereCount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereDisId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\IOCenter whereUpdatedDate($value)
 */
	class IOCenter extends \Eloquent {}
}

namespace App{
/**
 * App\Logging
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $count
 * @property string $json
 * @property string $created_by
 * @property string $error_type
 * @property bool $active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereCount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereErrorType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereJson($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereUpdatedAt($value)
 */
	class Logging extends \Eloquent {}
}

namespace App{
/**
 * App\Producer
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $address Địa chỉ
 * @property string $ward_code Phường, xã
 * @property string $district_code Quận, huyện
 * @property string $city_code Tỉnh, thành phố
 * @property string $phone Điện thoại
 * @property string $email Email
 * @property string $fax fax
 * @property string $note Ghi chú
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereAddress($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereCityCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereDistrictCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereFax($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereNote($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer wherePhone($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Producer whereWardCode($value)
 */
	class Producer extends \Eloquent {}
}

namespace App{
/**
 * App\Product
 *
 * @property int $id
 * @property string $code
 * @property string $barcode Mã vạch
 * @property string $name Tên
 * @property string $description Mô tả
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property bool $active Kích hoạt
 * @property int $product_type_id Loại
 * @property int $producer_id Nhà sản xuất
 * @property int $unit_id Đơn vị tính
 * @property bool $is_allowed Được cho phép
 * @property int $created_by Người tạo
 * @property int $updated_by Người cập nhật
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereBarcode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereIsAllowed($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereProducerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereProductTypeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereUnitId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Product whereUpdatedDate($value)
 */
	class Product extends \Eloquent {}
}

namespace App{
/**
 * App\UserCard
 *
 * @property int $id
 * @property int $user_id Người dùng
 * @property int $card_id Thẻ
 * @property float $total_money Tổng tiền
 * @property float $sum_dps Tổng nạp
 * @property float $sum_buy Tổng mua
 * @property int $count Biến đếm
 * @property int $created_by Người tạo
 * @property int $updated_by Người cập nhật
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property string $vsys_date Ngày bộ trung tâm
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereCardId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereCount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereCreatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereSumBuy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereSumDps($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereTotalMoney($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereUpdatedBy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereUpdatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserCard whereVsysDate($value)
 */
	class UserCard extends \Eloquent {}
}

namespace App{
/**
 * App\File
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $extension Phần mở rộng
 * @property string $mime_type MIME Type
 * @property string $path Đường dẫn
 * @property int $size Dung lượng
 * @property string $table_name Tên bảng
 * @property int $table_id Mã bảng
 * @property string $note Ghi chú
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\File whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereExtension($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereMimeType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereNote($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File wherePath($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereSize($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereTableId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereTableName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\File whereUpdatedDate($value)
 */
	class File extends \Eloquent {}
}

namespace App{
/**
 * App\ProductType
 *
 * @property int $id
 * @property string $code Mã
 * @property string $name Tên
 * @property string $description Mô tả
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\ProductType whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductType whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductType whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductType whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductType whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductType whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductType whereUpdatedAt($value)
 */
	class ProductType extends \Eloquent {}
}

namespace App{
/**
 * App\Device
 *
 * @property int $id
 * @property string $collect_code Mã bộ sưu tập
 * @property string $code Mã
 * @property string $name Tên
 * @property string $description Mô tả
 * @property int $quantum_product Số lượng sản phẩm
 * @property bool $active Kích hoạt
 * @property int $collect_id Bộ sưu tập
 * @property int $io_center_id Bộ trung tâm
 * @property int $parent_id Thiết bị cha
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereCollectCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereCollectId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereIoCenterId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereQuantumProduct($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device whereUpdatedAt($value)
 */
	class Device extends \Eloquent {}
}

namespace App{
/**
 * App\ProductPrice
 *
 * @property int $id
 * @property int $product_id Sản phẩm
 * @property float $price_input Giá nhập
 * @property float $price_output Giá bán
 * @property string $created_date Ngày tạo
 * @property string $updated_date Ngày cập nhật
 * @property bool $active Kích hoạt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice whereCreatedDate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice wherePriceInput($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice wherePriceOutput($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice whereProductId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProductPrice whereUpdatedDate($value)
 */
	class ProductPrice extends \Eloquent {}
}

