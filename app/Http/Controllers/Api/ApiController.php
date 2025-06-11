<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiMaster;
use App\Models\Api\ApiRole;
use App\Models\ApiCategory;
use App\Models\ApiScreenMapping;
use App\Models\DeveloperList;
use Exception;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    //create master
    public function createApi(Request $req)
    {
        try {
            $req->validate([
                'description' => 'required',
                'category'    => 'required',
                'endPoint'    => 'required',
                'tags'        => 'required|array',
            ]);

            $create = new ApiMaster();
            $create->addApi($req);

            return responseMsgs(true, "Api Saved", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    //update master
    public function updateApi(Request $req)
    {
        try {
            $req->validate([
                'id'          => 'required'
            ]);
            $update = new ApiMaster();
            $list  = $update->updateApi($req);

            return responseMsg(true, "Successfully Updated", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //master list by id
    public function ApibyId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $listById = new ApiMaster();
            $list  = $listById->listbyId($req);

            return responseMsg(true, "Api List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    //all master list
    public function getAllApi()
    {
        try {
            $list = new ApiMaster();
            $Api = $list->listApi();

            return responseMsg(true, "All Api List", $Api);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }


    //delete master
    public function deleteApi(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $delete = new ApiMaster();
            $delete->deleteApi($req);

            return responseMsg(true, "Data Deleted", '');
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Developer List
     */
    public function listDeveloper(Request $req)
    {
        try {

            $mDeveloperList = new DeveloperList();
            $list = $mDeveloperList->developerList();

            return responseMsg(true, "Developer List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | Category List
     */
    public function listCategory(Request $req)
    {
        try {

            $mApiCategory = new ApiCategory();
            $list = $mApiCategory->categoryList();

            return responseMsg(true, "Category List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'api_endpoint' => 'required|string',
                'method' => 'required|in:GET,POST,PUT,DELETE',
                'description' => 'nullable|string',
                'screens' => 'required|array',
                'screens.*.screen_name' => 'required|string',
                'screens.*.screen_url' => 'required|string',
                'screens.*.description' => 'nullable|string',
            ]);

            // Create or find the API endpoint
            $create = new ApiMaster();
            $apimaster = $create->addApi($request);
            // Collect screen IDs
            $screenIds = [];

            foreach ($validated['screens'] as $screenData) {
                $screen = ApiScreenMapping::create([
                    'api_id'       => $apimaster->id,
                    'screen_name'  => $screenData['screen_name'],
                    'url'          => $screenData['screen_url'],
                    'description'  => $screenData['description'] ?? null
                ]);
                $screenIds[] = $screen->id;
            }

            // Sync the pivot table
            return responseMsgs(true, "Api Saved", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
    //master list by Module Id
    /**
     * | List Api by Module Id
     */
    public function ApiByModuleId(Request $req)
    {
        try {
            $req->validate([
                'moduleId' => 'required',
                'page' => 'sometimes|integer|min:1',
                'perPage' => 'sometimes|integer|min:1'
            ]);

            $page = $req->input('page', 1);
            $perPage = $req->input('perPage', 10);

            $listById = new ApiMaster();
            $list = $listById->listApiByModuleId($req->moduleId);;

            $paginator = $list->paginate($perPage);
            $list = [
                "current_page"  => $paginator->currentPage(),
                "last_page"     => $paginator->lastPage(),
                "data"          => $paginator->items(),
                "total"         => $paginator->total(),
            ];

            return responseMsg(true, "Api List", $list);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | List Api by Module Id
     */
    public function ApiDetailsbyeId(Request $req)
    {
        try {
            $req->validate([
                'id' => 'required'
            ]);
            $listById = new ApiMaster();
            $api  = $listById->apiDetails($req);
            $list = ApiScreenMapping::where('api_id', $api->id)->get();
            $details = [
                'api' => $api,
                'screens' => $list
            ];

            return responseMsg(true, "Api List", $details);
        } catch (Exception $e) {
            return responseMsg(false, $e->getMessage(), "");
        }
    }
}
