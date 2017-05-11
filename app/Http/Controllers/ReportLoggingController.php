<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\Logging;
use App\Traits\UserHelper;
use App\Traits\DBHelper;
use DB;

class ReportLoggingController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

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

    public function getReadOne()
    {
        // TODO: Implement getReadOne() method.
    }

    public function postCreateOne(Request $request)
    {
        // TODO: Implement postCreateOne() method.
    }

    public function putUpdateOne(Request $request)
    {
        // TODO: Implement putUpdateOne() method.
    }

    public function patchDeactivateOne(Request $request)
    {
        // TODO: Implement patchDeactivateOne() method.
    }

    public function deleteDeleteOne(Request $request)
    {
        // TODO: Implement deleteDeleteOne() method.
    }

    public function getSearchOne()
    {
        $filter        = (array)json_decode($_GET['query']);
        $arr_datas = $this->searchOne($filter);
        return response()->json($arr_datas, 200);
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

        $response = [
            'first_day'     => $this->first_day,
            'last_day'      => $this->last_day,
            'today'         => $this->today
        ];
        return $response;
    }

    public function readOne($id)
    {
        // TODO: Implement readOne() method.
    }

    public function createOne($data)
    {
        // TODO: Implement createOne() method.
    }

    public function updateOne($data)
    {
        // TODO: Implement updateOne() method.
    }

    public function deactivateOne($id)
    {
        // TODO: Implement deactivateOne() method.
    }

    public function deleteOne($id)
    {
        // TODO: Implement deleteOne() method.
    }

    public function searchOne($filter)
    {
        $reports = Logging::whereActive(true)
            ->select('loggings.id', 'loggings.name', 'loggings.description', 'loggings.count'
                , 'loggings.created_by', 'loggings.error_type'
                , DB::raw($this->getWithDateFormat('loggings.created_at', 'created_date'))
                , DB::raw($this->getWithTimeFormat('loggings.created_at', 'created_time'))
            )
            ->orderBy('loggings.id', 'desc');

        $from_date       = $filter['from_date'];
        $to_date         = $filter['to_date'];
        $range           = $filter['range'];

        $reports = $this->searchFromDateToDate($reports, 'loggings.created_at', $from_date, $to_date);
        $reports = $this->searchRangeDate($reports, 'loggings.created_at', $range);

        return [
            'report_loggings' => $reports->get()
        ];
    }

    /** VALIDATION */
    public function validateInput($data)
    {
        // TODO: Implement validateInput() method.
    }

    public function validateEmpty($data)
    {
        // TODO: Implement validateEmpty() method.
    }

    public function validateLogic($data)
    {
        // TODO: Implement validateLogic() method.
    }

    /** My Function */

}