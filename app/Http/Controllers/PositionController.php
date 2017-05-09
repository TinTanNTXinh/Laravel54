<?php

namespace App\Http\Controllers;

use App\Position;
use Illuminate\Http\Request;
use League\Flysystem\Exception;
use Route;
use DB;

class PositionController extends Controller
{
    private $firstDayUTS;
    private $lastDayUTS;
    private $format_date;
    private $table_name;

    function __construct()
    {
        $this->format_date = '%d/%m/%Y';
        $this->firstDayUTS = mktime(0, 0, 0, date("m"), 1, date("Y"));
        $this->lastDayUTS  = mktime(0, 0, 0, date("m"), date('t'), date("Y"));
        $this->table_name  = 'position';
    }

    /* API METHOD */
    public function getReadAll()
    {
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    public function getReadOne(Request $request)
    {
        $id  = Route::current()->getParameter('id');
        $one = $this->readOne($id);
        return response()->json($one, 200);
    }

    public function postCreateOne(Request $request)
    {
        if (!$this->createOne($request->input($this->table_name)))
            return response()->json(['msg' => 'Create failed!'], 404);
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 201);
    }

    public function putUpdateOne(Request $request)
    {
        if (!$this->updateOne($request->input($this->table_name)))
            return response()->json(['msg' => 'Update failed!'], 404);
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
        $id = Route::current()->getParameter('id');
        if (!$this->deleteOne($id))
            return response()->json(['msg' => 'Delete failed!'], 404);
        $arr_datas = $this->readAll();
        return response()->json($arr_datas, 200);
    }

    /* LOGIC METHOD */
    private function readAll()
    {
        $positions    = Position::whereActive(true)->whereNotIn('id', [1, 2])->get();

        return [
            'positions'   => $positions,
            'first_day'    => date("d-m-Y", $this->firstDayUTS),
            'last_day'     => date("d-m-Y", $this->lastDayUTS),
            'today'        => date("d-m-Y")
        ];
    }

    private function readOne($id)
    {
        $one = Position::find($id);
        return ['position' => $one];
    }

    private function createOne($data)
    {
        if (!$this->validateInput($data)) return false;
        try {
            DB::beginTransaction();
            $one               = new Position();
            $one->code         = $data['code'];
            $one->name         = $data['name'];
            $one->description  = $data['description'];
            $one->active       = true;
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

    private function updateOne($data)
    {
        if (!$this->validateInput($data)) return false;
        try {
            DB::beginTransaction();
            $one               = Position::find($data['id']);
            $one->code         = $data['code'];
            $one->name         = $data['name'];
            $one->description  = $data['description'];
            $one->active       = true;
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

    private function deactivateOne($id)
    {
        try {
            DB::beginTransaction();
            $one         = Position::find($id);
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

    private function deleteOne($id)
    {
        try {
            DB::beginTransaction();
            $one = Position::find($id);
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

    private function validateInput($data)
    {
        if (!$data['code']) return false;
        if (!$data['name']) return false;
        return true;
    }
}
