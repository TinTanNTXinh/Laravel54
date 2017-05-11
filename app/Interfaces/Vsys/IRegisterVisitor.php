<?php

namespace App\Interfaces\Vsys;

interface IRegisterVisitor
{
    /** API METHOD */
    public function getRegisterVisitor();

    /** LOGIC METHOD */
    public function registerVisitor($json);

    /** VALIDATION */
    public function validateJsonRegVisitor($json);
}