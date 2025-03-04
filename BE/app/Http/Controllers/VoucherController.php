<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Http\Requests\VoucherRequest;
use Illuminate\Support\Facades\Storage;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::all();
        return view('admins.vouchers.index', compact('vouchers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.vouchers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VoucherRequest  $request)
    {   
        $validate = $request->validated();

        // Tạo mới voucher
        $voucher = Voucher::create([
            'code' => $validate['code'],
            'discount_percentage' => $validate['discount_percentage'],
            'max_discount_amount' => $validate['max_discount_amount'],
            'min_order_value' => $validate['min_order_value'],
            'start_date' => $validate['start_date'],
            'end_date' => $validate['end_date'],
            'usage_limit' => $validate['usage_limit']
        ]);

        return redirect()->route('vouchers.index')->with('success', 'Tạo mới voucher thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Chức năng hiển thị voucher có thể thêm vào sau nếu cần
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $voucher = Voucher::findOrFail($id);
        return view('admins.vouchers.edit', compact('voucher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VoucherRequest $request, string $id)
    {   

        $validate = $request->validated();
        // Tìm voucher cần sửa
        $voucher = Voucher::findOrFail($id);
        // Cập nhật voucher
        $voucher->update([
            'code' => $validate['code'],
            'discount_percentage' => $validate['discount_percentage'],
            'max_discount_amount' => $validate['max_discount_amount'],
            'min_order_value' => $validate['min_order_value'],
            'start_date' => $validate['start_date'],
            'end_date' => $validate['end_date'],
            'usage_limit' => $validate['usage_limit']
        ]);

        return redirect()->route('vouchers.index')->with('success', 'Sửa voucher thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Tìm voucher cần xóa
        $voucher = Voucher::findOrFail($id);

        // Nếu voucher có ảnh, xóa ảnh khỏi storage
        if ($voucher->image && Storage::exists('public/' . $voucher->image)) {
            Storage::delete('public/' . $voucher->image);
        }

        // Xóa voucher
        $voucher->delete();

        return redirect()->route('vouchers.index')->with('success', 'Xóa voucher thành công!');
    }
}
