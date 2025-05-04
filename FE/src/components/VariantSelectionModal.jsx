import { useState, useEffect } from 'react';
import axiosInstance from '../utils/axiosConfig';
import { toast } from 'react-hot-toast';
import { AiOutlineClose } from 'react-icons/ai';
import { FaShoppingCart } from 'react-icons/fa';
import { formatCurrency } from '../lib/utils';
import { motion, AnimatePresence } from 'framer-motion';

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

            try {
                // Kiểm tra và xử lý dữ liệu biến thể
                if (product.variants && Array.isArray(product.variants) && product.variants.length > 0) {
                    console.log('Variants data:', product.variants);

                    // Đảm bảo tất cả các biến thể đều có quantity là số
                    const processedVariants = product.variants.map(variant => {
                        // Tạo bản sao của variant
                        const variantCopy = { ...variant };

                        // Đảm bảo quantity là số
                        if (variantCopy.quantity === undefined || variantCopy.quantity === null) {
                            variantCopy.quantity = 0;
                        } else if (typeof variantCopy.quantity === 'string') {
                            variantCopy.quantity = parseInt(variantCopy.quantity) || 0;
                        }

                        // Đảm bảo variant_details là array
                        if (!Array.isArray(variantCopy.variant_details)) {
                            // Nếu là object, chuyển thành array của object
                            if (typeof variantCopy.variant_details === 'object' && variantCopy.variant_details !== null) {
                                variantCopy.variant_details = Object.entries(variantCopy.variant_details).map(([name, value]) => ({
                                    name,
                                    value
                                }));
                            } else {
                                variantCopy.variant_details = [];
                            }
                        }

                        return variantCopy;
                    });

                    console.log('Processed variants:', processedVariants);

                    // Tìm biến thể đầu tiên có số lượng > 0
                    const availableVariant = processedVariants.find(variant =>
                        variant &&
                        variant.quantity > 0 &&
                        variant.variant_details &&
                        Array.isArray(variant.variant_details) &&
                        variant.variant_details.length > 0
                    );

                    console.log('Available variant:', availableVariant);

                    // Nếu có biến thể khả dụng, chọn nó
                    if (availableVariant) {
                        const initialAttributes = {};
                        availableVariant.variant_details.forEach(detail => {
                            if (detail && detail.name && detail.value) {
                                initialAttributes[detail.name] = detail.value;
                            }
                        });

                        // Cập nhật state với biến thể có sẵn
                        if (Object.keys(initialAttributes).length > 0) {
                            console.log('Setting initial attributes:', initialAttributes);
                            setSelectedVariantAttributes(initialAttributes);
                            setSelectedVariantId(availableVariant.id);
                            console.log('Auto-selected available variant:', availableVariant.id, initialAttributes, 'quantity:', availableVariant.quantity);
                        }
                    } else {
                        // Nếu không tìm thấy biến thể có sẵn, chọn biến thể đầu tiên
                        const firstVariant = processedVariants[0];
                        if (firstVariant && firstVariant.variant_details && Array.isArray(firstVariant.variant_details)) {
                            const initialAttributes = {};
                            firstVariant.variant_details.forEach(detail => {
                                if (detail && detail.name && detail.value) {
                                    initialAttributes[detail.name] = detail.value;
                                }
                            });

                            if (Object.keys(initialAttributes).length > 0) {
                                console.log('Setting initial attributes for first variant:', initialAttributes);
                                setSelectedVariantAttributes(initialAttributes);
                                setSelectedVariantId(firstVariant.id);
                                console.log('Auto-selected first variant:', firstVariant.id, initialAttributes, 'quantity:', firstVariant.quantity);
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error processing variants in modal:', error);
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
            if (selectedVariantId && product.variants && Array.isArray(product.variants)) {
                const selectedVariant = product.variants.find(v => v.id === selectedVariantId);

                if (selectedVariant) {
                    // Debug
                    console.log('Selected variant stock check:', selectedVariant.id, selectedVariant.quantity);

                    // Đảm bảo quantity là số
                    let quantity = 0;
                    if (selectedVariant.quantity === undefined || selectedVariant.quantity === null) {
                        console.log('Variant quantity is undefined or null');
                        quantity = 0;
                    } else if (typeof selectedVariant.quantity === 'string') {
                        quantity = parseInt(selectedVariant.quantity) || 0;
                        console.log('Converted string quantity to number:', quantity);
                    } else {
                        quantity = selectedVariant.quantity;
                        console.log('Using original number quantity:', quantity);
                    }

                    // Đảm bảo luôn trả về số nguyên không âm
                    const stockQty = Math.max(0, Math.floor(quantity));
                    console.log('Final stock quantity:', stockQty);
                    return stockQty;
                } else {
                    console.log('Selected variant not found in product.variants');
                }
            } else {
                console.log('No selectedVariantId or invalid product.variants:', {
                    selectedVariantId,
                    hasVariants: product ? !!product.variants : false,
                    isArray: product && product.variants ? Array.isArray(product.variants) : false
                });
            }

            // Nếu không có biến thể được chọn, sử dụng số lượng sản phẩm chung
            if (product.quantity !== undefined && product.quantity !== null) {
                let productQty = 0;
                if (typeof product.quantity === 'string') {
                    productQty = parseInt(product.quantity) || 0;
                } else {
                    productQty = product.quantity;
                }

                // Đảm bảo luôn trả về số nguyên không âm
                const finalQty = Math.max(0, Math.floor(productQty));
                console.log('Using product quantity:', finalQty);
                return finalQty;
            } else {
                console.log('Product has no quantity property');
                return 0;
            }
        } catch (error) {
            console.error('Error in getStockQuantity:', error);
            return 0;
        }
    };

    // Cập nhật hàm xử lý thay đổi số lượng
    const handleQuantityChange = (e) => {
        const newValue = parseInt(e.target.value) || 0;
        const stockQty = getStockQuantity();

        if (newValue < 1) {
            setQuantity(1);
        } else if (newValue > stockQty) {
            setQuantity(stockQty);
            toast.error(`Số lượng tối đa có thể mua là ${stockQty}`);
        } else {
            setQuantity(newValue);
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
        console.log('Stock quantity check before add to cart:', stockQty);

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
                                    <motion.button
                                        key={value}
                                        onClick={() => handleVariantAttributeChange(variantName, value)}
                                        className={`px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-all duration-300 ${selectedVariantAttributes[variantName] === value
                                            ? "bg-amber-500 text-white shadow-md"
                                            : "bg-gray-100 text-gray-800 hover:bg-gray-200"
                                            }`}
                                        whileHover={{ scale: 1.05 }}
                                        whileTap={{ scale: 0.95 }}
                                    >
                                        {value}
                                    </motion.button>
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
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                >
                    <motion.div
                        className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                        initial={{ scale: 0.9, y: 20, opacity: 0 }}
                        animate={{ scale: 1, y: 0, opacity: 1 }}
                        exit={{ scale: 0.9, y: 20, opacity: 0 }}
                        transition={{ type: "spring", damping: 25, stiffness: 300 }}
                    >
                        {/* Header */}
                        <div className="px-6 py-4 bg-gradient-to-r from-amber-500 to-amber-600 flex justify-between items-center">
                            <h3 className="text-lg font-semibold text-white">Chọn biến thể sản phẩm</h3>
                            <motion.button
                                onClick={onClose}
                                className="text-white hover:text-gray-200 transition-colors p-1 rounded-full hover:bg-amber-600/50"
                                whileHover={{ scale: 1.1 }}
                                whileTap={{ scale: 0.9 }}
                            >
                                <AiOutlineClose size={20} />
                            </motion.button>
                        </div>

                        {/* Content */}
                        <div className="p-6">
                            {product && (
                                <>
                                    {/* Product info */}
                                    <div className="flex items-center mb-6 bg-amber-50 p-4 rounded-xl">
                                        <div className="w-20 h-20 bg-white rounded-lg overflow-hidden flex-shrink-0 shadow-md">
                                            {product.image_thumnail || product.image ? (
                                                <img
                                                    src={
                                                        product.image_thumnail
                                                            ? product.image_thumnail.startsWith("http")
                                                                ? product.image_thumnail
                                                                : `${import.meta.env.VITE_API_URL}/storage/${product.image_thumnail}`
                                                            : product.image.startsWith("http")
                                                                ? product.image
                                                                : `${import.meta.env.VITE_API_URL}/storage/${product.image}`
                                                    }
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
                                            <h4 className="font-semibold text-gray-800 text-lg">{product.name}</h4>
                                            <div className="flex items-center mt-1">
                                                <p className="text-amber-600 font-bold text-xl">
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
                                    <div className="mb-6">
                                        <h5 className="font-medium text-gray-700 mb-3 flex items-center">
                                            <span className="w-1 h-5 bg-amber-500 rounded-full mr-2 inline-block"></span>
                                            Lựa chọn biến thể
                                        </h5>
                                        {renderVariants()}
                                    </div>

                                    {/* Quantity with improved styling */}
                                    <div className="mb-8">
                                        <h5 className="font-medium text-gray-700 mb-3 flex items-center">
                                            <span className="w-1 h-5 bg-amber-500 rounded-full mr-2 inline-block"></span>
                                            Số lượng
                                        </h5>
                                        <div className="flex items-center">
                                            <motion.button
                                                onClick={decreaseQuantity}
                                                className={`w-10 h-10 flex items-center justify-center rounded-l-lg border border-gray-300 ${quantity <= 1 ? 'bg-gray-100 text-gray-400' : 'bg-amber-50 text-amber-600 hover:bg-amber-100'}`}
                                                disabled={quantity <= 1}
                                                whileTap={quantity > 1 ? { scale: 0.95 } : {}}
                                            >
                                                -
                                            </motion.button>
                                            <input
                                                type="number"
                                                value={quantity}
                                                onChange={handleQuantityChange}
                                                className="w-16 h-10 border-t border-b border-gray-300 text-center outline-none text-gray-700"
                                                min="1"
                                                max={getStockQuantity()}
                                                onKeyPress={(e) => {
                                                    if (!/[0-9]/.test(e.key)) {
                                                        e.preventDefault();
                                                    }
                                                }}
                                                onPaste={(e) => {
                                                    const pastedText = e.clipboardData.getData('text');
                                                    if (!/^\d+$/.test(pastedText)) {
                                                        e.preventDefault();
                                                    }
                                                }}
                                            />
                                            <motion.button
                                                onClick={increaseQuantity}
                                                className={`w-10 h-10 flex items-center justify-center rounded-r-lg border border-gray-300 ${quantity >= getStockQuantity() ? 'bg-gray-100 text-gray-400' : 'bg-amber-50 text-amber-600 hover:bg-amber-100'}`}
                                                disabled={quantity >= getStockQuantity()}
                                                whileTap={quantity < getStockQuantity() ? { scale: 0.95 } : {}}
                                            >
                                                +
                                            </motion.button>
                                            <span className="ml-4 text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                                                Còn <span className="font-medium text-amber-600">{getStockQuantity()}</span> sản phẩm
                                            </span>
                                        </div>
                                    </div>

                                    {/* Actions */}
                                    <div className="flex justify-end gap-3 mt-6">
                                        <motion.button
                                            onClick={onClose}
                                            className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"
                                            whileHover={{ scale: 1.02, boxShadow: "0 4px 6px rgba(0,0,0,0.05)" }}
                                            whileTap={{ scale: 0.98 }}
                                        >
                                            Hủy
                                        </motion.button>
                                        <motion.button
                                            onClick={handleAddToCart}
                                            disabled={loading || (!selectedVariantId && product.has_variants) || (selectedVariantId && getStockQuantity() <= 0)}
                                            className={`px-5 py-2 rounded-lg text-white font-medium flex items-center ${loading ?
                                                "bg-gray-400 cursor-not-allowed" :
                                                (!selectedVariantId && product.has_variants) || (selectedVariantId && getStockQuantity() <= 0) ?
                                                    "bg-gray-400 cursor-not-allowed" :
                                                    "bg-amber-500 hover:bg-amber-600"
                                                }`}
                                            whileHover={!(loading || (!selectedVariantId && product.has_variants) || (selectedVariantId && getStockQuantity() <= 0)) ? { scale: 1.02, boxShadow: "0 4px 12px rgba(0,0,0,0.1)" } : {}}
                                            whileTap={!(loading || (!selectedVariantId && product.has_variants) || (selectedVariantId && getStockQuantity() <= 0)) ? { scale: 0.98 } : {}}
                                        >
                                            <FaShoppingCart className="mr-2" />
                                            {loading ?
                                                "Đang xử lý..." :
                                                (selectedVariantId && getStockQuantity() <= 0) ?
                                                    "Hết hàng" :
                                                    "Thêm vào giỏ hàng"
                                            }
                                        </motion.button>
                                    </div>
                                </>
                            )}
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
};

export default VariantSelectionModal;