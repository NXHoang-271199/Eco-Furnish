<script>
$(document).ready(function() {
    $('#editCategoryForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Thành công!',
                        text: 'Cập nhật danh mục thành công!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('categories.index') }}";
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: response.message || 'Có lỗi xảy ra khi cập nhật danh mục',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = '';
                
                for (var key in errors) {
                    errorMessage += errors[key][0] + '\n';
                }
                
                Swal.fire({
                    title: 'Lỗi!',
                    text: errorMessage || 'Có lỗi xảy ra khi cập nhật danh mục',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script> 