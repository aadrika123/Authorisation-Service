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
                'is_active' => false
            ]);
    }

    public function check($user, $req)
    {
        return self::where('ulb_id', $user->ulb_id)
            ->where('module_id', $req->moduleId)
            ->where('is_active', false)
            ->first();
    }
}
