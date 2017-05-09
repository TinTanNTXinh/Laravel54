<?php

namespace App\Http\Controllers;

use App\Unit;
use Illuminate\Http\Request;
use League\Flysystem\Exception;
use Route;
use DB;
use App\Traits\UserHelper;
use App\Traits\DBHelper;
use App\Traits\DateTimeHelper;

class UnitController extends Controller
{
    use UserHelper, DBHelper, DateTimeHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;

    private $class_name = Unit::class;

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

        $this->table_name = 'unit';
    }

    /* API METHOD */
    public function getReadAll()
    {
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function getReadOne(Request $request)
    {
        $id  = Route::current()->getParameter('id');
        $one = $this->readOne($id);
        return response()->json($one, 200);
    }

    public function postCreateOne(Request $request)
    {
        $data      = $request->input($this->table_name);
        $validates = $this->validateInput($data);
        if (!$validates['status'])
            return response()->json(['msg' => $validates['errors']], 404);

        if (!$this->createOne($data))
            return response()->json(['msg' => ['Create failed!']], 404);
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 201);
    }

    public function putUpdateOne(Request $request)
    {
        $data      = $request->input($this->table_name);
        $validates = $this->validateInput($data);
        if (!$validates['status'])
            return response()->json(['msg' => $validates['errors']], 404);

        if (!$this->updateOne($data))
            return response()->json(['msg' => ['Update failed!']], 404);
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function patchDeactivateOne(Request $request)
    {
        $id = $request->input('id');
        if (!$this->deactivateOne($id))
            return response()->json(['msg' => 'Deactivate failed!'], 404);
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function deleteDeleteOne(Request $request)
    {
        $id = Route::current()->getParameter('id');
        if (!$this->deleteOne($id))
            return response()->json(['msg' => 'Delete failed!'], 404);
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function getSearchOne()
    {
        $filter        = (array)json_decode($_GET['query']);
        $arr_datas = $this->searchOne($filter);
        return response()->json($arr_datas, 200);
    }

    /* LOGIC METHOD */
    private function readAll()
    {
        $units = Unit::whereActive(true)->get();

        return [
            'units'            => $units,
            'first_day'        => $this->first_day,
            'last_day'         => $this->last_day,
            'today'            => $this->today,
            'placeholder_code' => $this->generateCode($this->class_name, 'UNIT')
        ];
    }

    private function readOne($id)
    {
        $one = Unit::find($id);
        return ['unit' => $one];
    }

    private function createOne($data)
    {
        try {
            DB::beginTransaction();
            $one              = new Unit();
            $one->code        = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'UNIT');
            $one->name        = $data['name'];
            $one->description = $data['description'];
            $one->active      = true;
            if (!$one->save()) {
                DB::rollback();
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollBack();
            return false;
        }
    }

    private function updateOne($data)
    {
        try {
            DB::beginTransaction();
            $one              = Unit::find($data['id']);
            $one->code        = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'UNIT');
            $one->name        = $data['name'];
            $one->description = $data['description'];
            $one->active      = true;
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollBack();
            return false;
        }
    }

    private function deactivateOne($id)
    {
        try {
            DB::beginTransaction();
            $one         = Unit::find($id);
            $one->active = false;
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollBack();
            return false;
        }
    }

    private function deleteOne($id)
    {
        try {
            DB::beginTransaction();
            $one = Unit::find($id);
            if (!$one->delete()) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollBack();
            return false;
        }
    }

    private function searchOne($filter)
    {
        $from_date = $filter['from_date'];
        $to_date   = $filter['to_date'];
        $range     = $filter['range'];
        $unit_id   = $filter['unit_id'];

        $units = Unit::whereActive(true);

        $units = $this->searchFromDateToDate($units, 'units.created_at', $from_date, $to_date);

        $units = $this->searchRangeDate($units, 'units.created_at', $range);

        $units = $this->searchFieldName($units, 'units.id', $unit_id);

        return [
            'units' => $units->get()
        ];
    }

    /** Validation */
    public function validateInput($data)
    {
        if (!$this->validateEmpty($data))
            return ['status' => false, 'errors' => 'Dữ liệu không hợp lệ.'];

        $msgs = $this->validateLogic($data);
        return $msgs;
    }

    public function validateEmpty($data)
    {
        if (!$data['name']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['code'] && $this->checkExistData(Unit::class, 'code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã đơn vị tính đã tồn tại.');

        if ($this->checkExistData(Unit::class, 'name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên đơn vị tính đã tồn tại.');

        return [
            'status' => count($msg_error) > 0 ? false : true,
            'errors' => $msg_error
        ];
    }
}