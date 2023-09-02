<?php

namespace App\Models\Landingpage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function metaReqs($req)
    {
        return [
            'scheme_type_id' => $req->schemeTypeId,
            'content' => $req->content,
            'link' => $req->link,
        ];
    }
    /**
     * | Add Scheme
     */
    public function addScheme($req){
        $metaRequest = $this->metaReqs($req);
        $res = Self::create($metaRequest);
        return $res;
    }


    public function listAllScheme(){
        return self::select('schemes.*','t2.type as scheme_type')
                    ->join('scheme_types as t2','schemes.scheme_type_id','t2.id')
                    ->where('schemes.status','1');
    }
}
