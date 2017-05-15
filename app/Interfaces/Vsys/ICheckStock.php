<?php

namespace App\Interfaces\Vsys;

interface ICheckStock
{
    /** API METHOD */
    public function getCheckStock();

    /** LOGIC METHOD */
    public function checkStock($json);

    /** VALIDATION */
}