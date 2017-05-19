<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Distributor;
use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\IOCenter;
use App\Position;
use App\Supplier;
use App\UserCardMoney;
use App\UserCard;
use App\User;
use App\Device;
use League\Flysystem\Exception;
use Route;
use DB;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class UserCardController extends Controller implements ICrud, IValidate
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
        $this->table_name = 'user_card';
        $this->skeleton = UserCard::where('user_cards.active', true)
            ->leftJoin('users', 'users.id', '=', 'user_cards.user_id')
            ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
            ->leftJoin('devices', 'devices.id', '=', 'user_cards.card_id')
            ->leftJoin('io_centers', 'io_centers.id', '=', 'devices.io_center_id')
            ->leftJoin('devices as parents', 'parents.id', '=', 'devices.parent_id');
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
        $user_cards = $this->skeleton;
        $user_cards = $this->searchFieldName($user_cards, 'users.dis_or_sup', $this->user->dis_or_sup);
        $user_cards = $user_cards->get();

        // Nhung nhan vien chua duoc phan the
        $staffs = User::where('users.active', true)
            ->whereNotIn('position_id', [1, 2])
            ->whereNotIn('id', $user_cards->pluck('user_id')->toArray())
            ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
            ->select('users.*', 'positions.name as position_name')
            ->get();

        $cards = Device::whereActive(true)
            ->where('collect_code', 'Card')
            ->whereNotIn('id', $user_cards->pluck('card_id')->toArray())
            ->get();

        // Lay tat ca user de search
        $users        = User::whereActive(true)->get();
        $suppliers    = Supplier::whereActive(true)->get();
        $distributors = Distributor::whereActive(true)->get();
        $io_centers   = IOCenter::whereActive(true)->get();
        $rfids        = Device::whereActive(true)->where('collect_code', 'RFID')->get();
        $positions    = Position::whereActive(true)->get();

        return [
            'staffs'       => $staffs,
            'cards'        => $cards,
            'user_cards'   => $user_cards,
            'users'        => $users,
            'suppliers'    => $suppliers,
            'distributors' => $distributors,
            'io_centers'   => $io_centers,
            'rfids'        => $rfids,
            'positions'    => $positions,
            'first_day'    => $this->first_day,
            'last_day'     => $this->last_day,
            'today'        => $this->today
        ];
    }

    public function readOne($id)
    {
        $one = UserCard::find($id);
        return ['user_card' => $one];
    }

    public function createOne($data)
    {
        // TODO: Implement createOne() method.
    }

    public function updateOne($data)
    {
        // TODO: Implement updateOne() method.
    }

    public function deactivateOne($id)
    {
        try {
            DB::beginTransaction();
            $one         = UserCard::find($id);
            $one->active = false;
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            if (UserCardMoney::whereActive(true)->where('user_card_id', $one->id)->update(['active' => false]) <= 0) {
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
            $one = UserCard::find($id);
            if (!$one->delete()) {
                DB::rollBack();
                return false;
            }

            if (UserCardMoney::whereActive(true)->where('user_card_id', $one->id)->delete() <= 0) {
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
        $dis_or_sup     = $filter['dis_or_sup'];
        $supplier_id    = $filter['supplier_id'];
        $distributor_id = $filter['distributor_id'];
        $position_id    = $filter['position_id'];
        $io_center_id   = $filter['io_center_id'];
        $rfid_id        = $filter['rfid_id'];
        $fullname       = $filter['fullname'];
        $phone          = $filter['phone'];

        $user_cards = $this->skeleton;

        $user_cards = $this->searchFieldName($user_cards, 'users.dis_or_sup', $dis_or_sup);

        switch ($dis_or_sup) {
            case 'sup':
                $user_cards = $user_cards
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'users.dis_or_sup_id')
                    ->select('user_cards.*', 'positions.name as position_name', 'users.fullname as user_fullname', 'users.phone as user_phone'
                        , 'devices.code as card_code', 'devices.name as card_name', 'devices.description as card_description'
                        , 'io_centers.code as io_center_code', 'io_centers.name as io_center_name', 'io_centers.description as io_center_description'
                        , 'parents.code as parent_code', 'parents.name as parent_name', 'parents.description as parent_description'
                        , 'suppliers.name as supplier_name'
                    );

                $user_cards = $this->searchFieldName($user_cards, 'users.dis_or_sup_id', $supplier_id);
                break;
            case 'dis':
                $user_cards = $user_cards
                    ->leftJoin('distributors', 'distributors.id', '=', 'users.dis_or_sup_id')
                    ->select('user_cards.*', 'positions.name as position_name', 'users.fullname as user_fullname'
                        , 'devices.code as card_code', 'devices.name as card_name', 'devices.description as card_description'
                        , 'io_centers.code as io_center_code', 'io_centers.name as io_center_name', 'io_centers.description as io_center_description'
                        , 'parents.code as parent_code', 'parents.name as parent_name', 'parents.description as parent_description'
                        , 'distributors.name as distributor_name'
                    );

                $user_cards = $this->searchFieldName($user_cards, 'users.dis_or_sup_id', $distributor_id);
                break;
            default:
                break;
        }

        $user_cards = $this->searchFromDateToDate($user_cards, 'user_cards.created_date', $from_date, $to_date);

        $user_cards = $this->searchRangeDate($user_cards, 'user_cards.created_date', $range);

        $user_cards = $this->searchFieldName($user_cards, 'positions.id', $position_id);
        $user_cards = $this->searchFieldName($user_cards, 'io_centers.id', $io_center_id);
        $user_cards = $this->searchFieldName($user_cards, 'parents.id', $rfid_id);
        $user_cards = $this->searchFieldName($user_cards, 'users.fullname', $fullname);
        $user_cards = $this->searchFieldName($user_cards, 'users.phone', $phone);

        return [
            'user_cards' => $user_cards->get()
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
        if (!$data['user_id']) return false;
        if (!$data['card_id']) return false;
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
    public function postCreateOrUpdateOne(Request $request)
    {
        $data = $request->input($this->table_name);
        $validates = $this->validateInput($data);
        if (!$validates['status'])
            return response()->json(['msg' => $validates['errors']], 404);

        if (!$this->createOrUpdateOne($data))
            return response()->json(['msg' => ['Create or Update One failed!']], 404);
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function createOrUpdateOne($data)
    {
        try {
            DB::beginTransaction();

            $user_id     = $data['user_id'];
            $check_exist = UserCard::where([['user_id', $user_id], ['active', true]])->first();
            if ($check_exist == null || $user_id == 3) {
                $one               = new UserCard();
                $one->user_id      = $user_id;
                $one->card_id      = $data['card_id'];
                $one->total_money  = 0;
                $one->count        = 0;
                $one->created_by   = $this->user->id;
                $one->updated_by   = 0;
                $one->created_date = date('Y-m-d H:i:s');
                $one->updated_date = null;
                $one->vsys_date    = date('Y-m-d H:i:s');
                $one->active       = true;
                if (!$one->save()) {
                    DB::rollBack();
                    return false;
                }

                $two               = new UserCardMoney();
                $two->io_center_id = 0;
                $two->device_id    = 0;
                $two->user_card_id = $one->id;
                $two->status       = 'DPS';
                $two->money        = $one->total_money;
                $two->count        = $one->count;
                $two->created_by   = $this->user->id;
                $two->updated_by   = 0;
                $two->created_date = date('Y-m-d H:i:s');
                $two->updated_date = null;
                $two->vsys_date    = date('Y-m-d H:i:s');
                $two->active       = true;
                if (!$two->save()) {
                    DB::rollBack();
                    return false;
                }
            } else {
                if ($check_exist->total_money > 0) {
                    DB::rollBack();
                    return false;
                }

                $check_exist->card_id      = $data['card_id'];
                $check_exist->updated_by   = $this->user->id;
                $check_exist->updated_date = date('Y-m-d H:i:s');
                $check_exist->vsys_date    = date('Y-m-d H:i:s');
                $check_exist->active       = true;
                if (!$check_exist->update()) {
                    DB::rollBack();
                    return false;
                }

                if (UserCardMoney::whereActive(true)->where('user_card_id', $check_exist->id)->update(['active' => false]) <= 0) {
                    DB::rollBack();
                    return false;
                }

                $two               = new UserCardMoney();
                $two->io_center_id = 0;
                $two->device_id    = 0;
                $two->user_card_id = $check_exist->id;
                $two->status       = 'DPS';
                $two->money        = $check_exist->total_money;
                $two->count        = $check_exist->count;
                $two->created_by   = $this->user->id;
                $two->updated_by   = 0;
                $two->created_date = date('Y-m-d H:i:s');
                $two->updated_date = null;
                $two->vsys_date    = date('Y-m-d H:i:s');
                $two->active       = true;
                if (!$two->save()) {
                    DB::rollBack();
                    return false;
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
