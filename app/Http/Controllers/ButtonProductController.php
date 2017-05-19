<?php

namespace App\Http\Controllers;

use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\HistoryInputOutput;
use App\Distributor;
use App\IOCenter;
use Illuminate\Http\Request;
use App\ButtonProduct;
use App\Product;
use App\Device;
use Route;
use DB;
use League\Flysystem\Exception;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class ButtonProductController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;
    private $skeleton;
    private $http_status_code;

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

        $this->table_name       = 'tray_product';
        $this->skeleton = ButtonProduct::where('button_products.active', true)
            ->leftJoin('products', 'products.id', '=', 'button_products.product_id')
            ->leftJoin('devices', 'devices.id', '=', 'button_products.button_id')
            ->leftJoin('io_centers', 'io_centers.id', '=', 'devices.io_center_id')
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->leftJoin('distributors', 'distributors.id', '=', 'io_centers.dis_id')
            ->select('button_products.id', 'button_products.button_id', 'button_products.total_quantum', 'products.name as product_name'
                , 'devices.code as tray_code', 'devices.name as tray_name', 'devices.description as tray_description', 'devices.quantum_product as tray_quantum_product'
                , 'io_centers.code as io_center_code', 'io_centers.name as io_center_name', 'io_centers.description as io_center_description'
                , 'cabinets.code as cabinet_code', 'cabinets.name as cabinet_name', 'cabinets.description as cabinet_description'
                , 'distributors.name as distributor_name'
            );
        $this->http_status_code = 200;
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
        // TODO: Implement postCreateOne() method.
    }

    public function putUpdateOne(Request $request)
    {
        $data = $request->input($this->table_name);
        if(!$data['id'] || !is_numeric($data['id'])) {
            return response()->json(['msg' => ['Dữ liệu không hợp lệ!']], 404);
        }
        if(!$data['total_quantum'] || !is_numeric($data['total_quantum'])) {
            return response()->json(['msg' => ['Dữ liệu không hợp lệ!']], 404);
        }

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
        $filter    = (array)json_decode($_GET['query']);
        $arr_datas = $this->searchOne($filter);
        return response()->json($arr_datas, 200);
    }

    /** LOGIC METHOD */
    public function readAll()
    {
        $tray_products = $this->skeleton->get();
        $products      = Product::whereActive(true)->get();
        $trays         = Device::where('devices.active', true)
            ->where('devices.collect_code', 'Tray')
            ->whereNotIn('devices.id', $tray_products->pluck('button_id')->toArray())
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->select('devices.*', 'cabinets.code as cabinet_code', 'cabinets.name as cabinet_name')
            ->get();

        $io_centers   = IOCenter::whereActive(true)->get();
        $cabinets     = Device::whereActive(true)->where('collect_code', 'Cabinet')->get();
        $distributors = Distributor::whereActive(true)->get();

        return [
            'products'      => $products,
            'trays'         => $trays,
            'tray_products' => $tray_products,
            'io_centers'    => $io_centers,
            'cabinets'      => $cabinets,
            'distributors'  => $distributors,
            'first_day'     => $this->first_day,
            'last_day'      => $this->last_day,
            'today'         => $this->today
        ];
    }

    public function readOne($id)
    {
        $one = ButtonProduct::find($id);
        return ['tray_product' => $one];
    }

    public function createOne($data)
    {
        // TODO: Implement createOne() method.
    }

    public function updateOne($data)
    {
        try {
            DB::beginTransaction();
            $one = ButtonProduct::find($data['id']);

            if ($one->total_quantum == $data['total_quantum']) {
                DB::rollBack();
                return false;
            }

            $old_total_quantum  = $one->total_quantum;
            $one->total_quantum = $data['total_quantum'];
            $one->active        = true;
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            $status        = ($data['total_quantum'] > $old_total_quantum) ? 'IN' : 'OUT';
            $changeQuantum = abs($data['total_quantum'] - $old_total_quantum);

            $tray = Device::find($one->button_id);

            $two                    = new HistoryInputOutput();
            $two->dis_id            = $one->dis_id;
            $two->io_center_id      = $tray->io_center_id;
            $two->button_id         = $one->button_id;
            $two->product_id        = $one->product_id;
            $two->button_product_id = $one->id;
            $two->status            = $status;
            switch ($status) {
                case 'IN':
                    $two->quantum_in  = $changeQuantum;
                    $two->quantum_out = 0;

                    $two->user_input_id  = $this->user->id;
                    $two->user_output_id = 0;
                    break;
                case 'OUT':
                    $two->quantum_in  = 0;
                    $two->quantum_out = $changeQuantum;

                    $two->user_input_id  = 0;
                    $two->user_output_id = $this->user->id;
                    break;
                default:
                    break;
            }

            $two->quantum_remain = $one->total_quantum;
            $sum                 = HistoryInputOutput::whereActive(true)
                ->where('button_product_id', $one->id)
                ->get();
            $sum_in              = $sum->where('status', 'IN')->sum('quantum_in') + $two->quantum_in;
            $sum_out             = $sum->where('status', 'OUT')->sum('quantum_out') + $two->quantum_out;
            $two->sum_in         = $sum_in;
            $two->sum_out        = $sum_out;
            $two->product_price  = 0;
            $two->total_pay      = $changeQuantum * $two->product_price;

            $two->count        = 0;
            $two->created_by   = $this->user->id;
            $two->updated_by   = 0;
            $two->created_date = date('Y-m-d H:i:s');
            $two->updated_date = null;
            $two->vsys_date    = date('Y-m-d H:i:s');
            $two->isDefault    = false;
            $two->adjust_by    = $this->user->id;
            $two->active       = true;
            if (!$two->save()) {
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
            $one         = ButtonProduct::find($id);
            $one->active = false;
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            if (HistoryInputOutput::whereActive(true)->where('button_product_id', $one->id)->update(['active' => false]) <= 0) {
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
            $one = ButtonProduct::find($id);
            if (!$one->delete()) {
                DB::rollBack();
                return false;
            }

            if (HistoryInputOutput::whereActive(true)->where('button_product_id', $one->id)->delete() <= 0) {
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
        $cabinet_id     = $filter['cabinet_id'];
        $distributor_id = $filter['distributor_id'];
        $product_id     = $filter['product_id'];

        $tray_products = $this->skeleton;

        $tray_products = $this->searchFromDateToDate($tray_products, 'button_products.created_date', $from_date, $to_date);

        $tray_products = $this->searchRangeDate($tray_products, 'button_products.created_date', $range);

        $tray_products = $this->searchFieldName($tray_products, 'io_centers.id', $io_center_id);
        $tray_products = $this->searchFieldName($tray_products, 'cabinets.id', $cabinet_id);
        $tray_products = $this->searchFieldName($tray_products, 'distributors.id', $distributor_id);
        $tray_products = $this->searchFieldName($tray_products, 'products.id', $product_id);

        return [
            'tray_products' => $tray_products->get()
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
        if (!$data['arr_tray_id']) return false;
        if (!$data['product_id']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        return [
            'status' => true,
            'errors' => []
        ];
    }

    /** My Function */
    public function postCreateOrUpdateMulti(Request $request)
    {
        $data = $request->input($this->table_name);
        $validates = $this->validateInput($data);
        if (!$validates['status'])
            return response()->json(['msg' => $validates['errors']], 404);

        if (!$this->createOrUpdateMulti($data))
            return response()->json(['msg' => ['Create or Update Multi failed!']], 404);
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function createOrUpdateMulti($data)
    {
        try {
            DB::beginTransaction();
            $arr_tray_id = $data['arr_tray_id'];
            foreach ($arr_tray_id as $tray_id) {
                $check_exist = ButtonProduct::whereActive(true)->where('button_id', $tray_id)->first();
                $tray        = Device::find($tray_id);
                $io_center   = IOCenter::find($tray->io_center_id);

                if ($io_center->dis_id == 0) {
                    DB::rollBack();
                    return false;
                }
                if ($check_exist == null) {
                    $one                = new ButtonProduct();
                    $one->dis_id        = $io_center->dis_id;
                    $one->button_id     = $tray_id;
                    $one->product_id    = $data['product_id'];
                    $one->total_quantum = 0;
                    $one->count         = 0;
                    $one->created_by    = $this->user->id;
                    $one->updated_by    = 0;
                    $one->created_date  = date('Y-m-d H:i:s');
                    $one->updated_date  = null;
                    $one->vsys_date     = date('Y-m-d H:i:s');
                    $one->active        = true;
                    if (!$one->save()) {
                        DB::rollBack();
                        return false;
                    }

                    $two                    = new HistoryInputOutput();
                    $two->dis_id            = $one->dis_id;
                    $two->io_center_id      = $tray->io_center_id;
                    $two->button_id         = $one->button_id;
                    $two->product_id        = $one->product_id;
                    $two->button_product_id = $one->id;
                    $two->status            = 'IN';
                    $two->quantum_in        = 0;
                    $two->quantum_out       = 0;
                    $two->count             = $one->count;
                    $two->created_by        = $one->created_by;
                    $two->updated_by        = 0;
                    $two->user_input_id     = $this->user->id;
                    $two->user_output_id    = 0;
                    $two->created_date      = date('Y-m-d H:i:s');
                    $two->updated_date      = null;
                    $two->vsys_date         = date('Y-m-d H:i:s');
                    $two->isDefault         = true;
                    $two->adjust_by         = 0;
                    $two->active            = true;
                    if (!$two->save()) {
                        DB::rollBack();
                        return false;
                    }
                } else {
                    if ($check_exist->total_quantum > 0) {
                        DB::rollBack();
                        return false;
                    }

                    $check_exist->product_id   = $data['product_id'];
                    $check_exist->updated_by   = $this->user->id;
                    $check_exist->updated_date = date('Y-m-d H:i:s');
                    $check_exist->vsys_date    = date('Y-m-d H:i:s');
                    $check_exist->active       = true;
                    if (!$check_exist->update()) {
                        DB::rollBack();
                        return false;
                    }

                    if (HistoryInputOutput::whereActive(true)->where('button_product_id', $check_exist->id)->update(['active' => false]) <= 0) {
                        DB::rollBack();
                        return false;
                    }

                    $two                    = new HistoryInputOutput();
                    $two->dis_id            = $check_exist->dis_id;
                    $two->io_center_id      = $tray->io_center_id;
                    $two->button_id         = $check_exist->button_id;
                    $two->product_id        = $check_exist->product_id;
                    $two->button_product_id = $check_exist->id;
                    $two->status            = 'IN';
                    $two->quantum_in        = 0;
                    $two->quantum_out       = 0;
                    $two->count             = $check_exist->count;
                    $two->created_by        = $check_exist->created_by;
                    $two->updated_by        = 0;
                    $two->user_input_id     = $this->user->id;
                    $two->user_output_id    = 0;
                    $two->created_date      = date('Y-m-d H:i:s');
                    $two->updated_date      = null;
                    $two->vsys_date         = date('Y-m-d H:i:s');
                    $two->isDefault         = true;
                    $two->adjust_by         = 0;
                    $two->active            = true;
                    if (!$two->save()) {
                        DB::rollBack();
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

}
