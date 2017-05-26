<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\Device;
use App\Collection;
use App\IOCenter;
use League\Flysystem\Exception;
use Route;
use DB;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class DeviceController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;
    private $skeleton;

    private $class_name = Device::class;

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

        $this->table_name = 'device';

        $this->skeleton = Device::where('devices.active', true)
            ->leftJoin('collections', 'collections.id', '=', 'devices.collect_id')
            ->leftJoin('io_centers', 'io_centers.id', '=', 'devices.io_center_id')
            ->leftJoin('devices as parents', 'parents.id', '=', 'devices.parent_id')
            ->select('devices.*', 'parents.name as parent_name'
                , 'collections.code as collect_code', 'collections.name as collect_name'
                , 'io_centers.code as io_center_code', 'io_centers.name as io_center_name');
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
        $devices     = $this->skeleton->get();
        $collections = Collection::where('collections.active', true)->get();
        $io_centers  = IOCenter::where('io_centers.active', true)->get();
        return [
            'devices'     => $devices,
            'collections' => $collections,
            'io_centers'  => $io_centers,
            'first_day'   => $this->first_day,
            'last_day'    => $this->last_day,
            'today'       => $this->today
        ];
    }

    public function readOne($id)
    {
        $device = Device::find($id);
        return [$this->table_name => $device];
    }

    public function createOne($data)
    {
        try {
            DB::beginTransaction();

            $collection = Collection::whereCode($data['collect_code'])->first();

            $one                  = new Device();
            $one->collect_code    = $data['collect_code'];
            $one->code            = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, strtoupper($one->collect_code));
            $one->name            = $data['name'];
            $one->description     = null;
            $one->quantum_product = $data['collect_code'] == 'Tray' ? $data['quantum_product'] : 0;
            $one->active          = true;
            $one->collect_id      = $collection->id;
            $one->io_center_id    = $data['io_center_id'];
            $one->parent_id       = $data['parent_id'];
            if (!$one->save()) {
                DB::rollback();
                return false;
            }

            if ($data['quantum_tray'] > 0 && $data['collect_code'] == 'Cabinet') {
                $collection = Collection::whereCode('Tray')->first();
                for ($i = 1; $i <= $data['quantum_tray']; $i++) {
                    $two                  = new Device();
                    $two->collect_code    = 'Tray';
                    $two->code            = $i;
                    $two->name            = 'Box ' . $i;
                    $two->description     = null;
                    $two->quantum_product = $data['quantum_product'];
                    $two->active          = true;
                    $two->collect_id      = $collection->id;
                    $two->io_center_id    = $data['io_center_id'];
                    $two->parent_id       = $one->id;
                    if (!$two->save()) {
                        DB::rollback();
                        return false;
                    }
                }
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
            $collection = Collection::whereCode($data['collect_code'])->first();

            $one                  = Device::find($data['id']);
            $one->collect_code    = $data['collect_code'];
            $one->code            = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, strtoupper($data['collect_code']));
            $one->name            = $data['name'];
            $one->description     = null;
            $one->quantum_product = $data['quantum_product'];
            $one->active          = true;
            $one->collect_id      = $collection->id;
            $one->io_center_id    = $data['io_center_id'];
            $one->parent_id       = $data['parent_id'];
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            if ($data['quantum_tray'] > 0 && $data['collect_code'] == 'Cabinet') {
                $collection = Collection::whereCode('Tray')->first();
                for ($i = 1; $i <= $data['quantum_tray']; $i++) {
                    $two                  = new Device();
                    $two->collect_code    = 'Tray';
                    $two->code            = $i;
                    $two->name            = 'Box ' . $i;
                    $two->description     = null;
                    $two->quantum_product = $data['quantum_product'];
                    $two->active          = true;
                    $two->collect_id      = $collection->id;
                    $two->io_center_id    = $data['io_center_id'];
                    $two->parent_id       = $one->id;
                    if (!$two->save()) {
                        DB::rollback();
                        return false;
                    }
                }
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
            $one         = Device::find($id);
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
            $one = Device::find($id);
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
        $from_date    = $filter['from_date'];
        $to_date      = $filter['to_date'];
        $range        = $filter['range'];
        $io_center_id = $filter['io_center_id'];
        $device_id    = $filter['device_id'];
        $collect_code = $filter['collect_code'];
        $parent_id    = $filter['parent_id'];

        $devices = $this->skeleton;

        $devices = $this->searchFromDateToDate($devices, 'devices.created_date', $from_date, $to_date);

        $devices = $this->searchRangeDate($devices, 'devices.created_date', $range);

        $devices = $this->searchFieldName($devices, 'io_centers.id', $io_center_id);
        $devices = $this->searchFieldName($devices, 'devices.id', $device_id);
        $devices = $this->searchFieldName($devices, 'devices.collect_code', $collect_code);
        $devices = $this->searchFieldName($devices, 'devices.parent_id', $parent_id);

        return [
            'devices' => $devices->get()
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
        if (!$data['collect_code']) return false;
        if (!$data['io_center_id']) return false;
        if (!is_numeric($data['quantum_product'])) return false;
        if (!is_numeric($data['quantum_tray'])) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['collect_code'] == 'Tray') {
            // Check exist Tray by code along with Cabinet
            $tray = $this->getDeviceByCode('Tray', null, $data['parent_id'], $data['code'], $skip_id);
            if ($tray)
                array_push($msg_error, 'Mã box đã tồn tại trong tủ này.');
        } else {
            if ($data['code'] && $this->checkExistData(Device::class, 'code', $data['code'], $skip_id))
                array_push($msg_error, 'Mã thiết bị đã tồn tại.');
        }

//        if ($this->checkExistData(Device::class, 'name', $data['name'], $skip_id))
//            array_push($msg_error, 'Tên thiết bị đã tồn tại.');

        if ($data['quantum_tray'] > 0 && $data['collect_code'] == 'Cabinet') {
            $childs = Device::whereActive(true)->whereIn('parent_id', $skip_id)->count();
            if($childs > 0) {
                array_push($msg_error, 'Chỉ được nhập nhanh box khi tủ không có box nào.');
            }
        }

        return [
            'status' => count($msg_error) > 0 ? false : true,
            'errors' => $msg_error
        ];
    }

    /** My Function */
}
