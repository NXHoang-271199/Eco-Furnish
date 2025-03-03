<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
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
    });
</script>