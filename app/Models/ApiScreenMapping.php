<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiScreenMapping extends Model
{
    use HasFactory;
    protected $table = 'api_screen_mappings';
    protected $fillable = ['api_id', 'screen_name', 'url', 'description'];
}
