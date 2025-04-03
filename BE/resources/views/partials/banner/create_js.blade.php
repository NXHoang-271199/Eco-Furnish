<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image');
        const previewContainer = document.getElementById('preview-container');
        const placeholder = previewContainer.querySelector('.placeholder');
        const removeButton = document.getElementById('remove-image');
        
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (previewContainer.querySelector('img')) {
                        previewContainer.querySelector('img').remove();
                    }
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    previewContainer.appendChild(img);
                    previewContainer.classList.add('has-image');
                    placeholder.style.display = 'none';
                }
                
                reader.readAsDataURL(file);
            }
        });
        
        removeButton.addEventListener('click', function() {
            if (previewContainer.querySelector('img')) {
                previewContainer.querySelector('img').remove();
                previewContainer.classList.remove('has-image');
                placeholder.style.display = 'block';
                imageInput.value = '';
            }
        });

        // Modern toggle switch functionality
        const toggleSwitch = document.querySelector('.toggle-switch-modern input');
        if (toggleSwitch) {
            // Khởi tạo trạng thái text ban đầu
            const statusOn = document.querySelector('.toggle-switch-modern .status-on');
            const statusOff = document.querySelector('.toggle-switch-modern .status-off');
            
            if (toggleSwitch.checked) {
                statusOn.style.display = 'inline-block';
                statusOff.style.display = 'none';
            } else {
                statusOn.style.display = 'none'; 
                statusOff.style.display = 'inline-block';
            }

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
        }
    });
</script> 