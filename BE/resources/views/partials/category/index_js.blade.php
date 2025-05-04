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
                            // Nếu có redirect URL, chuyển hướng ngay lập tức
                            if (response.redirect) {
                                window.location.href = response.redirect;
                                return;
                            }
                            
                            // Thêm thông báo thành công sử dụng SweetAlert2
                            Swal.fire({
                                title: 'Thành công!',
                                text: 'Đã thêm danh mục mới thành công',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            
                            // Thêm danh mục mới vào bảng
                            var table = $('table tbody');
                            var newRow = $('<tr></tr>');
                            var currentPage = {{ $categories->currentPage() }};
                            var perPage = {{ $categories->perPage() }};
                            var rowCount = table.find('tr').length;
                            var newIndex = ((currentPage - 1) * perPage) + rowCount + 1;
                            
                            // Sử dụng trực tiếp spacesDisplay từ phản hồi nếu có
                            var spacesDisplay = response.category.spacesDisplay || 'Chưa phân loại';
                            
                            // Tạo các ô dữ liệu
                            newRow.append('<td>' + newIndex + '</td>');
                            newRow.append('<td>' + response.category.name + '</td>');
                            newRow.append('<td>' + spacesDisplay + '</td>'); // Sử dụng chuỗi đã định dạng sẵn
                            newRow.append(
                                '<td>' +
                                '<div class="hstack gap-3 fs-15">' +
                                '<a href="javascript:void(0);" class="link-primary edit-trigger" data-id="' + response.category.id + '" data-bs-toggle="tooltip" data-bs-placement="top" title="Sửa">' +
                                '<i class="ri-pencil-fill align-bottom me-2"></i>' +
                                '</a>' +
                                '<a href="javascript:void(0);" class="link-danger delete-item" data-id="' + response.category.id + '" data-bs-toggle="tooltip" data-bs-placement="top" title="Xóa">' +
                                '<i class="ri-delete-bin-fill align-bottom"></i>' +
                                '</a>' +
                                '</div>' +
                                '</td>'
                            );
                            
                            // Thêm dòng mới vào đầu bảng thay vì cuối
                            table.prepend(newRow);
                            
                            // Cập nhật STT của tất cả các dòng
                            table.find('tr').each(function(index) {
                                $(this).find('td:first').text(index + 1);
                            });
                            
                            // Khởi tạo tooltip cho các phần tử mới
                            var newTooltips = [].slice.call(newRow[0].querySelectorAll('[data-bs-toggle="tooltip"]'));
                            newTooltips.forEach(function(element) {
                                new bootstrap.Tooltip(element);
                            });
                            
                            // Làm nổi bật dòng mới thêm vào
                            newRow.addClass('table-success');
                            setTimeout(function() {
                                newRow.removeClass('table-success');
                            }, 2000);
                            
                            // Xóa form
                            $('#categoryForm')[0].reset();
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

            // Xử lý khi click vào nút edit
            $(document).on('click', '.edit-trigger', function(e) {
                e.preventDefault();
                var categoryId = $(this).data('id');

                // Thay đổi cách xử lý: Gọi trực tiếp API lấy dữ liệu danh mục
                const getDataUrl = `/admin/categories/${categoryId}/data`;

                $.ajax({
                    url: getDataUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            const categoryData = response.data;

                            // Điền tên
                            editNameInput.val(categoryData.name);
                            editCategoryIdInput.val(categoryData.id);

                            // --- Xử lý Select Multiple ---
                            // Reset lựa chọn cũ trên select multiple
                            editSpacesSelect.val(null); // Đặt giá trị là null để xóa các lựa chọn

                            // Chọn các options tương ứng với categoryData.spaces
                            if (categoryData.spaces && Array.isArray(categoryData.spaces)) {
                                editSpacesSelect.val(categoryData.spaces);
                            }

                            // Cập nhật action form
                            const updateUrl = `/admin/categories/${categoryData.id}`;
                            editForm.attr('action', updateUrl);

                            // Hiển thị form sửa
                            addBlock.hide();
                            editBlock.show();
                            $('html, body').animate({ scrollTop: editBlock.offset().top - 100 }, 500);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: response.message || 'Không thể lấy dữ liệu danh mục.',
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                         console.error("AJAX error:", textStatus, errorThrown);
                         Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: 'Có lỗi xảy ra khi kết nối đến máy chủ.',
                        });
                    }
                });
            });

            // Xử lý chỉnh sửa danh mục trực tiếp
            $(document).on('click', '.edit-name', function() {
                var td = $(this);
                // Kiểm tra nếu đã có input thì không tạo input mới
                if (td.find('input').length > 0) {
                    return;
                }
                
                var categoryId = td.data('id');
                var currentName = td.text().trim();
                
                // Tạo input để chỉnh sửa
                var input = $('<input>')
                    .attr('type', 'text')
                    .val(currentName)
                    .addClass('form-control');
                
                // Thay thế text bằng input
                td.html(input);
                input.focus();
                
                // Xử lý khi nhấn Enter
                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        var newName = $(this).val().trim();
                        
                        if (newName !== '' && newName !== currentName) {
                            // Gửi request cập nhật
                            $.ajax({
                                url: '/admin/categories/' + categoryId,
                                type: 'PUT',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    name: newName
                                },
                                success: function(response) {
                                    if (response.success) {
                                        td.html(newName);
                                        // Hiển thị thông báo thành công dạng toast
                                        Toastify({
                                            text: response.message || 'Cập nhật danh mục thành công',
                                            duration: 3000,
                                            close: true,
                                            gravity: "top",
                                            position: "right",
                                            className: "bg-success",
                                            style: {
                                                background: "var(--vz-success)",
                                                color: "#fff",
                                                boxShadow: "0 10px 20px -10px var(--vz-success)"
                                            }
                                        }).showToast();
                                    }
                                },
                                error: function(xhr) {
                                    var errorMessage = '';
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        var errors = xhr.responseJSON.errors;
                                        for (var key in errors) {
                                            errorMessage += errors[key][0] + '\n';
                                        }
                                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage = xhr.responseJSON.message;
                                    } else {
                                        errorMessage = 'Có lỗi xảy ra khi cập nhật danh mục';
                                    }
                                    
                                    // Hiển thị thông báo lỗi dạng toast
                                    Toastify({
                                        text: errorMessage,
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
                                    
                                    // Khôi phục tên cũ
                                    td.html(currentName);
                                }
                            });
                        } else {
                            // Khôi phục tên cũ nếu không có thay đổi hoặc input rỗng
                            td.html(currentName);
                        }
                    }
                });

                // Xử lý khi click ra ngoài
                input.on('blur', function() {
                    setTimeout(function() {
                        td.html(currentName); // Khôi phục lại tên cũ khi click ra ngoài
                    }, 200); // Thêm độ trễ để đảm bảo sự kiện Enter được xử lý trước
                });
            });

            const addBlock = $('#addCategoryBlock');
            const editBlock = $('#editCategoryBlock');
            const editForm = $('#editCategoryForm');
            const editNameInput = $('#edit_name');
            const editSpacesSelect = $('#edit_spaces');
            const editCategoryIdInput = $('#edit_category_id'); // Input ẩn lưu ID
            const cancelEditBtn = $('#cancelEditBtn');

            // Xử lý khi click nút Hủy
            cancelEditBtn.on('click', function() {
                editBlock.hide();
                addBlock.show();
                editForm[0].reset();
                editSpacesSelect.val(null); // Reset select multiple
                // Nếu dùng choices.js, cũng cần reset nó
                // const choicesInstance = editSpacesSelect[0].choices;
                // if (choicesInstance) { choicesInstance.clearStore(); choicesInstance.clearInput(); }
                editForm.attr('action', '');
            });

            // Xử lý nếu có lỗi validation khi update
            const editIdOnError = @json(session('edit_id'));
            const hasUpdateErrors = @json($errors->update->isNotEmpty());

            if (editIdOnError && hasUpdateErrors) {
                editNameInput.val(@json(old('name')));
                editCategoryIdInput.val(editIdOnError);
                // Lấy lại giá trị spaces cũ và chọn lại select multiple
                const oldSpaces = @json(old('spaces', [])); // Lấy mảng spaces cũ
                 editSpacesSelect.val(oldSpaces);
                // Nếu dùng choices.js, cập nhật lại
                // const choicesInstance = editSpacesSelect[0].choices;
                // if (choicesInstance) { choicesInstance.setChoiceByValue(oldSpaces); }


                const updateUrl = `/admin/categories/${editIdOnError}`;
                editForm.attr('action', updateUrl);

                addBlock.hide();
                editBlock.show();
            }

            // Sử dụng event delegation cho nút xóa để xử lý các hàng được thêm vào sau
            $(document).on('click', '.delete-item', function() {
                const categoryId = $(this).data('id');
                const deleteUrl = `/admin/categories/${categoryId}`;

                Swal.fire({
                    title: 'Bạn chắc chắn muốn xóa?',
                    text: "Danh mục sẽ bị chuyển vào thùng rác!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Đồng ý, xóa!',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                         // Thay đổi từ form submit sang AJAX request
                         $.ajax({
                             url: deleteUrl,
                             type: 'POST',
                             data: {
                                 _token: '{{ csrf_token() }}',
                                 _method: 'DELETE'
                             },
                             success: function(response) {
                                 if (response.success) {
                                     Swal.fire({
                                         title: 'Đã xóa!',
                                         text: 'Danh mục đã được chuyển vào thùng rác.',
                                         icon: 'success',
                                         showConfirmButton: false,
                                         timer: 1500
                                     }).then(() => {
                                         if (response.redirect) {
                                             window.location.href = response.redirect;
                                         } else {
                                             window.location.reload();
                                         }
                                     });
                                 }
                             },
                             error: function(xhr) {
                                 let errorMessage = 'Có lỗi xảy ra khi xóa danh mục';
                                 if (xhr.responseJSON && xhr.responseJSON.message) {
                                     errorMessage = xhr.responseJSON.message;
                                 }
                                 
                                 Swal.fire({
                                     title: 'Lỗi!',
                                     text: errorMessage,
                                     icon: 'error'
                                 });
                             }
                         });
                    }
                });
            });

            // Khởi tạo tooltips (nếu dùng Bootstrap)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

        });
    </script>