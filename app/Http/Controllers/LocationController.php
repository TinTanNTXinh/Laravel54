<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\LocationHelper;

class LocationController extends Controller
{
    use LocationHelper;

    public function __construct()
    {

    }

    /* API METHOD */
    public function getReadAll()
    {
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    /* LOGIC METHOD */
    private function readAll()
    {
        $location = $this->getLocation();

        $cities    = $location['cities'];
        $districts = $location['districts'];
        $wards     = $location['wards'];

        return [
            'cities'    => $cities,
            'districts' => $districts,
            'wards'     => $wards
        ];
    }
}
