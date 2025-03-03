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
        });
    </script>