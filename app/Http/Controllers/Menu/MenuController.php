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
     * | Get Menu by module 
     */
    public function getMenuByModuleId(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
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
                'moduleId' => $req->moduleId
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

    public function generateMenuTree($req)
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
        if ($req->roleId && $req->moduleId) {
            $mRoleMenues = $mMenuMaster->getMenuByRole($req->roleId, $req->moduleId); //addition of module Id

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
            return responseMsgs(true, "OPERATION OK!", $retunProperValues->filter()->values(), "", "01", "308.ms", "POST", $req->deviceId);
        }
        return responseMsgs(true, "OPERATION OK!", $data, "", "01", "308.ms", "POST", $req->deviceId);
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
