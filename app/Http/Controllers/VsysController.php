<?php

namespace App\Http\Controllers;

use App\Distributor;
use App\UserCardMoney;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\ButtonProduct;
use App\HistoryInputOutput;
use App\Device;
use App\UserCard;
use App\User;
use App\IOCenter;
use Exception;
use DB;
use App\Mail\Reminder;
use Mail;
use App\Traits\UserHelper;
use App\Traits\DBHelper;
use App\Traits\DateTimeHelper;

class VsysController extends Controller
{
    use UserHelper, DBHelper, DateTimeHelper;

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

    /** LOGIC METHOD */
    public function productInputOutput($json)
    {
        if ($this->debugJson($json))
            return response()->json(['data' => $json], 200);

        if (!$this->validateJson($json) || !$this->validateJsonProductInputOutput($json)) {
            $this->createLogging('Dữ liệu không hợp lệ.', 'Dữ liệu bộ trung tâm gửi đến máy chủ không hợp lệ.', $json->cnt, 'Vsys', 'danger');
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
            DB::beginTransaction();

            # Find IOCenter & Update Count
            $io_center = IOCenter::whereActive(true)->whereCode($io_center_code)->first();

            # Check count
            if ($io_center->count >= $count) {
                DB::rollback();
                $this->createLogging('Dữ liệu không hợp lệ.', 'Biến đếm bộ trung tâm gửi đến bé hơn hoặc bằng biến đếm trên máy chủ.', $json->cnt, 'Vsys', 'danger');
                return 'ERROR';
            }

            # Update count
            $io_center->count = $count;
            if (!$io_center->update()) {
                DB::rollback();
                $this->createLogging('Cập nhật biến đếm cho bộ trung tâm thất bại.', '', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            # Find Cabinet
            $cabinet = $this->getDeviceByCode('Cabinet', $io_center->id, null, $cabinet_code);

            # Find Tray
            $tray = $this->getDeviceByCode('Tray', $io_center->id, $cabinet->id, $tray_code);

            # Find Distributor
            $distributor = Distributor::find($io_center->dis_id);
            if (!$distributor || !$distributor->active) {
                DB::rollback();
                $this->createLogging('Không tìm thấy Đại lý hoặc đã ngừng kích hoạt.', '', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            # Find Card -> UserCard -> User
            $card = $this->getDeviceByCode('Card', null, null, $card_code);

            $user_card = UserCard::whereActive(true)->where('card_id', $card->id)->first();

            $user = User::find($user_card->user_id);
            if (!$user || !$user->active) {
                DB::rollback();
                $this->createLogging('Không tìm thấy Người dùng hoặc đã ngừng kích hoạt.', '', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            # Update Button_Product
            $tray_product = ButtonProduct::whereActive(true)->where([['dis_id', $distributor->id], ['button_id', $tray->id]])->first();

            switch ($tray_status) {
                case "IN":
                    $tray_product->total_quantum += $quantum;
                    if ($tray_product->total_quantum > $tray->quantum_product) {
                        DB::rollback();
                        $this->createLogging('Nạp sản phẩm vượt số lượng tối đa trên mâm', '', $json->cnt, 'Vsys', 'danger');
                        return 'ERROR';
                    }
                    break;
                case "OUT":
                    $tray_product->total_quantum -= $quantum;
                    if ($tray_product->total_quantum < 0) {
                        DB::rollback();
                        $this->createLogging('Bán sản phẩm vượt số lượng còn lại trên mâm.', '', $json->cnt, 'Vsys', 'danger');
                        return 'ERROR';
                    }
                    break;
                default:
                    DB::rollBack();
                    $this->createLogging('Trạng thái không phải IN hoặc OUT.', '', $json->cnt, 'Vsys', 'danger');
                    return 'ERROR';
                    break;
            }

            $tray_product->count        = $count;
            $tray_product->updated_by   = $user->id;
            $tray_product->updated_date = $user_date;
            $tray_product->vsys_date    = $vsys_date;
            if (!$tray_product->update()) {
                DB::rollback();
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật TrayProduct thất bại.', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
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
                    DB::rollBack();
                    $this->createLogging('Trạng thái không phải IN hoặc OUT.', '', $json->cnt, 'Vsys', 'danger');
                    return 'ERROR';
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
            $history_input_output->isSysAdmin     = false;
            $history_input_output->active         = true;

            if (!$history_input_output->save()) {
                DB::rollback();
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật HistoryInputOutput thất bại.', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            if ($tray_status == 'OUT') {
                # Compute Total_Pay
                $total_pay = $quantum * $product_price;

                # Update User_Card
                $user_card = UserCard::whereActive(true)->where([['user_id', $user->id], ['card_id', $card->id]])->first();

                # Validate Total Money
                $total_money_on_server = $user_card->total_money - $total_pay;
                if ($total_money_on_server != $total_money_in_card) {
                    $this->createLogging('Cảnh báo tiền trong thẻ', "Số tiền tính toán trên Máy chủ và Bộ trung tâm gửi lên không bằng nhau. Số tiền máy chủ: {$total_money_on_server}, Số tiền bộ trung tâm: {$total_money_in_card}", $json->cnt, 'Vsys', 'warning');
                }

                $user_card->total_money  = $total_money_in_card;
                $user_card->sum_buy      += $total_pay;
                $user_card->count        = $count;
                $user_card->updated_by   = $user->id;
                $user_card->updated_date = $user_date;
                $user_card->vsys_date    = $vsys_date;
                if (!$user_card->update()) {
                    DB::rollback();
                    $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật UserCard thất bại.', $json->cnt, 'TinTan', 'danger');
                    return 'ERROR';
                }

                # Create User_Card_Money
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
                    DB::rollback();
                    $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật UserCardMoney thất bại.', $json->cnt, 'TinTan', 'danger');
                    return 'ERROR';
                }

                # Mail Reminder
                $tray = Device::find($tray_product->button_id);
                if ($tray_product->total_quantum <= $tray->quantum_product / 2) {
                    Mail::send(new Reminder($tray->id));
                }
            }

            DB::commit();
            return 'OK';
        } catch (Exception $ex) {
            DB::rollback();
            $this->createLogging('Lỗi thao tác dữ liệu máy chủ', $ex, $json->cnt, 'TinTan', 'danger');
            return 'ERROR';
        }
    }

    public function userCardMoney($json)
    {
        if ($this->debugJson($json))
            return response()->json(['data' => $json], 200);

        if (!$this->validateJson($json) || !$this->validateJsonUserCardMoney($json)) {
            $this->createLogging('Dữ liệu không hợp lệ.', 'Dữ liệu bộ trung tâm gửi đến máy chủ không hợp lệ.', $json->cnt, 'Vsys', 'danger');
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
            DB::beginTransaction();

            # Find IOCenter & Update Count
            $io_center = IOCenter::whereActive(true)->whereCode($io_center_code)->first();

            # Check count
            if ($io_center->count >= $count) {
                DB::rollback();
                $this->createLogging('Dữ liệu không hợp lệ.', 'Biến đếm bộ trung tâm gửi đến bé hơn hoặc bằng biến đếm trên máy chủ.', $json->cnt, 'Vsys', 'danger');
                return 'ERROR';
            }

            # Update count
            $io_center->count = $count;
            if (!$io_center->update()) {
                DB::rollback();
                $this->createLogging('Cập nhật biến đếm cho bộ trung tâm thất bại.', '', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            # Find CDM
            $cdm = $this->getDeviceByCode('CDM', $io_center->id, null, $cdm_code);

            # Find Card -> UserCard -> User
            $card = $this->getDeviceByCode('Card', null, null, $card_code);

            $user_card = UserCard::whereActive(true)->where('card_id', $card->id)->first();

            $user = User::find($user_card->user_id);
            if (!$user || !$user->active) {
                DB::rollBack();
                $this->createLogging('Không tìm thấy Người dùng hoặc đã ngừng kích hoạt.', '', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            # Update User_Card
            $user_card = UserCard::whereActive(true)->where([['user_id', $user->id], ['card_id', $card->id]])->first();

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
                $this->createLogging('Cảnh báo tiền trong thẻ', "Số tiền tính toán trên Máy chủ và Bộ trung tâm gửi lên không bằng nhau. Số tiền máy chủ: {$total_money_on_server}, Số tiền bộ trung tâm: {$total_money_in_card}", $json->cnt, 'Vsys', 'warning');
            }

            $user_card->total_money  = $total_money_in_card;
            $user_card->count        = $count;
            $user_card->updated_by   = $user->id;
            $user_card->updated_date = $user_date;
            $user_card->vsys_date    = $vsys_date;
            if (!$user_card->update()) {
                DB::rollBack();
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật UserCard thất bại.', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            # Create User_Card_Money
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
                DB::rollBack();
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Cập nhật UserCardMoney thất bại.', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            DB::commit();
            return 'OK';
        } catch (Exception $ex) {
            DB::rollback();
            $this->createLogging('Lỗi thao tác dữ liệu máy chủ', $ex, $json->cnt, 'TinTan', 'danger');
            return 'ERROR';
        }
    }

    public function registerVisitor($json)
    {
        if ($this->debugJson($json))
            return response()->json(['data' => $json], 200);

        if (!$this->validateJson($json) || !$this->validateJsonRegVisitor($json)) {
            $this->createLogging('Dữ liệu không hợp lệ.', 'Dữ liệu bộ trung tâm gửi đến máy chủ không hợp lệ.', $json->cnt, 'Vsys', 'danger');
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
            DB::beginTransaction();

            # Find IOCenter & Update Count
            $io_center = IOCenter::whereActive(true)->whereCode($io_center_code)->first();

            # Check count
            if ($io_center->count >= $count) {
                DB::rollback();
                $this->createLogging('Dữ liệu không hợp lệ.', 'Biến đếm bộ trung tâm gửi đến bé hơn hoặc bằng biến đếm trên máy chủ.', $json->cnt, 'Vsys', 'danger');
                return 'ERROR';
            }

            # Update count
            $io_center->count = $count;
            if (!$io_center->update()) {
                DB::rollback();
                $this->createLogging('Cập nhật biến đếm cho bộ trung tâm thất bại.', '', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            # Find Device
            $device = $this->getDeviceByCode(null, $io_center->id, null, $device_code);

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
                DB::rollBack();
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Thêm khách vãng lai thất bại.', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
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
                DB::rollBack();
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Thêm thẻ thất bại.', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
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
                DB::rollBack();
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Thêm UserCard thất bại.', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
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
                DB::rollBack();
                $this->createLogging('Lỗi thao tác dữ liệu máy chủ', 'Thêm UserCardMoney thất bại.', $json->cnt, 'TinTan', 'danger');
                return 'ERROR';
            }

            DB::commit();
            return 'OK';
        } catch (Exception $ex) {
            DB::rollback();
            $this->createLogging('Lỗi thao tác dữ liệu máy chủ', $ex, $json->cnt, 'TinTan', 'danger');
            return 'ERROR';
        }
    }

    /** Validation JSON */
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

    private function validateJsonProductInputOutput($json)
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

    private function validateJsonUserCardMoney($json)
    {
        $cdm_status = (strtoupper($json->c4) == 'DPS' || strtoupper($json->c4) == 'WDR') ? strtoupper($json->c4) : null;
        $money      = $json->c6;

        if (!$cdm_status) return false;
        if (!is_numeric($money) || $money < 0) return false;
        return true;
    }

    private function validateJsonRegVisitor($json)
    {
        $phone_number = $json->c6;
        if (!$phone_number) return false;
        return true;
    }

    /** Other */
    private function debugJson($json)
    {
        return (property_exists($json, 'debug') && $json->debug);
    }
}