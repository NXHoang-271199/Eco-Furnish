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

        function changeMainImage(src) {
            const mainImage = document.getElementById('main-product-image');
            const thumbnails = document.querySelectorAll('.thumbnail-wrapper');
            
            if (mainImage) {
                // Thêm hiệu ứng fade khi đổi ảnh
                mainImage.style.opacity = '0';
                mainImage.style.transform = 'scale(0.95)';
                
                setTimeout(() => {
                    mainImage.src = src;
                    mainImage.style.transition = 'all 0.3s ease';
                    mainImage.style.opacity = '1';
                    mainImage.style.transform = 'scale(1)';
                }, 150);
                
                thumbnails.forEach(thumb => {
                    thumb.classList.remove('active');
                    thumb.style.transform = 'scale(1)';
                });
                
                const activeThumbnail = Array.from(thumbnails).find(thumb => {
                    const img = thumb.querySelector('img');
                    return img && img.src === src;
                });
                
                if (activeThumbnail) {
                    activeThumbnail.classList.add('active');
                    activeThumbnail.style.transform = 'scale(1.05)';
                }
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
                    button.closest('form').submit();
                }
            });
        }

        function scrollGallery(direction) {
            const container = document.querySelector('.gallery-container');
            const scrollAmount = 200;
            
            if (container) {
                const maxScroll = container.scrollWidth - container.clientWidth;
                let newScrollPosition;

                if (direction === 'next') {
                    if (container.scrollLeft >= maxScroll) {
                        // Cuộn mượt về đầu
                        container.style.scrollBehavior = 'smooth';
                        container.scrollLeft = 0;
                    } else {
                        // Cuộn bình thường về phải
                        newScrollPosition = container.scrollLeft + scrollAmount;
                        container.scrollTo({
                            left: newScrollPosition,
                            behavior: 'smooth'
                        });
                    }
                } else {
                    if (container.scrollLeft <= 0) {
                        // Cuộn mượt về cuối
                        container.style.scrollBehavior = 'smooth';
                        container.scrollLeft = maxScroll;
                    } else {
                        // Cuộn bình thường về trái
                        newScrollPosition = container.scrollLeft - scrollAmount;
                        container.scrollTo({
                            left: newScrollPosition,
                            behavior: 'smooth'
                        });
                    }
                }

                // Cập nhật trạng thái active cho thumbnail
                setTimeout(() => {
                    updateActiveThumbByScroll();
                }, 300);
            }
        }

        function updateActiveThumbByScroll() {
            const container = document.querySelector('.gallery-container');
            const thumbnails = container.querySelectorAll('.thumbnail-wrapper');
            
            if (!thumbnails.length) return;

            let activeIndex = 0;
            let minDistance = Infinity;

            // Tìm thumbnail gần nhất với vị trí scroll hiện tại
            thumbnails.forEach((thumb, index) => {
                const thumbRect = thumb.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const distance = Math.abs(thumbRect.left - containerRect.left);
                
                if (distance < minDistance) {
                    minDistance = distance;
                    activeIndex = index;
                }
            });

            // Cập nhật trạng thái active
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            thumbnails[activeIndex].classList.add('active');

            // Cập nhật ảnh chính
            const activeThumb = thumbnails[activeIndex].querySelector('img');
            if (activeThumb) {
                const mainImage = document.getElementById('main-product-image');
                if (mainImage && mainImage.src !== activeThumb.src) {
                    changeMainImage(activeThumb.src);
                }
            }
        }
    </script>