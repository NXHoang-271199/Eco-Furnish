    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Xử lý form submit
            $('#categoryForm').on('submit', function(e) {
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
                            // Thêm danh mục mới vào bảng
                            var table = $('table tbody');
                            var newRow = $('<tr></tr>');
                            var currentPage = {{ $categories->currentPage() }};
                            var perPage = {{ $categories->perPage() }};
                            var rowCount = table.find('tr').length;
                            var newIndex = ((currentPage - 1) * perPage) + rowCount + 1;
                            
                            newRow.append('<td>' + newIndex + '</td>');
                            newRow.append('<td>' + response.category.name + '</td>');
                            newRow.append(
                                '<td>' +
                                '<div class="hstack gap-3 fs-15">' +
                                '<a href="/admin/categories/' + response.category.id + '/edit" class="link-primary">' +
                                '<i class="ri-pencil-fill align-bottom me-2"></i>' +
                                '</a>' +
                                '<a href="javascript:void(0);" class="link-danger delete-item" data-id="' + response.category.id + '">' +
                                '<i class="ri-delete-bin-fill align-bottom"></i>' +
                                '</a>' +
                                '</div>' +
                                '</td>'
                            );
                            
                            table.append(newRow);
                            
                            // Xóa form
                            $('#categoryForm')[0].reset();
                            
                            // Hiển thị thông báo thành công
                            var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            $('#category-list .card-body').prepend(alertHtml);
                            
                            // Tự động ẩn thông báo sau 2 giây
                            setTimeout(function() {
                                $('.alert').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 2000);
                        } else {
                            // Hiển thị thông báo lỗi
                            var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            $('#category-list .card-body').prepend(alertHtml);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = '';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            for (var key in errors) {
                                errorMessage += errors[key][0] + '\n';
                            }
                        } else if (xhr.responseJSON) {
                            if (xhr.responseJSON.category_in_trash) {
                                errorMessage = xhr.responseJSON.message;
                                var restoreLink = '/admin/trash/restore-category/' + xhr.responseJSON.category_id;
                                errorMessage += '<br><a href="' + restoreLink + '" class="btn btn-info btn-sm mt-2">Khôi phục danh mục</a>';
                            } else {
                                errorMessage = xhr.responseJSON.message;
                            }
                        } else {
                            errorMessage = 'Có lỗi xảy ra khi thêm danh mục';
                        }
                        
                        // Hiển thị thông báo lỗi
                        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            errorMessage +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('#category-list .card-body').prepend(alertHtml);
                    },
                    complete: function() {
                        // Restore nút submit về trạng thái ban đầu
                        submitButton.prop('disabled', false);
                        submitButton.html(originalButtonText);
                    }
                });
            });

            // Xử lý xóa danh mục sử dụng event delegation
            $(document).on('click', '.delete-item', function() {
                var deleteButton = $(this);
                if (confirm('Bạn có muốn xóa danh mục này không?')) {
                    var categoryId = deleteButton.data('id');
                    
                    $.ajax({
                        url: '/admin/categories/' + categoryId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Xóa hàng khỏi bảng ngay lập tức
                                var row = deleteButton.closest('tr');
                                row.fadeOut('fast', function() {
                                    $(this).remove();
                                    
                                    // Cập nhật lại số thứ tự
                                    $('table tbody tr').each(function(index) {
                                        var currentPage = {{ $categories->currentPage() }};
                                        var perPage = {{ $categories->perPage() }};
                                        $(this).find('td:first').text(((currentPage - 1) * perPage) + index + 1);
                                    });
                                });
                                
                                // Hiển thị thông báo thành công
                                var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                    response.message +
                                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                    '</div>';
                                $('#category-list .card-body').prepend(alertHtml);
                                
                                // Tự động ẩn thông báo sau 2 giây
                                setTimeout(function() {
                                    $('.alert').fadeOut('slow', function() {
                                        $(this).remove();
                                    });
                                }, 2000);
                            } else {
                                // Hiển thị thông báo lỗi
                                var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                    response.message +
                                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                    '</div>';
                                $('#category-list .card-body').prepend(alertHtml);
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = '';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else {
                                errorMessage = 'Có lỗi xảy ra khi xóa danh mục';
                            }
                            
                            // Hiển thị thông báo lỗi
                            var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                errorMessage +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            $('#category-list .card-body').prepend(alertHtml);
                        }
                    });
                }
            });

            // Tự động ẩn thông báo sau 400 miligiay
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 400);
        });
    </script>