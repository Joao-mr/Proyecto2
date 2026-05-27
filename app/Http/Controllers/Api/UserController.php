<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AppliesIndexFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use AppliesIndexFilters;

    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $this->authorize('user-list');

        $users = $this->applyIndexFilters(User::query())
            ->paginate(500);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('user-create');

        $role = Role::find($request->role_id);
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->surname1 = $request->surname1;
        $user->surname2 = $request->surname2;

        $user->password = Hash::make($request->password);

        if ($user->save()) {
            if ($role) {
                $user->assignRole($role);
            }

            return new UserResource($user);
        }
    }

    public function show(Request $request, User $user)
    {
        if ($request->user()->id !== $user->id && ! $request->user()->checkPermissionTo('user-list')) {
            abort(403);
        }

        $user->load('roles');

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('user-edit');

        $role = Role::find($request->role_id);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->surname1 = $request->surname1;
        $user->surname2 = $request->surname2;

        if (! empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
        if ($user->save()) {
            if ($role) {
                $user->syncRoles($role);
            }

            return new UserResource($user);
        }
    }

    public function updateImage(Request $request)
    {
        return $this->updateimg($request);
    }

    public function updateimg(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
            'picture' => ['required', 'image', 'max:1500'],
        ]);

        $user = User::findOrFail($request->id);
        if ($request->user()->id !== $user->id && ! $request->user()->checkPermissionTo('user-edit')) {
            abort(403);
        }

        if ($request->hasFile('picture')) {
            $user->media()->delete();
            $user->addMediaFromRequest('picture')->preservingOriginal()->toMediaCollection('images-users');

        }
        $user = User::with('media')->find($request->id);

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $this->authorize('user-delete');

        $user->delete();

        return response()->noContent();
    }
}
