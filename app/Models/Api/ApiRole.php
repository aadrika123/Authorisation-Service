<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRole extends Model
{
    use HasFactory;

    /**
     * | Save Api
     */
    public function store($request)
    {
        $newApis = new ApiRole();
        $newApis->api_role_name  =  $request->apiRoleName;
        $newApis->created_by     =  authUser()->id;
        $newApis->save();
    }

    /**
     * | Update the Api master details
     */
    public function edit($request)
    {
        $refValues = ApiRole::where('id', $request->id)->first();
        ApiRole::where('id', $request->id)
            ->update(
                [
                    'api_role_name' => $request->apiRoleName ?? $refValues->api_role_name,
                ]
            );
    }
}
