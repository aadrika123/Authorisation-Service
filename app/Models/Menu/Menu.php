<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'route',
        'is_href',
        'is_maintenance',
        'hidden_for_small_screen'
    ];

    public function subMenu(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->with('subMenu');
    }
}
