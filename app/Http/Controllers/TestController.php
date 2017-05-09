<?php

namespace App\Http\Controllers;

use App\Producer;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function getIndex()
    {
        $producer = new Producer();
    }
}
