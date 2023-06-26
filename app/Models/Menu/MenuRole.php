<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    use HasFactory;
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * | Save Menu
     */
    public function store($request)
    {
        $newMenues = new MenuRole();
        $newMenues->menu_role_name  =  $request->menuRoleName;
        $newMenues->created_by      =  authUser()->id;
        $newMenues->save();
    }

    /**
     * | Update the menu master details
     */
    public function edit($request)
    {
        $refValues = MenuRole::where('id', $request->id)->first();
        MenuRole::where('id', $request->id)
            ->update(
                [
                    'menu_role_name' => $request->menuRoleName ?? $refValues->menu_role_name,
                ]
            );
    }
}
