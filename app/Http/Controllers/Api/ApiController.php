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
use Spatie\FlareClient\Api;

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
            $checkApi = ApiMaster::where('end_point', $validated['api_endpoint'])
                ->where('method', $validated['method'])
                ->first();
            if ($checkApi) {
                return responseMsgs(false, "API endpoint already exists", "");
            }
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
                'perPage' => 'sometimes|integer|min:1',
                'q' => 'nullable|string|max:255',
            ]);

            $page = $req->input('page', 1);
            $perPage = $req->input('perPage', 10);

            $listById = new ApiMaster();
            $list = $listById->listApiByModuleId($req->moduleId);
            if ($req->has('q') && !empty($req->q)) {
                $list = $list->where('end_point', 'like', '%' . $req->q . '%');
            }

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

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:api_masters,id',
                'api_endpoint' => 'required|string',
                'method' => 'required|in:GET,POST,PUT,DELETE',
                'description' => 'nullable|string',
                'category' => 'nullable|string',
                'usage' => 'nullable|string',
                'pre_condition' => 'nullable|string',
                'request_payload' => 'nullable|string',
                'response_payload' => 'nullable|string',
                'post_condition' => 'nullable|string',
                'version' => 'nullable|string',
                'revision_no' => 'nullable|string',
                'remarks' => 'nullable|string',
                'tags' => 'nullable|string',
                'category_id' => 'nullable|integer',
                'developer_id' => 'nullable|integer',
                'screens' => 'required|array',
                'screens.*.id' => 'nullable|integer|exists:api_screen_mappings,id',
                'screens.*.screen_name' => 'required|string',
                'screens.*.screen_url' => 'required|string',
                'screens.*.description' => 'nullable|string',
            ]);

            // Step 1: Update ApiMaster
            $api = ApiMaster::find($validated['id']);
            $api->update([
                'description'       => $validated['description'] ?? $api->description,
                'category'          => $validated['category'] ?? $api->category,
                'end_point'         => $validated['api_endpoint'],
                'method'            => $validated['method'],
                'usage'             => $validated['usage'] ?? $api->usage,
                'pre_condition'     => $validated['pre_condition'] ?? $api->pre_condition,
                'request_payload'   => $validated['request_payload'] ?? $api->request_payload,
                'response_payload'  => $validated['response_payload'] ?? $api->response_payload,
                'post_condition'    => $validated['post_condition'] ?? $api->post_condition,
                'version'           => $validated['version'] ?? $api->version,
                'revision_no'       => $validated['revision_no'] ?? $api->revision_no,
                'remarks'           => $validated['remarks'] ?? $api->remarks,
                'tags'              => $validated['tags'] ?? $api->tags,
                'category_id'       => $validated['category_id'] ?? $api->category_id,
                'developer_id'      => $validated['developer_id'] ?? $api->developer_id,
            ]);

            // Step 2: Update or Create Screen Mappings
            $existingScreenIds = [];

            foreach ($validated['screens'] as $screenData) {
                if (!empty($screenData['id'])) {
                    // Update existing
                    $screen = ApiScreenMapping::find($screenData['id']);
                    $screen->update([
                        'screen_name' => $screenData['screen_name'],
                        'url' => $screenData['screen_url'],
                        'description' => $screenData['description'] ?? null,
                    ]);
                    $existingScreenIds[] = $screen->id;
                } else {
                    // Create new
                    $newScreen = ApiScreenMapping::create([
                        'api_id' => $api->id,
                        'screen_name' => $screenData['screen_name'],
                        'url' => $screenData['screen_url'],
                        'description' => $screenData['description'] ?? null,
                    ]);
                    $existingScreenIds[] = $newScreen->id;
                }
            }

            // Optional: Delete removed screen mappings
            ApiScreenMapping::where('api_id', $api->id)
                ->whereNotIn('id', $existingScreenIds)
                ->delete();

            return responseMsgs(true, "API and screens updated successfully", $api);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }

    public function deleteScreen(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            $apimaster = ApiMaster::find($request->id);

            if (!$apimaster) {
                return responseMsgs(false, "Screen not found", null);
            };
            $screen = ApiScreenMapping::where('api_id', $apimaster->id)->get();
            if ($screen->isEmpty()) {
                return responseMsgs(false, "No screens found for this API", null);
            }
            $apimaster->delete();
            $screen->each(function ($s) {
                $s->delete();
            });

            return responseMsgs(true, "Screen deleted successfully", null);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "");
        }
    }
}
