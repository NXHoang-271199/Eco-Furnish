import { useSelector } from 'react-redux';
import { motion } from 'framer-motion';

const CartBadge = () => {
  const cartItems = useSelector((state) => state.cart.items);
  const totalItems = cartItems.reduce((total, item) => total + item.quantity, 0);

  if (totalItems === 0) return null;

  return (
    <motion.div
      initial={{ scale: 0 }}
      animate={{ scale: 1 }}
      className="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
    >
      {totalItems}
    </motion.div>
  );
};

export default CartBadge; 