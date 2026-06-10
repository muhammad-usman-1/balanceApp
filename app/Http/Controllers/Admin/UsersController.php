<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // App customers — no roles assigned (mobile app registrations)
        $customers = User::with(['addresses', 'affiliatedCode'])
            ->whereDoesntHave('roles')
            ->whereNull('branch_id')
            ->orderByDesc('id')
            ->paginate(25);

        // Full admins — have roles, no branch restriction
        $fullAdmins = User::with(['roles'])
            ->whereHas('roles')
            ->whereNull('branch_id')
            ->orderByDesc('id')
            ->get();

        // Branch admins — have a branch assigned
        $branchAdmins = User::with(['roles', 'branch'])
            ->whereNotNull('branch_id')
            ->orderByDesc('id')
            ->get();

        return view('admin.users.index', compact('customers', 'fullAdmins', 'branchAdmins'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles    = Role::pluck('title', 'id');
        $branches = Branch::where('status', 'active')->pluck('name', 'id');

        return view('admin.users.create', compact('roles', 'branches'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles    = Role::pluck('title', 'id');
        $branches = Branch::where('status', 'active')->pluck('name', 'id');

        $user->load('roles');

        return view('admin.users.edit', compact('roles', 'branches', 'user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load(['roles', 'addresses', 'paymentMethods', 'affiliatedCode']);

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        $users = User::find(request('ids'));

        foreach ($users as $user) {
            $user->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
