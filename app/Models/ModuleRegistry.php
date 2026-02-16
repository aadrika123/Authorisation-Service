<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleRegistry extends Model
{
    use HasFactory;

    protected $table = 'module_registry';

    protected $fillable = [
        'table_name',
        'database_name',
        'module_id',
        'ulb_id',
        'status',
        'display_name'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}
