import React, { useState, useEffect } from "react";
import { CiEdit } from "react-icons/ci";
import { MdAddLocation, MdDeleteOutline } from "react-icons/md";
import { toast } from "react-toastify";
import addressService from "../../../../service/addressService";
import axios from "axios";

// Thêm CSS animation
const cssAnimations = `
  @keyframes scaleIn {
    from {
      opacity: 0;
      transform: scale(0.95);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }
  
  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }
  
  @keyframes slideInUp {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
    }
    70% {
      box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
    }
  }
  
  .animate-modalFadeIn {
    animation: scaleIn 0.3s ease-out forwards;
  }
  
  .animate-fadeIn {
    animation: fadeIn 0.5s ease-out forwards;
  }
  
  .animate-slideInUp {
    animation: slideInUp 0.4s ease-out forwards;
  }
  
  .animate-pulse-blue {
    animation: pulse 2s infinite;
  }
`;

// Thêm Style component vào đầu file
const Style = () => (
  <style jsx>{cssAnimations}</style>
);

// Modal Thêm Địa Chỉ
const AddAddressModal = ({
  formData,
  handleChange,
  handleModalProvinceChange,
  handleModalDistrictChange,
  handleModalWardChange,
  handleSaveNewAddress,
  setShowAddModal,
  loading,
  provinces,
  districts,
  wards
}) => (
  <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div
      className="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 animate-modalFadeIn"
      style={{
        animation: 'scaleIn 0.3s ease-out forwards',
      }}
    >
      <div className="flex justify-between items-center p-5 border-b">
        <h2 className="text-xl font-bold text-gray-800 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd" />
          </svg>
          Thêm địa chỉ mới
        </h2>
        <button
          onClick={() => setShowAddModal(false)}
          className="text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full p-2 transition-colors duration-200"
          aria-label="Đóng"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Cột trái */}
        <div className="md:col-span-1 space-y-4">
          <h4 className="font-bold text-gray-700 mb-3 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
            </svg>
            Thông Tin Liên Hệ
          </h4>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1 text-gray-700">Họ *</label>
              <input
                type="text"
                name="first_name"
                value={formData.first_name}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg px-3 py-2 transition-all duration-200"
                placeholder="Nguyễn"
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1 text-gray-700">Tên *</label>
              <input
                type="text"
                name="last_name"
                value={formData.last_name}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg px-3 py-2 transition-all duration-200"
                placeholder="Văn A"
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Số điện thoại *</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <input
                type="tel"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg pl-10 pr-3 py-2 transition-all duration-200"
                placeholder="0912345678"
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Email (Tùy chọn)</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg pl-10 pr-3 py-2 transition-all duration-200"
                placeholder="example@email.com"
              />
            </div>
          </div>
          <div className="flex items-center pt-2">
            <label className="flex items-center cursor-pointer">
              <div className="relative">
                <input
                  type="checkbox"
                  name="is_default"
                  checked={formData.is_default}
                  onChange={handleChange}
                  className="sr-only"
                />
                <div className={`block w-14 h-7 rounded-full transition duration-300 ease-in-out ${formData.is_default ? 'bg-blue-500' : 'bg-gray-300'}`}></div>
                <div className={`dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition duration-300 ease-in-out shadow-md transform ${formData.is_default ? 'translate-x-7' : 'translate-x-0'}`}></div>
              </div>
              <div className="ml-3 text-sm font-medium text-gray-700">Đặt làm địa chỉ mặc định</div>
            </label>
          </div>
        </div>
        {/* Cột phải */}
        <div className="md:col-span-1 space-y-4">
          <h4 className="font-bold text-gray-700 mb-3 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd" />
            </svg>
            Địa Chỉ Giao Hàng
          </h4>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Tên địa chỉ (VD: Nhà riêng, Công ty)</label>
            <input
              type="text"
              name="address_name"
              value={formData.address_name}
              onChange={handleChange}
              className="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg px-3 py-2 transition-all duration-200"
              placeholder="Nhà riêng, Công ty,..."
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Quốc gia</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                </svg>
              </div>
              <input
                type="text"
                name="country"
                value={formData.country}
                onChange={handleChange}
                className="w-full border border-gray-200 bg-gray-50 rounded-lg pl-10 pr-3 py-2"
                readOnly
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Tỉnh / Thành phố *</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <select
                name="province"
                value={provinces.find(p => p.name === formData.province)?.code || ""}
                onChange={handleModalProvinceChange}
                className="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg pl-10 pr-3 py-2 appearance-none bg-white transition-all duration-200"
              >
                <option value="">Chọn tỉnh/thành</option>
                {provinces.map(p => <option key={p.code} value={p.code}>{p.name}</option>)}
              </select>
              <div className="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1 text-gray-700">Quận / Huyện *</label>
              <div className="relative">
                <select
                  name="district"
                  value={districts.find(d => d.name === formData.district)?.code || ""}
                  onChange={handleModalDistrictChange}
                  className={`w-full border focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg px-3 py-2 appearance-none transition-all duration-200 ${!formData.province || districts.length === 0 ? 'bg-gray-100 border-gray-200 text-gray-500' : 'bg-white border-gray-300 text-gray-900'}`}
                  disabled={!formData.province || districts.length === 0}
                >
                  <option value="">Chọn quận/huyện</option>
                  {districts.map(d => <option key={d.code} value={d.code}>{d.name}</option>)}
                </select>
                <div className="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </div>
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium mb-1 text-gray-700">Xã / Phường *</label>
              <div className="relative">
                <select
                  name="ward"
                  value={wards.find(w => w.name === formData.ward)?.code || ""}
                  onChange={handleModalWardChange}
                  className={`w-full border focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg px-3 py-2 appearance-none transition-all duration-200 ${!formData.district || wards.length === 0 ? 'bg-gray-100 border-gray-200 text-gray-500' : 'bg-white border-gray-300 text-gray-900'}`}
                  disabled={!formData.district || wards.length === 0}
                >
                  <option value="">Chọn phường/xã</option>
                  {wards.map(w => <option key={w.code} value={w.code}>{w.name}</option>)}
                </select>
                <div className="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </div>
              </div>
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Địa chỉ đường *</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <input
                type="text"
                name="street_address"
                value={formData.street_address}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-lg pl-10 pr-3 py-2 transition-all duration-200"
                placeholder="Số nhà, tên đường..."
              />
            </div>
          </div>
        </div>
      </div>
      <div className="flex justify-end space-x-3 p-5 border-t bg-gray-50 rounded-b-xl">
        <button
          onClick={() => setShowAddModal(false)}
          className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-all duration-200"
          disabled={loading}
        >
          Hủy
        </button>
        <button
          onClick={handleSaveNewAddress}
          className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 flex items-center"
          disabled={loading}
        >
          {loading ? (
            <>
              <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Đang lưu...
            </>
          ) : (
            <>
              <svg className="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Lưu địa chỉ
            </>
          )}
        </button>
      </div>
    </div>
  </div>
);

