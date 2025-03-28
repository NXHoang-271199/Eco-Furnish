import { createSlice } from "@reduxjs/toolkit";

// Load cart from localStorage
const loadCart = () => {
  try {
    const serializedCart = localStorage.getItem('cart');
    if (serializedCart === null) {
      return [];
    }
    return JSON.parse(serializedCart);
  } catch (err) {
    console.error('Error loading cart:', err);
    return [];
  }
};

// Save cart to localStorage
const saveCart = (cart) => {
  try {
    const serializedCart = JSON.stringify(cart);
    localStorage.setItem('cart', serializedCart);
  } catch (err) {
    console.error('Error saving cart:', err);
  }
};

const initialState = {
  items: loadCart(),
  subtotal: 0,
  discount: 0,
  discountCode: "",
  shipping: 30000, // Phí vận chuyển mặc định 30,000đ
  total: 0,
};

const cartSlice = createSlice({
  name: "cart",
  initialState,
  reducers: {
    addToCart: (state, action) => {
      const { product, quantity, variants } = action.payload;
      
      // Kiểm tra xem sản phẩm đã tồn tại trong giỏ hàng chưa
      const existingItemIndex = state.items.findIndex(
        (item) => 
          item.product.id === product.id && 
          JSON.stringify(item.variants) === JSON.stringify(variants)
      );

      if (existingItemIndex !== -1) {
        // Nếu sản phẩm đã tồn tại, cập nhật số lượng
        state.items[existingItemIndex].quantity += quantity;
      } else {
        // Nếu sản phẩm chưa tồn tại, thêm mới
        state.items.push({
          product,
          quantity,
          variants,
        });
      }

      // Lưu vào localStorage
      saveCart(state.items);

      // Tính lại tổng tiền
      state.subtotal = state.items.reduce(
        (total, item) =>
          total +
          (item.product.discount_price || item.product.price) * item.quantity,
        0
      );
      state.total = state.subtotal - state.discount + state.shipping;
    },
    removeFromCart: (state, action) => {
      const { productId, variants } = action.payload;
      state.items = state.items.filter(
        (item) => 
          item.product.id !== productId || 
          JSON.stringify(item.variants) !== JSON.stringify(variants)
      );
      saveCart(state.items);

      // Tính lại tổng tiền
      state.subtotal = state.items.reduce(
        (total, item) =>
          total +
          (item.product.discount_price || item.product.price) * item.quantity,
        0
      );
      state.total = state.subtotal - state.discount + state.shipping;
    },
    updateQuantity: (state, action) => {
      const { productId, variants, quantity } = action.payload;
      const item = state.items.find(
        (item) => 
          item.product.id === productId && 
          JSON.stringify(item.variants) === JSON.stringify(variants)
      );
      if (item) {
        item.quantity = quantity;
        saveCart(state.items);
      }

      // Tính lại tổng tiền
      state.subtotal = state.items.reduce(
        (total, item) =>
          total +
          (item.product.discount_price || item.product.price) * item.quantity,
        0
      );
      state.total = state.subtotal - state.discount + state.shipping;
    },
    applyDiscount: (state, action) => {
      const { code, amount } = action.payload;
      state.discount = amount;
      state.discountCode = code;
      state.total = state.subtotal - state.discount + state.shipping;
    },
    clearCart: (state) => {
      state.items = [];
      localStorage.removeItem('cart');
      state.subtotal = 0;
      state.discount = 0;
      state.discountCode = "";
      state.total = state.shipping;
    },
  },
});

export const {
  addToCart,
  removeFromCart,
  updateQuantity,
  applyDiscount,
  clearCart,
} = cartSlice.actions;

export default cartSlice.reducer;
