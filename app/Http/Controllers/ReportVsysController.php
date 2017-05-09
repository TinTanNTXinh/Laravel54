<?php

namespace App\Http\Controllers;

use App\Device;
use App\Distributor;
use App\User;
use Illuminate\Http\Request;
use App\UserCard;
use App\UserCardMoney;
use DB;
use Route;
use Carbon\Carbon;
use App\Traits\UserHelper;
use App\Traits\DBHelper;
use App\Traits\FileHelper;

class ReportVsysController extends Controller
{
    use UserHelper, DBHelper, FileHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;

    public function __construct()
    {
        $format_date_time  = $this->getFormatDateTime();
        $this->format_date = $format_date_time['date'];
        $this->format_time = $format_date_time['time'];

        $current_month   = $this->getCurrentMonth();
        $this->first_day = $current_month['first_day'];
        $this->last_day  = $current_month['last_day'];
        $this->today     = $current_month['today'];

        $jwt_data = $this->getCurrentUser();
        if ($jwt_data['status']) {
            $user_data = $this->getInfoCurrentUser($jwt_data['user']);
            if ($user_data['status'])
                $this->user = $user_data['user'];
        }
    }

    /** API METHOD */
    public function getReadAll()
    {
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function getReportBalanceDetail()
    {
        $id  = Route::current()->parameter('id');
        $one = $this->reportBalanceDetail($id);
        return response()->json($one, 200);
    }

    public function getReportBalanceBySearch()
    {
        $filter          = (array)json_decode($_GET['query']);
        $report_balances = $this->reportBalanceBySearch($filter);
        return response()->json($report_balances, 200);
    }

    public function getReportDpsBySearch()
    {
        $filter      = (array)json_decode($_GET['query']);
        $report_dpss = $this->reportDpsBySearch($filter);
        return response()->json($report_dpss, 200);
    }

    /** LOGIC METHOD */
    public function readAll()
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                break;
            default:
                return null;
                break;
        }

        $cards        = Device::whereActive(true)->where('collect_code', 'Card')->get();
        $cdms         = Device::whereActive(true)->where('collect_code', 'CDM')->get();
        $distributors = Distributor::whereActive(true)->get();
        $visitors     = User::whereActive(true)->where('position_id', 6)->get();

