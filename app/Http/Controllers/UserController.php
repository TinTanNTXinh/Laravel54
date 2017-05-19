<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Distributor;
use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use App\Position;
use App\Supplier;
use App\User;
use App\File;
use App\UserRole;
use League\Flysystem\Exception;
use Route;
use DB;
use Hash;
use App\Traits\UserHelper;
use App\Traits\DBHelper;

class UserController extends Controller implements ICrud, IValidate
{
    use UserHelper, DBHelper;

    private $first_day, $last_day, $today;
    private $user;
    private $format_date, $format_time;
    private $table_name;
    private $carbon_format_date;
    private $fake_pwd;
    private $auth_unam_pwd;
    private $skeleton;

    private $class_name = User::class;

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

        $this->carbon_format_date = 'd/m/Y';
        $this->fake_pwd           = substr(config('app.key'), 10);
        $this->auth_unam_pwd      = ($this->user->position_id == 1) ? true : false;
        $this->table_name         = 'user';
        $this->skeleton = User::where('users.active', true)
            ->leftJoin('positions', 'positions.id', '=', 'users.position_id')
            ->leftJoin('user_cards', 'user_cards.id', '=', 'users.id');
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
        $users        = $this->skeleton;
        $users        = $this->searchFieldName($users, 'users.dis_or_sup', $this->user->dis_or_sup);
        $users        = $users->get();
        $positions    = Position::whereActive(true)->whereNotIn('id', [1, 2])->get();
        $distributors = Distributor::whereActive(true)->get();
        $suppliers    = Supplier::whereActive(true)->get();

