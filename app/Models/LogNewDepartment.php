<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogNewDepartment extends Model
{
    use HasFactory;

    protected $table = 'log_new_departments';

    protected $fillable = [
        'department_id',
        'department_name',
        'description',
        'status',
        'action',
        'old_data',
        'new_data',
        'created_by'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array'
    ];
}