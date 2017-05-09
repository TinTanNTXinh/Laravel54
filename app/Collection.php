<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    public $fillable = ['name','description', 'active'];
}
