<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AppliesIndexFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    use AppliesIndexFilters;

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $this->authorize('permission-list');

        $permissions = $this->applyIndexFilters(Permission::query())
            ->get();

        return PermissionResource::collection($permissions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return PermissionResource
     *
     * @throws AuthorizationException
     */
    public function store(StorePermissionRequest $request)
    {
        $this->authorize('permission-create');

        $permission = new Permission;
        $permission->name = $request->name;
        $permission->guard_name = 'web';
        $permission->save();

        return new PermissionResource($permission);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return PermissionResource
     */
    public function show(Permission $permission)
    {
        $this->authorize('permission-edit');

        return new PermissionResource($permission);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse|PermissionResource
     *
     * @throws AuthorizationException
     */
    public function update(Permission $permission, StorePermissionRequest $request)
    {
        $this->authorize('permission-edit');

        $permission->name = $request->name;
        $permission->save();

        return new PermissionResource($permission);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(Permission $permission)
    {
        $this->authorize('permission-delete');
        $permission->delete();

        return response()->noContent();
    }

    public function getRolePermissions($id)
    {
        $this->authorize('role-edit');

        $permissions = Role::findById($id, 'web')->permissions;

        return PermissionResource::collection($permissions);
    }

    public function updateRolePermissions(Request $request)
    {
        $this->authorize('role-edit');

        $permissions = json_decode($request->permissions, true);
        $permissions_where = Permission::whereIn('id', $permissions)->get();
        $role = Role::findById($request->role_id, 'web');
        $role->syncPermissions($permissions_where);

        return PermissionResource::collection($permissions_where);
    }
}
