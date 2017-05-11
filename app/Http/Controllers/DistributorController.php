<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\Distributor;
use App\Supplier;
use League\Flysystem\Exception;
use Route;
use DB;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class DistributorController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;

    private $class_name = Distributor::class;

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

        $this->table_name = 'distributor';
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
        $distributors = Distributor::where('distributors.active', true)
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->leftJoin('cities', 'cities.code', '=', 'distributors.city_code')
            ->leftJoin('districts', 'districts.code', '=', 'distributors.district_code')
            ->leftJoin('wards', 'wards.code', '=', 'distributors.ward_code')
            ->select('distributors.*', 'suppliers.name as supplier_name', 'cities.name as city', 'districts.name as district', 'wards.name as ward')
            ->get();
        $suppliers    = Supplier::whereActive(true)->get();

        return [
            'distributors'     => $distributors,
            'suppliers'        => $suppliers,
            'first_day'        => $this->first_day,
            'last_day'         => $this->last_day,
            'today'            => $this->today,
            'placeholder_code' => $this->generateCode($this->class_name, 'DISTRIBUTOR')
        ];
    }

    public function readOne($id)
    {
        $one = Distributor::find($id);
        return ['distributor' => $one];
    }

    public function createOne($data)
    {
        try {
            DB::beginTransaction();
            $one                = new Distributor();
            $one->code          = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'DISTRIBUTOR');
            $one->name          = $data['name'];
            $one->address       = $data['address'];
            $one->address       = $data['address'];
            $one->city_code     = $data['city_code'];
            $one->district_code = $data['district_code'];
            $one->phone         = $data['phone'];
            $one->email         = $data['email'];
            $one->fax           = $data['fax'];
            $one->note          = $data['note'];
            $one->active        = true;
            $one->sup_id        = $data['sup_id'];
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
            $one                = Distributor::find($data['id']);
            $one->code          = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'DISTRIBUTOR');
            $one->name          = $data['name'];
            $one->address       = $data['address'];
            $one->address       = $data['address'];
            $one->city_code     = $data['city_code'];
            $one->district_code = $data['district_code'];
            $one->phone         = $data['phone'];
            $one->email         = $data['email'];
            $one->fax           = $data['fax'];
            $one->note          = $data['note'];
            $one->active        = true;
            $one->sup_id        = $data['sup_id'];
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
            $one         = Distributor::find($id);
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
            $one = Distributor::find($id);
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
        $supplier_id    = $filter['supplier_id'];
        $distributor_id = $filter['distributor_id'];
        $city_code      = $filter['city_code'];
        $district_code  = $filter['district_code'];
        $ward_code      = $filter['ward_code'];

        $distributors = Distributor::where('distributors.active', true)
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->leftJoin('cities', 'cities.code', '=', 'distributors.city_code')
            ->leftJoin('districts', 'districts.code', '=', 'distributors.district_code')
            ->leftJoin('wards', 'wards.code', '=', 'distributors.ward_code')
            ->select('distributors.*', 'suppliers.name as supplier_name', 'cities.name as city', 'districts.name as district', 'wards.name as ward');

        $distributors = $this->searchFromDateToDate($distributors, 'distributors.created_at', $from_date, $to_date);

        $distributors = $this->searchRangeDate($distributors, 'distributors.created_at', $range);

        $distributors = $this->searchFieldName($distributors, 'suppliers.id', $supplier_id);
        $distributors = $this->searchFieldName($distributors, 'distributors.id', $distributor_id);
        $distributors = $this->searchFieldName($distributors, 'cities.code', $city_code);
        $distributors = $this->searchFieldName($distributors, 'districts.code', $district_code);
        $distributors = $this->searchFieldName($distributors, 'wards.code', $ward_code);

        return [
            'distributors' => $distributors->get()
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
        if (!$data['name']) return false;
        if (!$data['sup_id']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['code'] && $this->checkExistData(Distributor::class, 'code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã đại lý đã tồn tại.');

        if ($this->checkExistData(Distributor::class, 'name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên đại lý đã tồn tại.');

        if($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            array_push($msg_error, 'Địa chỉ email không hợp lệ.');
        }

        return [
            'status' => count($msg_error) > 0 ? false : true,
            'errors' => $msg_error
        ];
    }

    /** My Function */

}
