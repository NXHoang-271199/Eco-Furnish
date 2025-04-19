import { useState, useEffect } from 'react';

// Hook để theo dõi việc tải trước hình ảnh
const useImagePreloader = () => {
    const [imagesLoaded, setImagesLoaded] = useState(false);

    useEffect(() => {
        // Tính toán số lượng hình ảnh cần phải tải
        const calculateImagesToLoad = () => {
            const images = document.querySelectorAll('img');
            let loadedImages = 0;
            const totalImages = images.length;

            if (totalImages === 0) {
                // Nếu không có hình ảnh, đánh dấu là đã tải xong
                setImagesLoaded(true);
                return;
            }

            // Kiểm tra từng hình ảnh
            images.forEach((img) => {
                if (img.complete) {
                    loadedImages++;
                } else {
                    img.onload = () => {
                        loadedImages++;
                        if (loadedImages === totalImages) {
                            setImagesLoaded(true);
                        }
                    };

                    // Xử lý lỗi tải hình ảnh (vẫn tính là đã tải)
                    img.onerror = () => {
                        loadedImages++;
                        if (loadedImages === totalImages) {
                            setImagesLoaded(true);
                        }
                    };
                }
            });

            // Kiểm tra nếu tất cả hình ảnh đã tải xong ngay từ đầu
            if (loadedImages === totalImages) {
                setImagesLoaded(true);
            }
        };

        // Đảm bảo DOM đã sẵn sàng trước khi kiểm tra
        const checkImages = () => {
            setTimeout(calculateImagesToLoad, 500);
        };

        // Khi DOM sẵn sàng, bắt đầu kiểm tra
        if (document.readyState === 'complete') {
            checkImages();
        } else {
            window.addEventListener('load', checkImages);
            return () => window.removeEventListener('load', checkImages);
        }
    }, []);

    return { imagesLoaded };
};

export default useImagePreloader; 