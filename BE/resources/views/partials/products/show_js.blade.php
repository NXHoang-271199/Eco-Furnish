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

        function changeMainImage(imageUrl, thumbnailElement) {
            // Thêm hiệu ứng fade out cho ảnh chính
            const mainImage = document.getElementById('main-product-image');
            
            // Hiệu ứng fade out
            mainImage.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            mainImage.style.opacity = '0';
            mainImage.style.transform = 'scale(0.98)';
            
            // Thay đổi nguồn ảnh sau khi hiệu ứng fade out hoàn thành
            setTimeout(() => {
                mainImage.setAttribute('src', imageUrl);
                
                // Hiệu ứng fade in cho ảnh mới
                mainImage.style.opacity = '1';
                mainImage.style.transform = 'scale(1)';
            }, 300);
            
            // Xóa lớp active khỏi tất cả các thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail-wrapper');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            
            // Thêm lớp active cho thumbnail đã chọn
            thumbnailElement.classList.add('active');
        }

        function scrollGallery(direction) {
            const galleryContainer = document.querySelector('.gallery-container');
            const scrollAmount = 100; // Số pixel để cuộn
            
            // Hiệu ứng cuộn mượt
            if (direction === 'prev') {
                galleryContainer.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            } else {
                galleryContainer.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            }
        }

        function confirmDelete(button) {
            Swal.fire({
                title: 'Bạn có chắc chắn muốn xóa sản phẩm này?',
                text: "Thao tác này không thể hoàn tác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }

        // Định nghĩa Observer để xử lý hiệu ứng lướt chuột
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý hiệu ứng cho bảng biến thể khi lăn chuột
            const variantRows = document.querySelectorAll('.variant-table tbody tr');
            
            // Tạo Intersection Observer để theo dõi khi các hàng xuất hiện trong viewport
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        // Thêm animation với delay tăng dần để tạo hiệu ứng lần lượt
                        setTimeout(() => {
                            entry.target.style.opacity = '0';
                            entry.target.style.transform = 'translateY(20px)';
                            
                            // Kích hoạt animation
                            setTimeout(() => {
                                entry.target.style.transition = 'all 0.5s ease';
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            }, 50);
                            
                            // Ngừng quan sát phần tử này sau khi đã áp dụng hiệu ứng
                            observer.unobserve(entry.target);
                        }, index * 120); // Thời gian delay tăng dần cho mỗi hàng
                    }
                });
            }, { threshold: 0.1 });
            
            // Quan sát mỗi hàng trong bảng
            variantRows.forEach(row => {
                observer.observe(row);
            });
            
            // Xử lý sự kiện bấm vào tab biến thể
            const variantsTab = document.getElementById('nav-variants-tab');
            const detailsTab = document.getElementById('nav-details-tab');
            
            if (variantsTab && detailsTab) {
                // Thêm hiệu ứng khi chuyển tab
                variantsTab.addEventListener('click', function() {
                    // Reset animation cho các hàng khi tab được mở lại
                    variantRows.forEach(row => {
                        row.style.opacity = '0';
                        row.style.transform = 'translateY(20px)';
                        row.style.transition = 'none';
                        
                        // Theo dõi lại các hàng
                        observer.observe(row);
                    });
                });
                
                // Hiệu ứng nút chuyển tab
                const switchTabBtn = document.querySelector('.btn-switch-tab');
                if (switchTabBtn) {
                    switchTabBtn.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-3px) scale(1.05)';
                    });
                    
                    switchTabBtn.addEventListener('mouseleave', function() {
                        this.style.transform = '';
                    });
                    
                    switchTabBtn.addEventListener('click', function() {
                        // Thêm hiệu ứng pulse khi bấm nút
                        this.classList.add('pulse');
                        setTimeout(() => {
                            this.classList.remove('pulse');
                        }, 800);
                        
                        // Cuộn trang xuống phần biến thể nếu nằm ngoài viewport
                        const tabContent = document.getElementById('nav-variants');
                        if (tabContent) {
                            setTimeout(() => {
                                const rect = tabContent.getBoundingClientRect();
                                if (rect.top < 0 || rect.bottom > window.innerHeight) {
                                    tabContent.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'center'
                                    });
                                }
                            }, 400);
                        }
                    });
                }
            }
            
            // Thêm hiệu ứng cho gallery
            const galleryContainer = document.querySelector('.gallery-container');
            if (galleryContainer) {
                const thumbnails = galleryContainer.querySelectorAll('.thumbnail-wrapper');
                
                thumbnails.forEach((thumb, index) => {
                    // Thêm animation xuất hiện
                    thumb.style.opacity = '0';
                    thumb.style.transform = 'translateY(10px)';
                    
                    setTimeout(() => {
                        thumb.style.transition = 'all 0.4s ease';
                        thumb.style.opacity = thumb.classList.contains('active') ? '1' : '0.5';
                        thumb.style.transform = 'translateY(0)';
                    }, 100 + index * 100);
                    
                    // Thêm hiệu ứng hover
                    thumb.addEventListener('mouseenter', function() {
                        if (!this.classList.contains('active')) {
                            this.style.opacity = '1';
                            this.style.transform = 'scale(1.05)';
                        }
                    });
                    
                    thumb.addEventListener('mouseleave', function() {
                        if (!this.classList.contains('active')) {
                            this.style.opacity = '0.5';
                            this.style.transform = 'scale(0.95)';
                        }
                    });
                });
            }
        });
    </script>