<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Logging
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $count
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
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Logging whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Logging extends Model
{
    //
}
