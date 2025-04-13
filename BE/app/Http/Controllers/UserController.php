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
     * Constructor để kiểm tra quyền
     */
    public function __construct()
    {
        $this->middleware('permission:view-users');
        $this->middleware('permission:create-users', ['only' => ['create', 'store']]);
        $this->middleware('permission:update-users', ['only' => ['edit', 'update', 'toggleStatus']]);
        $this->middleware('permission:delete-users', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];

        $listUsers = User::search($filters)
            ->orderByDesc('id')
            ->paginate(15);

        // Kiểm tra xem người dùng hiện tại có phải là admin/staff không
        if (auth()->user()->role->slug === 'admin' || auth()->user()->role->slug === 'staff') {
            return view('admins.users.index', compact('listUsers'));
        } else {
            return view('admins.users.show_user', compact('listUsers'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function toggleStatus($id)
    {
        // Kiểm tra user hiện tại có phải admin không
        if (!auth()->user()->role->slug === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này!'
            ], 403);
        }

        $user = User::findOrFail($id);

        // Không cho phép thay đổi trạng thái của admin
        if ($user->role->slug === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thay đổi trạng thái của tài khoản Admin!'
            ], 403);
        }

        // Chỉ cho phép thay đổi trạng thái của client
        if ($user->role->slug === 'client') {
            // Đảo ngược trạng thái
            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
            $message = "Đã $status tài khoản người dùng thành công!";

            if ($user->is_active) {
                $message .= " Tài khoản đã được khôi phục.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $user->is_active
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không thể thực hiện hành động này!'
        ], 403);
    }

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
        $comments = $singerUser->comments()->paginate(7);
        return view('admins.users.show', compact('singerUser', 'listRoles', 'comments'));
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

    public function userList(Request $request)
    {
        $filters = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => 'client'
        ];

        $query = User::search($filters)
            ->where('role_id', Role::where('slug', 'client')->first()->id);

        // Lọc theo trạng thái
        if ($request->status === 'inactive') {
            $query->where('is_active', 0);
        } else {
            $query->where('is_active', 1);
        }

        $listUsers = $query->orderByDesc('id')
            ->paginate(10);

        $breadcrumbs = [
            ['name' => 'Trang chủ', 'url' => route('dashboard')],
            ['name' => 'Quản lý tài khoản', 'url' => null],
            ['name' => 'Danh sách người dùng', 'url' => null],
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('partials.users.user_table', compact('listUsers'))->render(),
                'pagination' => $listUsers->links('pagination::bootstrap-5')->render()
            ]);
        }

        return view('admins.users.show_user', compact('listUsers', 'breadcrumbs'));
    }

    public function adminList(Request $request)
    {
        $filters = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];

        $listUsers = User::whereHas('role', function($query) {
            $query->whereIn('slug', ['admin', 'staff']);
        })
        ->search($filters)
        ->orderByDesc('id')
        ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('partials.users.admin_table', compact('listUsers'))->render(),
                'pagination' => $listUsers->links('pagination::bootstrap-5')->render()
            ]);
        }

        return view('admins.users.show_admin', compact('listUsers'));
    }
}
