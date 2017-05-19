<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\Supplier;
use League\Flysystem\Exception;
use Route;
use DB;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class SupplierController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;
    private $skeleton;

    private $class_name = Supplier::class;

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

        $this->table_name = 'supplier';

        $this->skeleton = Supplier::where('suppliers.active', true)
            ->leftJoin('cities', 'cities.code', '=', 'suppliers.city_code')
            ->leftJoin('districts', 'districts.code', '=', 'suppliers.district_code')
            ->leftJoin('wards', 'wards.code', '=', 'suppliers.ward_code')
            ->select('suppliers.*', 'cities.name as city', 'districts.name as district', 'wards.name as ward');
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
        $suppliers = $this->skeleton->get();

        return [
            'suppliers'        => $suppliers,
            'first_day'        => $this->first_day,
            'last_day'         => $this->last_day,
            'today'            => $this->today,
            'placeholder_code' => $this->generateCode($this->class_name, 'SUPPLIER')
        ];
    }

    public function readOne($id)
    {
        $one = Supplier::find($id);
        return ['supplier' => $one];
    }

    public function createOne($data)
    {
        try {
            DB::beginTransaction();
            $one                = new Supplier();
            $one->code          = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'SUPPLIER');
            $one->name          = $data['name'];
            $one->address       = $data['address'];
            $one->city_code     = $data['city_code'];
            $one->district_code = $data['district_code'];
            $one->ward_code     = $data['ward_code'];
            $one->phone         = $data['phone'];
            $one->email         = $data['email'];
            $one->fax           = $data['fax'];
            $one->note          = $data['note'];
            $one->active        = true;
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
            $one                = Supplier::find($data['id']);
            $one->code          = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'SUPPLIER');
            $one->name          = $data['name'];
            $one->address       = $data['address'];
            $one->city_code     = $data['city_code'];
            $one->district_code = $data['district_code'];
            $one->ward_code     = $data['ward_code'];
            $one->phone         = $data['phone'];
            $one->email         = $data['email'];
            $one->fax           = $data['fax'];
            $one->note          = $data['note'];
            $one->active        = true;
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
            $one         = Supplier::find($id);
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
            $one = Supplier::find($id);
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
        $from_date     = $filter['from_date'];
        $to_date       = $filter['to_date'];
        $range         = $filter['range'];
        $supplier_id   = $filter['supplier_id'];
        $city_code     = $filter['city_code'];
        $district_code = $filter['district_code'];
        $ward_code     = $filter['ward_code'];

        $suppliers = $this->skeleton;

        $suppliers = $this->searchFromDateToDate($suppliers, 'suppliers.created_at', $from_date, $to_date);

        $suppliers = $this->searchRangeDate($suppliers, 'suppliers.created_at', $range);

        $suppliers = $this->searchFieldName($suppliers, 'suppliers.id', $supplier_id);
        $suppliers = $this->searchFieldName($suppliers, 'cities.code', $city_code);
        $suppliers = $this->searchFieldName($suppliers, 'districts.code', $district_code);
        $suppliers = $this->searchFieldName($suppliers, 'wards.code', $ward_code);

        return [
            'suppliers' => $suppliers->get()
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
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['code'] && $this->checkExistData(Supplier::class, 'code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã khách hàng đã tồn tại.');

        if ($this->checkExistData(Supplier::class, 'name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên khách hàng đã tồn tại.');

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
