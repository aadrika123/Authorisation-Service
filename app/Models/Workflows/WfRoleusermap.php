<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WfRoleusermap extends Model
{
    use HasFactory;

    /**
     * | Get Role details by User Id
     */
    public function getRoleDetailsByUserId($userId)
    {
        return WfRoleusermap::select(
            'wf_roles.role_name AS roles',
            'wf_roles.id AS roleId'
        )
            ->join('wf_roles', 'wf_roles.id', '=', 'wf_roleusermaps.wf_role_id')
            ->where('wf_roleusermaps.user_id', $userId)
            ->where('wf_roleusermaps.is_suspended', false)
            ->orderByDesc('wf_roles.id')
            ->get();
    }
}