// Modal Chỉnh Sửa Địa Chỉ
const EditAddressModal = ({
  formData,
  handleChange,
  handleModalProvinceChange,
  handleModalDistrictChange,
  handleModalWardChange,
  handleUpdateAddress,
  setShowEditModal,
  loading,
  provinces,
  districts,
  wards
}) => (
  <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div
      className="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 animate-modalFadeIn"
      style={{
        animation: 'scaleIn 0.3s ease-out forwards',
      }}
    >
      <div className="flex justify-between items-center p-5 border-b">
        <h2 className="text-xl font-bold text-gray-800 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
          </svg>
          Chỉnh sửa địa chỉ
        </h2>
        <button
          onClick={() => setShowEditModal(false)}
          className="text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full p-2 transition-colors duration-200"
          aria-label="Đóng"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Cột trái */}
        <div className="md:col-span-1 space-y-4">
          <h4 className="font-bold text-gray-700 mb-3 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
            </svg>
            Thông Tin Liên Hệ
          </h4>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1 text-gray-700">Họ *</label>
              <input
                type="text"
                name="first_name"
                value={formData.first_name}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg px-3 py-2 transition-all duration-200"
                placeholder="Nguyễn"
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1 text-gray-700">Tên *</label>
              <input
                type="text"
                name="last_name"
                value={formData.last_name}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg px-3 py-2 transition-all duration-200"
                placeholder="Văn A"
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Số điện thoại *</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <input
                type="tel"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg pl-10 pr-3 py-2 transition-all duration-200"
                placeholder="0912345678"
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Email (Tùy chọn)</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg pl-10 pr-3 py-2 transition-all duration-200"
                placeholder="example@email.com"
              />
            </div>
          </div>
          <div className="flex items-center pt-2">
            <label className="flex items-center cursor-pointer">
              <div className="relative">
                <input
                  type="checkbox"
                  name="is_default"
                  checked={formData.is_default}
                  onChange={handleChange}
                  className="sr-only"
                />
                <div className={`block w-14 h-7 rounded-full transition duration-300 ease-in-out ${formData.is_default ? 'bg-amber-500' : 'bg-gray-300'}`}></div>
                <div className={`dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full transition duration-300 ease-in-out shadow-md transform ${formData.is_default ? 'translate-x-7' : 'translate-x-0'}`}></div>
              </div>
              <div className="ml-3 text-sm font-medium text-gray-700">Đặt làm địa chỉ mặc định</div>
            </label>
          </div>
        </div>
        {/* Cột phải */}
        <div className="md:col-span-1 space-y-4">
          <h4 className="font-bold text-gray-700 mb-3 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd" />
            </svg>
            Địa Chỉ Giao Hàng
          </h4>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Tên địa chỉ (VD: Nhà riêng, Công ty)</label>
            <input
              type="text"
              name="address_name"
              value={formData.address_name}
              onChange={handleChange}
              className="w-full border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg px-3 py-2 transition-all duration-200"
              placeholder="Nhà riêng, Công ty,..."
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Quốc gia</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                </svg>
              </div>
              <input
                type="text"
                name="country"
                value={formData.country}
                onChange={handleChange}
                className="w-full border border-gray-200 bg-gray-50 rounded-lg pl-10 pr-3 py-2"
                readOnly
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Tỉnh / Thành phố *</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <select
                name="province"
                value={provinces.find(p => p.name === formData.province)?.code || ""}
                onChange={handleModalProvinceChange}
                className="w-full border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg pl-10 pr-3 py-2 appearance-none bg-white transition-all duration-200"
              >
                <option value="">Chọn tỉnh/thành</option>
                {provinces.map(p => <option key={p.code} value={p.code}>{p.name}</option>)}
              </select>
              <div className="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1 text-gray-700">Quận / Huyện *</label>
              <div className="relative">
                <select
                  name="district"
                  value={districts.find(d => d.name === formData.district)?.code || ""}
                  onChange={handleModalDistrictChange}
                  className={`w-full border focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg px-3 py-2 appearance-none transition-all duration-200 ${!formData.province || districts.length === 0 ? 'bg-gray-100 border-gray-200 text-gray-500' : 'bg-white border-gray-300 text-gray-900'}`}
                  disabled={!formData.province || districts.length === 0}
                >
                  <option value="">Chọn quận/huyện</option>
                  {districts.map(d => <option key={d.code} value={d.code}>{d.name}</option>)}
                </select>
                <div className="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </div>
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium mb-1 text-gray-700">Xã / Phường *</label>
              <div className="relative">
                <select
                  name="ward"
                  value={wards.find(w => w.name === formData.ward)?.code || ""}
                  onChange={handleModalWardChange}
                  className={`w-full border focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg px-3 py-2 appearance-none transition-all duration-200 ${!formData.district || wards.length === 0 ? 'bg-gray-100 border-gray-200 text-gray-500' : 'bg-white border-gray-300 text-gray-900'}`}
                  disabled={!formData.district || wards.length === 0}
                >
                  <option value="">Chọn phường/xã</option>
                  {wards.map(w => <option key={w.code} value={w.code}>{w.name}</option>)}
                </select>
                <div className="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </div>
              </div>
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1 text-gray-700">Địa chỉ đường *</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <input
                type="text"
                name="street_address"
                value={formData.street_address}
                onChange={handleChange}
                className="w-full border border-gray-300 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-lg pl-10 pr-3 py-2 transition-all duration-200"
                placeholder="Số nhà, tên đường..."
              />
            </div>
          </div>
        </div>
      </div>
      <div className="flex justify-end space-x-3 p-5 border-t bg-gray-50 rounded-b-xl">
        <button
          onClick={() => setShowEditModal(false)}
          className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-all duration-200"
          disabled={loading}
        >
          Hủy
        </button>
        <button
          onClick={handleUpdateAddress}
          className="px-6 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200 flex items-center"
          disabled={loading}
        >
          {loading ? (
            <>
              <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Đang lưu...
            </>
          ) : (
            <>
              <svg className="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Cập nhật địa chỉ
            </>
          )}
        </button>
      </div>
    </div>
  </div>
);

