<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Menu\MenuMaster;
use App\Models\Workflows\WfRoleusermap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{


    /**
     * | Save Menu
     */
    public function createMenu(Request $request)
    {
        try {
            $request->validate([
                'menuName'      => 'required',
                'route'         => 'nullable',
            ]);
            $mMenuMaster = new MenuMaster();
            $mMenuMaster->store($request);
            return responseMsgs(true, "Data Saved!", "", "", "02", "", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Update Menu
     */
    public function updateMenu(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'serial' => 'nullable|int',
            'parentSerial' => 'nullable|int',
            'route' => 'nullable|',
            'delete' => 'nullable|boolean'
        ]);
        try {
            $mMenuMaster = new MenuMaster();
            $mMenuMaster->edit($request);
            return responseMsgs(true, "Menu Updated!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Delete Menu
     */
    public function deleteMenu(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required'
            ]);
            MenuMaster::where('id', $request->id)
                ->update(['is_deleted' => true]);
            return responseMsgs(true, "Menu Deleted!", "", "", "02", "733", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Menu by Id
     */
    public function getMenuById(Request $request)
    {

        try {
            $request->validate([
                'menuId' => 'required|int'
            ]);
            $mMenuMaster = new MenuMaster();
            $menues = $mMenuMaster->getMenuById($request->menuId);
            if ($menues['parent_serial'] == 0) {
                return responseMsgs(true, "Menu List!", $menues, "", "01", "", "POST", "");
            }
            $parent = $mMenuMaster->getMenuById($menues['parent_serial']);
            $menues['parentName'] = $parent['menu_string'];
            return responseMsgs(true, "Menu List!", $menues, "", "01", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "01", "", "POST", "");
        }
    }

    /**
     * | List all Menus
     */
    public function menuList(Request $request)
    {
        try {
            $mMenuMaster = new MenuMaster();
            $refmenues = $mMenuMaster->fetchAllMenues();
            $menues = $refmenues->sortByDesc("id");
            $listedMenues = collect($menues)->map(function ($value) use ($mMenuMaster) {
                if ($value['parent_serial'] != 0) {
                    $parent = $mMenuMaster->getMenuById($value['parent_serial']);
                    $parentName = $parent['menu_string'];
                    $value['parentName'] = $parentName;
                    return $value;
                }
                return $value;
            })->values();
            return responseMsgs(true, "List of Menues!", $listedMenues, "", "02", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "02", "", "POST", "");
        }
    }


    /**
     * | Get Menu by module 
     */
    public function getMenuByModuleId(Request $request)
    {
        $validated = Validator::make(
            $request->all(),
            ['moduleId' => 'required']
        );
        if ($validated->fails()) {
            return validationError($validated);
        }
        try {
            $user = authUser();
            $userId = $user->id;
            $mWfRoleUserMap = new WfRoleusermap();
            $ulbId = $user->ulb_id;

            $ulbName =  User::select('ulb_name')
                ->join('ulb_masters', 'ulb_masters.id', 'users.ulb_id')
                ->where('ulb_id', $ulbId)
                ->where('users.id', $userId)
                ->first();

            $wfRole = $mWfRoleUserMap->getRoleDetailsByUserId($userId);
            $roleId = $wfRole->pluck('roleId');

            $mreqs = new Request([
                'roleId' => $roleId,
                'moduleId' => $request->moduleId
            ]);

            $treeStructure = $this->generateMenuTree($mreqs);
            $menu = collect($treeStructure)['original']['data'];

            $menuPermission['permission'] = $menu;
            $menuPermission['userDetails'] = [
                'userName' => $user->name,
                'ulb'      => $ulbName->ulb_name ?? 'No Ulb Assigned',
                'mobileNo' => $user->mobile,
                'email'    => $user->email,
                'imageUrl' => $user->photo_relative_path . '/' . $user->photo,
                'roles' => $wfRole->pluck('roles')                            # use in case of if the user has multiple roles
            ];
            return responseMsgs(true, "Parent Menu!", $menuPermission, "", "", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    public function generateMenuTree($request)
    {
        $mMenuMaster = new MenuMaster();
        $mMenues = $mMenuMaster->fetchAllMenues();

        $data = collect($mMenues)->map(function ($value, $key) {
            $return = array();
            $return['id'] = $value['id'];
            $return['parentId'] = $value['parent_serial'];
            $return['path'] = $value['route'];
            $return['icon'] = config('app.url') . '/api/getImageLink?path=' . $value['icon'];
            $return['name'] = $value['menu_string'];
            $return['order'] = $value['serial'];
            $return['children'] = array();
            return ($return);
        });

        $data = (objToArray($data));
        $itemsByReference = array();

        foreach ($data as $key => &$item) {
            $itemsByReference[$item['id']] = &$item;
        }

        # looping for the generation of child nodes / operation will end if the parentId is not match to id 
        foreach ($data as $key => &$item)
            if ($item['id'] && isset($itemsByReference[$item['parentId']]))
                $itemsByReference[$item['parentId']]['children'][] = &$item;

        # this loop is to remove the external loop of the child node ie. not allowing the child node to create its own treee
        foreach ($data as $key => &$item) {
            if ($item['parentId'] && isset($itemsByReference[$item['parentId']]))
                unset($data[$key]);
        }

        $data = collect($data)->values();
        if ($request->roleId && $request->moduleId) {
            $mRoleMenues = $mMenuMaster->getMenuByRole($request->roleId, $request->moduleId); //addition of module Id

            $roleWise = collect($mRoleMenues)->map(function ($value) use ($mMenuMaster) {
                if ($value['parent_serial'] > 0) {
                    return $roleWise = $this->getParent($value['parent_serial']);
                }
                return $value['id'];
            });
            $retunProperValues = collect($data)->map(function ($value, $key) use ($roleWise) {
                if ($roleWise->contains($value['id'])) {
                    return $value;
                }
            });
            return responseMsgs(true, "OPERATION OK!", $retunProperValues->filter()->values(), "", "01", "308.ms", "POST", $request->deviceId);
        }
        return responseMsgs(true, "OPERATION OK!", $data, "", "01", "308.ms", "POST", $request->deviceId);
    }

    public function getParent($parentId)
    {
        $mMenuMaster = new MenuMaster();
        $refvalue = $mMenuMaster->getMenuById($parentId);
        if ($refvalue['parent_serial'] > 0) {
            $this->getParent($refvalue['parent_serial']);
        }
        return $refvalue['id'];
    }
}
