<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Kiểm tra nếu cần reload trang (khi quay lại từ trang thùng rác)
        if (sessionStorage.getItem('needsReload') === 'true') {
            sessionStorage.removeItem('needsReload');
            location.reload();
        }
        
        // Xử lý form submit
        $('#createVariantValueForm').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitButton = form.find('button[type="submit"]');
            var originalButtonText = submitButton.html();
            
            // Xóa thông báo lỗi cũ nếu có
            $('.alert-danger').remove();
            
            // Disable nút submit và hiển thị loading
            submitButton.prop('disabled', true);
            submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        // Reset form
                        form[0].reset();
                        // Reload trang để hiển thị dữ liệu mới và toast message
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.variant_value_in_trash) {
                        // Hiển thị thông báo dạng alert cho dữ liệu trong thùng rác
                        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            xhr.responseJSON.message +
                            '<br><a href="/admin/trash/restore-variant-value/' + xhr.responseJSON.variant_value_id + '" class="btn btn-info btn-sm mt-2">Khôi phục giá trị biến thể</a>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('#variant-value-list .card-body').prepend(alertHtml);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        // Hiển thị thông báo lỗi khác
                        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            xhr.responseJSON.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('#variant-value-list .card-body').prepend(alertHtml);
                    }
                },
                complete: function() {
                    // Restore nút submit về trạng thái ban đầu
                    submitButton.prop('disabled', false);
                    submitButton.html(originalButtonText);
                }
            });
        });

        // Xử lý xóa giá trị biến thể
        $(document).on('click', '.delete-item', function() {
            var deleteButton = $(this);
            var valueId = deleteButton.data('id');
            var variantId = {{ $variant->id }}; // Lấy variant ID từ biến PHP
            
            if (confirm('Bạn có chắc chắn muốn xóa giá trị biến thể này không?')) {
                $.ajax({
                    url: '/admin/variants/' + variantId + '/values/' + valueId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Reload trang để hiển thị toast message
                            window.location.reload();
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Có lỗi xảy ra khi xóa giá trị biến thể';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);
                    }
                });
            }
        });

        // Xử lý khi click vào nút edit
        $(document).on('click', '.edit-trigger', function(e) {
            e.preventDefault();
            var valueId = $(this).data('id');
            var td = $(this).closest('tr').find('.edit-value');
            td.trigger('click');
        });

        // Xử lý chỉnh sửa giá trị biến thể trực tiếp
        $(document).on('click', '.edit-value', function() {
            var td = $(this);
            // Kiểm tra nếu đã có input thì không tạo input mới
            if (td.find('input').length > 0) {
                return;
            }
            
            var valueId = td.data('id');
            var currentValue = td.text().trim();
            var variantId = {{ $variant->id }}; // Lấy variant ID từ biến PHP
            
            // Tạo input để chỉnh sửa
            var input = $('<input>')
                .attr('type', 'text')
                .val(currentValue)
                .addClass('form-control');
            
            // Thay thế text bằng input
            td.html(input);
            input.focus();
            
            // Xử lý khi nhấn Enter
            input.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var newValue = $(this).val().trim();
                    
                    if (newValue !== '' && newValue !== currentValue) {
                        // Gửi request cập nhật
                        $.ajax({
                            url: '/admin/variants/' + variantId + '/values/' + valueId,
                            type: 'PUT',
                            data: {
                                _token: '{{ csrf_token() }}',
                                value: newValue
                            },
                            success: function(response) {
                                if (response.success) {
                                    td.html(newValue);
                                    // Hiển thị thông báo thành công dạng toast
                                    Toastify({
                                        text: response.message || 'Cập nhật giá trị biến thể thành công',
                                        duration: 3000,
                                        close: true,
                                        gravity: "top",
                                        position: "right",
                                        className: "bg-success",
                                        style: {
                                            background: "var(--vz-success)",
                                            color: "#fff",
                                            boxShadow: "0 10px 20px -10px var(--vz-success)"
                                        }
                                    }).showToast();
                                }
                            },
                            error: function(xhr) {
                                var errorMessage = '';
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    var errors = xhr.responseJSON.errors;
                                    for (var key in errors) {
                                        errorMessage += errors[key][0] + '\n';
                                    }
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else {
                                    errorMessage = 'Có lỗi xảy ra khi cập nhật giá trị biến thể';
                                }
                                
                                // Hiển thị thông báo lỗi dạng toast
                                Toastify({
                                    text: errorMessage,
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    className: "bg-danger",
                                    style: {
                                        background: "var(--vz-danger)",
                                        color: "#fff",
                                        boxShadow: "0 10px 20px -10px var(--vz-danger)"
                                    }
                                }).showToast();
                                
                                // Khôi phục giá trị cũ
                                td.html(currentValue);
                            }
                        });
                    } else {
                        // Khôi phục giá trị cũ nếu không có thay đổi hoặc input rỗng
                        td.html(currentValue);
                    }
                }
            });

            // Xử lý khi click ra ngoài
            input.on('blur', function() {
                setTimeout(function() {
                    td.html(currentValue); // Khôi phục lại giá trị cũ khi click ra ngoài
                }, 200); // Thêm độ trễ để đảm bảo sự kiện Enter được xử lý trước
            });
        });
    });
</script>