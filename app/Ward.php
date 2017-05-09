<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
 * @mixin \Eloquent
 */
class Ward extends Model
{
    //
}
