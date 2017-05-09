<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Collection;
use Route;

class CollectionController extends Controller
{
    /* API METHOD */
    public function getReadAll(){
        $arr_datas = $this->readAll();
        return response()->json([
            'collections'     => $arr_datas['collections']
        ], 200);
    }

    public function getReadOne(Request $request){
        $id = Route::current()->parameter('id');$id = 0;
        $collection = $this->readOne($id);
        return response()->json([
            'collection' => $collection
        ], 200);
    }

    public function postCreateOne(Request $request){
        $this->createOne($request->input('collection'));
        $arr_datas = $this->readAll();
        return response()->json([
            'collections'     => $arr_datas['collections'],
        ], 200);
    }

    public function putUpdateOne(Request $request){
        $this->updateOne($request->input('collection'));
        $arr_datas = $this->readAll();
        return response()->json([
            'collections'     => $arr_datas['collections']
        ], 200);
    }

    public function patchDeactivateOne(Request $request){
        $id = $request->input('id');
        $this->deactivateOne($id);
        $arr_datas = $this->readAll();
        return response()->json([
            'collections'     => $arr_datas['collections'],
        ], 200);
    }

    public function deleteDeleteOne(Request $request){
        $id = Route::current()->parameter('id');
        $this->deleteOne($id);
        $arr_datas = $this->readAll();
        return response()->json([
            'collections'     => $arr_datas['collections']
        ], 200);
    }

    /* LOGIC METHOD */
    public function readAll(){
        $collections = Collection::where('collections.active', true)->get();
        return [
            'collections'     => $collections,
        ];
    }

    public function readOne($id){
        $collection = Collection::find($id);
        return $collection;
    }

    public function createOne($data){
        if($data['name'] != '' && $data['description'] != ''){
            $collection = new Collection;
            $collection->name = $data['name'];
            $collection->description = $data['description'];
            $collection->active = true;
            $collection->save();
        }
    }

    public function updateOne($data){
        if($data['name'] != '' && $data['description'] != ''){
            $collection = Collection::find($data['id']);
            $collection->name = $data['name'];
            $collection->description = $data['description'];
            $collection->active = true;
            $collection->update();
        }
    }

    public function deactivateOne($id){
        $collection = Collection::find($id);
        $collection->active = false;
        $collection->update();
    }

    public function deleteOne($id){
        $collection = Collection::find($id);
        $collection->delete();
    }
}
