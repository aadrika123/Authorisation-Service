<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MenuRoleusermap extends Model
{
    use HasFactory;


    /**
     * | Create Role Map
     */
    public function addRoleUser($req)
    {
        $data = new MenuRoleusermap;
        $data->menu_role_id = $req->menuRoleId;
        $data->user_id      = $req->userId;
        $data->is_suspended = $req->isSuspended ?? false;
        $data->save();
    }

    /**
     * | Update Role Map
     */
    public function updateRoleUser($req)
    {
        $data = MenuRoleusermap::find($req->id);
        $data->menu_role_id = $req->menuRoleId  ?? $data->menu_role_id;
        $data->user_id      = $req->userId      ?? $data->user_id;
        $data->is_suspended = $req->isSuspended ?? $data->is_suspended;
        $data->save();
    }

    /**
     * | Menu Role Map list
     */
    public function listRoleUser()
    {
        $data = DB::table('menu_roleusermaps')
            ->select('menu_roleusermaps.id', 'menu_roles.menu_role_name', 'users.name as user_name')
            ->join('menu_roles', 'menu_roles.id', 'menu_roleusermaps.menu_role_id')
            ->join('users', 'users.id', 'menu_roleusermaps.user_id')
            ->where('menu_roleusermaps.is_suspended', false)
            ->orderBy('menu_roleusermaps.id');
        return $data;
    }

    /**
     * Delete Role Map
     */
    public function deleteRoleUser($req)
    {
        $data = MenuRoleusermap::find($req->id);
        $data->is_suspended = true;
        $data->save();
    }

    public function getRoleByUserId()
    {
        return MenuRoleusermap::select('menu_roleusermaps.id', 'menu_roleusermaps.menu_role_id', 'menu_roles.menu_role_name')
            ->join('menu_roles', 'menu_roles.id', 'menu_roleusermaps.menu_role_id')
            ->where('menu_roleusermaps.is_suspended', false)
            ->orderBy('menu_roleusermaps.id');
    }
}
