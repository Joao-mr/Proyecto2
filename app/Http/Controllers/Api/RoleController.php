<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AppliesIndexFilters;
use App\Http\Controllers\Controller;
 
use App\Http\Requests\StoreRoleRequest;
use App\Http\Resources\RoleResource;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AppliesIndexFilters;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $roles = $this->applyIndexFilters(Role::query())
            ->get();

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RoleResource
     */
    public function store(StoreRoleRequest $request)
    {
        $this->authorize('role-create');

        $role = new Role();
        $role->name = $request->name;
        $role->guard_name = 'web';
        $role->save();

        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return RoleResource
     */
    public function show(Role $role)
    {
        $this->authorize('role-edit');

        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Role $role
     * @param StoreRoleRequest $request
     * @return RoleResource
     * @throws AuthorizationException
     */
    public function update(Role $role, StoreRoleRequest $request)
    {
        $this->authorize('role-edit');

        $role->name = $request->name;
        $role->save();

        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role) {
        $this->authorize('role-delete');
        $role->delete();

        return response()->noContent();
    }

    public function getList()
    {
        return RoleResource::collection(Role::all());
    }
}
