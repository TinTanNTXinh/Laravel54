<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\Producer;
use App\ProductPrice;
use App\Supplier;
use App\Unit;
use App\Product;
use App\ProductType;
use League\Flysystem\Exception;
use Route;
use DB;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class ProductController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;
    private $skeleton;

    private $class_name = Product::class;

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

        $this->table_name = 'product';
        $this->skeleton = Product::where('products.active', true)
            ->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id')
            ->leftJoin('product_types', 'product_types.id', '=', 'products.product_type_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('producers', 'producers.id', '=', 'products.producer_id')
            ->select('products.*'
                , 'product_prices.price_input'
                , DB::raw($this->getWithCurrencyFormat('product_prices.price_input', 'fc_price_input'))
                , 'product_prices.price_output'
                , DB::raw($this->getWithCurrencyFormat('product_prices.price_output', 'fc_price_output'))
                , 'product_types.name as product_type_name'
                , 'producers.name as producer_name'
                , 'units.name as unit_name');
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
        $products      = $this->skeleton->get();
        $product_types = ProductType::whereActive(true)->get();
        $units         = Unit::whereActive(true)->get();
        $producers     = Producer::whereActive(true)->get();
        $suppliers     = Supplier::whereActive(true)->get();

        return [
            'products'      => $products,
            'product_types' => $product_types,
            'units'         => $units,
            'producers'     => $producers,
            'suppliers'     => $suppliers,
            'first_day'     => $this->first_day,
            'last_day'      => $this->last_day,
            'today'         => $this->today,
            'dis_or_sup'    => $this->user->dis_or_sup
        ];
    }

    public function readOne($id)
    {
        $one = Product::find($id);
        return [$this->table_name => $one];
    }

    public function createOne($data)
    {
        try {
            DB::beginTransaction();
            $one                  = new Product();
            $one->code            = $this->generateCode($this->class_name, 'PRODUCT');
            $one->barcode         = $data['barcode'];
            $one->name            = $data['name'];
            $one->description     = $data['description'];
            $one->created_date    = date('Y-m-d H:i:s');
            $one->updated_date    = null;
            $one->active          = true;
            $one->product_type_id = $data['product_type_id'];
            $one->producer_id     = $data['producer_id'];
            $one->unit_id         = $data['unit_id'];
            $one->is_allowed      = in_array($this->user->position_id, [1, 2]) ? $data['is_allowed'] : false;
            $one->created_by      = $this->user->id;
            $one->updated_by      = 0;
            if (!$one->save()) {
                DB::rollback();
                return false;
            }

            $two               = new ProductPrice();
            $two->product_id   = $one->id;
            $two->price_input  = $data['price_input'];
            $two->price_output = $data['price_output'];
            $two->created_date = date('Y-m-d H:i:s');
            $two->updated_date = null;
            $two->active       = true;
            if (!$two->save()) {
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
            $one                  = Product::find($data['id']);
            $one->code            = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'PRODUCT');
            $one->barcode         = $data['barcode'];
            $one->name            = $data['name'];
            $one->description     = $data['description'];
            $one->updated_date    = date('Y-m-d H:i:s');
            $one->active          = true;
            $one->product_type_id = $data['product_type_id'];
            $one->producer_id     = $data['producer_id'];
            $one->unit_id         = $data['unit_id'];
            $one->is_allowed      = in_array($this->user->position_id, [1, 2]) ? $data['is_allowed'] : false;
            $one->updated_by      = $this->user->id;
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            $two               = ProductPrice::whereActive(true)->where('product_id', $one->id)->first();
            $two->price_input  = $data['price_input'];
            $two->price_output = $data['price_output'];
            $two->updated_date = date('Y-m-d H:i:s');
            $two->active       = true;
            if (!$two->update()) {
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

    public function deactivateOne($id)
    {
        try {
            DB::beginTransaction();
            $one         = Product::find($id);
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
            $one = Product::find($id);
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
        $unit_id     = $filter['unit_id'];
        $barcode     = $filter['barcode'];
        $name        = $filter['name'];

        $products = $this->skeleton;

        $products = $this->searchFromDateToDate($products, 'products.created_date', $from_date, $to_date);

        $products = $this->searchRangeDate($products, 'products.created_date', $range);

        $products = $this->searchFieldName($products, 'products.producer_id', $producer_id);
        $products = $this->searchFieldName($products, 'products.unit_id', $unit_id);
        $products = $this->searchFieldName($products, 'products.barcode', $barcode);
        $products = $this->searchFieldName($products, 'products.name', $name);

        return [
            'products' => $products->get()
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
        if (!$data['producer_id']) return false;
        if (!$data['unit_id']) return false;
        if (!is_numeric($data['price_input'])) return false;
        if (!is_numeric($data['price_output'])) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['barcode'] && $this->checkExistData(Product::class, 'barcode', $data['barcode'], $skip_id))
            array_push($msg_error, 'Mã vạch sản phẩm đã tồn tại.');

        return [
            'status' => count($msg_error) > 0 ? false : true,
            'errors' => $msg_error
        ];
    }

    /** My Function */

}
