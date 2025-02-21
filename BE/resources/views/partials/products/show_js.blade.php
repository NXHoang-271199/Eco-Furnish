  <!-- Swiper js -->
  <script src="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Force horizontal layout
            const galleryContainer = document.querySelector('.gallery-container');
            if (galleryContainer) {
                
                // Force layout recalculation
                galleryContainer.style.display = 'flex';
                galleryContainer.style.flexDirection = 'row';
                galleryContainer.style.alignItems = 'center';
                
               
            }

            // Initialize first thumbnail
            const firstThumbnail = document.querySelector('.thumbnail-wrapper');
            if (firstThumbnail) {
                firstThumbnail.classList.add('active');
            }
        });

        function changeMainImage(src) {
            const mainImage = document.getElementById('main-product-image');
            const thumbnails = document.querySelectorAll('.thumbnail-wrapper');
            
            if (mainImage) {
                mainImage.src = src;
                
                thumbnails.forEach(thumb => {
                    thumb.classList.remove('active');
                });
                
                const activeThumbnail = Array.from(thumbnails).find(thumb => {
                    const img = thumb.querySelector('img');
                    return img && img.src === src;
                });
                
                if (activeThumbnail) {
                    activeThumbnail.classList.add('active');
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
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>