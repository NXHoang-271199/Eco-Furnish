import React, { createContext, useState, useContext, useEffect } from 'react';
import useImagePreloader from '../hooks/useImagePreloader';

const LoadingContext = createContext();

export const useLoading = () => useContext(LoadingContext);

export const LoadingProvider = ({ children }) => {
    const [isLoading, setIsLoading] = useState(true);
    const [apiLoaded, setApiLoaded] = useState(false);
    const { imagesLoaded } = useImagePreloader();

    const markApiAsLoaded = () => {
        setApiLoaded(true);
    };

    // Theo dõi cả việc tải API và hình ảnh
    useEffect(() => {
        // Đặt một timeout để đảm bảo ít nhất có hiệu ứng loading hiển thị một thời gian ngắn
        const minLoadingTime = setTimeout(() => {
            if (imagesLoaded && apiLoaded) {
                setIsLoading(false);
            }
        }, 1500); // Đảm bảo loading hiển thị ít nhất 1.5 giây

        return () => clearTimeout(minLoadingTime);
    }, [imagesLoaded, apiLoaded]);

    // Thiết lập timeout dự phòng để ẩn loading sau một khoảng thời gian nhất định
    // để tránh trường hợp loading hiển thị mãi mãi nếu có lỗi
    useEffect(() => {
        const fallbackTimer = setTimeout(() => {
            setIsLoading(false);
        }, 10000); // Tối đa 10 giây

        return () => clearTimeout(fallbackTimer);
    }, []);

    const showLoading = () => setIsLoading(true);
    const hideLoading = () => setIsLoading(false);

    return (
        <LoadingContext.Provider value={{
            isLoading,
            showLoading,
            hideLoading,
            markApiAsLoaded
        }}>
            {children}
        </LoadingContext.Provider>
    );
};

export default LoadingContext; 