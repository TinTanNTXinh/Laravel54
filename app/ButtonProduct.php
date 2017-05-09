<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ButtonProduct
 *
 * @property int $id
 * @property int $dis_id Distributor
 * @property int $button_id Mâm
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
 * @mixin \Eloquent
 */
class ButtonProduct extends Model
{
    //
}
