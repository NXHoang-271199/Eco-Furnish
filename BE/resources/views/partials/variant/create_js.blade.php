<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Sweet Alerts js -->
<script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#variantForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            // Kiểm tra validation
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }
            
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Thành công!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = response.redirect;
                            }
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
                    const response = xhr.responseJSON;
                    if (xhr.status === 422 && response.variant_in_trash) {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: response.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Khôi phục',
                            cancelButtonText: 'Đóng',
                            confirmButtonColor: '#3085d6'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Gọi API khôi phục biến thể
                                $.ajax({
                                    url: '/admin/trash/restore-variants/' + response.variant_id,
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(restoreResponse) {
                                        if (restoreResponse.success) {
                                            Swal.fire({
                                                title: 'Thành công!',
                                                text: 'Khôi phục biến thể thành công',
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = '/admin/variants';
                                                }
                                            });
                                        } else {
                                            Swal.fire({
                                                title: 'Lỗi!',
                                                text: restoreResponse.message,
                                                icon: 'error',
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    },
                                    error: function() {
                                        Swal.fire({
                                            title: 'Lỗi!',
                                            text: 'Có lỗi xảy ra khi khôi phục biến thể',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: response.message || 'Có lỗi xảy ra khi thêm biến thể',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            });
        });
    });
</script>