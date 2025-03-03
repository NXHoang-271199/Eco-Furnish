<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Lấy CSRF token từ meta tag hoặc từ form
    var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
    
    // Thiết lập CSRF token cho tất cả các request AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': token
        }
    });
    
    $('#editVariantValueForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        var submitButton = form.find('button[type="submit"]');
        var originalButtonText = submitButton.html();
        
        // Kiểm tra dữ liệu trước khi gửi
        var value = form.find('input[name="value"]').val();
        if (!value || value.trim() === '') {
            var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                'Vui lòng nhập giá trị biến thể!' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>';
            $('.card-body').prepend(alertHtml);
            return false;
        }
        
        // Disable nút submit và hiển thị loading
        submitButton.prop('disabled', true);
        submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');
        
        // Lấy dữ liệu form
        var formData = new FormData(form[0]);
        formData.append('_method', 'PUT');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = '';
                
                if (errors) {
                    for (var key in errors) {
                        errorMessage += errors[key][0] + '\n';
                    }
                } else {
                    errorMessage = 'Có lỗi xảy ra khi cập nhật giá trị biến thể';
                }
                
                 // Hiển thị thông báo lỗi dạng toast
                 Toastify({
                    text: errorMessage || 'Có lỗi xảy ra khi cập nhật danh mục',
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
            },
            complete: function() {
                // Restore nút submit về trạng thái ban đầu
                submitButton.prop('disabled', false);
                submitButton.html(originalButtonText);
            }
        });
    });
});
</script> 