<?php

namespace App\Http\Controllers;

use App\IOCenter;
use App\Unit;
use Illuminate\Http\Request;
use App\HistoryInputOutput;
use App\Distributor;
use App\Producer;
use App\ProductType;
use App\Supplier;
use App\Product;
use App\User;
use DB;
use App\Device;
use App\Traits\UserHelper;
use App\Traits\DBHelper;
use App\Traits\FileHelper;

class ReportCustomerController extends Controller
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

    /* API METHOD */
    public function getReadAll()
    {
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function getReportInputBySearch()
    {
        $filter        = (array)json_decode($_GET['query']);
        $report_inputs = $this->reportBySearch($filter, 'input');
        return response()->json($report_inputs, 200);
    }

    public function getReportStockBySearch()
    {
        $filter        = (array)json_decode($_GET['query']);
        $report_stocks = $this->reportStockBySearch($filter);
        return response()->json($report_stocks, 200);
    }

    public function getReportSaleBySearch()
    {
        $filter       = (array)json_decode($_GET['query']);
        $report_sales = $this->reportBySearch($filter, 'sale');
        return response()->json($report_sales, 200);
    }

    /* LOGIC METHOD */
    private function readAll()
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                $distributors = Distributor::whereActive(true)->get();
                $distributor  = [];

                $staffs = User::whereActive(true)->get();

                $products = Product::whereActive(true)->get();

                $producers = Producer::whereActive(true)->get();

                $product_types = ProductType::whereActive(true)->get();

                $suppliers = Supplier::whereActive(true)->get();
                $supplier  = null;

                $cabinets = Device::whereActive(true)->where('collect_code', 'Cabinet')->get();
                break;
            case 'sup':
                $suppliers = [];
                $supplier  = Supplier::whereActive(true)->where('id', $this->user->dis_or_sup_id)->first();

                $distributors = Distributor::whereActive(true)->where('sup_id', $supplier->id)->get();
                $distributor  = [];

                $distributor_ids        = $distributors->pluck('id')->toArray();
                $staffs_of_distributors = User::whereActive(true)->where('dis_or_sup', 'dis')->whereIn('dis_or_sup_id', $distributor_ids)->get();
                $staffs_of_supplier     = User::whereActive(true)->where('dis_or_sup', 'sup')->where('dis_or_sup_id', $supplier->id)->get();
                $staffs                 = $staffs_of_distributors;
                foreach ($staffs_of_supplier as $staff)
                    $staffs->push($staff);

                $products = Product::whereActive(true)->get();

                $producer_ids = $products->pluck('producer_id')->unique()->toArray();
                $producers    = Producer::whereActive(true)->whereIn('id', $producer_ids)->get();

                $product_type_ids = $products->pluck('product_type_id')->unique()->toArray();
                $product_types    = ProductType::whereActive(true)->whereIn('id', $product_type_ids)->get();

                $distributor_ids = $distributors->pluck('id')->toArray();
                $io_center_ids   = IOCenter::whereActive(true)->whereIn('dis_id', $distributor_ids)->pluck('id')->toArray();
                $cabinets        = Device::whereActive(true)->where('collect_code', 'Cabinet')->whereIn('io_center_id', $io_center_ids)->get();
                break;
            case 'dis':
                $distributors = [];
                $distributor  = Distributor::whereActive(true)->where('id', $this->user->dis_or_sup_id)->first();

                $suppliers = [];
                $supplier  = Supplier::whereActive(true)->where('id', $distributor->sup_id)->first();
                array_push($suppliers, $supplier);

                $staffs_of_distributor = User::whereActive(true)->where('dis_or_sup', 'dis')->where('dis_or_sup_id', $distributor->id)->get();
                $staffs_of_supplier    = User::whereActive(true)->where('dis_or_sup', 'sup')->where('dis_or_sup_id', $supplier->id)->get();
                $staffs                = $staffs_of_distributor;
                foreach ($staffs_of_supplier as $staff)
                    $staffs->push($staff);

                $products = Product::whereActive(true)->get();

                $producer_ids = $products->pluck('producer_id')->unique()->toArray();
                $producers    = Producer::whereActive(true)->whereIn('id', $producer_ids)->get();

                $product_type_ids = $products->pluck('product_type_id')->unique()->toArray();
                $product_types    = ProductType::whereActive(true)->whereIn('id', $product_type_ids)->get();

                $io_center_ids = IOCenter::whereActive(true)->where('dis_id', $distributor->id)->pluck('id')->toArray();
                $cabinets      = Device::whereActive(true)->where('collect_code', 'Cabinet')->whereIn('io_center_id', $io_center_ids)->get();
                break;
            default:
                return null;
                break;
        }

        $units = Unit::whereActive(true)->get();

        $response = [
            'suppliers'     => $suppliers,
            'supplier'      => $supplier,
            'distributors'  => $distributors,
            'distributor'   => $distributor,
            'staffs'        => $staffs,
            'products'      => $products,
            'producers'     => $producers,
            'product_types' => $product_types,
            'units'         => $units,
            'cabinets'      => $cabinets,
            'first_day'     => $this->first_day,
            'last_day'      => $this->last_day,
            'today'         => $this->today
        ];
        return $response;
    }

    // Tồn hàng
    private function reportStock()
    {
        // GET REPORT STOCK
        $TonDauKy = 0;

        $report_stocks = HistoryInputOutput::where('history_input_outputs.active', true)
            ->leftJoin('devices', 'devices.id', '=', 'history_input_outputs.button_id')
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->leftJoin('products', 'products.id', '=', 'history_input_outputs.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('distributors', 'distributors.id', '=', 'history_input_outputs.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->select('history_input_outputs.id'
                , 'history_input_outputs.quantum_remain'
                , 'history_input_outputs.sum_in', 'history_input_outputs.sum_out'
                , 'devices.id as tray_id', 'devices.name as tray_name'
                , 'cabinets.id as cabinet_id', 'cabinets.name as cabinet_name'
                , 'products.id as product_id', 'products.name as product_name', 'products.barcode as product_barcode'
                , 'units.id as unit_id', 'units.name as unit_name'
                , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name')
            ->selectRaw('? as TonDauKy', [$TonDauKy])
            ->orderBy('history_input_outputs.created_date', 'asc');

        /*
        BACKUP METHOD
        $report_stocks = HistoryInputOutput::where('history_input_outputs.active', true)
            ->whereMonth('history_input_outputs.created_date', $filter['month'])
            ->whereYear('history_input_outputs.created_date', $filter['year'])
            ->leftJoin('devices', 'devices.id', '=', 'history_input_outputs.button_id')
            ->leftJoin('products', 'products.id', '=', 'history_input_outputs.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('distributors', 'distributors.id', '=', 'history_input_outputs.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->select('history_input_outputs.id'
                , 'history_input_outputs.quantum_remain'
                , 'history_input_outputs.sum_in', 'history_input_outputs.sum_out'
                , 'devices.id as tray_id', 'devices.name as tray_name'
                , 'products.id as product_id', 'products.name', 'products.code'
                , 'units.id as unit_id', 'units.name as unit_name'
                , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name')
            ->selectRaw('? as TonDauKy', [$TonDauKy])
            ->orderBy('history_input_outputs.created_date', 'asc');
        if(isset($filter['tray_id']) && $filter['tray_id'] != 0){
            $report_stocks = $report_stocks->where('history_input_outputs.button_id', $filter['tray_id']);
        }
        if(isset($filter['product_id']) && $filter['product_id'] != 0){
            $report_stocks = $report_stocks->where('history_input_outputs.product_id', $filter['product_id']);
        }
        */

        return $report_stocks;
    }

    private function reportStockByUser($report_stocks)
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                return $report_stocks->get();
                break;
            case 'sup':
                $distributors    = Distributor::whereActive(true)->where('sup_id', $this->user->dis_or_sup_id)->get();
                $distributor_ids = $distributors->pluck('id')->toArray();
                return $report_stocks->whereIn('dis_id', $distributor_ids)->get();
                break;
            case 'dis':
                return $report_stocks->where('history_input_outputs.dis_id', $this->user->dis_or_sup_id)->get();
                break;
            default:
                return [];
                break;
        }
    }

    private function reportStockBySearch($filter)
    {
        $report_stocks = $this->reportStock();

        $month          = $filter['month'];
        $year           = $filter['year'];
        $product_id     = $filter['product_id'];
        $unit_id        = $filter['unit_id'];
        $distributor_id = isset($filter['distributor_id']) ? $filter['distributor_id'] : null;
        $cabinet_id     = $filter['cabinet_id'];

        /***/
        // Get LIST ALL ID REPORT STOCK
        $report_stocks_src = HistoryInputOutput::where('history_input_outputs.active', true)
            ->whereMonth('history_input_outputs.created_date', $month)
            ->whereYear('history_input_outputs.created_date', $year)
            ->leftJoin('devices', 'devices.id', '=', 'history_input_outputs.button_id')
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->leftJoin('products', 'products.id', '=', 'history_input_outputs.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('distributors', 'distributors.id', '=', 'history_input_outputs.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id');

        $report_stocks_src = $this->searchFieldName($report_stocks_src, 'products.id', $product_id);
        $report_stocks_src = $this->searchFieldName($report_stocks_src, 'units.id', $unit_id);
        $report_stocks_src = $this->searchFieldName($report_stocks_src, 'distributors.id', $distributor_id);
        $report_stocks_src = $this->searchFieldName($report_stocks_src, 'cabinets.id', $cabinet_id);
        $report_stocks_src = $report_stocks_src->pluck('history_input_outputs.id');
        $arr_id            = '(';
        foreach ($report_stocks_src as $id) {
            $arr_id .= $id . ',';
        }
        $arr_id .= '0)';
        /***/

        $report_stocks = $report_stocks->whereRaw("history_input_outputs.id IN (SELECT MAX(h2.id) FROM (SELECT * FROM history_input_outputs WHERE id IN {$arr_id}) as h2 GROUP BY h2.button_id)");

        $report_stocks = $this->reportStockByUser($report_stocks);

        // Update value TonDauKy
        foreach ($report_stocks as $stock) {
            $report_stocks_prev = HistoryInputOutput::whereActive(true)
                ->whereMonth('history_input_outputs.created_date', $filter['month'] - 1)
                ->whereYear('history_input_outputs.created_date', $filter['year'])
                ->where('button_id', $stock->tray_id)
                ->where('product_id', $stock->product_id)
                ->orderBy('created_date', 'asc')
                ->get();

            $TonDauKy = 0;
            if ($report_stocks_prev->count() > 0) {
                $TonDauKy = $report_stocks_prev->last() ? $report_stocks_prev->last()->quantum_remain : 0;
            }
            $stock->TonDauKy = $TonDauKy;
        }

        return $this->downloadFile($this->changeColumnName($report_stocks, 'stock'), 'Báo cáo tồn hàng');
        /*
        return [
            'report_stocks' => $report_stocks
        ];
        */
    }

    // Nhập hàng
    private function reportInput()
    {
        $report_inputs = HistoryInputOutput::where([['history_input_outputs.active', true], ['history_input_outputs.status', 'IN'], ['history_input_outputs.isSysAdmin', false]])
            ->leftJoin('devices', 'devices.id', '=', 'history_input_outputs.button_id')
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->leftJoin('products', 'products.id', '=', 'history_input_outputs.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('users as staff_input', 'staff_input.id', '=', 'history_input_outputs.user_input_id')
            ->leftJoin('distributors', 'distributors.id', '=', 'history_input_outputs.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->select(DB::raw('SUM(history_input_outputs.quantum_in) as quantum_in')
                , DB::raw('SUM(history_input_outputs.total_pay) as total_pay')
                , DB::raw($this->getWithCurrencyFormat('SUM(history_input_outputs.total_pay)', 'fc_total_pay'))
                , 'history_input_outputs.product_price'
                , DB::raw($this->getWithCurrencyFormat('history_input_outputs.product_price', 'fc_product_price'))
                , 'devices.id as tray_id', 'devices.name as tray_name'
                , 'cabinets.id as cabinet_id', 'cabinets.name as cabinet_name'
                , 'products.id as product_id', 'products.name as product_name', 'products.barcode as product_barcode'
                , 'units.id as unit_id', 'units.name as unit_name'
                , 'staff_input.id as staff_input_id', 'staff_input.fullname as staff_input_fullname'
                , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name'
                , 'history_input_outputs.created_date'
                , DB::raw($this->getWithDateFormat('history_input_outputs.created_date', 'date_input'))
                , DB::raw($this->getWithTimeFormat('history_input_outputs.created_date', 'time_input')))
            ->groupBy('tray_id', 'tray_name', 'product_id', 'products.name', 'products.code', 'unit_id', 'unit_name', 'staff_input_id', 'staff_input_fullname', 'distributor_id', 'distributor_name', 'supplier_id', 'supplier_name', 'date_input', 'time_input')
            ->orderBy('history_input_outputs.created_date', 'desc');
        return $report_inputs;
    }

    private function reportInputByUser($report_inputs)
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                return $report_inputs->get();
                break;
            case 'sup':
                $distributors    = Distributor::whereActive(true)->where('sup_id', $this->user->dis_or_sup_id)->get();
                $distributor_ids = $distributors->pluck('id')->toArray();

                if ($this->user->positon_id == 4) // NV Nhap
                    $report_inputs = $report_inputs->where('history_input_outputs.created_by', $this->user->id);

                return $report_inputs->whereIn('dis_id', $distributor_ids)->get();
                break;
            case 'dis':
                return $report_inputs->where('history_input_outputs.dis_id', $this->user->dir_or_sup_id)->get();
                break;
            default:
                return [];
                break;
        }
    }

    private function reportBySearch($filter, $mode)
    {
        switch ($mode) {
            case 'input':
                $reports = $this->reportInput();
                break;
            case 'sale':
                $reports = $this->reportSale();
                break;
            default:
                $reports = collect([]);
                break;
        }

        $from_date       = $filter['from_date'];
        $to_date         = $filter['to_date'];
        $range           = $filter['range'];
        $product_id      = $filter['product_id'];
        $unit_id         = $filter['unit_id'];
        $distributor_id  = isset($filter['distributor_id']) ? $filter['distributor_id'] : null;
        $staff_input_id  = isset($filter['staff_input_id']) ? $filter['staff_input_id'] : null;
        $staff_output_id = isset($filter['staff_output_id']) ? $filter['staff_output_id'] : null;
        $cabinet_id      = $filter['cabinet_id'];

        $reports = $this->searchFromDateToDate($reports, 'history_input_outputs.created_date', $from_date, $to_date);
        $reports = $this->searchRangeDate($reports, 'history_input_outputs.created_date', $range);

        $reports = $this->searchFieldName($reports, 'products.id', $product_id);
        $reports = $this->searchFieldName($reports, 'units.id', $unit_id);
        $reports = $this->searchFieldName($reports, 'distributors.id', $distributor_id);
        $reports = $this->searchFieldName($reports, 'staff_input.id', $staff_input_id);
        $reports = $this->searchFieldName($reports, 'staff_output.id', $staff_output_id);
        $reports = $this->searchFieldName($reports, 'cabinets.id', $cabinet_id);

        switch ($mode) {
            case 'input':
                $report_inputs = $this->reportInputByUser($reports);
                return $this->downloadFile($this->changeColumnName($report_inputs, 'input'), 'Báo cáo nhập hàng');
                /*
                return [
                    'report_inputs' => $report_inputs
                ];
                */
                break;
            case 'sale':
                $report_sales = $this->reportSaleByUser($reports);
                return $this->downloadFile($this->changeColumnName($report_sales, 'sale'), 'Báo cáo bán hàng');
                /*
                return [
                    'report_sales' => $report_sales
                ];
                */
                break;
            default:
                return [];
                break;
        }
    }

    // Bán hàng
    private function reportSale()
    {
        $report_sales = HistoryInputOutput::where([['history_input_outputs.active', true], ['history_input_outputs.status', 'OUT'], ['history_input_outputs.isSysAdmin', false]])
            ->leftJoin('devices', 'devices.id', '=', 'history_input_outputs.button_id')
            ->leftJoin('devices as cabinets', 'cabinets.id', '=', 'devices.parent_id')
            ->leftJoin('products', 'products.id', '=', 'history_input_outputs.product_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('distributors', 'distributors.id', '=', 'history_input_outputs.dis_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'distributors.sup_id')
            ->leftJoin('users as staff_output', 'staff_output.id', '=', 'history_input_outputs.user_output_id')
            ->select('history_input_outputs.total_pay'
                , 'history_input_outputs.total_pay'
                , DB::raw($this->getWithCurrencyFormat('history_input_outputs.total_pay', 'fc_total_pay'))
                , 'history_input_outputs.product_price'
                , DB::raw($this->getWithCurrencyFormat('history_input_outputs.product_price', 'fc_product_price'))
                , 'history_input_outputs.quantum_out', 'history_input_outputs.product_price'
                , 'devices.id as tray_id', 'devices.name as tray_name'
                , 'cabinets.id as cabinet_id', 'cabinets.name as cabinet_name'
                , 'products.id as product_id', 'products.name as product_name', 'products.barcode as product_barcode'
                , 'units.id as unit_id', 'units.name as unit_name'
                , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name'
                , 'staff_output.id as staff_output_id', 'staff_output.fullname as staff_output_fullname', 'staff_output.phone as staff_output_phone'
                , DB::raw($this->getWithDateFormat('history_input_outputs.created_date', 'date_output'))
                , DB::raw($this->getWithTimeFormat('history_input_outputs.created_date', 'time_output')))
            ->orderBy('history_input_outputs.created_date', 'desc');
        return $report_sales;
    }

    private function reportSaleByUser($report_sales)
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                return $report_sales->get();
                break;
            case 'sup':
                $distributors    = Distributor::whereActive(true)->where('sup_id', $this->user->dis_or_sup_id)->get();
                $distributor_ids = $distributors->pluck('id')->toArray();
                $user_ids        = User::whereActive(true)->where('dis_or_sup', 'dis')->whereIn('dis_or_sup_id', $distributor_ids)->pluck('id');
                return $report_sales->whereIn('dis_id', $distributor_ids)
                    ->whereIn('history_input_outputs.user_output_id', $user_ids)
                    ->get();
                break;
            case 'dis':
                $user_ids = User::whereActive(true)->where([['dis_or_sup', 'dis'], ['dis_or_sup_id', $this->user->dis_or_sup_id]])->pluck('id');
                return $report_sales
                    ->whereIn('history_input_outputs.user_output_id', $user_ids)
                    ->get();
                break;
            default:
                return [];
                break;
        }
    }

    // Other
    private function changeColumnName($data, $mode)
    {
        switch ($this->user->dis_or_sup) {
            case 'system':
                foreach ($data as $key => $item) {
                    $data[$key]['Khách hàng'] = $data[$key]['supplier_name'];
                    unset($data[$key]['supplier_name']);
                    $data[$key]['Đại lý'] = $data[$key]['distributor_name'];
                    unset($data[$key]['distributor_name']);
                }
                break;
            case 'sup':
                foreach ($data as $key => $item) {
                    $data[$key]['Đại lý'] = $data[$key]['distributor_name'];
                    unset($data[$key]['distributor_name']);
                    unset($data[$key]['supplier_name']);
                }
                break;
            case 'dis':
                foreach ($data as $key => $item) {
                    unset($data[$key]['supplier_name']);
                    unset($data[$key]['distributor_name']);
                }
                break;
            default:
                break;
        }

        switch ($mode) {
            case 'input':
                foreach ($data as $key => $item) {
                    $data[$key]['Ngày'] = $data[$key]['date_input'];
                    unset($data[$key]['date_input']);
                    $data[$key]['Giờ'] = $data[$key]['time_input'];
                    unset($data[$key]['time_input']);
                    $data[$key]['Nhân viên nhập'] = $data[$key]['staff_input_fullname'];
                    unset($data[$key]['staff_input_fullname']);
                    $data[$key]['Tủ'] = $data[$key]['cabinet_name'];
                    unset($data[$key]['cabinet_name']);
                    $data[$key]['Mâm'] = $data[$key]['tray_name'];
                    unset($data[$key]['tray_name']);
                    $data[$key]['Mã vạch SP'] = $data[$key]['product_barcode'];
                    unset($data[$key]['product_barcode']);
                    $data[$key]['Tên sản phẩm'] = $data[$key]['product_name'];
                    unset($data[$key]['product_name']);
                    $data[$key]['ĐVT'] = $data[$key]['unit_name'];
                    unset($data[$key]['unit_name']);
                    $data[$key]['Số lượng nhập'] = $data[$key]['quantum_in'];
                    unset($data[$key]['quantum_in']);
                    $data[$key]['Đơn giá'] = $data[$key]['product_price'];
                    unset($data[$key]['product_price']);
                    $data[$key]['Thành tiền'] = $data[$key]['total_pay'];
                    unset($data[$key]['total_pay']);

                    unset($data[$key]['cabinet_id']);
                    unset($data[$key]['created_date']);
                    unset($data[$key]['distributor_id']);
                    unset($data[$key]['supplier_id']);
                    unset($data[$key]['fc_product_price']);
                    unset($data[$key]['fc_total_pay']);
                    unset($data[$key]['product_id']);
                    unset($data[$key]['staff_input_id']);
                    unset($data[$key]['unit_id']);
                    unset($data[$key]['tray_id']);
                }
                break;
            case 'sale':
                foreach ($data as $key => $item) {
                    $data[$key]['Ngày bán'] = $data[$key]['date_output'];
                    unset($data[$key]['date_output']);
                    $data[$key]['Giờ bán'] = $data[$key]['time_output'];
                    unset($data[$key]['time_output']);
                    $data[$key]['Tủ'] = $data[$key]['cabinet_name'];
                    unset($data[$key]['cabinet_name']);
                    $data[$key]['Mâm'] = $data[$key]['tray_name'];
                    unset($data[$key]['tray_name']);
                    $data[$key]['SĐT'] = $data[$key]['staff_output_phone'];
                    unset($data[$key]['staff_output_phone']);
                    $data[$key]['Nhân viên xuất'] = $data[$key]['staff_output_fullname'];
                    unset($data[$key]['staff_output_fullname']);
                    $data[$key]['Mã vạch SP'] = $data[$key]['product_barcode'];
                    unset($data[$key]['product_barcode']);
                    $data[$key]['Tên sản phẩm'] = $data[$key]['product_name'];
                    unset($data[$key]['product_name']);
                    $data[$key]['ĐVT'] = $data[$key]['unit_name'];
                    unset($data[$key]['unit_name']);
                    $data[$key]['Số lượng bán'] = $data[$key]['quantum_out'];
                    unset($data[$key]['quantum_out']);
                    $data[$key]['Đơn giá'] = $data[$key]['product_price'];
                    unset($data[$key]['product_price']);
                    $data[$key]['Thành tiền'] = $data[$key]['total_pay'];
                    unset($data[$key]['total_pay']);

                    unset($data[$key]['fc_total_pay']);
                    unset($data[$key]['fc_product_price']);
                    unset($data[$key]['tray_id']);
                    unset($data[$key]['cabinet_id']);
                    unset($data[$key]['product_id']);
                    unset($data[$key]['unit_id']);
                    unset($data[$key]['distributor_id']);
                    unset($data[$key]['supplier_id']);
                    unset($data[$key]['staff_output_id']);
                }
                break;
            case 'stock':
                foreach ($data as $key => $item) {
                    $data[$key]['Tủ'] = $data[$key]['cabinet_name'];
                    unset($data[$key]['cabinet_name']);
                    $data[$key]['Mâm'] = $data[$key]['tray_name'];
                    unset($data[$key]['tray_name']);
                    $data[$key]['Mã vạch SP'] = $data[$key]['product_barcode'];
                    unset($data[$key]['product_barcode']);
                    $data[$key]['Tên sản phẩm'] = $data[$key]['product_name'];
                    unset($data[$key]['product_name']);
                    $data[$key]['ĐVT'] = $data[$key]['unit_name'];
                    unset($data[$key]['unit_name']);
                    $data[$key]['Tồn đầu kỳ'] = $data[$key]['TonDauKy'];
                    unset($data[$key]['TonDauKy']);
                    $data[$key]['Nhập trong kỳ'] = $data[$key]['sum_in'];
                    unset($data[$key]['sum_in']);
                    $data[$key]['Xuất trong kỳ'] = $data[$key]['sum_out'];
                    unset($data[$key]['sum_out']);
                    $data[$key]['Tồn cuối kỳ'] = $data[$key]['quantum_remain'];
                    unset($data[$key]['quantum_remain']);

                    unset($data[$key]['id']);
                    unset($data[$key]['cabinet_id']);
                    unset($data[$key]['tray_id']);
                    unset($data[$key]['product_id']);
                    unset($data[$key]['unit_id']);
                    unset($data[$key]['distributor_id']);
                    unset($data[$key]['supplier_id']);
                }
                break;
            default:
                break;
        }
        return $data;
    }
}
