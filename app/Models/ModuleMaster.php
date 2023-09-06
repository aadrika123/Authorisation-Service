<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return ModuleMaster::where('is_suspended', false)
            ->can_view('can_view',true)
            ->orderby('id')
            ->get();
    }

    public function duesApi()
    {
        return ModuleMaster::orderby('id')->get();
    }
}
