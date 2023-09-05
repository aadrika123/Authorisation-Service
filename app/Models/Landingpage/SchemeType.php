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
        return self::select('id', 'type')->where('status', '1')->get();
    }
}
