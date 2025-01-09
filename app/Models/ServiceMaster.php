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
    // get service 
    public function getServices($req)
    {
        return self::select(
            'service_masters.id',
            'service_masters.service_name',
            'module_masters.module_name',
            'service_masters.status',
            'service_masters.path'
        )
            ->join('module_masters', 'module_masters.id', 'service_masters.module_id')
            ->where('status', 1)
            ->where('service_masters.module_id', $req->moduleId)
            ->orderby('id', 'Desc')
            ->get();
    }
}
