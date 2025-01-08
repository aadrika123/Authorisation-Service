<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UlbService extends Model
{
    use HasFactory;
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    //create warduser
    public function addModuleUlb($req)
    {
        $createdBy = Auth()->user()->id;
        $mUlbModulePermission = new UlbService;
        $mUlbModulePermission->ulb_id = $req->ulbId;
        $mUlbModulePermission->service_id = $req->serviceId;
        $mUlbModulePermission->created_by = $createdBy;
        $mUlbModulePermission->save();
    }

    //update ward user
    public function updateModuleUlb($req)
    {
        $mUlbModulePermission = UlbService::find($req->id);
        $mUlbModulePermission->ulb_id      = $req->ulbId      ?? $mUlbModulePermission->ulb_id;
        $mUlbModulePermission->service_id      = $req->serviceId      ?? $mUlbModulePermission->service_id;
        // $mUlbModulePermission->is_admin     = $req->isAdmin     ?? $mUlbModulePermission->is_admin;
        $mUlbModulePermission->is_suspended = $req->isSuspended ?? $mUlbModulePermission->is_suspended;
        $mUlbModulePermission->save();
    }
}
