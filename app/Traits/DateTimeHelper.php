<?php

namespace App\Traits;

trait DateTimeHelper
{
    /** TIME HELPER */
    public function getCurrentMonth($pattern = 'd-m-Y')
    {
        $first_day_UTS = mktime(0, 0, 0, date("m"), 1, date("Y"));
        $last_day_UTS  = mktime(0, 0, 0, date("m"), date('t'), date("Y"));
        return [
            'first_day' => date($pattern, $first_day_UTS),
            'last_day'  => date($pattern, $last_day_UTS),
            'today'     => date($pattern)
        ];
    }

    public function getFirstDayLastDay($month, $year, $pattern = 'd-m-Y')
    {
        $first_day_UTS = mktime(0, 0, 0, $month, 1, $year);
        $last_day_UTS  = mktime(0, 0, 0, $month, date('t', strtotime($year . '-' . $month . '-' . '01')), $year);
        return [
            'first_day' => date($pattern, $first_day_UTS),
            'last_day'  => date($pattern, $last_day_UTS),
        ];
    }

    public function getYesterday($pattern = 'd-m-Y')
    {
        $yesterday = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
        return [
            'yesterday' => date($pattern, $yesterday)
        ];
    }

    public function getFormatDateTime()
    {
        return [
            'date' => '%d/%m/%Y',
            'time' => '%H:%i'
        ];
    }

    public function getFormatDateTimeVsys()
    {
        return [
            'datetime' => 'd/m/y H:i:s',
        ];
    }

    public function addTimeForDate($date, $mode)
    {
        // mark: yyyy/mm/dd hh:ii:ss
        switch ($mode) {
            case 'min':
                return substr($date, 0, 10) . ' 00:00:00';
                break;
            case 'max':
                return substr($date, 0, 10) . ' 23:59:59';
                break;
            case 'none':
                return substr($date, 0, 10);
                break;
            default:
                return $date;
                break;
        }
    }
}