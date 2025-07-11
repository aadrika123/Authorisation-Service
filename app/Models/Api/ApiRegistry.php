<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRegistry extends Model
{
    use HasFactory;
    protected $table = 'api_registries';
    protected $guarded = [];

    public function addApi($req)
    {
        $data = new ApiRegistry;
        $data->description      = $req->description;
        $data->module_id        = $req->module_id;
        $data->description      = $req->description;
        $data->category         = $req->category;
        $data->end_point        = $req->api_endpoint;
        $data->usage            = $req->usage;
        $data->pre_condition    = $req->preCondition;
        // $data->request_payload  = $req->requestPayload;
        // $data->response_payload = $req->responsePayload;
        $data->post_condition   = $req->postCondition;
        $data->version          = $req->version;
        $data->created_by       = $req->createdBy;
        $data->revision_no      = $req->revisionNo ?? 1;
        $data->remarks          = $req->remarks;
        $data->method          = $req->method;
        // $data->tags             = implode(',', $req->tags);
        $data->category_id      = $req->categoryId;
        $data->developer_id     = $req->developerId;
        $data->request          = $req->request_payload;
        $data->response         = $req->response_payload;
        $data->save();
        return $data;
    }

    public function updateApi($req)
    {
        $data = ApiRegistry::find($req->id);
        $data->description   = $req->description ?? $data->description;
        $data->category      = $req->category ?? $data->category;
        $data->end_point     = $req->endPoint ?? $data->end_point;
        $data->usage         = $req->usage ?? $data->usage;
        $data->pre_condition = $req->preCondition ?? $data->pre_condition;
        $data->request_payload  = $req->requestPayload ?? $data->request_payload;
        $data->response_payload = $req->responsePayload ?? $data->response_payload;
        $data->post_condition   = $req->postCondition ?? $data->post_condition;
        $data->version          = $req->version ?? $data->version;
        $data->revision_no      = $req->revisionNo ?? $data->revision_no;
        $data->remarks          = $req->remarks ?? $data->remarks;
        $data->tags             = $req->tags ?? $data->tags;
        $data->category_id      = $req->categoryId ?? $data->category_id;
        $data->developer_id     = $req->developerId ?? $data->developer_id;
        $data->save();
    }

    /**
     * | List Api by module id
     */
    public function listApiByModuleId($moduleId)
    {
        $data = ApiRegistry::where('module_id', $moduleId)
            ->orderbydesc('api_masters.id');
        return $data;
    }

    public function apiDetails($request)
    {
        $data = ApiRegistry::select(
            'api_masters.id',
            'api_masters.end_point',
            'api_masters.description',
            'api_masters.category',
            'api_masters.usage',
            'api_masters.pre_condition',
            'api_masters.post_condition',
            'api_masters.version',
            'api_masters.revision_no',
            'api_masters.created_by',
            'api_masters.method',
            'api_masters.category_id',
            'api_masters.developer_id',
            'api_masters.request',
            'api_masters.response'
        )
            ->where('api_masters.id', $request->id)
            ->where('api_masters.discontinued', false)
            ->orderbydesc('id')
            ->first();
        return $data;
    }
}
