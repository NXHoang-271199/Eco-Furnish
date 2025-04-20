<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-banners');
        $this->middleware('permission:create-banners', ['only' => ['create', 'store']]);
        $this->middleware('permission:update-banners', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-banners', ['only' => ['destroy']]);
        $this->middleware('permission:arrange-banners', ['only' => ['updatePosition']]);
    }

    /**
     * Hiển thị danh sách banner.
     */
    public function index()
    {
        $banners = Banner::orderBy('position', 'asc')->get();
        return view('admins.banners.index', compact('banners'));
    }

    /**
     * Hiển thị form tạo mới banner.
     */
    public function create()
    {
        return view('admins.banners.create');
    }

    /**
     * Lưu banner mới vào database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Xử lý upload ảnh
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('uploads/banners'), $imageName);

        // Tạo banner mới
        Banner::create([
            'title' => $request->title,
            'image' => 'uploads/banners/' . $imageName,
            'link' => $request->link,
            'description' => $request->description,
            'position' => Banner::count() + 1,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('banners.index')
            ->with('success', 'Banner đã được tạo thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa banner.
     */
    public function edit(Banner $banner)
    {
        return view('admins.banners.edit', compact('banner'));
    }

    /**
     * Cập nhật thông tin banner.
     */
    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Dữ liệu cập nhật
        $data = [
            'title' => $request->title,
            'link' => $request->link,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ];

        // Xóa ảnh nếu có yêu cầu
        if ($request->has('remove_image')) {
            if (file_exists(public_path($banner->image))) {
                unlink(public_path($banner->image));
            }
            $data['image'] = null;
        }
        // Xử lý upload ảnh mới nếu có
        elseif ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu tồn tại
            if (file_exists(public_path($banner->image))) {
                unlink(public_path($banner->image));
            }
            
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/banners'), $imageName);
            $data['image'] = 'uploads/banners/' . $imageName;
        }

        // Cập nhật banner
        $banner->update($data);

        return redirect()->route('banners.index')
            ->with('success', 'Banner đã được cập nhật thành công.');
    }

    /**
     * Xóa banner.
     */
    public function destroy(Banner $banner)
    {
        // Xóa ảnh
        if (file_exists(public_path($banner->image))) {
            unlink(public_path($banner->image));
        }
        
        // Xóa banner
        $banner->delete();

        return redirect()->route('banners.index')
            ->with('success', 'Banner đã được xóa thành công.');
    }

    /**
     * Cập nhật vị trí của banner.
     */
    public function updatePosition(Request $request)
    {
        $positions = $request->positions;
        
        foreach ($positions as $position) {
            $banner = Banner::find($position['id']);
            $banner->position = $position['position'];
            $banner->save();
        }
        
        return response()->json(['success' => true]);
    }
}
