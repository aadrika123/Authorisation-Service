<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuMaster extends Model
{
    use HasFactory;

    /**
     * | get All list of Menues form the master table of menues
     */
    public function fetchAllMenues()
    {
        return MenuMaster::where('is_deleted', false)
            ->orderBy("menu_masters.serial", "Asc")
            ->get();
    }

    /**
     * | Get menu by Role Id
     */
    public function getMenuByRole($roleId, $moduleId)
    {
        $a = MenuMaster::select(
            'menu_masters.id',
            'menu_masters.parent_serial'
        )
            ->join('wf_rolemenus', 'wf_rolemenus.menu_id', '=', 'menu_masters.id')
            ->where('menu_masters.is_deleted', false)
            ->where('wf_rolemenus.status', true)
            ->whereIn('wf_rolemenus.role_id', $roleId)
            ->where('module_id', $moduleId)         //changes by mrinal and sam
            ->orderBy("menu_masters.serial", "Asc")
            ->get();
        return  objToArray($a);
    }

    /**
     * | Get Menues By Id
     */
    public function getMenuById($id)
    {
        return MenuMaster::where('id', $id)
            ->where('is_deleted', false)
            ->firstOrFail();
    }
}
