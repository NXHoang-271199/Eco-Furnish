import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  items: [],
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
      const existingItem = state.items.find(
        (item) =>
          item.product.id === product.id &&
          JSON.stringify(item.variants) === JSON.stringify(variants)
      );

      if (existingItem) {
        existingItem.quantity += quantity;
      } else {
        state.items.push({
          product,
          quantity,
          variants,
        });
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
    removeFromCart: (state, action) => {
      const { productId, variants } = action.payload;
      state.items = state.items.filter(
        (item) =>
          !(
            item.product.id === productId &&
            JSON.stringify(item.variants) === JSON.stringify(variants)
          )
      );

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
      const { productId, quantity, variants } = action.payload;
      const item = state.items.find(
        (item) =>
          item.product.id === productId &&
          JSON.stringify(item.variants) === JSON.stringify(variants)
      );

      if (item) {
        item.quantity = quantity;
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
