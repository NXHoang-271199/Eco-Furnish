import { useState, useEffect } from 'react';
import axiosInstance from '../utils/axiosConfig';
import { toast } from 'react-hot-toast';
import { AiOutlineClose } from 'react-icons/ai';
import { FaShoppingCart } from 'react-icons/fa';
import { formatCurrency } from '../lib/utils';

const VariantSelectionModal = ({
    isOpen,
    onClose,
    product,
    onAddToCart,
}) => {
    const [selectedVariantAttributes, setSelectedVariantAttributes] = useState({});
    const [selectedVariantId, setSelectedVariantId] = useState(null);
    const [quantity, setQuantity] = useState(1);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (isOpen && product && product.variants) {
            // Reset selection when modal opens
            setSelectedVariantAttributes({});
            setSelectedVariantId(null);
            setQuantity(1);

            // Debug để kiểm tra dữ liệu sản phẩm
            console.log('Product data in modal:', product);
            if (product.variants) {
                console.log('Variants data:', product.variants);
            }
        }
    }, [isOpen, product]);

    // Handle attribute selection
    const handleVariantAttributeChange = (variantName, value) => {
        const newAttributes = {
            ...selectedVariantAttributes,
            [variantName]: value,
        };
        setSelectedVariantAttributes(newAttributes);

        // Find the correct variant ID based on selected attributes
        if (product && product.variants && Array.isArray(product.variants)) {
            const matchedVariant = findMatchingVariant(newAttributes);
            setSelectedVariantId(matchedVariant ? matchedVariant.id : null);

            // Debug
            console.log('Selected variant:', matchedVariant);
        }
    };

    // Find matching variant based on selected attributes
    const findMatchingVariant = (attributes) => {
        if (!product || !product.variants) return null;

        const attributeKeys = Object.keys(attributes);
        if (attributeKeys.length === 0) return null;

        return product.variants.find((variant) => {
            if (!variant.variant_details || !Array.isArray(variant.variant_details)) {
                return false;
            }

            const variantAttributes = {};
            variant.variant_details.forEach((detail) => {
                if (detail && detail.name && detail.value) {
                    variantAttributes[detail.name] = detail.value;
                }
            });

            // Check if all selected attributes match
            return attributeKeys.every(
                (key) => variantAttributes[key] === attributes[key]
            );
        });
    };

    // Get current price based on selected variant
    const getCurrentPrice = () => {
        if (!product) return 0;

        if (selectedVariantId && product.variants) {
            const selectedVariant = product.variants.find(
                (v) => v.id === selectedVariantId
            );
            if (selectedVariant) {
                return selectedVariant.discount_price || selectedVariant.price || 0;
            }
        }

        return product.discount_price || product.price || 0;
    };

    // Get stock quantity based on selected variant
    const getStockQuantity = () => {
        if (!product) return 0;

        try {
            if (selectedVariantId && product.variants) {
                const selectedVariant = product.variants.find(
                    (v) => v.id === selectedVariantId
                );
                if (selectedVariant) {
                    // Thêm log để kiểm tra dữ liệu
                    console.log('Selected variant data:', selectedVariant);
                    console.log('Selected variant quantity:', selectedVariant.quantity);

                    // Kiểm tra rõ ràng giá trị undefined và null
                    if (selectedVariant.quantity === undefined || selectedVariant.quantity === null) {
                        console.warn('Variant quantity is undefined or null');
                        return 0;
                    }

                    return parseInt(selectedVariant.quantity) || 0;
                }
            }

            // Nếu không tìm thấy biến thể hoặc không có biến thể nào được chọn
            return product.quantity ? parseInt(product.quantity) : 0;
        } catch (error) {
            console.error("Error getting stock quantity:", error);
            return 0;
        }
    };

    // Increment quantity
    const increaseQuantity = () => {
        const stockQuantity = getStockQuantity();
        if (quantity < stockQuantity) {
            setQuantity(quantity + 1);
        }
    };

    // Decrement quantity
    const decreaseQuantity = () => {
        if (quantity > 1) {
            setQuantity(quantity - 1);
        }
    };

    // Add to cart
    const handleAddToCart = async () => {
        if (!selectedVariantId) {
            toast.error("Vui lòng chọn biến thể sản phẩm");
            return;
        }

        // Kiểm tra số lượng tồn kho
        const stockQty = getStockQuantity();
        console.log('Stock quantity check:', stockQty);

        if (stockQty <= 0) {
            toast.error("Sản phẩm đã hết hàng");
            return;
        }

        setLoading(true);

        try {
            const cartData = {
                product_id: product.id,
                product_variant_id: selectedVariantId,
                quantity: quantity,
            };

            console.log('Sending cart data:', cartData);

            const response = await axiosInstance.post("/cart/add", cartData);

            if (response.status === 201) {
                toast.success("Đã thêm sản phẩm vào giỏ hàng!");
                onAddToCart && onAddToCart(product, selectedVariantId, quantity);
                onClose();
            }
        } catch (error) {
            console.error("Lỗi khi thêm vào giỏ hàng:", error);
            toast.error(error.response?.data?.message || "Có lỗi xảy ra khi thêm vào giỏ hàng");
        } finally {
            setLoading(false);
        }
    };

    // Render variant options
    const renderVariants = () => {
        if (
            !product ||
            !product.variants ||
            !Array.isArray(product.variants) ||
            product.variants.length === 0
        ) {
            return null;
        }

        try {
            // Extract unique variant details
            const uniqueVariantDetails = product.variants.reduce((acc, variant) => {
                if (variant.variant_details && Array.isArray(variant.variant_details)) {
                    variant.variant_details.forEach((detail) => {
                        if (!detail || !detail.name) return;

                        if (!acc[detail.name]) {
                            acc[detail.name] = new Set();
                        }

                        if (detail.value) {
                            acc[detail.name].add(detail.value);
                        }
                    });
                }
                return acc;
            }, {});

            return (
                <div className="space-y-4 mt-4">
                    {Object.entries(uniqueVariantDetails).map(([variantName, values], index) => (
                        <div key={index} className="space-y-2">
                            <label className="block text-sm font-medium text-gray-700">
                                {variantName}:
                            </label>
                            <div className="flex flex-wrap gap-2">
                                {Array.from(values).map((value) => (
                                    <button
                                        key={value}
                                        onClick={() => handleVariantAttributeChange(variantName, value)}
                                        className={`px-3 py-1 rounded-md text-sm font-medium ${selectedVariantAttributes[variantName] === value
                                            ? "bg-blue-500 text-white"
                                            : "bg-gray-100 text-gray-800 hover:bg-gray-200"
                                            }`}
                                    >
                                        {value}
                                    </button>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            );
        } catch (error) {
            console.error("Error rendering variants:", error);
            return <div className="text-red-500">Lỗi hiển thị biến thể</div>;
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
                {/* Header */}
                <div className="px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 flex justify-between items-center">
                    <h3 className="text-lg font-medium text-white">Chọn biến thể sản phẩm</h3>
                    <button
                        onClick={onClose}
                        className="text-white hover:text-gray-200 transition-colors"
                    >
                        <AiOutlineClose size={24} />
                    </button>
                </div>

                {/* Content */}
                <div className="p-4">
                    {product && (
                        <>
                            {/* Product info */}
                            <div className="flex items-center mb-4">
                                <div className="w-16 h-16 bg-gray-100 rounded-md overflow-hidden flex-shrink-0">
                                    {product.image_thumnail || product.image ? (
                                        <img
                                            src={product.image_thumnail ? `/storage/${product.image_thumnail}` : `/storage/${product.image}`}
                                            alt={product.name}
                                            className="w-full h-full object-cover"
                                            onError={(e) => {
                                                console.log("Lỗi tải ảnh:", e.target.src);
                                                e.target.onerror = null;
                                                e.target.src = "https://via.placeholder.com/150?text=Hình+ảnh";
                                            }}
                                        />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center bg-gray-200 text-gray-500">
                                            Không có ảnh
                                        </div>
                                    )}
                                </div>
                                <div className="ml-4">
                                    <h4 className="font-medium text-gray-800">{product.name}</h4>
                                    <div className="flex items-center mt-1">
                                        <p className="text-blue-600 font-bold">
                                            {formatCurrency(getCurrentPrice())}
                                        </p>
                                        {product.discount_price && product.price !== product.discount_price && (
                                            <p className="ml-2 text-gray-400 line-through text-sm">
                                                {formatCurrency(product.price)}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Variant selection */}
                            <div className="mb-4">
                                <h5 className="font-medium text-gray-700 mb-2">Lựa chọn biến thể:</h5>
                                {renderVariants()}
                            </div>

                            {/* Quantity */}
                            <div className="mb-6">
                                <h5 className="font-medium text-gray-700 mb-2">Số lượng:</h5>
                                <div className="flex items-center">
                                    <button
                                        onClick={decreaseQuantity}
                                        className="w-8 h-8 flex items-center justify-center rounded-l border border-gray-300 bg-gray-100 text-gray-600"
                                        disabled={quantity <= 1}
                                    >
                                        -
                                    </button>
                                    <input
                                        type="number"
                                        value={quantity}
                                        onChange={(e) => setQuantity(Math.max(1, parseInt(e.target.value) || 1))}
                                        className="w-16 h-8 border-t border-b border-gray-300 text-center"
                                    />
                                    <button
                                        onClick={increaseQuantity}
                                        className="w-8 h-8 flex items-center justify-center rounded-r border border-gray-300 bg-gray-100 text-gray-600"
                                        disabled={quantity >= getStockQuantity()}
                                    >
                                        +
                                    </button>
                                    <span className="ml-3 text-sm text-gray-500">
                                        {getStockQuantity() > 0 ? (
                                            <>{getStockQuantity()} sản phẩm có sẵn</>
                                        ) : (
                                            <span className="text-red-500 font-medium">Hết hàng</span>
                                        )}
                                    </span>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex justify-end">
                                <button
                                    onClick={onClose}
                                    className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 mr-2"
                                >
                                    Hủy
                                </button>
                                <button
                                    onClick={handleAddToCart}
                                    disabled={!selectedVariantId || loading || getStockQuantity() <= 0}
                                    className={`px-4 py-2 rounded-md text-white flex items-center ${!selectedVariantId || loading || getStockQuantity() <= 0
                                        ? "bg-gray-400 cursor-not-allowed"
                                        : "bg-blue-600 hover:bg-blue-700"
                                        }`}
                                >
                                    <FaShoppingCart className="mr-2" />
                                    {loading ? "Đang xử lý..." : getStockQuantity() <= 0 ? "Hết hàng" : "Thêm vào giỏ hàng"}
                                </button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

export default VariantSelectionModal; 