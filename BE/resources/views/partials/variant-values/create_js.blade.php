<script>
$(document).ready(function() {
    $('#createVariantValueForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        var submitButton = form.find('button[type="submit"]');
        var originalButtonText = submitButton.html();
        
        // Disable nút submit và hiển thị loading
        submitButton.prop('disabled', true);
        submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Reset form
                    form[0].reset();
                    
                    // Hiển thị thông báo thành công
                    Swal.fire({
                        title: 'Thành công!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    
                    // Focus vào input để nhập giá trị mới
                    form.find('input[name="value"]').focus();
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
                if (xhr.status === 422) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: xhr.responseJSON.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else {
                    var errorMessage = '';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        for (var key in errors) {
                            errorMessage += errors[key][0] + '\n';
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else {
                        errorMessage = 'Có lỗi xảy ra khi thêm giá trị biến thể';
                    }
                    
                    Swal.fire({
                        title: 'Lỗi!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            complete: function() {
                // Restore nút submit về trạng thái ban đầu
                submitButton.prop('disabled', false);
                submitButton.html(originalButtonText);
            }
        });
    });
});</script> 