        return [
            'fake_pwd'         => $this->fake_pwd,
            'users'            => $users,
            'distributors'     => $distributors,
            'suppliers'        => $suppliers,
            'positions'        => $positions,
            'first_day'        => $this->first_day,
            'last_day'         => $this->last_day,
            'today'            => $this->today,
            'placeholder_code' => $this->generateCode($this->class_name, 'USER'),
            'auth'             => $this->auth_unam_pwd
        ];
    }

    public function readOne($id)
    {
        $one = User::find($id);
        return ['user' => $one];
    }

    public function createOne($data)
    {
        try {
            DB::beginTransaction();
            # User
            $one           = new User();
            $one->code     = $data['code'] ? $data['code'] : $this->generateCode($this->class_name, 'USER');
            $one->fullname = $data['fullname'];
            if ($this->auth_unam_pwd) {
                $one->username = $data['username'];
                $one->password = Hash::make($data['password']);
            } else {
                $one->username = null;
                $one->password = null;
            }
            $one->address = $data['address'];
            $one->phone   = $data['phone'];
            // $one->birthday      = Carbon::createFromFormat($this->carbon_format_date, $data['birthday']);
            $one->birthday      = date('Y-m-d');
            $one->sex           = $data['sex'];
            $one->email         = $data['email'];
            $one->note          = $data['note'];
            $one->created_by    = $this->user->id;
            $one->updated_by    = 0;
            $one->created_date  = date('Y-m-d H:i:s');
            $one->updated_date  = null;
            $one->active        = true;
            $one->position_id   = $data['position_id'];
            $one->dis_or_sup    = $data['dis_or_sup'];
            $one->dis_or_sup_id = $data['dis_or_sup_id'];
            if (!$one->save()) {
                DB::rollback();
                return false;
            }

            # UserRole

            # Find Array Role
            $roles = [];
            switch ($one->dis_or_sup) {
                case 'sup':
                    switch ($one->position_id) {
                        case 3:
                            array_push($roles, 15); //ReportSupplier
                            array_push($roles, 12); //ButtonProduct
                            array_push($roles, 6); //Product
                            break;
                        case 4:
                        case 5:
                            array_push($roles, 17); //ReportStaffInput
                            break;
                        default:
                            break;
                    }
                    break;
                case 'dis':
                    switch ($one->position_id) {
                        case 3:
                            array_push($roles, 16); //ReportDistributor
                            array_push($roles, 12);
                            array_push($roles, 6);
                            break;
                        case 4:
                        case 5:
                            array_push($roles, 16);
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }

            # Add Role
            foreach($roles as $role_id) {
                $user_role               = new UserRole();
                $user_role->user_id      = $one->id;
                $user_role->role_id      = $role_id;
                $user_role->created_by   = $this->user->id;
                $user_role->updated_by   = 0;
                $user_role->created_date = date('Y-m-d H:i:s');
                $user_role->updated_date = null;
                $user_role->active       = true;
                if (!$user_role->save()) {
                    DB::rollback();
                    return false;
                }
            }

            # File
            $two               = new File();
            $two->code         = $this->generateCode(File::class, 'FILE');
            $two->name         = $one->fullname;
            $two->extension    = 'png';
            $two->mime_type    = 'image/png';
            $two->path         = 'assets/img/a' . 'default' . '.png';
            $two->size         = 0;
            $two->table_name   = 'users';
            $two->table_id     = $one->id;
            $two->note         = '';
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
            # User
            $one           = User::find($data['id']);
            $one->code     = $data['code'];
            $one->fullname = $data['fullname'];

            if ($this->auth_unam_pwd) {
                $one->username = $data['username'];
                if ($data['password'] != $this->fake_pwd)
                    $one->password = Hash::make($data['password']);
            }

            $one->address = $data['address'];
            $one->phone   = $data['phone'];
            // $one->birthday      = Carbon::createFromFormat($this->carbon_format_date, $data['birthday']);
            $one->birthday      = date('Y-m-d');
            $one->sex           = $data['sex'];
            $one->email         = $data['email'];
            $one->note          = $data['note'];
            $one->updated_by    = $this->user->id;
            $one->updated_date  = date('Y-m-d H:i:s');
            $one->active        = true;
            $one->position_id   = $data['position_id'];
            $one->dis_or_sup    = $data['dis_or_sup'];
            $one->dis_or_sup_id = $data['dis_or_sup_id'];
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            # UserRole
            if (UserRole::whereActive(true)->where('user_id', $one->id)->delete() <= 0) {
                DB::rollBack();
                return false;
            }

            $user_role          = new UserRole();
            $user_role->user_id = $one->id;
            switch ($one->dis_or_sup) {
                case 'sup':
                    switch ($one->position_id) {
                        case 3:
                            $user_role->role_id = 15;
                            break;
                        case 4:
                        case 5:
                            $user_role->role_id = 17;
                            break;
                        default:
                            break;
                    }
                    break;
                case 'dis':
                    switch ($one->position_id) {
                        case 3:
                        case 4:
                        case 5:
                            $user_role->role_id = 16;
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
            $user_role->created_by   = $this->user->id;
            $user_role->updated_by   = 0;
            $user_role->created_date = date('Y-m-d H:i:s');
            $user_role->updated_date = null;
            $user_role->active       = true;
            if (!$user_role->save()) {
                DB::rollback();
                return false;
            }

            # File
            if (File::whereActive(true)->where([['table_name', 'users'], ['table_id', $one->id]])->delete() <= 0) {
                DB::rollBack();
                return false;
            }

            $two               = new File();
            $two->code         = $one->username;
            $two->name         = $one->fullname;
            $two->extension    = 'png';
            $two->mime_type    = 'image/png';
            $two->path         = 'assets/img/a' . 'default' . '.png';
            $two->size         = 0;
            $two->table_name   = 'users';
            $two->table_id     = $one->id;
            $two->note         = '';
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

    public function deactivateOne($id)
    {
        try {
            DB::beginTransaction();
            # User
            $one         = User::find($id);
            $one->active = false;
            if (!$one->update()) {
                DB::rollBack();
                return false;
            }

            # UserRole
            if (UserRole::whereActive(true)->where('user_id', $one->id)->update(['active' => false]) <= 0) {
                DB::rollBack();
                return false;
            }

            # File
            if (File::whereActive(true)->where([['table_name', 'users'], ['table_id', $one->id]])->update(['active' => false]) <= 0) {
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
            # User
            $one = User::find($id);
            if (!$one->delete()) {
                DB::rollBack();
                return false;
            }

            # UserRole
            if (UserRole::whereActive(true)->where('user_id', $one->id)->delete() <= 0) {
                DB::rollBack();
                return false;
            }

            # File
            if (File::whereActive(true)->where([['table_name', 'users'], ['table_id', $one->id]])->delete() <= 0) {
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
        $dis_or_sup  = $filter['dis_or_sup'];
        $supplier_id = $filter['supplier_id'];
        $distributor_id = $filter['distributor_id'];
        $position_id = $filter['position_id'];
        $code        = $filter['code'];
        $fullname    = $filter['fullname'];
        $username    = $filter['username'];
        $phone       = $filter['phone'];

        $users = $this->skeleton;

        $users = $this->searchFieldName($users, 'users.dis_or_sup', $dis_or_sup);

        switch ($dis_or_sup) {
            case 'sup':
                $users = $users
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'users.dis_or_sup_id')
                    ->select('users.*'
                        , 'user_cards.total_money'
                        , DB::raw('CONCAT(FORMAT(user_cards.total_money, 0), \' đ\') as fc_total_money')
                        , 'positions.name as position_name'
                        , 'suppliers.id as supplier_id', 'suppliers.name as supplier_name'
                        , DB::raw('DATE_FORMAT(users.birthday,\'' . $this->format_date . '\') as birthday'));

                $users = $this->searchFieldName($users, 'users.dis_or_sup_id', $supplier_id);
                break;
            case 'dis':
                $users = $users
                    ->leftJoin('distributors', 'distributors.id', '=', 'users.dis_or_sup_id')
                    ->select('users.*'
                        , 'user_cards.total_money'
                        , DB::raw('CONCAT(FORMAT(user_cards.total_money, 0), \' đ\') as fc_total_money')
                        , 'positions.name as position_name'
                        , 'distributors.id as distributor_id', 'distributors.name as distributor_name'
                        , DB::raw('DATE_FORMAT(users.birthday,\'' . $this->format_date . '\') as birthday'));

                $users = $this->searchFieldName($users, 'users.dis_or_sup_id', $distributor_id);
                break;
            default:
                break;
        }

        $users = $this->searchFromDateToDate($users, 'users.created_date', $from_date, $to_date);

        $users = $this->searchRangeDate($users, 'users.created_date', $range);

        $users = $this->searchFieldName($users, 'users.position_id', $position_id);
        $users = $this->searchFieldName($users, 'users.code', $code);
        $users = $this->searchFieldName($users, 'users.fullname', $fullname);
        $users = $this->searchFieldName($users, 'users.username', $username);
        $users = $this->searchFieldName($users, 'users.phone', $phone);

        return [
            'users' => $users->get()
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
        if (!$data['fullname']) return false;
        if (!$data['position_id']) return false;
        if (!$data['dis_or_sup']) return false;
        if (!$data['dis_or_sup_id']) return false;
        return true;
    }

    public function validateLogic($data)
    {
        $msg_error = [];

        $skip_id = isset($data['id']) ? [$data['id']] : [];

        if ($data['code'] && $this->checkExistData(User::class, 'code', $data['code'], $skip_id))
            array_push($msg_error, 'Mã người dùng đã tồn tại.');

        if ($this->checkExistData(User::class, 'fullname', $data['fullname'], $skip_id))
            array_push($msg_error, 'Tên người dùng đã tồn tại.');

        if($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            array_push($msg_error, 'Địa chỉ email không hợp lệ.');
        }

        return [
            'status' => count($msg_error) > 0 ? false : true,
            'errors' => $msg_error
        ];
    }

    /** My Function */
    public function postChangePassword(Request $request)
    {
        $data      = $request->input('data');
        $arr_datas = $this->changePassword($data);
        return response()->json($arr_datas, $arr_datas['status_code']);
    }

    public function changePassword($data)
    {
        if ($data['password'] == $data['new_password'])
            return ['error' => 'Mật khẩu cũ và mới không được trùng nhau.', 'status_code' => 404];
        $user = User::find($this->user->id);

        if (!$user) {
            return ['error' => 'Người dùng không tồn tại.', 'error_en' => 'user is not exist', 'status_code' => 401];
        }
        $password_check = Hash::check($data['password'], $user->password);
        if (!$password_check) {
            return ['error' => 'Mật khẩu không hợp lệ.', 'error_en' => 'password is not correct', 'status_code' => 401];
        }

        $user->password = Hash::make($data['new_password']);
        if (!$user->update())
            return ['error' => 'Kết nối đến máy chủ thất bại, vui lòng làm mới trình duyệt và thử lại.', 'status_code' => 404];
        return ['status_code' => 200];
    }

}
