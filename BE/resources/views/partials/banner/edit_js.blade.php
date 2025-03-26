<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image');
        const previewContainer = document.getElementById('preview-container');
        const placeholder = previewContainer.querySelector('.placeholder');
        const removeButton = document.getElementById('remove-image');
        const currentImage = document.getElementById('current-image');
        
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (currentImage) {
                        currentImage.src = e.target.result;
                    } else {
                        if (previewContainer.querySelector('img')) {
                            previewContainer.querySelector('img').remove();
                        }
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        previewContainer.appendChild(img);
                        
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    }
                    
                    previewContainer.classList.add('has-image');
                }
                
                reader.readAsDataURL(file);
            }
        });
        
        removeButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Bạn có chắc chắn muốn xóa ảnh hiện tại?')) {
                if (currentImage) {
                    currentImage.remove();
                    previewContainer.classList.remove('has-image');
                    
                    // Tạo placeholder nếu chưa có
                    if (!placeholder) {
                        const placeholderDiv = document.createElement('div');
                        placeholderDiv.className = 'placeholder';
                        placeholderDiv.innerHTML = '<i class="fas fa-image fa-3x mb-2"></i><p>Ảnh xem trước</p>';
                        previewContainer.appendChild(placeholderDiv);
                    } else {
                        placeholder.style.display = 'block';
                    }
                    
                    // Thêm trường ẩn để xóa ảnh
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_image';
                    input.value = '1';
                    previewContainer.closest('form').appendChild(input);
                }
                
                imageInput.value = '';
            }
        });

        // Modern toggle switch functionality
        const toggleSwitch = document.querySelector('.toggle-switch-modern input');
        if (toggleSwitch) {
            toggleSwitch.addEventListener('change', function() {
                const statusOn = document.querySelector('.toggle-switch-modern .status-on');
                const statusOff = document.querySelector('.toggle-switch-modern .status-off');
                
                if (this.checked) {
                    statusOn.style.display = 'inline-block';
                    statusOff.style.display = 'none';
                } else {
                    statusOn.style.display = 'none';
                    statusOff.style.display = 'inline-block';
                }
            });

            // Trigger change event to set initial state
            toggleSwitch.dispatchEvent(new Event('change'));
        }
    });
</script> 