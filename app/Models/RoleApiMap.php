<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleApiMap extends Model
{
    use HasFactory;


    /**
     * Create Role Map
     */
    public function addRoleMap($req)
    {
        $data = new RoleApiMap;
        $data->role_id      = $req->roleId;
        $data->api_mstr_id  = $req->apiId;
        $data->is_suspended = $req->isSuspended ?? false;
        $data->save();
    }

    /**
     * Update Role Map
     */
    public function updateRoleMap($req)
    {
        $data = RoleApiMap::find($req->id);
        $data->role_id      = $req->roleId ?? $data->role_id;
        $data->api_mstr_id  = $req->apiId ?? $data->api_mstr_id;
        $data->is_suspended = $req->isSuspended ?? $data->is_suspended;
        $data->save();
    }
}
