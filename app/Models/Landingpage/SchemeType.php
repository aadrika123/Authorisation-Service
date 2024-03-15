<?php

namespace App\Models\Landingpage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchemeType extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Get All List Scheme Where Status is 1
     */
    public function listSchemeTypes()
    {
        return self::select('id', 'type')->where('status as is_suspended', '1')->get();
    }
    #add scheme type
    public function addSchemeType($req)
    {
        $data = new SchemeType;
        $data->type= $req->type;
        $data->save();
    }
    #update
    public function updateSchemeType($req)
    {
        $data = SchemeType::find($req->id);
        $data->type= $req->type;
        $data->save();
    }
    #get scheme type by id 
    public function getSchemeTypeByid($req)
    {
      return self::select('id','type','status as is_suspended')
      ->where('status','1')
      ->where('id',$req->id)
      ->get();
    }
    #active or inactive
    public function activeOrDeatcive($req)
    {
        $data = SchemeType::find($req->id);

        if ($req->status == 1) {
           
            $data->status = 1;
        } else {
            
            $data->status = 0;
        }
        $data->save();
    }
}
