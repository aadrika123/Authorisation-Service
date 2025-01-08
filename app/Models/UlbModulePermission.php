<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UlbModulePermission extends Model
{
    use HasFactory;
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    /**
     * |add module with ulb
     */
    public function mapModuleUlB($req)
    {
        $data = new UlbModulePermission();
        $data->ulb_id = $req->ulbId;
        $data->module_id = $req->moduleId;
        $data->module_name = $req->moduleName;
        $data->save();
        return $data->id;
    }

    public function getModuleListByUlbId($req)
    {
        return self::select("*")
            ->where('ulb_id', $req->ulbId)
            ->get();
    }
    //  remove module from specific ulb
    public function removeModuleFromUlb($req)
    {
        return self::select(".*")
            ->where('id', $req->id)
            ->update([
                'is_suspended' => true
            ]);
    }

    public function check($user, $req)
    {
        return self::where('ulb_id', $user->ulb_id)
            ->where('module_id', $req->moduleId)
            ->where('is_suspended',)
            ->first();
    }

    //create warduser
    public function addModuleUlb($req)
    {
        $createdBy = Auth()->user()->id;
        $mUlbModulePermission = new UlbModulePermission;
        $mUlbModulePermission->ulb_id = $req->ulbId;
        $mUlbModulePermission->module_id = $req->moduleId;
        $mUlbModulePermission->created_by = $createdBy;
        $mUlbModulePermission->save();
    }

    //update ward user
    public function updateModuleUlb($req)
    {
        $mUlbModulePermission = UlbModulePermission::find($req->id);
        $mUlbModulePermission->ulb_id      = $req->ulbId      ?? $mUlbModulePermission->ulb_id;
        $mUlbModulePermission->module_id      = $req->moduleId      ?? $mUlbModulePermission->module_id;
        // $mUlbModulePermission->is_admin     = $req->isAdmin     ?? $mUlbModulePermission->is_admin;
        $mUlbModulePermission->is_suspended = $req->isSuspended ?? $mUlbModulePermission->is_suspended;
        $mUlbModulePermission->save();
    }
}
