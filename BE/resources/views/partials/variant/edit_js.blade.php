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
                        if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
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
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            });
        });
    });
</script>