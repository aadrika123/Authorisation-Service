<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Repository\WorkflowMaster\Interface\iWorkflowMapRepository;
use Illuminate\Http\Request;

class WorkflowMapController extends Controller
{
    protected $wfMap;
    // Initializing Construct function
    public function __construct(iWorkflowMapRepository $wfMap)
    {
        $this->wfMap = $wfMap;
    }

    //Mapping 
    public function getRoleDetails(Request $req)
    {
        return $this->wfMap->getRoleDetails($req);
    }

    public function getUserById(Request $request)
    {
        return $this->wfMap->getUserById($request);
    }

    public function getWorkflowNameByUlb(Request $request)
    {
        // return 'Hii';
        return $this->wfMap->getWorkflowNameByUlb($request);
    }

    public function getRoleByUlb(Request $request)
    {
        return $this->wfMap->getRoleByUlb($request);
    }


}
