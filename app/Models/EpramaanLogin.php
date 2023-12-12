<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpramaanLogin extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function store($req)
    {
        $data = EpramaanLogin::create($req);
        return $data;
    }
}
