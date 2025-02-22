@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách giá trị biến thể: {{ $variant->name }}</h3>
                    <div class="card-tools">
                        <div class="d-flex gap-1">
                            <a href="{{ route('trash.variant-values') }}" class="btn btn-warning">
                                <i class="ri-delete-bin-line align-bottom me-1"></i> Thùng rác
                            </a>
                            <a href="{{ route('variants.values.create', $variant->id) }}" class="btn btn-success">
                                <i class="ri-add-line align-bottom me-1"></i> Thêm giá trị
                            </a>
                        </div>
                        <a href="{{ route('variants.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Giá trị</th>
                                <th style="width: 150px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($values as $value)
                                <tr>
                                    <td>{{ ($values->currentPage() - 1) * $values->perPage() + $loop->iteration }}</td>
                                    <td>{{ $value->value }}</td>
                                    <td>
                                        <a href="{{ route('variants.values.edit', [$variant, $value]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-item" data-id="{{ $value->id }}">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $values->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('CSS')

@endsection

@section('JS')
<<<<<<< HEAD
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Sweet Alerts js -->
<script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // Xử lý xóa giá trị biến thể
        $('.delete-item').click(function() {
            var valueId = $(this).data('id');
            var variantId = '{{ $variant->id }}';

            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Bạn sẽ không thể khôi phục lại dữ liệu này!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy',
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-light'
                },
                buttonsStyling: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/variants/{{ $variant->id }}/values/' + valueId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Thành công!',
                                    text: response.message,
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: response.message,
                                    icon: 'error',
                                    customClass: {
                                        confirmButton: 'btn btn-danger'
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: xhr.responseJSON.message,
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                }
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
=======
    @include('partials.variant-values.index_js')
@endsection 
>>>>>>> aceb2aa46eb463e6e2b422ff821fecf6cfe1d60b
