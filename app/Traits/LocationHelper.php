<?php

namespace App\Traits;

use App\City;
use App\District;
use App\Ward;

trait LocationHelper
{
    /** ADDRESS HELPER */
    public function getLocation()
    {
        $cities    = City::select('cities.code', 'cities.name')->get();
        $districts = District::select('districts.code', 'districts.name', 'districts.city_code')->get();
        $wards     = Ward::select('wards.code', 'wards.name', 'wards.district_code')->get();

        return [
            'cities'    => $cities,
            'districts' => $districts,
            'wards'     => $wards
        ];
    }
}