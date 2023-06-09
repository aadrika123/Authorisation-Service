<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\MenuRoleusermap;
use Exception;
use Illuminate\Http\Request;

class MenuRoleUserMapController extends Controller
{
    /**
     * | Create Menu Role Mapping
     */
    public function createRoleUser(Request $req)
    {
        try {
            $req->validate([
                'userId'     => 'required',
                'menuRoleId' => 'required'
            ]);
            $mMenuRoleusermap = new MenuRoleusermap();
            $mMenuRoleusermap->addRoleUser($req);

            return responseMsgs(true, "Data Saved", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Menu Role Mapping
     */
    public function updateRoleUser(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $mMenuRoleusermap = new MenuRoleusermap();
            $list  = $mMenuRoleusermap->updateRoleUser($req);

            return responseMsgs(true, "Data Updated", $list);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Menu Role Mapping By id
     */
    public function roleUserbyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);

            $mMenuRoleusermap = new MenuRoleusermap();
            $list  = $mMenuRoleusermap->listRoleUser($req)
                ->where('menu_roleusermaps.id', $req->id)
                ->first();

            return responseMsgs(true, "Menu Role Map", remove_null($list));
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }


    /**
     * | Menu Role Mapping List
     */
    public function getAllRoleUser()
    {
        try {
            $mMenuRoleusermap = new MenuRoleusermap();
            $menuRole = $mMenuRoleusermap->listRoleUser()->get();

            return responseMsgs(true, "Menu Role Map List", $menuRole);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Menu Role Mapping
     */
    public function deleteRoleUser(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new MenuRoleusermap();
            $delete->deleteRoleUser($req);

            return responseMsgs(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
