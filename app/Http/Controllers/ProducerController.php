<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\Producer;
use League\Flysystem\Exception;
use Route;
use DB;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class ProducerController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;
    private $skeleton;

    private $class_name = Producer::class;

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

        $this->table_name = 'producer';
        $this->skeleton = Producer::whereActive(true);
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
        $producers = $this->skeleton->get();

        return [
            'producers'        => $producers,
            'first_day'        => $this->first_day,
            'last_day'         => $this->last_day,
            'today'            => $this->today,
            'placeholder_code' => $this->generateCode($this->class_name, 'PRODUCER')
        ];
    }

    public function readOne($id)
    {
        $one = Producer::find($id);
        return [$this->table_name => $one];
    }

    public function createOne($data)
    {
        try {
            DB::beginTransaction();
            $one          = new Producer();
            $one->code    = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'PRODUCER');
            $one->name    = $data['name'];
            $one->address = $data['address'];
            $one->phone   = $data['phone'];
            $one->email   = $data['email'];
            $one->fax     = $data['fax'];
            $one->note    = $data['note'];
            $one->active  = true;
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
            $one          = Producer::find($data['id']);
            $one->code    = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'PRODUCER');
            $one->name    = $data['name'];
            $one->address = $data['address'];
            $one->phone   = $data['phone'];
            $one->email   = $data['email'];
            $one->fax     = $data['fax'];
            $one->note    = $data['note'];
            $one->active  = true;
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
            $one         = Producer::find($id);
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
            $one = Producer::find($id);
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
        $from_date   = $filter['from_date'];
        $to_date     = $filter['to_date'];
        $range       = $filter['range'];
        $producer_id = $filter['producer_id'];

        $producers = $this->skeleton;

        $producers = $this->searchFromDateToDate($producers, 'producers.created_at', $from_date, $to_date);

        $producers = $this->searchRangeDate($producers, 'producers.created_at', $range);

        $producers = $this->searchFieldName($producers, 'producers.id', $producer_id);

        return [
            'producers' => $producers->get()
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

        if ($data['code'] && $this->checkExistData(Producer::class, 'code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã nhà cung cấp sản phẩm đã tồn tại.');

        if ($this->checkExistData(Producer::class, 'name', $data['name'], $skip_id))
            array_push($msg_error, 'Tên nhà cung cấp sản phẩm đã tồn tại.');

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
