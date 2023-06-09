<?php

namespace App\Repository\WorkflowMaster\Concrete;

use App\Repository\WorkflowMaster\Interface\iWorkflowRoleUserMapRepository;
use Illuminate\Http\Request;
use App\Models\Workflows\WfRoleusermap;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Repository for Save Edit and View 
 * Parent Controller -App\Controllers\WorkflowRoleUserMapController
 * -------------------------------------------------------------------------------------------------
 * Created On-07-10-2022 
 * Created By-Mrinal Kumar
 * -------------------------------------------------------------------------------------------------
 * 
 */



class WorkflowRoleUserMapRepository implements iWorkflowRoleUserMapRepository
{
    private $_redis;
    public function __construct()
    {
        // $this->_redis = Redis::connection();
    }

    /**
     * | Get All Permitted Roles By User ID
     * | @param Request req
     * | @var query 
     * | Status-Closed
     * | Query Run Time-400 ms
     * | Rating-1
        | handel the suspended 
     */
    public function getRolesByUserId($req)
    {
        try {
            // $roles = json_decode(Redis::get('roles-user-u-' . $req->userId));
            // if (!$roles) {
            $userId = authUser()->id;
            $query = "SELECT 
                                r.id AS role_id,
                                r.role_name,
                                rum.wf_role_id,
                                (CASE 
                                WHEN rum.wf_role_id IS NOT NULL THEN TRUE 
                                ELSE 
                                FALSE END) AS permission_status,
                                rum.user_id

                        FROM wf_roles r

                LEFT JOIN (SELECT * FROM wf_roleusermaps WHERE user_id= $userId AND is_suspended = false) rum ON rum.wf_role_id=r.id
                WHERE r.is_suspended = false
                AND r.status = 1
                ";

            $roles = DB::select($query);
            //$this->_redis->set('roles-user-u-' . $req->userId, json_encode($roles));               // Caching Should Be flush on New role Permission to the user
            // }
            return responseMsg(true, "Role Permissions", remove_null($roles));
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Enable or Disable the User Role Permission
     * | @param req
     * | Status-closed
     * | RunTime Complexity-353 ms
     * | Rating-2
     */
    public function updateUserRoles($req)
    {
        try {
            // Redis::del('roles-user-u-' . $req->userId);                                 // Flush Key of the User Role Permission

            $userRoles = WfRoleusermap::where('wf_role_id', $req->roleId)
                ->where('user_id', $req->userId)
                ->first();

            if ($userRoles) {                                                           // If Data Already Existing
                switch ($req->status) {
                    case 1:
                        $userRoles->is_suspended = 0;
                        $userRoles->save();
                        return responseMsg(true, "Successfully Enabled the Role Permission for User", "");
                        break;
                    case 0:
                        $userRoles->is_suspended = 1;
                        $userRoles->save();
                        return responseMsg(true, "Successfully Disabled the Role Permission", "");
                        break;
                }
            }

            $userRoles = new WfRoleusermap();
            $userRoles->wf_role_id = $req->roleId;
            $userRoles->user_id = $req->userId;
            $userRoles->created_by = authUser()->id;
            $userRoles->save();

            return responseMsg(true, "Successfully Enabled the Role Permission for the User", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //role of logged in user
    public function roleUser()
    {
        $userId = authUser()->id;
        $role = WfRoleusermap::select('wf_roleusermaps.*')
            ->where('user_id', $userId)
            ->where('is_suspended', false)
            ->get();
        return $role;
    }
}
