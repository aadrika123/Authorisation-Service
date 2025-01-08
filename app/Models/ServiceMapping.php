<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMapping extends Model
{
    use HasFactory;

    //create warduser
    public function addModuleUlb($req)
    {
        $createdBy = Auth()->user()->id;
        $mUlbModulePermission = new ServiceMapping;
        $mUlbModulePermission->ulb_id = $req->ulbId;
        // $mUlbModulePermission->module_id = $req->moduleId;
        $mUlbModulePermission->service_id = $req->serviceId;
        $mUlbModulePermission->save();
    }

    //update ward user
    public function updateModuleUlb($req)
    {
        $mUlbModulePermission = ServiceMapping::find($req->id);
        $mUlbModulePermission->ulb_id      = $req->ulbId      ?? $mUlbModulePermission->ulb_id;
        $mUlbModulePermission->service_id     = $req->serviceId      ?? $mUlbModulePermission->service_id;
        // $mUlbModulePermission->is_admin     = $req->isAdmin     ?? $mUlbModulePermission->is_admin;
        $mUlbModulePermission->status = $req->isSuspended ?? $mUlbModulePermission->status;
        $mUlbModulePermission->save();
    }
}
