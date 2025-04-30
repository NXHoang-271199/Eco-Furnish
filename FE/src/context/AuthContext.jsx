import React, { createContext, useState, useEffect, useContext } from "react";
import { registerSessionExpiredCallback } from "../utils/axiosConfig";
import SessionExpiredPopup from "../components/SessionExpiredPopup";

const AuthContext = createContext();

export const useAuth = () => useContext(AuthContext);

export const AuthProvider = ({ children }) => {
  const [isExpiredPopupOpen, setIsExpiredPopupOpen] = useState(false);

  useEffect(() => {
    // Đăng ký callback khi phiên hết hạn
    registerSessionExpiredCallback(() => {
      setIsExpiredPopupOpen(true);
    });
  }, []);

  // Đóng popup phiên hết hạn
  const closeExpiredPopup = () => {
    setIsExpiredPopupOpen(false);
  };

  return (
    <AuthContext.Provider
      value={{
        isExpiredPopupOpen,
        closeExpiredPopup,
      }}
    >
      {children}
      <SessionExpiredPopup
        isOpen={isExpiredPopupOpen}
        onClose={closeExpiredPopup}
      />
    </AuthContext.Provider>
  );
};

export default AuthContext;
