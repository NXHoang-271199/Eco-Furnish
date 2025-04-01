  <!-- Swiper js -->
  <script src="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeGallery();
            initializeTabEffects();
            initializeImageZoom();
        });

        function initializeGallery() {
            const galleryContainer = document.querySelector('.gallery-container');
            const firstThumbnail = document.querySelector('.thumbnail-wrapper');
            
            if (galleryContainer) {
                galleryContainer.style.opacity = '0';
                setTimeout(() => {
                    galleryContainer.style.transition = 'opacity 0.5s ease';
                    galleryContainer.style.opacity = '1';
                }, 100);
            }

            if (firstThumbnail) {
                firstThumbnail.classList.add('active');
            }
        }

        function initializeTabEffects() {
            const tabLinks = document.querySelectorAll('.nav-link');
            const tabContents = document.querySelectorAll('.tab-pane');

            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Thêm hiệu ứng fade cho tab content
                    tabContents.forEach(content => {
                        if (content.classList.contains('show')) {
                            content.style.opacity = '0';
                            setTimeout(() => {
                                content.style.opacity = '1';
                            }, 150);
                        }
                    });
                });
            });
        }

        function initializeImageZoom() {
            const mainImage = document.getElementById('main-product-image');
            if (mainImage) {
                mainImage.style.transition = 'transform 0.3s ease';
                mainImage.addEventListener('mousemove', handleImageZoom);
                mainImage.addEventListener('mouseleave', resetImageZoom);
            }
        }

        function handleImageZoom(e) {
            const image = e.target;
            const rect = image.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width;
            const y = (e.clientY - rect.top) / rect.height;
            
            const scale = 1.1;
            const moveX = (x - 0.5) * 20;
            const moveY = (y - 0.5) * 20;
            
            image.style.transform = `scale(${scale}) translate(${moveX}px, ${moveY}px)`;
        }

        function resetImageZoom(e) {
            e.target.style.transform = 'scale(1) translate(0, 0)';
        }

        function changeMainImage(src, clickedThumb) {
            const mainImage = document.getElementById('main-product-image');
            const thumbnails = document.querySelectorAll('.thumbnail-wrapper');
            const container = document.querySelector('.gallery-container');
            
            if (mainImage && clickedThumb) {
                // Cập nhật ảnh chính với hiệu ứng fade
                mainImage.style.opacity = '0';
                mainImage.style.transform = 'scale(0.95)';
                
                setTimeout(() => {
                    mainImage.src = src;
                    mainImage.style.transition = 'all 0.3s ease';
                    mainImage.style.opacity = '1';
                    mainImage.style.transform = 'scale(1)';
                }, 150);
                
                // Cập nhật trạng thái active cho thumbnails
                thumbnails.forEach(thumb => thumb.classList.remove('active'));
                clickedThumb.classList.add('active');

                // Tính toán vị trí để thumbnail được chọn nằm giữa
                const containerWidth = container.offsetWidth;
                const thumbWidth = clickedThumb.offsetWidth;
                const thumbLeft = clickedThumb.offsetLeft;
                const scrollPosition = thumbLeft - (containerWidth / 2) + (thumbWidth / 2);

                // Cuộn đến vị trí đã tính
                container.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });
            }
        }

        function scrollGallery(direction) {
            const container = document.querySelector('.gallery-container');
            const scrollAmount = 200;
            
            if (container) {
                const currentScroll = container.scrollLeft;
                const newScroll = direction === 'next' 
                    ? currentScroll + scrollAmount 
                    : currentScroll - scrollAmount;
                    
                container.scrollTo({
                    left: newScroll,
                    behavior: 'smooth'
                });
            }
        }

        function confirmDelete(button) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn có chắc chắn muốn xóa sản phẩm này không?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Có, xóa!',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                customClass: {
                    popup: 'animated zoomIn'
                },
                showClass: {
                    popup: 'animated zoomIn faster'
                },
                hideClass: {
                    popup: 'animated zoomOut faster'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = button.closest('form');
                    const formData = new FormData(form);
                    
                    // Thực hiện AJAX request
                    $.ajax({
                        url: form.action,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Thành công!',
                                    text: 'Xóa sản phẩm thành công',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.href = '/admin/products';
                                });
                            } else {
                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: response.message || 'Có lỗi xảy ra khi xóa sản phẩm',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: 'Có lỗi xảy ra khi xóa sản phẩm',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }
    </script>