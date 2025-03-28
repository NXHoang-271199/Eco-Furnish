import { createSlice } from "@reduxjs/toolkit";

// Đọc state từ localStorage nếu có
const loadState = () => {
  try {
    const serializedState = localStorage.getItem("cart");
    if (serializedState === null) {
      return {
        items: [],
        subtotal: 0,
        discount: 0,
        discountCode: "",
        shipping: 30000,
        total: 0,
      };
    }
    return JSON.parse(serializedState);
  } catch (err) {
    return {
      items: [],
      subtotal: 0,
      discount: 0,
      discountCode: "",
      shipping: 30000,
      total: 0,
    };
  }
};

const initialState = loadState();

const cartSlice = createSlice({
  name: "cart",
  initialState,
  reducers: {
    addToCart: (state, action) => {
      const { product_id, product, quantity, variant_details, price } =
        action.payload;
      const existingItem = state.items.find(
        (item) =>
          item.product.id === product_id &&
          JSON.stringify(item.variant_details) ===
            JSON.stringify(variant_details)
      );

      if (existingItem) {
        existingItem.quantity += quantity;
      } else {
        state.items.push({
          product,
          quantity,
          variant_details,
          price,
        });
      }

      // Tính lại tổng tiền
      state.subtotal = state.items.reduce(
        (total, item) => total + item.price * item.quantity,
        0
      );
      state.total = state.subtotal - state.discount + state.shipping;

      // Lưu state vào localStorage
      localStorage.setItem("cart", JSON.stringify(state));
    },
    removeFromCart: (state, action) => {
      const { productId, variant_details } = action.payload;
      state.items = state.items.filter(
        (item) =>
          !(
            item.product.id === productId &&
            JSON.stringify(item.variant_details) ===
              JSON.stringify(variant_details)
          )
      );

      // Tính lại tổng tiền
      state.subtotal = state.items.reduce(
        (total, item) => total + item.price * item.quantity,
        0
      );
      state.total = state.subtotal - state.discount + state.shipping;

      // Lưu state vào localStorage
      localStorage.setItem("cart", JSON.stringify(state));
    },
    updateQuantity: (state, action) => {
      const { productId, quantity, variant_details } = action.payload;
      const item = state.items.find(
        (item) =>
          item.product.id === productId &&
          JSON.stringify(item.variant_details) ===
            JSON.stringify(variant_details)
      );

      if (item) {
        item.quantity = quantity;
      }

      // Tính lại tổng tiền
      state.subtotal = state.items.reduce(
        (total, item) => total + item.price * item.quantity,
        0
      );
      state.total = state.subtotal - state.discount + state.shipping;

      // Lưu state vào localStorage
      localStorage.setItem("cart", JSON.stringify(state));
    },
    applyDiscount: (state, action) => {
      const { code, amount } = action.payload;
      state.discount = amount;
      state.discountCode = code;
      state.total = state.subtotal - state.discount + state.shipping;

      // Lưu state vào localStorage
      localStorage.setItem("cart", JSON.stringify(state));
    },
    clearCart: (state) => {
      state.items = [];
      state.subtotal = 0;
      state.discount = 0;
      state.discountCode = "";
      state.total = state.shipping;

      // Lưu state vào localStorage
      localStorage.setItem("cart", JSON.stringify(state));
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
