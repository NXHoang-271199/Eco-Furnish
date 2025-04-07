<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Sweet Alerts js -->
<script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    function confirmRestore(id) {
        Swal.fire({
            title: 'Xác nhận khôi phục?',
            text: "Bạn có chắc chắn muốn khôi phục sản phẩm này?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Có, khôi phục!',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/trash/trash-products/${id}`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Thành công!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = response.redirect;
                            });
                        } else {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        // Lấy thông báo lỗi cụ thể từ phản hồi JSON của server
                        let errorMessage = 'Có lỗi không xác định xảy ra.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            title: 'Lỗi!',
                            text: errorMessage, // Hiển thị thông báo lỗi từ server
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }

    function confirmForceDelete(id) {
        Swal.fire({
            title: 'Xác nhận xóa vĩnh viễn?',
            text: "Hành động này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Có, xóa vĩnh viễn!',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/trash/trash-products/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Thành công!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        // Lấy thông báo lỗi cụ thể từ phản hồi JSON của server
                        let errorMessage = 'Có lỗi không xác định xảy ra.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            title: 'Lỗi!',
                            text: errorMessage, // Hiển thị thông báo lỗi từ server
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }
</script>