<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMaster extends Model
{
    use HasFactory;

    //create warduser
    public function store($req)
    {
        // $createdBy = Auth()->user()->id;
        $mUlbModulePermission = new ServiceMaster;
        $mUlbModulePermission->service_name = $req->serviceName;
        $mUlbModulePermission->path = $req->path;
        $mUlbModulePermission->module_id = $req->moduleId;
        $mUlbModulePermission->save();
    }
}
