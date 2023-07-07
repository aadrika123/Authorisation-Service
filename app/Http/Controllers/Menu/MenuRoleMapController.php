<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\MenuRolemap;
use Exception;
use Illuminate\Http\Request;

class MenuRoleMapController extends Controller
{
    public function createRoleMap(Request $req)
    {
        try {
            $req->validate([
                'menuId'     => 'required',
                'menuRoleId' => 'required'
            ]);
            $mMenuRolemap = new MenuRolemap();
            $mMenuRolemap->addRoleMap($req);

            return responseMsg(true, "Data Saved", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //update master
    public function updateRoleMap(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $mMenuRolemap = new MenuRolemap();
            $list  = $mMenuRolemap->updateRoleMap($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //master list by id
    public function roleMapbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);

            $mMenuRolemap = new MenuRolemap();
            $list  = $mMenuRolemap->roleMaps($req)
                ->where('menu_rolemaps.id', $req->id)
                ->first();

            return responseMsg(true, "Menu Role Map", remove_null($list));
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    /**
     * | All Role Maps
     */
    public function getAllRoleMap()
    {
        try {

            $mMenuRolemap = new MenuRolemap();
            $menuRole = $mMenuRolemap->roleMaps()->get();

            return responseMsg(true, "Menu Role Map List", $menuRole);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //delete master
    public function deleteRoleMap(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new MenuRolemap();
            $delete->deleteRoleMap($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
