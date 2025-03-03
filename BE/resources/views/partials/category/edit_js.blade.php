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
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = '';
                
                for (var key in errors) {
                    errorMessage += errors[key][0] + '\n';
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
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        });
    });
});
</script> 