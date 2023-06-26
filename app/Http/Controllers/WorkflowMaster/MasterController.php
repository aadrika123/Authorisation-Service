<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\Workflows\WfMaster;
use Illuminate\Http\Request;
use Exception;

class MasterController extends Controller
{
    /**
     * Controller for Add, Update, View , Delete of Workflow Master Table
     * -------------------------------------------------------------------------------------------------
     * Created On-07-10-2022
     * Created By-Mrinal Kumar
     * Modification On: 19-12-2022
     * Status : Closed
     * -------------------------------------------------------------------------------------------------
     */


    //create master
    public function createMaster(Request $req)
    {
        try {
            $req->validate([
                'workflowName' => 'required'
            ]);

            $create = new WfMaster();
            $create->addMaster($req);

            return responseMsg(true, "Successfully Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //update master
    public function updateMaster(Request $req)
    {
        try {
            $req->validate([
                'workflowName' => 'required',
                'id' => 'required'
            ]);
            $update = new WfMaster();
            $list  = $update->updateMaster($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //master list by id
    public function masterbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $listById = new WfMaster();
            $list  = $listById->listbyId($req);

            return responseMsg(true, "Master List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //all master list
    public function getAllMaster()
    {
        try {

            $list = new WfMaster();
            $masters = $list->listMaster();

            return responseMsg(true, "All Master List", $masters);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    //delete master
    public function deleteMaster(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new WfMaster();
            $delete->deleteMaster($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
