<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentMethodRequest;
use PDOException;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::all();
        return view('admins.payment-methods.index', compact('methods'));
    }
    public function create()
    {
        return view('admins.payment-methods.create');
    }
    public function store(PaymentMethodRequest $request)
    {
        try {
            $filePath = null;
            if ($request->hasFile('image')) {
                $filePath = $request->file('image')->store('uploads/payment-method', 'public');
            }
            $dataPaymentMethod = [
                'image' => $filePath,
                'name' => $request->input('name'),
                'config' => $request->input('config'),
                'is_connected' => $request->input('name') === 'Tiền mặt' ? true : false,
                'created_at' => now()
            ];
            DB::table('payment_methods')->insert($dataPaymentMethod);
            DB::commit();
            return redirect()->route('payment-methods.index')
                ->with('success', 'Phương thức thanh toán được thêm thành công!');
        } catch (PDOException $e) {
            DB::rollBack();
            return redirect()->route('payment-methods.index')
                ->with('error', 'Thêm thất bại');
        }
    }
    public function edit(string $id)
    {
        $method = PaymentMethod::findOrFail($id);
        return view('admins.payment-methods.edit', compact('method'));
    }
    public function update(PaymentMethodRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $method = PaymentMethod::findOrFail($id);
            if (!$method) {
                return redirect()->route('payment-methods.index')
                    ->with('error', 'Phương thức thanh toán không tồn tại');
            }
            $filePath = $method->image;
            if ($request->hasFile('image')) {
                $filePath = $request->file('image')->store('uploads/payment-method', 'public');
                if ($method->image && Storage::disk('public')->exists($method->image)) {
                    Storage::disk('public')->delete($method->image);
                }
            }
            $dataPaymentMethod = [
                'image' => $filePath,
                'name' => $request->input('name'),
                'config' => $request->input('config'),
                'is_connected' => $request->input('name') === 'Tiền mặt' ? true : false,
                'updated_at' => now()
            ];
            $method->update($dataPaymentMethod);
            DB::commit();
            return redirect()->route('payment-methods.index')
                ->with('success', 'Phương thức thanh toán được sửa thành công!');
        } catch (PDOException $e) {
            DB::rollBack();
            return redirect()->route('payment-methods.index')
                ->with('error', 'Sửa thất bại');
        }
    }
    public function destroy(string $id)
    {
        $method = PaymentMethod::findOrFail($id);
        if (!$method) {
            return redirect()->route('payment-methods.index')
                ->with('error', 'Phương thức thanh toán không tồn tại');
        }
        $filePath = $method->image;
        $deleteMethod = $method->delete();
        if ($deleteMethod) {
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            return redirect()->route('payment-methods.index')
                ->with('success', 'Phương thức thanh toán đã được xóa!');
        }
        return redirect()->route('payment-methods.index')
            ->with('error', 'Xóa thất bại');
    }
    public function getConnectForm($id)
    {
        $method = PaymentMethod::findOrFail($id);
        return view('admins.payment-methods.connect_form', compact('method'));
    }
    public function connect(Request $request, string $id)
    {
        $method = PaymentMethod::findOrFail($id);

        if ($method->name === 'MoMo') {
            $request->validate([
                'partner_code' => 'required',
                'access_key' => 'required',
                'secret_key' => 'required',
            ]);

            $method->update([
                'is_connected' => true,
                'config' => [
                    'partner_code' => $request->partner_code,
                    'access_key' => $request->access_key,
                    'secret_key' => $request->secret_key,
                ]
            ]);
        } elseif ($method->name === 'VNPAY') {
            $request->validate([
                'vnp_TmnCode' => 'required',
                'vnp_HashSecret' => 'required',
            ]);

            $method->update([
                'is_connected' => true,
                'config' => [
                    'vnp_TmnCode' => $request->vnp_TmnCode,
                    'vnp_HashSecret' => $request->vnp_HashSecret,
                ]
            ]);
        }

        return redirect()->route('payment-methods.index')
                    ->with('success', 'Phương thức thanh toán đã kết nối thành công!');
    }
    public function disconnect(string $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $method->update([
            'is_connected' => false,
            'config' => null
        ]);

        return redirect()->route('payment-methods.index')->with('success', 'Đã hủy kết nối phương thức thanh toán!');
    }
}
