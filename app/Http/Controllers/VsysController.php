<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Vsys\IProductInputOutput;
use App\Interfaces\Vsys\IRegisterVisitor;
use App\Interfaces\Vsys\IUserCardMoney;
use App\Interfaces\Vsys\ICheckStock;
use App\Distributor;
use App\UserCardMoney;
use Carbon\Carbon;
use App\ButtonProduct;
use App\HistoryInputOutput;
use App\Device;
use App\UserCard;
use App\User;
use App\IOCenter;
use Exception;
use App\Mail\Reminder;
use Mail;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class VsysController extends Controller implements
    IProductInputOutput, IUserCardMoney, IRegisterVisitor, ICheckStock
{
    use UserHelper, DBHelper;

    private $format_datetime;

    public function __construct()
    {
        $this->format_datetime = $this->getFormatDateTimeVsys()['datetime'];
    }

    /** API METHOD */
    public function getProductInputOutput()
    {
        error_log($_GET['param']);
        $json = json_decode($_GET['param']);
        $msg  = $this->productInputOutput($json);
        return $msg;
    }

    public function getUserCardMoney()
    {
        error_log($_GET['param']);
        $json = json_decode($_GET['param']);
        $msg  = $this->userCardMoney($json);
        return $msg;
    }

    public function getRegisterVisitor()
    {
        error_log($_GET['param']);
        $json = json_decode($_GET['param']);
        $msg  = $this->registerVisitor($json);
        return $msg;
    }

    public function getCheckStock() {
        error_log($_GET['param']);
        $json = json_decode($_GET['param']);
        $msg  = $this->checkStock($json);
        return $msg;
    }

    /** LOGIC METHOD */
    public function productInputOutput($json)
    {
        if ($this->debugJson($json))
            return response()->json(['data' => $json], 200);

        if (!$this->validateJson($json) || !$this->validateJsonProductInputOutput($json)) {
            $this->createLogging('Dữ liệu không hợp lệ.', 'Dữ liệu bộ trung tâm gửi đến máy chủ không hợp lệ.', $json->cnt, $json, 'Vsys', 'danger');
            return 'ERROR';
        }

        $io_center_code      = $json->id;
        $count               = $json->cnt;
        $vsys_date           = $json->t;
        $user_date           = Carbon::createFromFormat($this->format_datetime, $json->c1);
        $card_code           = $json->c2;
        $tray_code           = $json->c3;
        $tray_status         = $json->c4;
        $total_money_in_card = $json->c5;
        $quantum             = $json->c6;
        $product_price       = $json->c7;
        $cabinet_code        = $json->c8;

        try {

            # Find IOCenter
            $io_center = IOCenter::whereActive(true)->whereCode($io_center_code)->first();
            if(!$io_center) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Bộ trung tâm không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Check count
            if ($io_center->count >= $count) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Biến đếm bộ trung tâm gửi đến bé hơn hoặc bằng biến đếm trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Update count
            $io_center->count = $count;
            if (!$io_center->update()) {
                $this->createLogging('Cập nhật biến đếm cho bộ trung tâm thất bại.', '', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Find Cabinet
            $cabinet = $this->getDeviceByCode('Cabinet', $io_center->id, null, $cabinet_code);
            if(!$cabinet) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Tủ không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Find Tray
            $tray = $this->getDeviceByCode('Tray', $io_center->id, $cabinet->id, $tray_code);
            if(!$tray) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Box không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Find Distributor
            $distributor = Distributor::find($io_center->dis_id);
            if (!$distributor || !$distributor->active) {
                $this->createLogging('Không tìm thấy Đại lý hoặc đã ngừng kích hoạt.', '', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Find Card
            $card = $this->getDeviceByCode('Card', null, null, $card_code);
            if(!$card) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Thẻ không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Find UserCard
            $user_card = UserCard::whereActive(true)->where('card_id', $card->id)->first();
            if (!$user_card) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Thẻ chưa cài đặt cho người dùng.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Find User
            $user = User::find($user_card->user_id);
            if (!$user || !$user->active) {
                $this->createLogging('Không tìm thấy Người dùng hoặc đã ngừng kích hoạt.', '', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Find ButtonProduct
            $tray_product = ButtonProduct::whereActive(true)->where([['dis_id', $distributor->id], ['button_id', $tray->id]])->first();
            if (!$tray_product) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Box chưa cài đặt cho người dùng.', $json->cnt, $json, 'Vsys', 'danger');
            }

            switch ($tray_status) {
                case "IN":
                    $tray_product->total_quantum += $quantum;
                    if ($tray_product->total_quantum > $tray->quantum_product) {
                        $this->createLogging('Nạp sản phẩm vượt số lượng tối đa trên mâm', '', $json->cnt, $json, 'Vsys', 'danger');
                    }
                    break;
                case "OUT":
                    $tray_product->total_quantum -= $quantum;
                    if ($tray_product->total_quantum < 0) {
                        $this->createLogging('Bán sản phẩm vượt số lượng còn lại trên mâm.', '', $json->cnt, $json, 'Vsys', 'danger');
                    }
                    break;
                default:
                    $this->createLogging('Trạng thái không phải IN hoặc OUT.', '', $json->cnt, $json, 'Vsys', 'danger');
                    break;
            }

            # Update ButtonProduct
            $tray_product->count        = $count;
            $tray_product->updated_by   = $user->id;
            $tray_product->updated_date = $user_date;
            $tray_product->vsys_date    = $vsys_date;
            if (!$tray_product->update()) {
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật TrayProduct thất bại.', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Create History_Input_Output
            $history_input_output                    = new HistoryInputOutput();
            $history_input_output->dis_id            = $tray_product->dis_id;
            $history_input_output->io_center_id      = $io_center->id;
            $history_input_output->button_id         = $tray_product->button_id;
            $history_input_output->product_id        = $tray_product->product_id;
            $history_input_output->button_product_id = $tray_product->id;
            $history_input_output->status            = $tray_status;

            switch ($tray_status) {
                case "IN":
                    $history_input_output->quantum_in  = $quantum;
                    $history_input_output->quantum_out = 0;

                    $history_input_output->user_input_id  = $user->id;
                    $history_input_output->user_output_id = 0;
                    break;
                case "OUT":
                    $history_input_output->quantum_in  = 0;
                    $history_input_output->quantum_out = $quantum;

                    $history_input_output->user_input_id  = 0;
                    $history_input_output->user_output_id = $user->id;
                    break;
                default:
                    $this->createLogging('Trạng thái không phải IN hoặc OUT.', '', $json->cnt, $json, 'Vsys', 'danger');
                    break;
            }

            $history_input_output->quantum_remain = $tray_product->total_quantum;
            $sum                                  = HistoryInputOutput::whereActive(true)
                ->where('button_product_id', $tray_product->id)
                ->get();
            $sum_in                               = $sum->where('status', 'IN')->sum('quantum_in') + $history_input_output->quantum_in;
            $sum_out                              = $sum->where('status', 'OUT')->sum('quantum_out') + $history_input_output->quantum_out;
            $history_input_output->sum_in         = $sum_in;
            $history_input_output->sum_out        = $sum_out;
            $history_input_output->product_price  = $product_price;
            $history_input_output->total_pay      = $quantum * $history_input_output->product_price;
            $history_input_output->count          = $count;
            $history_input_output->created_by     = $user->id;
            $history_input_output->updated_by     = 0;
            $history_input_output->created_date   = $user_date;
            $history_input_output->updated_date   = null;
            $history_input_output->vsys_date      = $vsys_date;
            $history_input_output->isDefault      = false;
            $history_input_output->adjust_by      = 0;
            $history_input_output->active         = true;

            if (!$history_input_output->save()) {
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật HistoryInputOutput thất bại.', $json->cnt, $json, 'TinTan', 'danger');
            }

            if ($tray_status == 'OUT') {
                # Compute Total_Pay
                $total_pay = $quantum * $product_price;

                # Find UserCard
                $user_card = UserCard::whereActive(true)->where([['user_id', $user->id], ['card_id', $card->id]])->first();
                if (!$user_card) {
                    $this->createLogging('Dữ liệu không hợp lệ.', 'Thẻ chưa cài đặt cho người dùng.', $json->cnt, $json, 'Vsys', 'danger');
                }

                # Validate Total Money
                $total_money_on_server = $user_card->total_money - $total_pay;
                if ($total_money_on_server != $total_money_in_card) {
                    $this->createLogging('Cảnh báo tiền trong thẻ', "Số tiền tính toán trên Máy chủ và Bộ trung tâm gửi lên không bằng nhau. Số tiền máy chủ: {$total_money_on_server}, Số tiền bộ trung tâm: {$total_money_in_card}", $json->cnt, $json, 'Vsys', 'warning');
                }

                # Update UserCard
                $user_card->total_money  = $total_money_in_card;
                $user_card->sum_buy      += $total_pay;
                $user_card->count        = $count;
                $user_card->updated_by   = $user->id;
                $user_card->updated_date = $user_date;
                $user_card->vsys_date    = $vsys_date;
                if (!$user_card->update()) {
                    $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật UserCard thất bại.', $json->cnt, $json, 'TinTan', 'danger');
                }

                # Create UserCardMoney
                $user_card_money               = new UserCardMoney();
                $user_card_money->io_center_id = $io_center->id;
                $user_card_money->device_id    = $tray->id;
                $user_card_money->user_card_id = $user_card->id;
                $user_card_money->status       = 'BUY';
                $user_card_money->money        = $total_pay;
                $user_card_money->count        = $count;
                $user_card_money->created_by   = $user->id;
                $user_card_money->updated_by   = 0;
                $user_card_money->created_date = $user_date;
                $user_card_money->updated_date = null;
                $user_card_money->vsys_date    = $vsys_date;
                $user_card_money->active       = true;
                if (!$user_card_money->save()) {
                    $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật UserCardMoney thất bại.', $json->cnt, $json, 'TinTan', 'danger');
                }

                # Mail Reminder
                $tray = Device::find($tray_product->button_id);
                if ($tray_product->total_quantum <= $tray->quantum_product / 2) {
                    Mail::send(new Reminder($tray->id));
                }
            }

            return 'OK';
        } catch (Exception $ex) {
            $this->createLogging('Lỗi thao tác dữ liệu máy chủ', $ex, $json->cnt, $json, 'TinTan', 'danger');
            return 'ERROR';
        }
    }

    public function userCardMoney($json)
    {
        if ($this->debugJson($json))
            return response()->json(['data' => $json], 200);

        if (!$this->validateJson($json) || !$this->validateJsonUserCardMoney($json)) {
            $this->createLogging('Dữ liệu không hợp lệ.', 'Dữ liệu bộ trung tâm gửi đến máy chủ không hợp lệ.', $json->cnt, $json, 'Vsys', 'danger');
            return 'ERROR';
        }

        $io_center_code      = $json->id;
        $count               = $json->cnt;
        $vsys_date           = $json->t;
        $user_date           = Carbon::createFromFormat($this->format_datetime, $json->c1);
        $card_code           = $json->c2;
        $cdm_code            = $json->c3;
        $cdm_status          = $json->c4;
        $total_money_in_card = $json->c5;
        $money               = $json->c6;

        try {

            # Find IOCenter
            $io_center = IOCenter::whereActive(true)->whereCode($io_center_code)->first();
            if(!$io_center) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Bộ trung tâm không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Check count
            if ($io_center->count >= $count) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Biến đếm bộ trung tâm gửi đến bé hơn hoặc bằng biến đếm trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Update count
            $io_center->count = $count;
            if (!$io_center->update()) {
                $this->createLogging('Cập nhật biến đếm cho bộ trung tâm thất bại.', '', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Find CDM
            $cdm = $this->getDeviceByCode('CDM', $io_center->id, null, $cdm_code);
            if(!$cdm) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Máy nạp tiền không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Find Card
            $card = $this->getDeviceByCode('Card', null, null, $card_code);
            if(!$card) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Thẻ không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Find UserCard
            $user_card = UserCard::whereActive(true)->where('card_id', $card->id)->first();
            if (!$user_card) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Thẻ chưa cài đặt cho người dùng.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Find User
            $user = User::find($user_card->user_id);
            if (!$user || !$user->active) {
                $this->createLogging('Không tìm thấy Người dùng hoặc đã ngừng kích hoạt.', '', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Validate Total Money
            $total_money_on_server = 0;
            switch ($cdm_status) {
                case 'DPS':
                    $total_money_on_server = $user_card->total_money + $money;
                    $user_card->sum_dps    += $money;
                    break;
                case 'WDR':
                    $total_money_on_server = $user_card->total_money - $money;
                    $user_card->sum_dps    += 0;
                    break;
                default:
                    break;
            }
            if ($total_money_on_server != $total_money_in_card) {
                $this->createLogging('Cảnh báo tiền trong thẻ', "Số tiền tính toán trên Máy chủ và Bộ trung tâm gửi lên không bằng nhau. Số tiền máy chủ: {$total_money_on_server}, Số tiền bộ trung tâm: {$total_money_in_card}", $json->cnt, $json, 'Vsys', 'warning');
            }

            # Update UserCard
            $user_card->total_money  = $total_money_in_card;
            $user_card->count        = $count;
            $user_card->updated_by   = $user->id;
            $user_card->updated_date = $user_date;
            $user_card->vsys_date    = $vsys_date;
            if (!$user_card->update()) {
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật UserCard thất bại.', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Create UserCardMoney
            $user_card_money               = new UserCardMoney();
            $user_card_money->io_center_id = $io_center->id;
            $user_card_money->device_id    = $cdm->id;
            $user_card_money->user_card_id = $user_card->id;
            $user_card_money->status       = $cdm_status;
            $user_card_money->money        = $money;
            $user_card_money->count        = $count;
            $user_card_money->created_by   = $user->id;
            $user_card_money->updated_by   = 0;
            $user_card_money->created_date = $user_date;
            $user_card_money->updated_date = null;
            $user_card_money->vsys_date    = $vsys_date;
            $user_card_money->active       = true;
            if (!$user_card_money->save()) {
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật UserCardMoney thất bại.', $json->cnt, $json, 'TinTan', 'danger');
            }

            return 'OK';
        } catch (Exception $ex) {
            $this->createLogging('Lỗi thao tác dữ liệu máy chủ', $ex, $json->cnt, $json, 'TinTan', 'danger');
            return 'ERROR';
        }
    }

    public function registerVisitor($json)
    {
        if ($this->debugJson($json))
            return response()->json(['data' => $json], 200);

        if (!$this->validateJson($json) || !$this->validateJsonRegVisitor($json)) {
            $this->createLogging('Dữ liệu không hợp lệ.', 'Dữ liệu bộ trung tâm gửi đến máy chủ không hợp lệ.', $json->cnt, $json, 'Vsys', 'danger');
            return 'ERROR';
        }

        $io_center_code      = $json->id;
        $count               = $json->cnt;
        $vsys_date           = $json->t;
        $user_date           = Carbon::createFromFormat($this->format_datetime, $json->c1);
        $card_code           = $json->c2;
        $device_code         = $json->c3;

        $total_money_in_card = $json->c5;
        $phone_number        = $json->c6;

        try {

            # Find IOCenter
            $io_center = IOCenter::whereActive(true)->whereCode($io_center_code)->first();
            if (!$io_center) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Bộ trung tâm không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Check count
            if ($io_center->count >= $count) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Biến đếm bộ trung tâm gửi đến bé hơn hoặc bằng biến đếm trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Update count
            $io_center->count = $count;
            if (!$io_center->update()) {
                $this->createLogging('Cập nhật biến đếm cho bộ trung tâm thất bại.', '', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Find Device
            $device = $this->getDeviceByCode(null, $io_center->id, null, $device_code);
            if (!$device) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Thiết bị không tồn tại trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Create User (KVL)
            $kvl                = new User();
            $kvl->code          = $this->generateCode(User::class, 'KVL');
            $kvl->fullname      = '';
            $kvl->username      = null;
            $kvl->password      = null;
            $kvl->address       = null;
            $kvl->phone         = $phone_number;
            $kvl->birthday      = null;
            $kvl->sex           = 'Khác';
            $kvl->email         = null;
            $kvl->note          = null;
            $kvl->created_by    = 0;
            $kvl->updated_by    = 0;
            $kvl->created_date  = $user_date;
            $kvl->updated_date  = null;
            $kvl->active        = true;
            $kvl->position_id   = 6; // KVL
            $kvl->dis_or_sup    = 'dis';
            $kvl->dis_or_sup_id = $io_center->dis_id;
            if (!$kvl->save()) {
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Thêm khách vãng lai thất bại.', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Create Card
            $card                  = new Device();
            $card->collect_code    = 'Card';
            $card->code            = $card_code;
            $card->name            = '';
            $card->description     = 'Thẻ của khách vãng lai';
            $card->quantum_product = 0;
            $card->active          = true;
            $card->collect_id      = 4;
            $card->io_center_id    = $io_center->id;
            $card->parent_id       = 0;
            if (!$card->save()) {
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Thêm thẻ thất bại.', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Create UserCard
            $user_card               = new UserCard();
            $user_card->user_id      = $kvl->id;
            $user_card->card_id      = $card->id;
            $user_card->total_money  = $total_money_in_card;
            $user_card->sum_dps      = $total_money_in_card;
            $user_card->sum_buy      = 0;
            $user_card->count        = $count;
            $user_card->created_by   = 0;
            $user_card->updated_by   = 0;
            $user_card->created_date = $user_date;
            $user_card->updated_date = null;
            $user_card->vsys_date    = $vsys_date;
            $user_card->active       = true;
            if (!$user_card->save()) {
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Thêm UserCard thất bại.', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Create UserCardMoney
            $user_card_money               = new UserCardMoney();
            $user_card_money->io_center_id = $io_center->id;
            $user_card_money->device_id    = $device->id;
            $user_card_money->user_card_id = $user_card->id;
            $user_card_money->status       = 'DPS';
            $user_card_money->money        = $total_money_in_card;
            $user_card_money->count        = $count;
            $user_card_money->created_by   = 0;
            $user_card_money->updated_by   = 0;
            $user_card_money->created_date = $user_date;
            $user_card_money->updated_date = null;
            $user_card_money->vsys_date    = $vsys_date;
            $user_card_money->active       = true;
            if (!$user_card_money->save()) {
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Thêm UserCardMoney thất bại.', $json->cnt, $json, 'TinTan', 'danger');
            }

            return 'OK';
        } catch (Exception $ex) {
            $this->createLogging('Lỗi thao tác dữ liệu máy chủ', $ex, $json->cnt, $json, 'TinTan', 'danger');
            return 'ERROR';
        }
    }

    public function checkStock($json) {
        if ($this->debugJson($json))
            return response()->json(['data' => $json], 200);

        $io_center_code      = $json->id;
        $count               = $json->cnt;
        $vsys_date           = $json->t;
        $user_date           = Carbon::createFromFormat($this->format_datetime, $json->c1);
        $cabinet_code        = $json->c2;
        $tray_code           = $json->c3;

        try {

            # Find IOCenter
            $io_center = IOCenter::whereActive(true)->whereCode($io_center_code)->first();

            # Check count
            if ($io_center->count >= $count) {
                $this->createLogging('Dữ liệu không hợp lệ.', 'Biến đếm bộ trung tâm gửi đến bé hơn hoặc bằng biến đếm trên máy chủ.', $json->cnt, $json, 'Vsys', 'danger');
            }

            # Update count
            $io_center->count = $count;
            if (!$io_center->update()) {
                $this->createLogging('Cập nhật biến đếm cho bộ trung tâm thất bại.', '', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Find Cabinet
            $cabinet = $this->getDeviceByCode('Cabinet', $io_center->id, null, $cabinet_code);

            # Find Tray
            $tray = $this->getDeviceByCode('Tray', $io_center->id, $cabinet->id, $tray_code);

            # Find Distributor
            $distributor = Distributor::find($io_center->dis_id);
            if (!$distributor || !$distributor->active) {
                $this->createLogging('Không tìm thấy Đại lý hoặc đã ngừng kích hoạt.', '', $json->cnt, $json, 'TinTan', 'danger');
            }

            # Find ButtonProduct
            $tray_product = ButtonProduct::whereActive(true)->where([['dis_id', $distributor->id], ['button_id', $tray->id]])->first();

            return $tray_product->total_quantum;
        } catch (Exception $ex) {
            $this->createLogging('Lỗi thao tác dữ liệu máy chủ', $ex, $json->cnt, $json, 'TinTan', 'danger');
            return 'ERROR';
        }
    }

    /** VALIDATE JSON */
    public function validateJsonProductInputOutput($json)
    {
        $tray_status   = (strtoupper($json->c4) == 'IN' || strtoupper($json->c4) == 'OUT') ? strtoupper($json->c4) : null;
        $quantum       = $json->c6;
        $product_price = $json->c7;
        $cabinet_code  = $json->c8;

        if (!$tray_status) return false;
        if (!is_numeric($quantum) || $quantum < 0) return false;
        if (!is_numeric($product_price) || $product_price < 0) return false;
        if (!$cabinet_code) return false;
        return true;
    }

    public function validateJsonUserCardMoney($json)
    {
        $cdm_status = (strtoupper($json->c4) == 'DPS' || strtoupper($json->c4) == 'WDR') ? strtoupper($json->c4) : null;
        $money      = $json->c6;

        if (!$cdm_status) return false;
        if (!is_numeric($money) || $money < 0) return false;
        return true;
    }

    public function validateJsonRegVisitor($json)
    {
        $phone_number = $json->c6;
        if (!$phone_number) return false;
        return true;
    }

    /** MY FUNCTION */
    public function validateJson($json)
    {
        // $username       = $json->u;
        // $password       = $json->p;
        $io_center_code      = $json->id;
        $count               = $json->cnt;
        $vsys_date           = $json->t;
        $user_date           = Carbon::createFromFormat($this->format_datetime, $json->c1);
        $card_code           = $json->c2;
        $device_code         = $json->c3;
        $total_money_in_card = $json->c5;

        if (!$io_center_code) return false;
        if (!is_numeric($count) || $count < 0) return false;
        if (!$vsys_date) return false;
        if (!$user_date) return false;
        if (!$card_code) return false;
        if (!$device_code) return false;
        if (!is_numeric($total_money_in_card) || $total_money_in_card < 0) return false;
        return true;
    }

    private function debugJson($json)
    {
        return (property_exists($json, 'debug') && $json->debug);
    }
}
