<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#editCategoryForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Disable nút submit và hiển thị loading
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Hiển thị thông báo thành công
                    var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        'Cập nhật chuyên mục thành công.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                    $('.card-body').prepend(alertHtml);
                    
                    // Tự động ẩn thông báo sau 2 giây và chuyển hướng
                    setTimeout(function() {
                        $('.alert').fadeOut('slow', function() {
                            $(this).remove();
                            window.location.href = "{{ route('categories.index') }}";
                        });
                    }, 300);
                } else {
                    // Hiển thị thông báo lỗi
                    var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        (response.message || 'Có lỗi xảy ra khi cập nhật danh mục') +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                    $('.card-body').prepend(alertHtml);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = '';
                
                for (var key in errors) {
                    errorMessage += errors[key][0] + '\n';
                }
                
                // Hiển thị thông báo lỗi
                var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    (errorMessage || 'Có lỗi xảy ra khi cập nhật danh mục') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                $('.card-body').prepend(alertHtml);
            },
            complete: function() {
                // Restore nút submit về trạng thái ban đầu
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        });
    });
});
</script> 