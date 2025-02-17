<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
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
        $listUsers = User::search($filters)->orderByDesc('id')->paginate(15);
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'age' => 'required|integer|min:1',
            'role_id' => 'required|exists:roles,id',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {

            $filePath = null;
            if ($request->hasFile('avatar')) {
                $filePath = $request->file('avatar')->store('upload/avatar', 'public');
            }

            // Tạo người dùng mới
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'age' => $validated['age'],
                'role_id' => $validated['role_id'],
                'address' => $validated['address'] ?? null,
                'avatar' => $filePath
            ]);

            return redirect()
                ->route('users.index')
                ->with('success', 'Tạo mới người dùng thành công');
            // return redirect()->route('users.index')->with('status', 'success')->with('message', 'Tạo mới người dùng thành công');
        } catch (\Exception $e) {
            // Nếu có lỗi và đã upload ảnh thì xóa ảnh
            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Lỗi: ' . $e->getMessage());
        }
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
    public function update(Request $request, string $id)
    {
        $singerUser = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $singerUser->id,
            'age' => 'required|integer',
            'role_id' => 'required|exists:roles,id',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $filePath = $singerUser->avatar;
            if ($request->hasFile('avatar')) {
                $filePath = $request->file('avatar')->store('upload/avatar', 'public');

                if ($singerUser->avatar && Storage::disk()->exists($singerUser->avatar)) {
                    Storage::disk()->delete($singerUser->avatar);
                }
            }
            $singerUser->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'age' => $validated['age'],
                'role_id' => $validated['role_id'],
                'address' => $validated['address'] ?? $singerUser->address,
                'avatar' => $filePath,
            ]);

            return redirect()->route('users.index')->with('success', 'Cập nhật người dùng thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $singerUser = User::findOrFail($id);

        $deleteUser = User::where('id', $id)->delete();

        $filePath = $singerUser->avatar;
        if ($deleteUser && isset($filePath) && Storage::disk()->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
            return redirect()->route('users.index')->with('success', 'Xóa thành công !');
        }
        if ($deleteUser) {
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            return redirect()->route('users.index')
                ->with('success', 'Xóa người dùng thành công !');
        }
        return redirect()->route('users.index')
            ->with('error', 'Xóa người dùng lỗi !');
    }
}
