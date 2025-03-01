<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('#editVariantForm').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        // Hiển thị thông báo thành công
                        var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('.card-body').prepend(alertHtml);
                        
                        // Tự động ẩn thông báo sau 2 giây
                        setTimeout(function() {
                            $('.alert').fadeOut('slow', function() {
                                $(this).remove();
                                window.location.href = response.redirect;
                            });
                        }, 2000);
                    } else {
                        // Hiển thị thông báo lỗi
                        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('.card-body').prepend(alertHtml);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    // Hiển thị thông báo lỗi
                    var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        (response.message || 'Có lỗi xảy ra khi cập nhật biến thể') +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                    $('.card-body').prepend(alertHtml);
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            });
        });
    });
</script>