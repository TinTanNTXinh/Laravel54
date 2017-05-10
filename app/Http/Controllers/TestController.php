<?php

namespace App\Http\Controllers;

use App\Interfaces\ICrud;
use App\Interfaces\IValidate;
use Illuminate\Http\Request;

class TestController extends Controller implements ICrud, IValidate
{
    /** API METHOD */
    public function getReadAll()
    {
        // TODO: Implement getReadAll() method.
    }

    public function getReadOne(Request $request)
    {
        // TODO: Implement getReadOne() method.
    }

    public function postCreateOne(Request $request)
    {
        // TODO: Implement postCreateOne() method.
    }

    public function putUpdateOne(Request $request)
    {
        // TODO: Implement putUpdateOne() method.
    }

    public function patchDeactivateOne(Request $request)
    {
        // TODO: Implement patchDeactivateOne() method.
    }

    public function deleteDeleteOne(Request $request)
    {
        // TODO: Implement deleteDeleteOne() method.
    }

    public function getSearchOne()
    {
        // TODO: Implement getSearchOne() method.
    }

    /** LOGIC METHOD */
    public function readAll()
    {
        // TODO: Implement readAll() method.
    }

    public function readOne($id)
    {
        // TODO: Implement readOne() method.
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
        // TODO: Implement deactivateOne() method.
    }

    public function deleteOne($id)
    {
        // TODO: Implement deleteOne() method.
    }

    public function searchOne($filter)
    {
        // TODO: Implement searchOne() method.
    }

    /** VALIDATION */
    public function validateInput($data)
    {
        // TODO: Implement validateInput() method.
    }

    public function validateEmpty($data)
    {
        // TODO: Implement validateEmpty() method.
    }

    public function validateLogic($data)
    {
        // TODO: Implement validateLogic() method.
    }

}
