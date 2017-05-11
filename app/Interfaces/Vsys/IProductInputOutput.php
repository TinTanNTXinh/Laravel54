<?php

namespace App\Interfaces\Vsys;

interface IProductInputOutput
{
    /** API METHOD */
    public function getProductInputOutput();

    /** LOGIC METHOD */
    public function productInputOutput($json);

    /** VALIDATION */
    public function validateJsonProductInputOutput($json);
}