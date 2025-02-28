@extends('layouts.admin')

@section('title')
    Danh sách vouchers
@endsection

@section('CSS')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('JS')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault(); // Ngăn form gửi ngay lập tức
                console.log("Nút xóa được nhấn!"); // Kiểm tra xem sự kiện có kích hoạt không

                let form = $(this).closest("form");
                Swal.fire({
                    title: "Bạn có chắc chắn muốn xóa?",
                    text: "Hành động này không thể hoàn tác!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#dc3545",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Có, xóa!",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log("Đã xác nhận xóa!"); // Kiểm tra xác nhận
                        form.submit();
                    } else {
                        console.log("Hủy xóa!");
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Tiêu đề trang -->
                <div
                    class="page-title-box d-sm-flex align-items-center justify-content-between bg-gradient-info text-white p-4 rounded-3 shadow-sm">
                    <h4 class="mb-sm-0 fw-bold">Vouchers</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            @foreach ($breadcrumbs as $breadcrumb)
                                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                    @if ($breadcrumb['url'])
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                    @else
                                        {{ $breadcrumb['name'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

            <x-alert />

            <div class="card">
                <div class="card-body">
                    <div class="listjs-table" id="customerList">
                        <div class="row g-4 mb-3">
                            <div class="col-sm-auto">
                                <div>
                                    <a href="{{ route('vouchers.create') }}" class="btn btn-success add-btn"><i
                                            class="ri-add-line align-bottom me-1"></i> Thêm mới voucher</a>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive table-card mt-3 mb-1">
                            <table class="table align-middle table-nowrap" id="customerTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã Vouchers</th>
                                        <th>Tỷ lệ chiết khấu</th>
                                        <th>Số tiền chiết khấu tối đa</th>
                                        <th>Giá trị đơn hàng tối thiểu</th>
                                        <th>Ngày bắt đầu</th>
                                        <th>Ngày kết thúc</th>
                                        <th>Giới hạn sử dụng</th>
                                        <th>Hành Động</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                <tbody>
                                    @foreach ($vouchers as $voucher)
                                        <tr class="hover:bg-light transition-all duration-200">
                                            <td>{{ $voucher->code }}</td>
                                            <td>{{ number_format($voucher->discount_percentage, 2) }}%</td>
                                            <td>{{ number_format($voucher->max_discount_amount) }}</td>
                                            <td>{{ number_format($voucher->min_order_value) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($voucher->start_date)->format('d-m-Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($voucher->end_date)->format('d-m-Y') }}</td>
                                            <td>{{ $voucher->usage_limit }}</td>
                                            <td class="d-flex">
                                                <!-- Nút sửa -->
                                                <a href="{{ route('vouchers.edit', $voucher->id) }}"
                                                    class="btn btn-sm btn-outline-primary rounded-3 shadow-sm me-2 transition-all duration-300 hover:bg-primary-light">
                                                    <i class="ri-settings-4-line"></i>
                                                </a>

                                                <!-- Form xóa -->
                                                <form action="{{ route('vouchers.destroy', $voucher->id) }}" method="POST"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-3 shadow-sm transition-all duration-300 hover:bg-danger-light delete-btn">
                                                        <i class="ri-delete-bin-5-line"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- end card -->
            </div>
        </div>
    </div>
@endsection
