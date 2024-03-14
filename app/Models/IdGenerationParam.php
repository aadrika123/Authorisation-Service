<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdGenerationParam extends Model
{
    use HasFactory;

    public function addParam($req)
    {
        $data = new IdGenerationParam;
        $data->string_val = $req->stingVal;
        $data->int_val = $req->intVal;
        $data->save();
    }
    public function updateParamId($req)
    {
        $data = IdGenerationParam::find($req->id);
        $data->string_val = $req->stingVal;
        $data->int_val = $req->intVal;
        $data->save();
    }
}