// Modal Xác nhận xóa
const DeleteConfirmModal = ({
  setShowDeleteModal,
  handleConfirmDelete,
  deletingAddress,
  loading
}) => (
  <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div
      className="bg-white rounded-xl w-full max-w-md shadow-2xl transform transition-all duration-300 animate-modalFadeIn"
      style={{
        animation: 'scaleIn 0.3s ease-out forwards',
      }}
    >
      <div className="p-6 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-red-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <h3 className="text-xl font-bold text-gray-800 mt-5">Xác nhận xóa địa chỉ</h3>
        <p className="text-gray-600 my-4">
          Bạn có chắc chắn muốn xóa địa chỉ này? Thao tác này không thể hoàn tác.
        </p>
        <div className="flex space-x-3 mt-6 justify-center">
          <button
            onClick={() => setShowDeleteModal(false)}
            className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-all duration-200 min-w-[100px]"
            disabled={loading}
          >
            Hủy
          </button>
          <button
            onClick={handleConfirmDelete}
            className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 flex items-center justify-center min-w-[100px]"
            disabled={loading}
          >
            {loading ? (
              <>
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Đang xóa...
              </>
            ) : (
              <>
                <MdDeleteOutline className="mr-1" />
                Xóa
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  </div>
);

// ---- Component Address chính ----
const Address = () => {
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(false);
  const [showAddModal, setShowAddModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [currentAddress, setCurrentAddress] = useState(null);
  const [deletingAddress, setDeletingAddress] = useState(null);
  const [formData, setFormData] = useState({
    first_name: "",
    last_name: "",
    phone: "",
    email: "",
    address_name: "",
    country: "Việt Nam",
    province: "",
    district: "",
    ward: "",
    street_address: "",
    is_default: false,
  });
  const [editingAddressId, setEditingAddressId] = useState(null);

  const [provinces, setProvinces] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [wards, setWards] = useState([]);

  const [userId, setUserId] = useState(null);

  useEffect(() => {
    const storedUserData = localStorage.getItem("userData");
    const authToken = localStorage.getItem("authToken");

    if (storedUserData && authToken) {
      const userData = JSON.parse(storedUserData);
      setUserId(userData.id);
    } else {
      console.error("Không tìm thấy thông tin đăng nhập.");
      toast.error("Vui lòng đăng nhập để sử dụng chức năng này.");
      // Có thể thêm logic chuyển hướng đến trang đăng nhập ở đây nếu cần
    }
  }, []);

  const fetchAddresses = async () => {
    if (!userId) return; // Không gọi API nếu không có userId

    setLoading(true);
    try {
      const response = await addressService.getUserAddresses(userId);
      if (response && response.status === 'success' && response.data) {
        setAddresses(response.data);
      }
    } catch (error) {
      console.error("Lỗi khi lấy danh sách địa chỉ:", error);
      if (error.response?.status === 401) {
        toast.error("Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.");
        // Có thể thêm logic xử lý logout ở đây nếu cần
      } else {
        toast.error("Không thể lấy danh sách địa chỉ. Vui lòng thử lại sau.");
      }
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (userId) {
      fetchAddresses();
    }
  }, [userId]);

  useEffect(() => {
    fetchProvinces();
  }, []);

  const fetchProvinces = async () => {
    try {
      const response = await axios.get("https://provinces.open-api.vn/api/p/");
      setProvinces(response.data);
    } catch (err) {
      console.error("Lỗi khi lấy danh sách tỉnh/thành phố:", err);
    }
  };

  const fetchDistricts = async (provinceCode) => {
    try {
      const response = await axios.get(
        `https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`
      );
      setDistricts(response.data.districts || []);
      setWards([]);
    } catch (err) {
      console.error("Lỗi khi lấy danh sách quận/huyện:", err);
      setDistricts([]);
      setWards([]);
    }
  };

  const fetchWards = async (districtCode) => {
    try {
      const response = await axios.get(
        `https://provinces.open-api.vn/api/d/${districtCode}?depth=2`
      );
      setWards(response.data.wards || []);
    } catch (err) {
      console.error("Lỗi khi lấy danh sách phường/xã:", err);
      setWards([]);
    }
  };

  const handleModalProvinceChange = (e) => {
    const selectedProvinceCode = e.target.value;
    const selectedProvince = provinces.find(
      (p) => p.code === Number(selectedProvinceCode)
    );
    setFormData(prev => ({
      ...prev,
      province: selectedProvince?.name || "",
      district: "",
      ward: "",
    }));
    if (selectedProvinceCode) {
      fetchDistricts(selectedProvinceCode);
    } else {
      setDistricts([]);
      setWards([]);
    }
  };

  const handleModalDistrictChange = (e) => {
    const selectedDistrictCode = e.target.value;
    const selectedDistrict = districts.find(
      (d) => d.code === Number(selectedDistrictCode)
    );
    setFormData(prev => ({
      ...prev,
      district: selectedDistrict?.name || "",
      ward: "",
    }));
    if (selectedDistrictCode) {
      fetchWards(selectedDistrictCode);
    } else {
      setWards([]);
    }
  };

  const handleModalWardChange = (e) => {
    const selectedWardCode = e.target.value;
    const selectedWard = wards.find((w) => w.code === Number(selectedWardCode));
    setFormData(prev => ({
      ...prev,
      ward: selectedWard?.name || "",
    }));
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData({
      ...formData,
      [name]: type === "checkbox" ? checked : value,
    });
  };

  const handleAddAddress = () => {
    setEditingAddressId(null);
    setFormData({
      first_name: "",
      last_name: "",
      phone: "",
      email: "",
      address_name: "",
      country: "Việt Nam",
      province: "",
      district: "",
      ward: "",
      street_address: "",
      is_default: false
    });
    setDistricts([]);
    setWards([]);
    setShowAddModal(true);
  };

  const handleEditAddress = (address) => {
    setEditingAddressId(address.id);
    setCurrentAddress(address);
    setFormData({
      first_name: address.first_name || "",
      last_name: address.last_name || "",
      phone: address.phone || "",
      email: address.email || "",
      address_name: address.address_name || "",
      country: address.country || "Việt Nam",
      province: address.province || "",
      district: address.district || "",
      ward: address.ward || "",
      street_address: address.street_address || "",
      is_default: address.is_default || false,
    });
    const provinceCode = provinces.find(p => p.name === address.province)?.code;
    if (provinceCode) {
      fetchDistricts(provinceCode).then(() => {
        setTimeout(() => {
          const updatedDistricts = districts;
          const districtCode = updatedDistricts.find(d => d.name === address.district)?.code;
          if (districtCode) {
            fetchWards(districtCode);
          }
        }, 100);
      });
    } else {
      setDistricts([]);
      setWards([]);
    }
    setShowEditModal(true);
  };

  const handleSaveNewAddress = async () => {
    if (!formData.first_name || !formData.last_name || !formData.phone || !formData.province || !formData.district || !formData.ward || !formData.street_address) {
      toast.error("Vui lòng điền đầy đủ các trường bắt buộc (*)");
      return;
    }

    setLoading(true);
    try {
      const response = await addressService.addAddress(userId, formData);

      if (response && response.status === 'success') {
        toast.success("Thêm địa chỉ thành công");
        fetchAddresses();
        setShowAddModal(false);
      }
    } catch (error) {
      console.error("Lỗi khi thêm địa chỉ:", error);
      const errorMsg = error.response?.data?.message?.email?.[0] ||
        error.response?.data?.message?.phone?.[0] ||
        "Không thể thêm địa chỉ. Vui lòng thử lại sau.";
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateAddress = async () => {
    if (!formData.first_name || !formData.last_name || !formData.phone || !formData.province || !formData.district || !formData.ward || !formData.street_address) {
      toast.error("Vui lòng điền đầy đủ các trường bắt buộc (*)");
      return;
    }
    if (!editingAddressId) return;

    setLoading(true);
    try {
      const response = await addressService.updateAddress(
        userId,
        editingAddressId,
        formData
      );

      if (response && response.status === 'success') {
        toast.success("Cập nhật địa chỉ thành công");
        fetchAddresses();
        setShowEditModal(false);
      }
    } catch (error) {
      console.error("Lỗi khi cập nhật địa chỉ:", error);
      const errorMsg = error.response?.data?.message?.email?.[0] ||
        error.response?.data?.message?.phone?.[0] ||
        "Không thể cập nhật địa chỉ. Vui lòng thử lại sau.";
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteAddress = (addressId) => {
    const addressToDelete = addresses.find(addr => addr.id === addressId);
    setDeletingAddress(addressToDelete);
    setShowDeleteModal(true);
  };

  const handleConfirmDelete = async () => {
    if (!deletingAddress) return;

    setLoading(true);
    try {
      await addressService.deleteAddress(userId, deletingAddress.id);

      // Xóa địa chỉ khỏi state sau khi xóa thành công
      setAddresses(prevAddresses => prevAddresses.filter(addr => addr.id !== deletingAddress.id));
      toast.success("Địa chỉ đã được xóa thành công!");
      setShowDeleteModal(false);
    } catch (error) {
      console.error("Lỗi khi xóa địa chỉ:", error);
      if (error.response && error.response.data && error.response.data.message) {
        toast.error(error.response.data.message);
      } else {
        toast.error("Có lỗi xảy ra khi xóa địa chỉ. Vui lòng thử lại.");
      }
    } finally {
      setLoading(false);
      setDeletingAddress(null);
    }
  };

  return (
    <>
      <Style />
      <main className="w-full md:w-3/4 p-6">
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
          <div className="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <h1 className="text-2xl font-bold text-gray-800">Địa chỉ của tôi</h1>
          </div>
          <button
            onClick={handleAddAddress}
            className="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg flex items-center justify-center transition-all duration-300 shadow-md hover:shadow-lg transform hover:translate-y-[-2px] w-full md:w-auto"
          >
            <MdAddLocation className="mr-2 text-xl" />
            Thêm địa chỉ nhận hàng
          </button>
        </div>

        {loading && (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-14">
            {[1, 2].map((item) => (
              <div key={item} className="rounded-lg border border-gray-100 bg-white w-full max-w-md mx-auto shadow-md p-6 animate-pulse">
                <div className="h-2 bg-gray-200 rounded w-1/3 mb-4"></div>
                <div className="h-6 bg-gray-200 rounded w-3/4 mb-6"></div>
                <div className="space-y-3">
                  <div className="h-4 bg-gray-200 rounded w-2/3"></div>
                  <div className="h-4 bg-gray-200 rounded w-5/6"></div>
                  <div className="h-4 bg-gray-200 rounded w-full"></div>
                </div>
              </div>
            ))}
          </div>
        )}

        {!loading && addresses.length === 0 && (
          <div className="text-center my-12 bg-gray-50 p-8 rounded-lg shadow-inner">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            <h3 className="text-lg font-medium text-gray-700 mb-2">Chưa có địa chỉ nào</h3>
            <p className="text-gray-500 mb-6">Bạn chưa có địa chỉ nào được lưu. Thêm địa chỉ để dễ dàng thanh toán.</p>
            <button
              onClick={handleAddAddress}
              className="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-300"
            >
              <MdAddLocation className="mr-2" />
              Thêm địa chỉ đầu tiên
            </button>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-2 mt-5 gap-6 mb-14">
          {addresses.map((address) => (
            <div
              key={address.id}
              className="rounded-lg border border-gray-100 bg-white text-slate-950 w-full max-w-md mx-auto shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] overflow-hidden"
            >
              <div className={`h-2 w-full ${address.is_default ? 'bg-emerald-500' : 'bg-blue-400'}`}></div>
              <div className="p-6 flex flex-row items-center space-y-0 pb-2">
                <div className="flex items-center gap-x-2">
                  <h2 className="text-xl font-semibold tracking-tight flex items-center">
                    {address.address_name || address.full_name}
                    {address.is_default && (
                      <span className="ml-2 text-xs bg-emerald-50 text-emerald-700 px-2 py-1 rounded-full font-medium flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                        Mặc định
                      </span>
                    )}
                  </h2>
                </div>
                <div className="ml-auto flex gap-1">
                  <button
                    onClick={() => handleEditAddress(address)}
                    className="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 w-8 bg-gray-50 hover:bg-gray-100 text-gray-700"
                    type="button"
                    aria-label="Chỉnh sửa địa chỉ"
                  >
                    <CiEdit className="text-lg" />
                  </button>
                  <button
                    onClick={() => handleDeleteAddress(address.id)}
                    className="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 w-8 bg-red-50 hover:bg-red-100 text-red-600"
                    type="button"
                    aria-label="Xóa địa chỉ"
                  >
                    <MdDeleteOutline className="text-lg" />
                  </button>
                </div>
              </div>
              <div className="p-6 pt-0 space-y-3">
                <div className="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <p className="text-sm font-medium">{address.full_name} - {address.phone}</p>
                </div>
                {address.email && (
                  <div className="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <p className="text-sm text-gray-500">{address.email}</p>
                  </div>
                )}
                <div className="flex items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <p className="text-gray-600 text-sm">
                    {`${address.street_address || ''}, ${address.ward || ''}, ${address.district || ''}, ${address.province || ''}, ${address.country || ''}`}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </main>

      {showAddModal &&
        <AddAddressModal
          formData={formData}
          handleChange={handleChange}
          handleModalProvinceChange={handleModalProvinceChange}
          handleModalDistrictChange={handleModalDistrictChange}
          handleModalWardChange={handleModalWardChange}
          handleSaveNewAddress={handleSaveNewAddress}
          setShowAddModal={setShowAddModal}
          loading={loading}
          provinces={provinces}
          districts={districts}
          wards={wards}
        />
      }
      {showEditModal &&
        <EditAddressModal
          formData={formData}
          handleChange={handleChange}
          handleModalProvinceChange={handleModalProvinceChange}
          handleModalDistrictChange={handleModalDistrictChange}
          handleModalWardChange={handleModalWardChange}
          handleUpdateAddress={handleUpdateAddress}
          setShowEditModal={setShowEditModal}
          loading={loading}
          provinces={provinces}
          districts={districts}
          wards={wards}
        />
      }
      {showDeleteModal &&
        <DeleteConfirmModal
          setShowDeleteModal={setShowDeleteModal}
          handleConfirmDelete={handleConfirmDelete}
          deletingAddress={deletingAddress}
          loading={loading}
        />
      }
    </>
  );
};

export default Address;
