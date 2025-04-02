import { motion, AnimatePresence } from 'framer-motion';
import { IoCartOutline } from 'react-icons/io5';

const AddToCartToast = ({ isVisible, product, onClose }) => {
  return (
    <AnimatePresence>
      {isVisible && (
        <motion.div
          initial={{ opacity: 0, y: 50, scale: 0.3 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          exit={{ opacity: 0, scale: 0.5, transition: { duration: 0.2 } }}
          className="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 z-50 max-w-sm"
        >
          <div className="flex items-start gap-4">
            <div className="flex-shrink-0">
              <div className="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <IoCartOutline className="w-6 h-6 text-orange-500" />
              </div>
            </div>
            <div className="flex-1">
              <h4 className="font-medium text-gray-900">Đã thêm vào giỏ hàng</h4>
              <p className="text-sm text-gray-500 mt-1">{product.name}</p>
              <div className="mt-2 flex items-center justify-between">
                <span className="text-sm font-medium text-orange-500">
                  {new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                  }).format(product.discount_price || product.price)}
                </span>
                <button
                  onClick={onClose}
                  className="text-sm text-gray-400 hover:text-gray-500"
                >
                  Đóng
                </button>
              </div>
            </div>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
};

export default AddToCartToast; 