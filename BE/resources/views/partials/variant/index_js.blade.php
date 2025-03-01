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
                            // Thêm biến thể mới vào bảng
                            var table = $('table tbody');
                            var newRow = $('<tr></tr>');
                            var currentPage = {{ $variants->currentPage() }};
                            var perPage = {{ $variants->perPage() }};
                            var rowCount = table.find('tr').length;
                            var newIndex = ((currentPage - 1) * perPage) + rowCount + 1;
                            
                            newRow.append('<td>' + newIndex + '</td>');
                            newRow.append('<td>' + response.variant.name + '</td>');
                            newRow.append('<td></td>');
                            newRow.append(
                                '<td>' +
                                '<div class="hstack gap-3 fs-15">' +
                                '<a href="/admin/variants/' + response.variant.id + '/values" class="btn btn-soft-info btn-sm" title="Giá trị">' +
                                '<i class="ri-list-check-line align-bottom"></i> Giá trị' +
                                '</a>' +
                                '<a href="/admin/variants/' + response.variant.id + '/edit" class="btn btn-soft-warning btn-sm" title="Chỉnh sửa">' +
                                '<i class="ri-pencil-fill align-bottom"></i> Sửa' +
                                '</a>' +
                                '<a href="javascript:void(0);" class="btn btn-soft-danger btn-sm delete-item" data-id="' + response.variant.id + '" title="Xóa">' +
                                '<i class="ri-delete-bin-fill align-bottom"></i> Xóa' +
                                '</a>' +
                                '</div>' +
                                '</td>'
                            );
                            
                            table.append(newRow);
                            
                            // Xóa form
                            $('#variantForm')[0].reset();
                            
                            // Hiển thị thông báo thành công
                            var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            $('#variant-list .card-body').prepend(alertHtml);
                            
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
                            $('#variant-list .card-body').prepend(alertHtml);
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
                            if (xhr.responseJSON.variant_in_trash) {
                                errorMessage = xhr.responseJSON.message;
                                var restoreLink = '/admin/trash/restore-variant/' + xhr.responseJSON.variant_id;
                                errorMessage += '<br><a href="' + restoreLink + '" class="btn btn-info btn-sm mt-2">Khôi phục biến thể</a>';
                            } else {
                                errorMessage = xhr.responseJSON.message;
                            }
                        } else {
                            errorMessage = 'Có lỗi xảy ra khi thêm biến thể';
                        }
                        
                        // Hiển thị thông báo lỗi
                        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            errorMessage +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('#variant-list .card-body').prepend(alertHtml);
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
                                // Xóa hàng khỏi bảng ngay lập tức
                                var row = deleteButton.closest('tr');
                                row.fadeOut('fast', function() {
                                    $(this).remove();
                                    
                                    // Cập nhật lại số thứ tự
                                    $('table tbody tr').each(function(index) {
                                        var currentPage = {{ $variants->currentPage() }};
                                        var perPage = {{ $variants->perPage() }};
                                        $(this).find('td:first').text(((currentPage - 1) * perPage) + index + 1);
                                    });
                                });
                                
                                // Hiển thị thông báo thành công
                                var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                    response.message +
                                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                    '</div>';
                                $('#variant-list .card-body').prepend(alertHtml);
                                
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
                                $('#variant-list .card-body').prepend(alertHtml);
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = '';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else {
                                errorMessage = 'Có lỗi xảy ra khi xóa biến thể';
                            }
                            
                            // Hiển thị thông báo lỗi
                            var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                errorMessage +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            $('#variant-list .card-body').prepend(alertHtml);
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