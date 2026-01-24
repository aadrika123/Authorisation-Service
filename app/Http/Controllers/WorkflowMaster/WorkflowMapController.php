<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\ModuleMaster;
use App\Models\UlbMaster;
use App\Models\UlbModulePermission;
use App\Models\UlbWardMaster;
use App\Models\Workflows\WfRole;
use App\Models\Workflows\WfRoleusermap;
use App\Models\Workflows\WfWorkflow;
use App\Models\Workflows\WfWorkflowrolemap;
use App\Repository\WorkflowMaster\Interface\iWorkflowMapRepository;
use Config;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowMapController extends Controller
{
    protected $wfMap;
    // Initializing Construct function
    public function __construct(iWorkflowMapRepository $wfMap)
    {
        $this->wfMap = $wfMap;
    }

    //Mapping
    public function getRoleDetails(Request $request)
    {

        $ulbId = auth()->user()->ulb_id;
        $request->validate([
            'workflowId' => 'required|int',
            'wfRoleId' => 'required|int'
        ]);

        $roledetails = new WfWorkflowrolemap();
        $get = $roledetails->getRoleDetails($request);
        return responseMsg(true, 'All Role Deatils', $get);
    }



    public function getUserById(Request $request)
    {
        return $this->wfMap->getUserById($request);
    }

    public function getWorkflowNameByUlb(Request $request)
    {
        return $this->wfMap->getWorkflowNameByUlb($request);
    }

    public function getRoleByUlb(Request $request)
    {
        return $this->wfMap->getRoleByUlb($request);
    }

    public function getWardByUlb(Request $request)
    {
        return $this->wfMap->getWardByUlb($request);
    }

    /**
     * Check ULB + Ward validity and optionally verify module permission.
     *
     * This API validates the given ULB ID and Ward ID, then returns:
     * - ULB details (name, logo)
     * - Ward details (ward name)
     * - Tax Collector (TC) details (name, mobile) if assigned
     * - If module_id is provided, it checks whether that module is permitted in that ULB
     *
     */
    // public function checkUlbWardModule(Request $request)
    // {
    //     $request->validate([
    //         'ulb_id' => 'required|integer',
    //         'ward_id' => 'nullable|integer',
    //         'module_id' => 'nullable|integer',
    //     ]);

    //     try {
    //         // Get ULB Details
    //         $ulb = UlbMaster::find($request->ulb_id);
    //         if (!$ulb) {
    //             throw new Exception("ULB not found");
    //         }

    //         $docUrl = Config::get('constants.DOC_URL');

    //         $responseData = [
    //             'ulb_name' => $ulb->ulb_name,
    //             'ulb_image' => $docUrl . '/' . $ulb->logo,
    //         ];

    //         // Get Ward Details
    //         if ($request->ward_id) {
    //             // Try to find by ward_name first (User might send Ward Number)
    //             $ward = UlbWardMaster::where('ulb_id', $request->ulb_id)
    //                 ->where('ward_name', $request->ward_id)
    //                 ->where('status', 1)
    //                 ->first();

    //             // If not found, try by ID
    //             if (!$ward) {
    //                 $ward = UlbWardMaster::where('ulb_id', $request->ulb_id)
    //                     ->where('id', $request->ward_id)
    //                     ->where('status', 1)
    //                     ->first();
    //             }

    //             if (!$ward) {
    //                 throw new Exception("Ward not found or not active in this ULB");
    //             }

    //             $responseData['ward_name'] = $ward->ward_name;

    //             // Get TC (Tax Collector) Details using RESOLVED $ward->id
    //             $tcUsers = User::join('wf_ward_users', 'wf_ward_users.user_id', 'users.id')
    //                 ->where('users.ulb_id', $request->ulb_id)
    //                 ->where('wf_ward_users.ward_id', $ward->id)
    //                 ->where('users.user_type', 'TC')
    //                 ->where('users.suspended', false)
    //                 ->where('wf_ward_users.is_suspended', false)
    //                 ->select('users.name', 'users.user_name', 'users.mobile', 'users.photo')
    //                 ->get();

    //             $responseData['tc_details'] = $tcUsers->map(function ($tc) use ($docUrl, $ward) {
    //                 return [
    //                     'tc_name' => $tc->name ?: $tc->user_name,
    //                     'tc_mobile' => $tc->mobile,
    //                     'tc_image' => $tc->photo ? $docUrl . '/' . $tc->photo : null,
    //                     'ward_name' => $ward->ward_name,
    //                 ];
    //             });
    //         } else {
    //             // Get All TCs if no ward_id
    //             $allTcs = User::join('wf_ward_users', 'wf_ward_users.user_id', 'users.id')
    //                 ->join('ulb_ward_masters', 'ulb_ward_masters.id', 'wf_ward_users.ward_id')
    //                 ->where('users.ulb_id', $request->ulb_id)
    //                 ->where('users.user_type', 'TC')
    //                 ->where('users.suspended', false)
    //                 ->where('wf_ward_users.is_suspended', false)
    //                 ->select('users.name', 'users.user_name', 'users.mobile', 'users.photo', 'ulb_ward_masters.ward_name')
    //                 ->get();

    //             $responseData['tc_details'] = $allTcs->map(function ($tc) use ($docUrl) {
    //                 return [
    //                     'tc_name' => $tc->name ?: $tc->user_name,
    //                     'tc_mobile' => $tc->mobile,
    //                     'tc_image' => $tc->photo ? $docUrl . '/' . $tc->photo : null,
    //                     'ward_name' => $tc->ward_name,
    //                 ];
    //             });
    //         }

    //         // If module_id is provided, check permission
    //         if ($request->module_id) {
    //             // Check if module exists and is active
    //             $module = ModuleMaster::where('id', $request->module_id)
    //                 ->where('is_suspended', false)
    //                 ->first();

    //             if ($module) {
    //                 $permission = UlbModulePermission::where('ulb_id', $request->ulb_id)
    //                     ->where('module_id', $request->module_id)
    //                     ->where('is_suspended', false)
    //                     ->first();

    //                 if ($permission) {
    //                     $responseData['module_permission'] = $module;
    //                 } else {
    //                     $responseData['module_permission'] = null;
    //                 }
    //             } else {
    //                 $responseData['module_permission'] = null;
    //             }
    //         }

    //         return responseMsg(true, "Data Retrieved Successfully", $responseData);

    //     } catch (Exception $e) {
    //         return responseMsg(false, $e->getMessage(), null);
    //     }
    // }
    public function checkUlbWardModule(Request $request)
    {
        $request->validate([
            'ulb_id' => 'required|integer',
            'ward_id' => 'nullable|integer',
            'module_id' => 'nullable|integer',
        ]);

        try {
            // Get ULB Details
            $ulb = UlbMaster::find($request->ulb_id);
            if (!$ulb) {
                throw new Exception("ULB not found");
            }

            $docUrl = Config::get('constants.DMS_URL');
            $ulbUrl = Config::get('constants.DOC_URL');


            $responseData = [
                'ulb_name' => $ulb->ulb_name,
                'ulb_image' => $ulbUrl . '/' . $ulb->logo,
            ];

            $tcUsers = collect(); // default empty collection

            // Get Ward Details
            if ($request->ward_id) {
                // Try to find by ward_name first
                $ward = UlbWardMaster::where('ulb_id', $request->ulb_id)
                    ->where('ward_name', $request->ward_id)
                    ->where('status', 1)
                    ->first();

                // If not found, try by ID
                if (!$ward) {
                    $ward = UlbWardMaster::where('ulb_id', $request->ulb_id)
                        ->where('id', $request->ward_id)
                        ->where('status', 1)
                        ->first();
                }

                if (!$ward) {
                    throw new Exception("Ward not found or not active in this ULB");
                }

                $responseData['ward_name'] = $ward->ward_name;

                // Get TC Users (WARD WISE)
                $tcUsers = User::join('wf_ward_users', 'wf_ward_users.user_id', '=', 'users.id')
                    ->join('ulb_ward_masters', 'ulb_ward_masters.id', '=', 'wf_ward_users.ward_id')
                    ->where('users.ulb_id', $request->ulb_id)
                    ->where('wf_ward_users.ward_id', $ward->id)
                    ->where('users.user_type', 'TC')
                    ->where('users.suspended', false)
                    ->where('wf_ward_users.is_suspended', false)
                    ->select(
                        'users.id as user_id',
                        'users.name',
                        'users.mobile',
                        'users.email',
                        'users.photo',
                        'ulb_ward_masters.ward_name'
                    )
                    ->get();
            } else {
                // Get All TCs (ULB WISE)
                $tcUsers = User::join('wf_ward_users', 'wf_ward_users.user_id', '=', 'users.id')
                    ->join('ulb_ward_masters', 'ulb_ward_masters.id', '=', 'wf_ward_users.ward_id')
                    ->where('users.ulb_id', $request->ulb_id)
                    ->where('users.user_type', 'TC')
                    ->where('users.suspended', false)
                    ->where('wf_ward_users.is_suspended', false)
                    ->select(
                        'users.id as user_id',
                        'users.name',
                        'users.mobile',
                        'users.email',
                        'users.photo',
                        'ulb_ward_masters.ward_name'
                    )
                    ->get();
            }

            // check permission
            $ulbPermission = null;
            $allowedUserIds = [];
            $moduleName = null;

            if ($request->module_id) {
                // Check if module exists and active
                $module = ModuleMaster::where('id', $request->module_id)
                    ->where('is_suspended', false)
                    ->first();

                if ($module) {
                    $moduleName = $module->module_name;
                    // Check ULB level permission
                    $ulbPermission = UlbModulePermission::where('ulb_id', $request->ulb_id)
                        ->where('module_id', $request->module_id)
                        ->where('is_suspended', false)
                        ->first();

                    $responseData['module_permission'] = $ulbPermission ? $module : null;

                    // ✅ If ULB is permitted, Check Granular User Permissions
                    if ($ulbPermission) {
                        $tcIds = $tcUsers->pluck('user_id')->unique()->toArray();
                        if (!empty($tcIds)) {
                            // Find users who have roles in workflows linked to this module
                            $allowedUserIds = DB::table('wf_roleusermaps as rum')
                                ->join('wf_workflowrolemaps as wrm', 'wrm.wf_role_id', '=', 'rum.wf_role_id')
                                ->join('wf_workflows as wf', 'wf.id', '=', 'wrm.workflow_id')
                                ->join('wf_masters as wm', 'wm.id', '=', 'wf.wf_master_id')
                                ->where('wm.module_id', $request->module_id)
                                ->whereIn('rum.user_id', $tcIds)
                                ->where('rum.is_suspended', false)
                                ->where('wrm.is_suspended', false)
                                ->where('wf.is_suspended', false)
                                ->where('wm.is_suspended', false)
                                ->pluck('rum.user_id')
                                ->unique()
                                ->toArray();
                        }
                    }

                } else {
                    $responseData['module_permission'] = null;
                }
            }

            // FINAL TC OUTPUT
            $tcDetails = $tcUsers->map(function ($tc) use ($docUrl, $allowedUserIds, $request, $moduleName) {
                $tcData = [
                    'tc_name' => $tc->name ?: $tc->name,
                    'tc_mobile' => $tc->mobile,
                    'tc_image' => $tc->photo ? $docUrl . '/' . $tc->photo : null,
                    'ward_name' => $tc->ward_name ?? null,
                    'email' => $tc->email,
                    'module' => $moduleName,
                ];

                if ($request->module_id) {
                    $hasPermission = in_array($tc->user_id, $allowedUserIds);
                    // Add permission flag internally for filtering
                    $tcData['has_permission'] = $hasPermission;
                    // Note: User doesn't want the boolean shown, just the filtered list
                } else {
                    $tcData['has_permission'] = true; // Include all if no module specified
                }

                return $tcData;
            });

            // Filter if module_id is provided
            if ($request->module_id) {
                $tcDetails = $tcDetails->filter(function ($item) {
                    return $item['has_permission'] === true;
                });
            }

            // Remove internal flag and re-index
            $responseData['tc_details'] = $tcDetails->map(function ($item) {
                unset($item['has_permission']);
                return $item;
            })->values();

            return responseMsg(true, "Data Retrieved Successfully", $responseData);

        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), null);
        }
    }

    public function getUserByRole(Request $request)
    {
        return $this->wfMap->getUserByRole($request);
    }


    //----------------------------------------------------------------------
    //By Model
    //----------------------------------------------------------------------
    public function getRoleByWorkflow(Request $request)
    {
        $ulbId = authUser()->ulb_id;
        $request->validate([
            'workflowId' => 'required|int'
        ]);

        $roledetails = new WfWorkflowrolemap();
        $get = $roledetails->getRoleByWorkflow($request, $ulbId);
        return responseMsg(true, 'All Role Details', $get);
    }

    public function getUserByWorkflow(Request $request)
    {
        $request->validate([
            'workflowId' => 'required|int'
        ]);

        $users = new WfWorkflowrolemap();
        $getusers = $users->getUserByWorkflow($request);
        return responseMsg(true, 'All user Details', $getusers);
    }


    public function getWardsInWorkflow(Request $request)
    {
        try {
            $wards = new WfWorkflowrolemap();
            $getwards = $wards->getWardsInWorkflow($request);
            return responseMsg(true, 'All wards in Workflow', $getwards);
        } catch (Exception $e) {
            return response()->json(false, $e->getmessage());
        }
    }


    public function getUlbInWorkflow(Request $request)
    {
        try {
            $ulb = new WfWorkflow();
            $getulb = $ulb->getUlbInWorkflow($request);
            return responseMsg(true, 'All Ulb in Workflow', $getulb);
        } catch (Exception $e) {
            return response()->json(false, $e->getmessage());
        }
    }


    public function getWorkflowByRole(Request $request)
    {
        try {
            $workflow = new WfWorkflowrolemap();
            $getworkflow = $workflow->getWorkflowByRole($request);
            return responseMsg(true, 'Workflow By Role', $getworkflow);
        } catch (Exception $e) {
            return response()->json(false, $e->getmessage());
        }
    }


    public function getUserByRoleId(Request $request)
    {
        try {
            $workflow = new WfRoleusermap();
            $getworkflow = $workflow->getUserByRoleId($request);
            return responseMsg(true, 'User By Role', $getworkflow);
        } catch (Exception $e) {
            return responseMsg(false, $e->getmessage(), '');
        }
    }

    //workking
    //table = ulb_ward_master
    //ulbId->WardName
    //wards in ulb
    public function getWardByRole(Request $request)
    {

        try {
            $ward = new WfRoleusermap();
            $getward = $ward->getWardByRole($request);
            return responseMsg(true, 'Ward  By Role', $getward);
        } catch (Exception $e) {
            return responseMsg(false, $e->getmessage(), '');
        }
    }

    public function workflowbyModule(Request $request)
    {
        try {
            $request->validate([
                'moduleId' => 'required|int'
            ]);
            $mWfWorkflow = new WfWorkflow();
            $moduleList = $mWfWorkflow->workflowbyModule($request->moduleId);
            return responseMsg(true, 'Workflow List', $moduleList);
        } catch (Exception $e) {
            return responseMsg(false, $e->getmessage(), '');
        }
    }

    /**
     * |
     */
    //working
    //get workflow by ulb and master id
    public function getWorkflow(Request $request)
    {
        try {
            $request->validate([
                "ulbId" => "required|numeric",
                "workflowMstrId" => "required|numeric",
            ]);
            $workflow = WfWorkflow::select('wf_workflows.*')
                ->where('ulb_id', $request->ulbId)
                ->where('wf_master_id', $request->workflowMstrId)
                ->where('is_suspended', false)
                ->first();

            return responseMsg(true, "Workflow Details", $workflow);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | in use
     */
    // working
    // workflow in ulb
    public function getWorkflowInUlb(Request $request)
    {
        try {
            $ulbId = $request->ulbId ?? authUser()->ulb_id;
            if (!$ulbId)
                throw new Exception("ulbId is required");

            $users = WfWorkflow::select('wf_masters.workflow_name', 'wf_workflows.id')
                ->join('wf_masters', 'wf_masters.id', '=', 'wf_workflows.wf_master_id')
                ->where('wf_workflows.ulb_id', $ulbId)
                ->where('wf_masters.is_suspended', false)
                ->where('wf_workflows.is_suspended', false)
                ->get();
            return responseMsg(true, "Data Retrived", $users);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
