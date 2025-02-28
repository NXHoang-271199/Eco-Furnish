<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];
        $userRoleId = Role::where('name', 'user')->value('id');

        $listUsers = User::search($filters)
            ->orderByDesc('id')
            ->paginate(15);

        return view('admins.users.index', compact('listUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $listRoles = Role::all();
        return view('admins.users.create', compact('listRoles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {

        $validated = $request->validated();
        $filePath = null;
        if ($request->hasFile('avatar')) {
            $filePath = $request->file('avatar')->store('uploads/avatar', 'public');
        }
        // dd($request->all());
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'age' => $validated['age'],
            'role_id' => $validated['role_id'],
            'is_active' => 1,
            'address' => $validated['address'] ?? null,
            'avatar' => $filePath
        ]);

        return redirect()->route('users.index')->with('success', 'Tạo mới người dùng thành công.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singerUser = User::findOrFail($id);
        $listRoles = Role::all();
        return view('admins.users.show', compact('singerUser', 'listRoles'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $singerUser = User::findOrFail($id);
        $listRoles = Role::all();
        return view('admins.users.edit', compact('singerUser', 'listRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $singerUser = User::findOrFail($id);
        $validated = $request->validated();
        $filePath = $singerUser->avatar;
        if ($request->hasFile('avatar')) {
            $filePath = $request->file('avatar')->store('uploads/avatar', 'public');

            if ($singerUser->avatar && Storage::disk()->exists($singerUser->avatar)) {
                Storage::disk()->delete($singerUser->avatar);
            }
        }

        $singerUser->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'age' => $validated['age'],
            'role_id' => $validated['role_id'],
            'is_active' => (int) $request->input('is_active'),
            'address' => $validated['address'] ?? $singerUser->address,
            'avatar' => $filePath,
        ]);

        return redirect()->route('users.index')->with('success', 'Cập nhật người dùng thành công.');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $user = User::findOrFail($id);
        $deleteUser = User::where('id', $id)->delete();

        if ($deleteUser) {
            if (!empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Kiểm tra nếu request là AJAX
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Xóa người dùng thành công!',
                    'user_id' => $id
                ]);
            }

            return redirect()->route('users.index')->with('success', 'Xóa người dùng thành công!');
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Xóa người dùng thất bại!'
            ], 500);
        }

        return redirect()->route('users.index')->with('error', 'Xóa người dùng thất bại!');
    }
}
