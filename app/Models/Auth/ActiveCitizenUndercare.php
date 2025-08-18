<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveCitizenUndercare extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * | Store
     */
    public function store(array $req)
    {
        ActiveCitizenUndercare::create($req);
    }
}
