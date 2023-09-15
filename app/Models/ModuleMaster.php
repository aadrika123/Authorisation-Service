<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ModuleMaster extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function moduleList()
    {
        return ModuleMaster::where('is_suspended', false)
            ->orderby('id')
            ->get();
    }
    public function moduleListv2()
    {
        $docUrl = Config::get('constants.DOC_URL');
        return ModuleMaster::select(
            '*',
            DB::raw("concat('$docUrl/',image)as image"),
        )
            ->where('is_suspended', false)
            ->where('can_view', true)
            ->orderby('id')
            ->get();
    }

    public function duesApi()
    {
        return ModuleMaster::orderby('id')->get();
    }
}
