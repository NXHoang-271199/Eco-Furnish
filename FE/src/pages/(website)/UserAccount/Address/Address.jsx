import React, { useState, useEffect } from "react";
import { CiEdit } from "react-icons/ci";
import { MdAddLocation, MdDeleteOutline } from "react-icons/md";
import { toast } from "react-toastify";
import addressService from "../../../../service/addressService";
import axios from "axios";

// ---- Định nghĩa Modal Components bên ngoài ----

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
  <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div className="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div className="flex justify-between items-center p-4 border-b">
        <h2 className="text-lg font-semibold">Thêm địa chỉ mới</h2>
        <button onClick={() => setShowAddModal(false)} className="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>
      <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        {/* Cột trái */}
        <div className="md:col-span-1 space-y-4">
          <h4 className="font-semibold text-gray-700 mb-2">Thông Tin Liên Hệ</h4>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1">Họ *</label>
              <input type="text" name="first_name" value={formData.first_name} onChange={handleChange} className="w-full border rounded-md p-2" />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Tên *</label>
              <input type="text" name="last_name" value={formData.last_name} onChange={handleChange} className="w-full border rounded-md p-2" />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Số điện thoại *</label>
            <input type="tel" name="phone" value={formData.phone} onChange={handleChange} className="w-full border rounded-md p-2" />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Email (Tùy chọn)</label>
            <input type="email" name="email" value={formData.email} onChange={handleChange} className="w-full border rounded-md p-2" />
          </div>
          <div className="flex items-center pt-2">
            <label className="flex items-center cursor-pointer">
              <div className="relative">
                <input type="checkbox" name="is_default" checked={formData.is_default} onChange={handleChange} className="sr-only" />
                <div className={`block w-10 h-6 rounded-full transition ${formData.is_default ? 'bg-blue-500' : 'bg-gray-300'}`}></div>
                <div className={`dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform ${formData.is_default ? 'translate-x-4' : ''}`}></div>
              </div>
              <div className="ml-3 text-sm text-gray-700">Đặt làm địa chỉ mặc định</div>
            </label>
          </div>
        </div>
        {/* Cột phải */}
        <div className="md:col-span-1 space-y-4">
          <h4 className="font-semibold text-gray-700 mb-2">Địa Chỉ Giao Hàng</h4>
          <div>
            <label className="block text-sm font-medium mb-1">Tên địa chỉ (VD: Nhà riêng, Công ty)</label>
            <input type="text" name="address_name" value={formData.address_name} onChange={handleChange} className="w-full border rounded-md p-2" placeholder="Tùy chọn" />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Quốc gia</label>
            <input type="text" name="country" value={formData.country} onChange={handleChange} className="w-full border rounded-md p-2 bg-gray-100" readOnly />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Tỉnh / Thành phố *</label>
            <select name="province" value={provinces.find(p => p.name === formData.province)?.code || ""} onChange={handleModalProvinceChange} className="w-full border rounded-md p-2">
              <option value="">Chọn tỉnh/thành</option>
              {provinces.map(p => <option key={p.code} value={p.code}>{p.name}</option>)}
            </select>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1">Quận / Huyện *</label>
              <select name="district" value={districts.find(d => d.name === formData.district)?.code || ""} onChange={handleModalDistrictChange} className="w-full border rounded-md p-2" disabled={!formData.province || districts.length === 0}>
                <option value="">Chọn quận/huyện</option>
                {districts.map(d => <option key={d.code} value={d.code}>{d.name}</option>)}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Xã / Phường *</label>
              <select name="ward" value={wards.find(w => w.name === formData.ward)?.code || ""} onChange={handleModalWardChange} className="w-full border rounded-md p-2" disabled={!formData.district || wards.length === 0}>
                <option value="">Chọn phường/xã</option>
                {wards.map(w => <option key={w.code} value={w.code}>{w.name}</option>)}
              </select>
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Địa chỉ đường *</label>
            <input type="text" name="street_address" value={formData.street_address} onChange={handleChange} className="w-full border rounded-md p-2" placeholder="Số nhà, tên đường..." />
          </div>
        </div>
      </div>
      <div className="flex justify-end space-x-3 p-4 border-t">
        <button
          onClick={() => setShowAddModal(false)}
          className="px-4 py-2 border rounded-md"
        >
          Hủy
        </button>
        <button
          onClick={handleSaveNewAddress}
          className="px-6 py-2 bg-black text-white rounded-md"
          disabled={loading}
        >
          {loading ? "Đang lưu..." : "Lưu"}
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
  <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div className="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div className="flex justify-between items-center p-4 border-b">
        <h2 className="text-lg font-semibold">Chỉnh sửa địa chỉ</h2>
        <button onClick={() => setShowEditModal(false)} className="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>
      <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        {/* Cột trái */}
        <div className="md:col-span-1 space-y-4">
          <h4 className="font-semibold text-gray-700 mb-2">Thông Tin Liên Hệ</h4>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1">Họ *</label>
              <input type="text" name="first_name" value={formData.first_name} onChange={handleChange} className="w-full border rounded-md p-2" />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Tên *</label>
              <input type="text" name="last_name" value={formData.last_name} onChange={handleChange} className="w-full border rounded-md p-2" />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Số điện thoại *</label>
            <input type="tel" name="phone" value={formData.phone} onChange={handleChange} className="w-full border rounded-md p-2" />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Email (Tùy chọn)</label>
            <input type="email" name="email" value={formData.email} onChange={handleChange} className="w-full border rounded-md p-2" />
          </div>
          <div className="flex items-center pt-2">
            <label className="flex items-center cursor-pointer">
              <div className="relative">
                <input type="checkbox" name="is_default" checked={formData.is_default} onChange={handleChange} className="sr-only" />
                <div className={`block w-10 h-6 rounded-full transition ${formData.is_default ? 'bg-blue-500' : 'bg-gray-300'}`}></div>
                <div className={`dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform ${formData.is_default ? 'translate-x-4' : ''}`}></div>
              </div>
              <div className="ml-3 text-sm text-gray-700">Đặt làm địa chỉ mặc định</div>
            </label>
          </div>
        </div>
        {/* Cột phải */}
        <div className="md:col-span-1 space-y-4">
          <h4 className="font-semibold text-gray-700 mb-2">Địa Chỉ Giao Hàng</h4>
          <div>
            <label className="block text-sm font-medium mb-1">Tên địa chỉ (VD: Nhà riêng, Công ty)</label>
            <input type="text" name="address_name" value={formData.address_name} onChange={handleChange} className="w-full border rounded-md p-2" placeholder="Tùy chọn" />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Quốc gia</label>
            <input type="text" name="country" value={formData.country} onChange={handleChange} className="w-full border rounded-md p-2 bg-gray-100" readOnly />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Tỉnh / Thành phố *</label>
            <select name="province" value={provinces.find(p => p.name === formData.province)?.code || ""} onChange={handleModalProvinceChange} className="w-full border rounded-md p-2">
              <option value="">Chọn tỉnh/thành</option>
              {provinces.map(p => <option key={p.code} value={p.code}>{p.name}</option>)}
            </select>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1">Quận / Huyện *</label>
              <select name="district" value={districts.find(d => d.name === formData.district)?.code || ""} onChange={handleModalDistrictChange} className="w-full border rounded-md p-2" disabled={!formData.province || districts.length === 0}>
                <option value="">Chọn quận/huyện</option>
                {districts.map(d => <option key={d.code} value={d.code}>{d.name}</option>)}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Xã / Phường *</label>
              <select name="ward" value={wards.find(w => w.name === formData.ward)?.code || ""} onChange={handleModalWardChange} className="w-full border rounded-md p-2" disabled={!formData.district || wards.length === 0}>
                <option value="">Chọn phường/xã</option>
                {wards.map(w => <option key={w.code} value={w.code}>{w.name}</option>)}
              </select>
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Địa chỉ đường *</label>
            <input type="text" name="street_address" value={formData.street_address} onChange={handleChange} className="w-full border rounded-md p-2" placeholder="Số nhà, tên đường..." />
          </div>
        </div>
      </div>
      <div className="flex justify-end space-x-3 p-4 border-t">
        <button
          onClick={() => setShowEditModal(false)}
          className="px-4 py-2 border rounded-md"
        >
          Hủy
        </button>
        <button
          onClick={handleUpdateAddress}
          className="px-6 py-2 bg-black text-white rounded-md"
          disabled={loading}
        >
          {loading ? "Đang lưu..." : "Lưu"}
        </button>
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
  const [currentAddress, setCurrentAddress] = useState(null);
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
    if (storedUserData) {
      const userData = JSON.parse(storedUserData);
      setUserId(userData.id);
    } else {
      console.error("Không tìm thấy userData trong localStorage.");
      toast.error("Vui lòng đăng nhập để sử dụng chức năng này.")
    }
  }, []);

  const fetchAddresses = async () => {
    setLoading(true);
    try {
      const response = await addressService.getUserAddresses(userId);
      if (response && response.status === 'success' && response.data) {
        setAddresses(response.data);
      }
    } catch (error) {
      console.error("Lỗi khi lấy danh sách địa chỉ:", error);
      toast.error("Không thể lấy danh sách địa chỉ. Vui lòng thử lại sau.");
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

  const handleDeleteAddress = async (addressId) => {
    if (window.confirm("Bạn có chắc chắn muốn xóa địa chỉ này không?")) {
      setLoading(true);
      try {
        const response = await addressService.deleteAddress(userId, addressId);

        if (response && response.status === 'success') {
          toast.success("Xóa địa chỉ thành công");
          fetchAddresses();
        }
      } catch (error) {
        console.error("Lỗi khi xóa địa chỉ:", error);
        toast.error("Không thể xóa địa chỉ. Vui lòng thử lại sau.");
      } finally {
        setLoading(false);
      }
    }
  };

  return (
    <>
      <main className="w-full md:w-3/4 p-6">
        <div className="flex justify-between items-center">
          <div className="text-xl font-semibold">Địa chỉ</div>
          <button
            onClick={handleAddAddress}
            className="bg-black text-white px-4 py-2 rounded-md flex items-center"
          >
            <MdAddLocation className="mr-2" />
            Thêm địa chỉ nhận hàng
          </button>
        </div>

        {loading && <div className="text-center my-4">Đang tải...</div>}

        {!loading && addresses.length === 0 && (
          <div className="text-center my-8 text-gray-500">
            Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới.
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-2 mt-5 gap-6 mb-14">
          {addresses.map((address) => (
            <div key={address.id} className="rounded-lg border border-slate-200 bg-white text-slate-950 w-full max-w-md mx-auto shadow-lg hover:shadow-xl transition-shadow">
              <div className="p-6 flex flex-row items-center space-y-0 pb-2">
                <div className="flex items-center gap-x-2">
                  <h2 className="text-xl font-semibold tracking-tight">
                    {address.address_name || address.full_name}
                    {address.is_default && (
                      <span className="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                        Mặc định
                      </span>
                    )}
                  </h2>
                </div>
                <div className="ml-auto flex">
                  <button
                    onClick={() => handleEditAddress(address)}
                    className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-50 h-8 w-8"
                    type="button"
                  >
                    <CiEdit className="text-lg" />
                    <span className="sr-only">Cập nhật</span>
                  </button>
                  <button
                    onClick={() => handleDeleteAddress(address.id)}
                    className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-50 h-8 w-8"
                    type="button"
                  >
                    <MdDeleteOutline className="text-lg text-red-600" />
                    <span className="sr-only">Xóa</span>
                  </button>
                </div>
              </div>
              <div className="p-6 pt-0 space-y-2">
                <p className="text-sm font-medium">{address.full_name} - {address.phone}</p>
                {address.email && <p className="text-sm text-gray-500">{address.email}</p>}
                <p className="text-muted-foreground text-sm">
                  {`${address.street_address || ''}, ${address.ward || ''}, ${address.district || ''}, ${address.province || ''}, ${address.country || ''}`}
                </p>
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
    </>
  );
};

export default Address;
