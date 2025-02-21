@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách giá trị biến thể: {{ $variant->name }}</h3>
                    <div class="card-tools">
<<<<<<< HEAD
                        <a href="{{ route('variants.values.create', $variant) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm giá trị
                        </a>
                        <a href="{{ route('variants.index') }}" class="btn btn-default">
=======
                        <a href="{{ route('admin.variants.values.create', $variant) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm giá trị
                        </a>
                        <a href="{{ route('admin.variants.index') }}" class="btn btn-default">
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
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

<<<<<<< HEAD
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

=======
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
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
<<<<<<< HEAD
                                    <td>{{ ($values->currentPage() - 1) * $values->perPage() + $loop->iteration }}</td>
                                    <td>{{ $value->value }}</td>
                                    <td>
                                        <a href="{{ route('variants.values.edit', [$variant, $value]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-item" data-id="{{ $value->id }}">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
=======
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->value }}</td>
                                    <td>
                                        <a href="{{ route('admin.variants.values.edit', [$variant, $value]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <form action="{{ route('admin.variants.values.destroy', [$variant, $value]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
<<<<<<< HEAD

                    <div class="mt-3">
                        {{ $values->links() }}
                    </div>
=======
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<<<<<<< HEAD

@section('CSS')
<!-- Sweet Alert css-->
<link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('JS')
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
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
