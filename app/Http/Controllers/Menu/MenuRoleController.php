<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\MenuRole;
use Exception;
use Illuminate\Http\Request;

class MenuRoleController extends Controller
{
    /**
     * | Save Menu Role
     */
    public function createMenuRole(Request $request)
    {
        try {
            $request->validate([
                'menuRoleName' => 'required',
            ]);
            $mMenuRole = new MenuRole();
            $mMenuRole->store($request);
            return responseMsgs(true, "Data Saved!", "", "", "02", "", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Menu Role
     */
    public function updateMenuRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'menuRoleName' => 'required',
            ]);
            $mMenuRole = new MenuRole();
            $mMenuRole->edit($request);
            return responseMsgs(true, "Menu Role Updated!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Menu Role
     */
    public function deleteMenuRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            MenuRole::where('id', $request->id)
                ->update(['is_suspended' => true]);
            return responseMsgs(true, "Menu Role Deleted!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Menu Role by Id
     */
    public function getMenuRole(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|int'
            ]);
            $mMenuRole = MenuRole::find($request->id);

            return responseMsgs(true, "Menu Role!", $mMenuRole, "", "01", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", "", "POST", "");
        }
    }

    /**
     * | Menu Role List
     */
    public function listMenuRole(Request $request)
    {
        try {
            $mMenuRole = MenuRole::all();

            return responseMsgs(true, "List of Menu Role!", $mMenuRole, "", "01", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", "", "POST", "");
        }
    }
}