        $response                    = [
            'cards'        => $cards,
            'cdms'         => $cdms,
            'distributors' => $distributors,
            'visitors'     => $visitors,
            'first_day'    => $this->first_day,
            'last_day'     => $this->last_day,
            'today'        => $this->today
        ];
        $response['report_dpss']     = [];
        $response['report_balances'] = [];
        return $response;
    }

    // DPS
    public function reportDps()
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                $report_dpss = UserCardMoney::where([['user_card_moneys.active', true], ['user_card_moneys.status', 'DPS'], ['user_card_moneys.io_center_id', '<>', 0], ['visitors.dis_or_sup', 'dis']])
                    ->leftJoin('user_cards', 'user_cards.id', '=', 'user_card_moneys.user_card_id')
                    ->leftJoin('users as visitors', 'visitors.id', '=', 'user_cards.user_id')
                    ->leftJoin('distributors', 'distributors.id', '=', 'visitors.dis_or_sup_id')
                    ->leftJoin('devices as cdms', 'cdms.id', '=', 'user_card_moneys.device_id')
                    ->leftJoin('devices as cards', 'cards.id', '=', 'user_cards.card_id')
                    ->select('user_card_moneys.money'
                        , 'visitors.id as visitor_id', 'visitors.phone as visitor_phone'
                        , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                        , 'cards.code as card_code', 'cards.name as card_name'
                        , 'cdms.id as cdm_id', 'cdms.code as cdm_code', 'cdms.name as cdm_name'
                        , DB::raw($this->getWithCurrencyFormat('user_card_moneys.money', 'fc_money'))
                        , DB::raw($this->getWithDateFormat('user_card_moneys.created_date', 'date_dps'))
                        , DB::raw($this->getWithTimeFormat('user_card_moneys.created_date', 'time_dps')))
                    ->orderBy('user_card_moneys.created_date', 'desc');
                break;
            default:
                return null;
                break;
        }
        return $report_dpss;
    }

    public function reportDpsBySearch($filter)
    {
        $reports = $this->reportDps();

        $from_date      = $filter['from_date'];
        $to_date        = $filter['to_date'];
        $range          = $filter['range'];
        $card_id        = $filter['card_id'];
        $cdm_id         = $filter['cdm_id'];
        $distributor_id = $filter['distributor_id'];
        $visitor_id     = $filter['visitor_id'];

        $reports = $this->searchFromDateToDate($reports, 'user_card_moneys.created_date', $from_date, $to_date);
        $reports = $this->searchRangeDate($reports, 'user_card_moneys.created_date', $range);

        $reports = $this->searchFieldName($reports, 'user_cards.card_id', $card_id);
        $reports = $this->searchFieldName($reports, 'cdms.id', $cdm_id);
        $reports = $this->searchFieldName($reports, 'distributors.id', $distributor_id);
        $reports = $this->searchFieldName($reports, 'visitors.id', $visitor_id);

        return $this->downloadFile($this->changeColumnName($reports->get(), 'dps'));
        /*
        return [
            'report_dpss' => $reports->get()
        ];
        */
    }

    // BUY
    public function reportBuy()
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                $report_buy = UserCardMoney::where([['user_card_moneys.active', true], ['user_card_moneys.status', 'BUY'], ['user_card_moneys.io_center_id', '<>', 0]])
                    ->leftJoin('user_cards', 'user_cards.id', '=', 'user_card_moneys.user_card_id')
                    ->leftJoin('users', 'users.id', '=', 'user_cards.user_id')
                    ->leftJoin('devices', 'devices.id', '=', 'user_card_moneys.device_id')
                    ->leftJoin('devices as cards', 'cards.id', '=', 'user_cards.card_id')
                    ->select('user_card_moneys.money'
                        , 'users.fullname as user_fullname'
                        , 'cards.code as card_code', 'cards.name as card_name'
                        , 'devices.code as device_code', 'devices.name as device_name'
                        , DB::raw($this->getWithCurrencyFormat('user_card_moneys.money', 'fc_money'))
                        , DB::raw($this->getWithDateFormat('user_card_moneys.created_date', 'date_buy'))
                        , DB::raw($this->getWithTimeFormat('user_card_moneys.created_date', 'time_buy')))
                    ->orderBy('user_card_moneys.created_date', 'desc')
                    ->get();
                break;
            default:
                return null;
                break;
        }
        return $report_buy;
    }

    // Balance
    public function reportBalance()
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                $user_cards = UserCard::where([['user_cards.active', true], ['visitors.dis_or_sup', 'dis']])
                    ->leftJoin('users as visitors', 'visitors.id', '=', 'user_cards.user_id')
                    ->leftJoin('devices as cards', 'cards.id', '=', 'user_cards.card_id')
                    ->leftJoin('distributors', 'distributors.id', '=', 'visitors.dis_or_sup_id')
                    ->select('user_cards.id'
                        , DB::raw($this->getWithCurrencyFormat('user_cards.total_money', 'fc_total_money'))
                        , DB::raw($this->getWithCurrencyFormat('user_cards.sum_dps', 'fc_sum_dps'))
                        , DB::raw($this->getWithCurrencyFormat('user_cards.sum_buy', 'fc_sum_buy'))
                        , 'visitors.id as visitor_id', 'visitors.phone as visitor_phone'
                        , 'cards.id as card_id', 'cards.code as card_code', 'cards.name as card_name'
                        , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                        , DB::raw($this->getWithDateTimeFormat('user_cards.updated_date', 'last_updated')))
                    ->orderBy('user_cards.updated_date', 'desc');
                break;
            default:
                return null;
                break;
        }
        return $user_cards;
    }

    public function reportBalanceBySearch($filter)
    {
        $reports = $this->reportBalance();

        $distributor_id = $filter['distributor_id'];
        $visitor_id     = $filter['visitor_id'];
        $card_id        = $filter['card_id'];

        $reports = $this->searchFieldName($reports, 'cards.id', $card_id);
        $reports = $this->searchFieldName($reports, 'distributors.id', $distributor_id);
        $reports = $this->searchFieldName($reports, 'visitors.id', $visitor_id);

        return [
            'report_balances' => $reports->get()
        ];
    }

    public function reportBalanceDetail($id)
    {
        $details = UserCardMoney::where('user_card_moneys.active', true)
            ->where('user_card_moneys.user_card_id', $id)
            ->leftJoin('devices', 'devices.id', '=', 'user_card_moneys.device_id')
            ->select('user_card_moneys.id', 'user_card_moneys.status'
                , DB::raw('CASE WHEN user_card_moneys.status = \'DPS\' THEN \'Gửi\' 
                                    WHEN user_card_moneys.status = \'WDR\' THEN \'Rút\' 
                                    WHEN user_card_moneys.status =\'BUY\' THEN \'Mua\' 
                                    ELSE \'\' END as status_vi')
                , DB::raw($this->getWithCurrencyFormat('user_card_moneys.money', 'fc_money'))
                , 'devices.code as cdm_code', 'devices.name as cdm_name'
                , DB::raw($this->getWithDateFormat('user_card_moneys.created_date', 'created_date'))
                , DB::raw($this->getWithTimeFormat('user_card_moneys.created_date', 'created_time')))
            ->orderBy('user_card_moneys.created_date', 'desc')
            ->get();
        return ['report_balance_details' => $details];
    }

    // Other
    private function changeColumnName($data, $mode)
    {
        switch ($mode) {
            case 'dps':
                foreach ($data as $key => $item) {
                    $data[$key]['Đại lý'] = $data[$key]['distributor_name'];
                    unset($data[$key]['distributor_name']);
                    $data[$key]['SĐT'] = $data[$key]['visitor_phone'];
                    unset($data[$key]['visitor_phone']);
                    $data[$key]['Mã thẻ'] = $data[$key]['card_code'];
                    unset($data[$key]['card_code']);
                    $data[$key]['Máy nạp'] = $data[$key]['cdm_name'];
                    unset($data[$key]['cdm_name']);
                    $data[$key]['Ngày'] = $data[$key]['date_dps'];
                    unset($data[$key]['date_dps']);
                    $data[$key]['Giờ'] = $data[$key]['time_dps'];
                    unset($data[$key]['time_dps']);
                    $data[$key]['Số tiền'] = $data[$key]['money'];
                    unset($data[$key]['money']);

                    unset($data[$key]['fc_money']);
                    unset($data[$key]['visitor_id']);
                    unset($data[$key]['distributor_id']);
                    unset($data[$key]['card_name']);
                    unset($data[$key]['cdm_id']);
                    unset($data[$key]['cdm_code']);
                }
                break;
            case 'balance':
                break;
            default:
                break;
        }
        return $data;
    }
}
