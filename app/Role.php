<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
 * @mixin \Eloquent
 */
class Role extends Model
{
    //
}
