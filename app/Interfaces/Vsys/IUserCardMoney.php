<?php

namespace App\Interfaces\Vsys;

interface IUserCardMoney
{
    /** API METHOD */
    public function getUserCardMoney();

    /** LOGIC METHOD */
    public function userCardMoney($json);

    /** VALIDATION */
    public function validateJsonUserCardMoney($json);
}