  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    
    <script>
        $(document).ready(function() {
            // Xử lý form submit
            $('#variantForm').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var submitButton = form.find('button[type="submit"]');
                var originalButtonText = submitButton.html();
                
                // Disable nút submit và hiển thị loading
                submitButton.prop('disabled', true);
                submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.variant_in_trash) {
                            // Hiển thị thông báo dạng alert cho dữ liệu trong thùng rác
                            var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                xhr.responseJSON.message +
                                '<br><a href="/admin/trash/restore-variant/' + xhr.responseJSON.variant_id + '" class="btn btn-info btn-sm mt-2">Khôi phục biến thể</a>' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            $('#variant-list .card-body').prepend(alertHtml);
                        }
                    },
                    complete: function() {
                        // Restore nút submit về trạng thái ban đầu
                        submitButton.prop('disabled', false);
                        submitButton.html(originalButtonText);
                    }
                });
            });

            // Xử lý xóa biến thể sử dụng event delegation
            $(document).on('click', '.delete-item', function() {
                var deleteButton = $(this);
                if (confirm('Bạn có muốn xóa biến thể này không?')) {
                    var variantId = deleteButton.data('id');
                    
                    $.ajax({
                        url: '/admin/variants/' + variantId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            }
                        }
                    });
                }
            });

            // Xử lý khi click vào nút edit
            $(document).on('click', '.edit-trigger', function(e) {
                e.preventDefault();
                var variantId = $(this).data('id');
                var td = $(this).closest('tr').find('.edit-name');
                td.trigger('click');
            });

            // Xử lý chỉnh sửa biến thể trực tiếp
            $(document).on('click', '.edit-name', function() {
                var td = $(this);
                // Kiểm tra nếu đã có input thì không tạo input mới
                if (td.find('input').length > 0) {
                    return;
                }
                
                var variantId = td.data('id');
                var currentName = td.text().trim();
                
                // Tạo input để chỉnh sửa
                var input = $('<input>')
                    .attr('type', 'text')
                    .val(currentName)
                    .addClass('form-control');
                
                // Thay thế text bằng input
                td.html(input);
                input.focus();
                
                // Xử lý khi nhấn Enter
                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        var newName = $(this).val().trim();
                        
                        if (newName !== '' && newName !== currentName) {
                            // Gửi request cập nhật
                            $.ajax({
                                url: '/admin/variants/' + variantId,
                                type: 'PUT',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    name: newName
                                },
                                success: function(response) {
                                    if (response.success) {
                                        td.html(newName);
                                        // Hiển thị thông báo thành công dạng toast
                                        Toastify({
                                            text: response.message || 'Cập nhật biến thể thành công',
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
                                        errorMessage = 'Có lỗi xảy ra khi cập nhật biến thể';
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
                                    
                                    // Khôi phục tên cũ
                                    td.html(currentName);
                                }
                            });
                        } else {
                            // Khôi phục tên cũ nếu không có thay đổi hoặc input rỗng
                            td.html(currentName);
                        }
                    }
                });

                // Xử lý khi click ra ngoài
                input.on('blur', function() {
                    setTimeout(function() {
                        td.html(currentName); // Khôi phục lại tên cũ khi click ra ngoài
                    }, 200); // Thêm độ trễ để đảm bảo sự kiện Enter được xử lý trước
                });
            });
        });
    </script>