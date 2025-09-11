<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Menu\Menu;
use App\Models\Menu\MenuMaster;
use App\Models\Menu\MenuRoleusermap;
use App\Models\ModuleMaster;
use App\Models\Workflows\WfRoleusermap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

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
                'moduleId'      => 'required',
                'route'         => 'nullable',
                'workflowId'    => 'nullable'
            ]);
            $mMenuMaster = new MenuMaster();
            $mMenuMaster->store($request);
            return responseMsgs(true, "Data Saved!", "", "120101", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "120101", "01", responseTime(), $request->getMethod(), $request->deviceId);
        }
    }

    /**
     * | Update Menu
     */
    public function updateMenu(Request $request)
    {
        try {
            $request->validate([
                'id'           => 'required',
                'serial'       => 'nullable|int',
                'parentSerial' => 'nullable|int',
                'route'        => 'nullable',
                'delete'       => 'nullable|boolean',
                'workflowId'   => 'nullable|int',
            ]);
            $mMenuMaster = new MenuMaster();
            $mMenuMaster->edit($request);
            return responseMsgs(true, "Menu Updated!", "", "120102", "01", responseTime(), $request->getMethod(), $request->deviceId);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "", "", "120102", "01", responseTime(), $request->getMethod(), $request->deviceId);
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
            return responseMsgs(false, $e->getMessage(), "", "", "02", "", "POST", "");
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
            if ($menues['parent_id'] == 0) {
                return responseMsgs(true, "Menu List!", $menues, "", "01", "", "POST", "");
            }
            $parent = $mMenuMaster->getMenuById($menues['parent_id']);
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
            $refmenues = $mMenuMaster->fetchAllMenues()
                ->get();
            $menues = $refmenues->sortByDesc("id");
            $listedMenues = collect($menues)->map(function ($value) use ($mMenuMaster) {
                if ($value['parent_id'] != 0) {
                    $parent = $mMenuMaster->getMenuById($value['parent_id']);
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
     * | List all Menus
     */
    // public function menuList(Request $request)
    // {
    //     try {
    //         $mMenuMaster = new MenuMaster();

    //         // Fetch and sort menus
    //         $refmenues = $mMenuMaster->fetchAllMenues()->get();
    //         $menues = $refmenues->sortByDesc("id");

    //         // Initialize an array to store the final menu list
    //         $listedMenues = [];

    //         // Iterate through the menus
    //         foreach ($menues as $menu) {
    //             if ($menu['parent_id'] != 0) {
    //                 // Fetch parent menu if parent_id exists
    //                 $parent = $mMenuMaster->getMenuById($menu['parent_id']);
    //                 $menu['parentName'] = $parent['menu_string'];
    //             }

    //             // Add menu to the final list
    //             $listedMenues[] = $menu;
    //         }

    //         // Return the response
    //         return responseMsgs(true, "List of Menues!", $listedMenues, "", "02", "", "POST", "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "", "02", "", "POST", "");
    //     }
    // }


    /**
     * | Module List
     */
    public function moduleList(Request $request)
    {
        try {
            $mModuleMaster = new ModuleMaster();
            $data = $mModuleMaster->moduleList();

            return responseMsgs(true, "List of Module!", $data, "", "02", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "02", "", "POST", "");
        }
    }


    /**
     * | Module List 
        | V2
     */
    public function moduleListV2(Request $request)
    {
        try {
            $mModuleMaster = new ModuleMaster();
            $data = $mModuleMaster->moduleListv2();

            return responseMsgs(true, "List of Module!", $data, "", "02", "", "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "", "02", "", "POST", "");
        }
    }

    public function listParentSerial(Request $req)
    {
        try {
            $mMenuMaster = new MenuMaster();
            $parentMenu = $mMenuMaster->getParentMenue()
                ->where('module_id', $req->moduleId)
                ->get();
            return responseMsgs(true, "parent Menu!", $parentMenu, "", "", "", "POST", "");
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | List of parent menu by module
     */
    // public function listParentByModule()
    // {
    //     try {
    //         $mMenuMaster = new MenuMaster();
    //         $parentMenu = $mMenuMaster->getParentMenue()
    //             ->get();
    //         return responseMsgs(true, "parent Menu!", $parentMenu, "", "", "", "POST", "");
    //     } catch (Exception $e) {
    //         return responseMsg(false, $e->getMessage(), "");
    //     }
    // }

    /**
     * | Get Menu by module 
     */
    // public function getMenuByModuleId(Request $request)
    // {
    //     $validated = Validator::make(
    //         $request->all(),
    //         ['moduleId' => 'required']
    //     );
    //     if ($validated->fails()) {
    //         return validationError($validated);
    //     }
    //     try {
    //         $user = authUser();
    //         $userId = $user->id;
    //         $mWfRoleUserMap = new WfRoleusermap();
    //         $ulbId = $user->ulb_id;

    //         $ulbName =  User::select('ulb_name')
    //             ->join('ulb_masters', 'ulb_masters.id', 'users.ulb_id')
    //             ->where('ulb_id', $ulbId)
    //             ->where('users.id', $userId)
    //             ->first();

    //         $wfRole = $mWfRoleUserMap->getRoleDetailsByUserId($userId);
    //         $roleId = $wfRole->pluck('roleId');

    //         $mreqs = new Request([
    //             'roleId' => $roleId,
    //             'moduleId' => $request->moduleId
    //         ]);

    //         $treeStructure = $this->generateMenuTree($mreqs);
    //         $menu = collect($treeStructure)['original']['data'];

    //         $menuPermission['permission'] = $menu;
    //         $menuPermission['userDetails'] = [
    //             'userName' => $user->name,
    //             'ulb'      => $ulbName->ulb_name ?? 'No Ulb Assigned',
    //             'mobileNo' => $user->mobile,
    //             'email'    => $user->email,
    //             'imageUrl' => $user->photo_relative_path . '/' . $user->photo,
    //             'roles' => $wfRole->pluck('roles')                            # use in case of if the user has multiple roles
    //         ];
    //         return responseMsgs(true, "Parent Menu!", $menuPermission, "", "", "", "POST", "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), "", "", "02", "", "POST", "");
    //     }
    // }

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
            $mMenuRoleusermap = new MenuRoleusermap();
            $ulbId = $user->ulb_id;

            $ulbName =  User::select('ulb_name')
                ->join('ulb_masters', 'ulb_masters.id', 'users.ulb_id')
                ->where('ulb_id', $ulbId)
                ->where('users.id', $userId)
                ->first();

            $wfRole = $mWfRoleUserMap->getRoleDetailsByUserId($userId);
            // $roleId = $wfRole->pluck('roleId');

            $menuRole = $mMenuRoleusermap->getRoleByUserId()
                ->where('menu_roleusermaps.user_id', $userId)
                ->get();

            $roleId = $menuRole->pluck('menu_role_id');

            $mreqs = new Request([
                'roleId' => $roleId,
                'moduleId' => $request->moduleId
            ]);

            $treeStructure = $this->generateMenuTree($mreqs);
            $menu = collect($treeStructure)['original']['data'];

            // if (collect($menu)->isEmpty()) {
            //     // Revoke the current token
            //     $request->user()->currentAccessToken()->delete();
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthenticated. Permission is empty, token expired.',
            //         'code' => 401
            //     ], 401);
            // }
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
            return responseMsgs(false, $e->getMessage(), "", "", "02", "", "POST", "");
        }
    }


    /**
     * | menu Tree
     */
    public function generateMenuTree($request)
    {
        $docUrl = Config::get('constants.DOC_URL');
        $mMenuMaster = new MenuMaster();
        $mMenues = $mMenuMaster->fetchAllMenues()
            ->get();

        $data = collect($mMenues)->map(function ($value, $key) use ($docUrl) {
            $return = array();
            $return['id']       = $value['id'];
            $return['parentId'] = $value['parent_id'];
            $return['path']     = $value['route'];
            $return['icon']     = $docUrl . '/' . $value['icon'];
            $return['name']     = $value['menu_string'];
            $return['order']    = $value['serial'];
            $return['children'] = array();
            return ($return);
        });

        $data = (objToArray($data));
        $itemsByReference = array();

        foreach ($data as $key => &$item) {
            $itemsByReference[$item['id']] = &$item;
        }

        # looping for the generation of child nodes / operation will end if the parentId is not match to id 
        foreach ($data as $key => &$item) {
            if ($item['id'] && isset($itemsByReference[$item['parentId']]))
                $itemsByReference[$item['parentId']]['children'][] = &$item;

            # to remove the external loop of the child node ie. not allowing the child node to create its own treee
            if ($item['parentId'] && isset($itemsByReference[$item['parentId']]))
                unset($data[$key]);
        }

        # this loop is to remove the external loop of the child node ie. not allowing the child node to create its own treee
        // foreach ($data as $key => &$item) {
        //     if ($item['parentId'] && isset($itemsByReference[$item['parentId']]))
        //         unset($data[$key]);
        // }

        $data = collect($data)->values();
        if ($request->roleId && $request->moduleId) {
            $mRoleMenues = $mMenuMaster->getMenuByRole($request->roleId, $request->moduleId); //addition of module Id

            $roleWise = collect($mRoleMenues)->map(function ($value) {
                if ($value['parent_id'] > 0) {
                    return $this->getParent($value['parent_id']);
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
        if ($refvalue['parent_id'] > 0) {
            $this->getParent($refvalue['parent_id']);
        }
        return $refvalue['id'];
    }

    /**
     * get menu for citizen
     */
    public function getMenu()
    {
        $menus = Menu::whereNull('parent_id')
            ->with(['subMenu' => function ($q) {
                $q->select('id', 'parent_id', 'name', 'route', 'is_maintenance');
            }])
            ->get(['id', 'name', 'route', 'is_href', 'is_maintenance', 'hidden_for_small_screen']);

        return response()->json(
            ['mainMenu' => $menus],
            200,
            [],
            JSON_UNESCAPED_SLASHES
        );
    }
}
