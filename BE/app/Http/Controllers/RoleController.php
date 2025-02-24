<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listRole = Role::all();
        return view('admins.roles.index', compact('listRole'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::with('role')->paginate(10);
        return view('admins.roles.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('roles.index')->with('success', 'Vai trò đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singerRole = Role::findOrFail($id);
        $users = $singerRole->users()->with('role')->get();
        $roles = Role::all();
        return view('admins.roles.show', compact('singerRole', 'users', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $singerRole = Role::findOrFail($id);
        $users = $singerRole->users()->with('role')->paginate(10);
        return view('admins.roles.edit', compact('singerRole', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, string $id)
    {
        $validated = $request->validated();
        $role = Role::findOrFail($id);
        $role->update([
            'name' => $validated['name'],
        ]);
        return redirect()->route('roles.index')->with('success', 'Vai trò đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Không thể xóa vai trò vì có người dùng đang sử dụng.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Vai trò đã được xóa thành công!');
    }
}
