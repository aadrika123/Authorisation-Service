<?php

namespace App\Models\Workflows;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WfRole extends Model
{
    use HasFactory;

    public function getRoleByUserUlbId($request)
    {
        try {
            $users = WfRole::select('wf_roles.*')
                ->where('ulb_ward_masters.ulb_id', $request->ulbId)
                ->where('wf_roleusermaps.user_id', $request->userId)
                ->join('wf_roleusermaps', 'wf_roleusermaps.wf_role_id', 'wf_roles.id')
                ->join('wf_ward_users', 'wf_ward_users.user_id', 'wf_roleusermaps.user_id')
                ->join('ulb_ward_masters', 'ulb_ward_masters.id', 'wf_ward_users.ward_id')
                ->first();
            if ($users) {
                return $users;
            }
            return responseMsg(false, "No Data Available", "");
        } catch (Exception $e) {
            return $e;
        }
    }
}
