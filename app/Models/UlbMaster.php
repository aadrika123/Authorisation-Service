<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UlbMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulb_name',
        'ulb_type',
        'city_id',
        'state_id',
        'district_id',
        'district_code',
        'category',
        'code',
        'short_name',
        'hindi_name',
        'address',
        'hindi_address',
        'email',
        'mobile_no',
        'toll_free_no',
        'current_website',
        'parent_website',
        'incorporation_date',
        'department_id',
        'has_zone',
        'latitude',
        'longitude',
        'active_status',
        'remarks',
        'is_hidden_from_citizen',
        'is_hidden_from_admin',
        'is_disabled_login',
        'is_deactivated',
        'pin_code'
    ];

    /**
     * | Get Ulbs by district code
     */
    public function getUlbsByDistrictCode($districtCode)
    {
        return UlbMaster::where('district_code', $districtCode)
            ->get();
    }
    #get all ulb
    public function getAll()
    {
        return UlbMaster::select('id', 'ulb_name', 'active_status as is_suspended')
            // ->where('active_status', true)
            ->get();
    }
    /**
     * |deactive or active ulb
     */
    public function deactiveUlb($req)
    {

        $data = UlbMaster::find($req->id);

        if ($req->status == 1) {
            $data->active_status = true;
        } else {
            $data->active_status = false;
        }
        // Save the changes to the database
        $data->save();
    }
    #get data by id
    public function getDataByIdDtls($request)
    {
        return self::select(
            'id',
            'ulb_name',
            'city_id',
            'state_id',
            'department_id',
            'active_status as is_suspended'
        )
            ->where('id', $request->id)
            ->first();
    }
    /**
     * |add ulb master
     */
    public function addUlbMaster($req)
    {
        $data = new UlbMaster();
        $data->ulb_name = $req->ulbName;
        $data->ulb_type = $req->ulbType;
        $data->remarks = $req->remarks;
        $data->city_id = $req->cityId;
        $data->incorporation_date = $req->incorporationDate;
        $data->department_id = $req->departmentId;
        $data->district_code = $req->districtCode;
        $data->category = $req->category;
        $data->code = $req->code;
        $data->short_name = $req->shortName;
        $data->toll_free_no = $req->tollFreeNo;
        $data->current_website = $req->currentWebsite;
        $data->parent_website = $req->parentWebsite;
        $data->email = $req->email;
        $data->mobile_no = $req->mobileNo;
        $data->state_id = $req->stateId;
        $data->district_id = $req->districtId;
        $data->association_with = $req->associationWith;
        $data->latitude = $req->latitude;
        $data->longitude = $req->longitude;
        $data->active_status = true;
        $data->pin_code = $req->pincode;
        $data->address = $req->address;
        $data->save();
        return $data->id;
    }
    /**
     * |update ulb master
     */
    public function updateUlbById($req)
    {
        $data = MCity::find($req->id);
        $data->ulb_name = $req->ulbName;
        $data->ulb_type = $req->ulbType;
        $data->remarks = $req->remarks;
        $data->city_id = $req->cityId;
        $data->incorporation_date = $req->incorporationDate;
        $data->department_id = $req->departmentId;
        $data->district_code = $req->districtCode;
        $data->category = $req->category;
        $data->code = $req->code;
        $data->short_name = $req->shortName;
        $data->toll_free_no = $req->tollFreeNo;
        $data->current_website = $req->currentWebsite;
        $data->parent_website = $req->parentWebsite;
        $data->email = $req->email;
        $data->mobile_no = $req->mobileNo;
        $data->state_id = $req->stateId;
        $data->district_id = $req->districtId;
        $data->association_with = $req->associationWith;
        $data->latitude = $req->latitude;
        $data->longitude = $req->longitude;
        $data->save();
        return $data->id;
    }

    //create warduser
    public function addUlbMasterv1($req)
    {
        $createdBy = Auth()->user()->id;
        $mUlbModulePermission = new UlbMaster;
        $mUlbModulePermission->ulb_id = $req->ulbId;
        // $mUlbModulePermission->module_id = $req->moduleId;
        $mUlbModulePermission->service_id = $req->serviceId;
        $mUlbModulePermission->save();
    }

    //update ward user
    public function updateUlbPermissions($req)
    {
        $mUlbModulePermission = UlbMaster::find($req->ulbId);
        $mUlbModulePermission->active_status     = $req->permissions      ?? $mUlbModulePermission->active_status;
        // $mUlbModulePermission->is_admin     = $req->isAdmin     ?? $mUlbModulePermission->is_admin;
        // $mUlbModulePermission->status = $req->isSuspended ?? $mUlbModulePermission->status;
        $mUlbModulePermission->save();
    }

    public function checkUlb($user)
    {
        return UlbMaster::select(
            'id'
        )
            ->where('id', $user->ulb_id)
            // ->where('active_status', true)
            ->whereRaw('active_status::boolean = TRUE')
            ->first();
    }

    /**
     * | Update ULB logo
     */
    public function updateUlbLogo($req)
    {
        $ulb = UlbMaster::find($req->id);
        $ulb->logo = $req->logoPath;
        $ulb->save();
        return $ulb;
    }

    /**
     * | Get all ULB logos with full URL
     */
    public function getAllUlbLogos()
    {
        $appUrl = config('app.url');
        $ulbs = UlbMaster::select('id', 'ulb_name', 'logo')
            ->whereNotNull('logo')
            ->get();
        
        return $ulbs->map(function($ulb) use ($appUrl) {
            $ulb->logo_url = rtrim($appUrl, '/') . '/' . ltrim($ulb->logo, '/');
            unset($ulb->logo);
            return $ulb;
        });
    }
}
