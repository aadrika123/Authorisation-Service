<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\MenuRole;
use App\Models\Menu\MenuRoleusermap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

            return responseMsgs(true, "Data Saved", "", "120901", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120901", "01", responseTime(), $req->getMethod(), $req->deviceId);
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
            $mMenuRoleusermap->updateRoleUser($req);

            return responseMsgs(true, "Data Updated", [], "120902", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120902", "01", responseTime(), $req->getMethod(), $req->deviceId);
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

            return responseMsgs(true, "Menu Role Map", remove_null($list), "120903", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120903", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }


    /**
     * | Menu Role Mapping List
     */
    public function getAllRoleUser(Request $req)
    {
        try {
            $mMenuRoleusermap = new MenuRoleusermap();
            $menuRole = $mMenuRoleusermap->listRoleUser()->get();

            return responseMsgs(true, "Menu Role Map List", $menuRole, "120904", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120904", "01", responseTime(), $req->getMethod(), $req->deviceId);
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

            return responseMsgs(true, "Data Deleted", '', "120905", "01", responseTime(), $req->getMethod(), $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120905", "01", responseTime(), $req->getMethod(), $req->deviceId);
        }
    }

    /**
     * created by : Ashutosh Kumar
     * created at : 14-07-23
     */

    // Roles by User Id
    public function roleByUserId(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'userId' => 'required|integer'
            ]);
            if ($validator->fails())
                return validationError($validator);

            $menuRoleUserMap = new MenuRoleusermap;
            $data = $menuRoleUserMap->getRoleByUserId()
                ->where('menu_roleusermaps.user_id', '=', $req->userId)
                ->get();
            return responseMsgs(true, 'Menu Role Map By User Id', $data, "120907", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "120907", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }

    //Roles Except Given user id
    public function roleExcludingUserId(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'userId' => 'required|integer'
            ]);
            if ($validator->fails())
                return validationError($validator);

            $menuRoleUserMap = new MenuRoleusermap;
            $mMenuRole = new MenuRole();
            $menuRole = $menuRoleUserMap->getRoleByUserId()
                ->where('menu_roleusermaps.user_id', $req->userId)
                ->get();

            $menuRoleId = $menuRole->pluck('menu_role_id');

            $menuRole = $mMenuRole->listMenuRole()
                ->whereNotIn('menu_roles.id', $menuRoleId)
                ->get();

            return responseMsgs(true, 'Menu Role Map Except User Id', $menuRole, "120908", "1.0", responseTime(), "POST", $req->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "120908", "1.0", responseTime(), "POST", $req->deviceId);
        }
    }
}
