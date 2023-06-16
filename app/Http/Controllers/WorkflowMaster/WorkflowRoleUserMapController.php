<?php

namespace App\Http\Controllers\WorkflowMaster;

use App\Http\Controllers\Controller;
use App\Repository\WorkflowMaster\Interface\iWorkflowRoleUserMapRepository;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class WorkflowRoleUserMapController extends Controller
{
    protected $eloquentRoleUserMap;

    // Initializing Construct function
    public function __construct(iWorkflowRoleUserMapRepository $eloquentRoleUserMap)
    {
        $this->EloquentRoleUserMap = $eloquentRoleUserMap;
    }
    public function index()
    {
        return $this->EloquentRoleUserMap->list();
    }


    public function create()
    {
        //
    }

    //create
    public function store(Request $request)
    {
        return $this->EloquentRoleUserMap->create($request);
    }

    //list by id
    public function show($id)
    {
        return $this->EloquentRoleUserMap->view($id);
    }


    public function edit($id)
    {
        //
    }

    //update
    public function update(Request $request, $id)
    {
        return $this->EloquentRoleUserMap->update($request, $id);
    }

    //delete
    public function destroy($id)
    {
        return $this->EloquentRoleUserMap->delete($id);
    }


    // Get Permitted Roles By User ID
    public function getRolesByUserId(Request $req)
    {
        $validated = FacadesValidator::make(
            $req->all(),
            [
                'userId'=>'required'
            ]
            );
            if($validated->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=>'validation error',
                    'errors'=> $validated->errors()
                ],422);
            }
   
        return $this->EloquentRoleUserMap->getRolesByUserId($req);
     }

    // Enable or Disable User Roles
    public function updateUserRoles(Request $req)
    {
        $validated = FacadesValidator::make(
            $req->all(),
            [
            'roleId' => 'required|int',
            'is_suspended' => 'required|bool',
            'userId' => 'required|int'
        ]);
        if($validated->fails()){
            return response()->json([
                'status'=>false,
                'message'=>'validation error',
                'errors'=> $validated->errors()
            ],422);
        }
        return $this->EloquentRoleUserMap->updateUserRoles($req);
    }
}
