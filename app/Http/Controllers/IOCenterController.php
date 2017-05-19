<?php

namespace App\Http\Controllers;

use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\Distributor;
use App\IOCenter;
use App\Supplier;
use Illuminate\Http\Request;
use League\Flysystem\Exception;
use Route;
use DB;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class IOCenterController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;
    private $skeleton;

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

        $this->table_name = 'io_center';
        $this->skeleton = IOCenter::where('io_centers.active', true)
            ->leftJoin('distributors', 'distributors.id', '=', 'io_centers.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->select('io_centers.*'
                , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name');
    }

    /** API METHOD */
    public function getReadAll()
    {
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function getReadOne()
    {
        $id  = Route::current()->parameter('id');
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
        $id = Route::current()->parameter('id');
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

    /** LOGIC METHOD */
    public function readAll()
    {
        $io_centers   = $this->skeleton->get();
        $distributors = Distributor::whereActive(true)->get();
        $suppliers    = Supplier::whereActive(true)->get();

        return [
            'io_centers'   => $io_centers,
            'distributors' => $distributors,
            'suppliers'    => $suppliers,
            'first_day'    => $this->first_day,
            'last_day'     => $this->last_day,
            'today'        => $this->today
        ];
    }

    public function readOne($id)
    {
        $one = IOCenter::find($id);
        return [$this->table_name => $one];
    }

    public function createOne($data)
    {
        try {
            DB::beginTransaction();
            $one               = new IOCenter();
            $one->code         = $data['code'];
            $one->name         = $data['name'];
            $one->description  = null;
            $one->created_date = date('Y-m-d H:i:s');
            $one->updated_date = null;
            $one->active       = true;
            $one->dis_id       = $data['dis_id'];
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

    public function updateOne($data)
    {
        try {
            DB::beginTransaction();
            $one               = IOCenter::find($data['id']);
            $one->code         = $data['code'];
            $one->name         = $data['name'];
            $one->description  = null;
            $one->updated_date = date('Y-m-d H:i:s');
            $one->active       = true;
            $one->dis_id       = $data['dis_id'];
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

    public function deactivateOne($id)
    {
        try {
            DB::beginTransaction();
            $one         = IOCenter::find($id);
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

    public function deleteOne($id)
    {
        try {
            DB::beginTransaction();
            $one = IOCenter::find($id);
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

    public function searchOne($filter)
    {
        $from_date      = $filter['from_date'];
        $to_date        = $filter['to_date'];
        $range          = $filter['range'];
        $io_center_id   = $filter['io_center_id'];
        $supplier_id    = $filter['supplier_id'];
        $distributor_id = $filter['distributor_id'];

        $io_centers = $this->skeleton;

        $io_centers = $this->searchFromDateToDate($io_centers, 'io_centers.created_date', $from_date, $to_date);

        $io_centers = $this->searchRangeDate($io_centers, 'io_centers.created_date', $range);

        $io_centers = $this->searchFieldName($io_centers, 'io_centers.id', $io_center_id);

        $io_centers = $this->searchFieldName($io_centers, 'suppliers.id', $supplier_id);

        $io_centers = $this->searchFieldName($io_centers, 'distributors.id', $distributor_id);

        return [
            'io_centers' => $io_centers->get()
        ];
    }

    /** VALIDATION */
    public function validateInput($data)
    {
        if (!$this->validateEmpty($data))
            return ['status' => false, 'errors' => 'Dữ liệu không hợp lệ.'];

        $msgs = $this->validateLogic($data);
        return $msgs;
    }

    public function validateEmpty($data)
    {
        if (!$data['code']) return false;
        if (!$data['name']) return false;
        if (!$data['dis_id']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($this->checkExistData(IOCenter::class, 'code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã bộ trung tâm đã tồn tại.');

        if ($this->checkExistData(IOCenter::class, 'name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên bộ trung tâm đã tồn tại.');

        return [
            'status' => count($msg_error) > 0 ? false : true,
            'errors' => $msg_error
        ];
    }

    /** My Function */
    public function getReadAllWithPage()
    {
        $page      = $id = Route::current()->parameter('page');
        $pageSize  = $id = Route::current()->parameter('pageSize');
        $arr_datas = $this->readAllWithPage($page, $pageSize);
        return response()->json($arr_datas, 200);
    }

    public function readAllWithPage($page, $pageSize)
    {
        $io_centers = IOCenter::where('io_centers.active', true)
            ->leftJoin('distributors', 'distributors.id', '=', 'io_centers.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->select('io_centers.*', 'distributors.id as distributor_id', 'distributors.name as distributor_name', 'suppliers.id as supplier_id', 'suppliers.name as supplier_name')
            ->skip($page)
            ->take($pageSize)
            ->get();

        $total_records = IOCenter::whereActive(true)->count();

        return [
            'io_centers'    => $io_centers,
            'total_records' => $total_records
        ];
    }

}
