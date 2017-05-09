<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
 * @mixin \Eloquent
 */
class SessionVsys extends Model
{
    //
}
