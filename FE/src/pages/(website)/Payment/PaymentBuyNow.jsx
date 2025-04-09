import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import { clearSelectedItems } from "../../../store/cartSlice";
import axios from "axios";
import { useLocation } from "react-router-dom";
import axiosInstance from "../../../utils/axiosConfig";
import addressService from "../../../service/addressService";
import { toast } from "react-toastify";

const PaymentBuyNow = () => {
  const navigate = useNavigate();
  const { state } = useLocation(); // Lấy dữ liệu từ state của navigate
  const selectedProducts =
    state?.selectedProducts ||
    JSON.parse(localStorage.getItem("tempSelectedProducts")) ||
    []; // Lấy selectedProducts từ state
  const total = state?.total || 0; // Lấy tổng tiền từ state
  const [userName, setUserName] = useState("");
  const [paymentMethod, setPaymentMethod] = useState("");
  const [address, setAddress] = useState({
    name: "",
    email: "",
    phone: "",
    address: "",
    province: "",
    district: "",
    ward: "",
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [discountCode, setDiscountCode] = useState(""); // State lưu mã giảm giá người dùng nhập
  const [discountError, setDiscountError] = useState(""); // State lưu lỗi khi áp dụng mã giảm giá
  const [isVerifying, setIsVerifying] = useState(false); // State kiểm tra xem mã giảm giá đang được xác minh hay không
  const [discountAmount, setDiscountAmount] = useState(0); // Lưu giá trị giảm giá (số)
  const [voucherId, setVoucherId] = useState(null); // Lưu ID của voucher được áp dụng
  const [paymentMethods, setPaymentMethods] = useState([]);
  const [orderCode, setOrderCode] = useState("");

  const [provinces, setProvinces] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [wards, setWards] = useState([]);

  // States mới cho quản lý địa chỉ
  const [userAddresses, setUserAddresses] = useState([]);
  const [selectedAddress, setSelectedAddress] = useState(null);
  const [showAddressModal, setShowAddressModal] = useState(false);
  const [modalMode, setModalMode] = useState('select'); // 'select', 'add', 'edit'
  const [addressFormData, setAddressFormData] = useState({
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
  const [editingAddressId, setEditingAddressId] = useState(null); // ID của địa chỉ đang sửa

  useEffect(() => {
    // Lấy địa chỉ từ localStorage khi component mount
    const savedAddress = JSON.parse(localStorage.getItem("userAddress")) || {};
    setAddress((prev) => ({
      ...prev,
      ...savedAddress,
    }));

    // Lấy thông tin người dùng từ localStorage
    const userData = JSON.parse(localStorage.getItem("userData")) || {};
    setUserName(userData.name || "Khách"); // Nếu không có tên thì hiển thị "Khách"

    console.log("Selected Products:", state?.selectedProducts);
    console.log("Total:", state?.total);

    // Kiểm tra nếu không có sản phẩm được chọn
    if (!selectedProducts || selectedProducts.length === 0) {
      navigate("/products");
    }

    getPaymentMethod();
    fetchProvinces();
  }, [selectedProducts, navigate, state]);

  // Lấy danh sách tỉnh/thành phố
  const fetchProvinces = async () => {
    try {
      const response = await axios.get("https://provinces.open-api.vn/api/p/");
      setProvinces(response.data);
    } catch (err) {
      console.error("Lỗi khi lấy danh sách tỉnh/thành phố:", err);
    }
  };

  // Lấy danh sách quận/huyện dựa trên tỉnh/thành phố
  const fetchDistricts = async (provinceCode) => {
    try {
      const response = await axios.get(
        `https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`
      );
      setDistricts(response.data.districts);
      setWards([]); // Reset danh sách phường/xã khi chọn tỉnh mới
      setAddress((prev) => ({ ...prev, district: "", ward: "" })); // Reset quận/huyện và phường/xã
    } catch (err) {
      console.error("Lỗi khi lấy danh sách quận/huyện:", err);
    }
  };

  // Lấy danh sách phường/xã dựa trên quận/huyện
  const fetchWards = async (districtCode) => {
    try {
      const response = await axios.get(
        `https://provinces.open-api.vn/api/d/${districtCode}?depth=2`
      );
      setWards(response.data.wards);
      setAddress((prev) => ({ ...prev, ward: "" })); // Reset phường/xã khi chọn quận/huyện mới
    } catch (err) {
      console.error("Lỗi khi lấy danh sách phường/xã:", err);
    }
  };

  // Hàm lấy user ID
  const getUserId = () => {
    const userData = JSON.parse(localStorage.getItem("userData"));
    return userData?.id || null;
  }

  // useEffect để lấy địa chỉ người dùng và set địa chỉ mặc định
  useEffect(() => {
    const userId = getUserId();
    if (userId) {
      fetchUserAddresses(userId);
      // Lấy thông tin người dùng để điền vào form nếu cần
      const userData = JSON.parse(localStorage.getItem("userData")) || {};
      setAddressFormData(prev => ({
        ...prev,
        first_name: userData.first_name || '', // Giả sử có first_name trong userData
        last_name: userData.last_name || '', // Giả sử có last_name trong userData
        email: userData.email || '',
        phone: userData.phone || ''
      }));
    }
    fetchProvinces(); // Gọi fetchProvinces ở đây nếu chưa gọi
    getPaymentMethod(); // Gọi getPaymentMethod ở đây nếu chưa gọi

    // Lấy selectedProducts từ state hoặc localStorage (đã có)
    // if (!selectedProducts || selectedProducts.length === 0) {
    //   navigate("/products");
    // }

  }, [navigate]); // Chỉ chạy 1 lần khi mount

  // useEffect để tự động chọn địa chỉ mặc định khi danh sách địa chỉ thay đổi
  useEffect(() => {
    if (userAddresses.length > 0 && !selectedAddress) {
      const defaultAddress = userAddresses.find(addr => addr.is_default);
      setSelectedAddress(defaultAddress || userAddresses[0]);
    }
  }, [userAddresses, selectedAddress]);

  // Hàm lấy danh sách địa chỉ người dùng
  const fetchUserAddresses = async (userId) => {
    setLoading(true);
    try {
      const response = await addressService.getUserAddresses(userId);
      if (response && response.status === 'success') {
        setUserAddresses(response.data || []);
        // Tự động chọn địa chỉ mặc định hoặc địa chỉ đầu tiên
        if (response.data && response.data.length > 0) {
          const defaultAddr = response.data.find(a => a.is_default);
          setSelectedAddress(defaultAddr || response.data[0]);
        } else {
          setSelectedAddress(null); // Không có địa chỉ nào
        }
      } else {
        setUserAddresses([]);
        setSelectedAddress(null);
      }
    } catch (err) {
      console.error("Lỗi khi lấy địa chỉ người dùng:", err);
      toast.error("Không thể tải danh sách địa chỉ của bạn.");
      setUserAddresses([]);
      setSelectedAddress(null);
    } finally {
      setLoading(false);
    }
  };

  // Bỏ các hàm xử lý địa chỉ cũ (handleProvinceChange, handleDistrictChange, handleWardChange)
  // Thay bằng các hàm xử lý trong modal
  const handleModalProvinceChange = (e) => {
    const selectedProvinceCode = e.target.value;
    const selectedProvince = provinces.find(
      (p) => p.code === Number(selectedProvinceCode)
    );
    setAddressFormData(prev => ({
      ...prev,
      province: selectedProvince?.name || "",
      district: "", // Reset khi tỉnh thay đổi
      ward: "", // Reset khi tỉnh thay đổi
    }));
    if (selectedProvinceCode) {
      fetchDistricts(selectedProvinceCode); // Fetch districts cho modal
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
    setAddressFormData(prev => ({
      ...prev,
      district: selectedDistrict?.name || "",
      ward: "", // Reset khi quận thay đổi
    }));
    if (selectedDistrictCode) {
      fetchWards(selectedDistrictCode); // Fetch wards cho modal
    } else {
      setWards([]);
    }
  };

  const handleModalWardChange = (e) => {
    const selectedWardCode = e.target.value;
    const selectedWard = wards.find((w) => w.code === Number(selectedWardCode));
    setAddressFormData(prev => ({
      ...prev,
      ward: selectedWard?.name || "",
    }));
  };

  // Hàm xử lý thay đổi input trong modal
  const handleAddressFormChange = (e) => {
    const { name, value, type, checked } = e.target;
    setAddressFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  // Hàm mở modal chọn địa chỉ
  const openSelectAddressModal = () => {
    setModalMode('select');
    setShowAddressModal(true);
  };

  // Hàm mở modal thêm địa chỉ
  const openAddAddressModal = () => {
    setEditingAddressId(null);
    setAddressFormData({
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
    setDistricts([]); // Reset district và ward khi mở modal thêm
    setWards([]);
    setModalMode('add');
    setShowAddressModal(true);
  };

  // Hàm mở modal sửa địa chỉ
  const openEditAddressModal = (addressToEdit) => {
    setEditingAddressId(addressToEdit.id);
    setAddressFormData({
      first_name: addressToEdit.first_name || "",
      last_name: addressToEdit.last_name || "",
      phone: addressToEdit.phone || "",
      email: addressToEdit.email || "",
      address_name: addressToEdit.address_name || "",
      country: addressToEdit.country || "Việt Nam",
      province: addressToEdit.province || "",
      district: addressToEdit.district || "",
      ward: addressToEdit.ward || "",
      street_address: addressToEdit.street_address || "",
      is_default: addressToEdit.is_default || false,
    });
    // Cần fetch lại district và ward nếu province/district đã có
    const provinceCode = provinces.find(p => p.name === addressToEdit.province)?.code;
    if (provinceCode) {
      fetchDistricts(provinceCode).then(() => {
        const districtCode = districts.find(d => d.name === addressToEdit.district)?.code;
        if (districtCode) {
          fetchWards(districtCode);
        }
      });
    } else {
      setDistricts([]);
      setWards([]);
    }
    setModalMode('edit');
    setShowAddressModal(true);
  };

  // Hàm lưu địa chỉ (Thêm hoặc Sửa)
  const handleSaveAddress = async () => {
    const userId = getUserId();
    if (!userId) {
      toast.error("Vui lòng đăng nhập.");
      return;
    }

    // Basic validation
    if (!addressFormData.first_name || !addressFormData.last_name || !addressFormData.phone || !addressFormData.province || !addressFormData.district || !addressFormData.ward || !addressFormData.street_address) {
      toast.error("Vui lòng điền đầy đủ các trường bắt buộc (*)");
      return;
    }

    setLoading(true);
    try {
      let response;
      if (modalMode === 'add') {
        response = await addressService.addAddress(userId, addressFormData);
      } else if (modalMode === 'edit' && editingAddressId) {
        response = await addressService.updateAddress(userId, editingAddressId, addressFormData);
      } else {
        throw new Error("Chế độ modal không hợp lệ hoặc thiếu ID địa chỉ.");
      }

      if (response && response.status === 'success') {
        toast.success(`Đã ${modalMode === 'add' ? 'thêm' : 'cập nhật'} địa chỉ!`);
        fetchUserAddresses(userId); // Tải lại danh sách
        setShowAddressModal(false); // Đóng modal
      } else {
        toast.error(response?.message || `Không thể ${modalMode === 'add' ? 'thêm' : 'cập nhật'} địa chỉ.`);
      }
    } catch (err) {
      console.error("Lỗi khi lưu địa chỉ:", err);
      toast.error(`Đã xảy ra lỗi khi ${modalMode === 'add' ? 'thêm' : 'cập nhật'} địa chỉ.`);
    } finally {
      setLoading(false);
    }
  };

  // Hàm xóa địa chỉ (có thể thêm vào modal chọn địa chỉ)
  const handleDeleteAddress = async (addressId) => {
    const userId = getUserId();
    if (!userId) return;

    if (window.confirm("Bạn chắc chắn muốn xóa địa chỉ này?")) {
      setLoading(true);
      try {
        const response = await addressService.deleteAddress(userId, addressId);
        if (response && response.status === 'success') {
          toast.success("Đã xóa địa chỉ.");
          fetchUserAddresses(userId);
          // Nếu địa chỉ bị xóa đang được chọn, cần chọn lại địa chỉ khác
          if (selectedAddress && selectedAddress.id === addressId) {
            setSelectedAddress(null); // Sẽ tự động chọn lại trong useEffect
          }
          // Nếu đang ở modal chọn và xóa hết, chuyển sang modal thêm
          if (modalMode === 'select' && userAddresses.length === 1) {
            setTimeout(openAddAddressModal, 100); // Đợi state update rồi mở modal add
          }
        } else {
          toast.error("Không thể xóa địa chỉ.");
        }
      } catch (err) {
        console.error("Lỗi khi xóa địa chỉ:", err);
        toast.error("Đã xảy ra lỗi khi xóa địa chỉ.");
      } finally {
        setLoading(false);
      }
    }
  };

  // Hàm chọn địa chỉ từ modal
  const handleSelectAddress = (address) => {
    setSelectedAddress(address);
    setShowAddressModal(false);
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  const calculateSubtotal = () => {
    return selectedProducts.reduce((total, item) => {
      return total + (Number(item.total_price) || 0);
    }, 0);
  };

  const calculateTotal = () => {
    return calculateSubtotal() - discountAmount;
  };

  const handleApplyDiscount = async () => {
    try {
      setIsVerifying(true);
      setDiscountError("");

      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post(
        "/check-voucher",
        {
          voucher_code: discountCode,
          subtotal: calculateSubtotal(),
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
          },
        }
      );

      if (response.data.status === "success") {
        setDiscountAmount(response.data.discount_amount);
        setVoucherId(response.data.voucher_id);
      } else {
        setDiscountError(response.data.message || "Mã giảm giá không hợp lệ");
        setDiscountAmount(0);
        setVoucherId(null);
      }
    } catch (err) {
      console.error("Lỗi khi áp dụng mã giảm giá:", err);
      setDiscountError(
        err.response?.data?.message || "Có lỗi xảy ra khi áp dụng mã giảm giá"
      );
      setDiscountAmount(0);
      setVoucherId(null);
    } finally {
      setIsVerifying(false);
    }
  };

  const getPaymentMethod = async () => {
    const token = localStorage.getItem("authToken");
    if (token) {
      try {
        const response = await axiosInstance.get("/payment-methods", {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        setPaymentMethods(response.data.data);
      } catch (error) {
        console.error("Lỗi khi lấy phương thức thanh toán:", error);
      }
    }
  };

  const handlePaymentBuyNow = async () => {
    if (!paymentMethod) {
      setError("Vui lòng chọn phương thức thanh toán");
      return;
    }

    // Thay vì kiểm tra state address cũ, kiểm tra selectedAddress
    if (!selectedAddress) {
      setError("Vui lòng chọn hoặc thêm địa chỉ giao hàng");
      return;
    }

    // Kiểm tra các trường cần thiết của selectedAddress
    if (!selectedAddress.full_name || !selectedAddress.phone || !selectedAddress.province || !selectedAddress.district || !selectedAddress.street_address) {
      setError("Địa chỉ được chọn thiếu thông tin. Vui lòng cập nhật địa chỉ.");
      // Có thể mở modal edit trực tiếp
      // openEditAddressModal(selectedAddress);
      return;
    }

    setLoading(true);
    setError("");

    // Không cần lưu address vào localStorage nữa
    // localStorage.setItem("userAddress", JSON.stringify(address));

    try {
      const token = localStorage.getItem("authToken");

      // Lấy thông tin sản phẩm duy nhất từ selectedProducts (giữ nguyên)
      const singleProductItem = selectedProducts[0];
      if (!singleProductItem) {
        setError("Không tìm thấy thông tin sản phẩm để mua ngay.");
        setLoading(false);
        return;
      }

      const orderData = {
        product_id: singleProductItem.product.id,
        product_variant_id: singleProductItem.product_variant
          ? singleProductItem.product_variant.id
          : null,
        quantity: singleProductItem.quantity,
        // Lấy thông tin từ selectedAddress
        user_name: selectedAddress.full_name,
        user_email: selectedAddress.email || '', // Email có thể null
        user_address: `${selectedAddress.street_address}, ${selectedAddress.ward}, ${selectedAddress.district}, ${selectedAddress.province}, ${selectedAddress.country}`,
        user_phone: selectedAddress.phone,
        payment_method_id: Number(paymentMethod),
        voucher_id: voucherId && discountAmount > 0 ? voucherId : null,
      };

      // Thêm voucher_id nếu đã áp dụng mã giảm giá
      if (voucherId && discountAmount > 0) {
        orderData.voucher_id = voucherId;
      }

      // Gọi API orders/buy-now
      const response = await axiosInstance.post("/orders/buy-now", orderData, {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
      });

      // Lấy mã đơn hàng từ response API
      const newOrderCode =
        response.data.order?.order_code ||
        response.data.order_code ||
        response.data.data?.order_code;
      if (!newOrderCode) {
        console.error(
          "Không thể lấy mã đơn hàng từ response API:",
          response.data
        );
      }
      setOrderCode(newOrderCode);

      if (response.status === 200 || response.status === 201) {
        const selectedMethod = paymentMethods.find(
          (method) => method.id === Number(paymentMethod)
        );
        if (selectedMethod.name === "MoMo") {
          if (response.data && response.data.payUrl) {
            window.location.href = response.data.payUrl;
          } else {
            setError("Không tìm thấy đường dẫn thanh toán");
          }
        } else if (selectedMethod.name === "VNPAY") {
          if (response.data && response.data.data) {
            window.location.href = response.data.data;
          } else {
            setError("Không nhận được đường dẫn thanh toán từ VNPAY");
          }
        } else {
          localStorage.setItem(
            "orderInfo",
            JSON.stringify({
              order_code: newOrderCode,
              total: calculateTotal(),
              payment_method: selectedMethod.name,
              order_date: new Date().toISOString(),
            })
          );
          navigate("/order-success");
        }
      }
    } catch (err) {
      console.error("Lỗi khi gọi API:", err);
      setError(
        err.response?.data?.message || "Có lỗi xảy ra khi xử lý đơn hàng"
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="my-20">
      <div className="max-w-6xl mx-auto py-10 px-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border">
          <h2 className="text-xl font-bold">Eco-Furnish</h2>
          {/* <p className="text-gray-600">Giỏ hàng - Thông tin giao hàng</p> */}

          <div className="mt-4 border-b pb-4">
            <div className="flex justify-between items-center mb-3">
              <h3 className="font-semibold text-lg">Địa chỉ nhận hàng</h3>
              <button
                onClick={openSelectAddressModal}
                className="text-sm text-blue-600 hover:text-blue-800 font-medium"
              >
                Thay đổi
              </button>
            </div>
            {loading && <p>Đang tải địa chỉ...</p>}
            {!loading && selectedAddress ? (
              <div className="text-sm text-gray-700">
                <p className="font-medium">{selectedAddress.full_name} - {selectedAddress.phone}</p>
                <p>{`${selectedAddress.street_address}, ${selectedAddress.ward}, ${selectedAddress.district}, ${selectedAddress.province}, ${selectedAddress.country}`}</p>
                {selectedAddress.is_default && (
                  <span className="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full ml-2">Mặc định</span>
                )}
              </div>
            ) : (
              !loading && <p className="text-sm text-gray-500">Bạn chưa có địa chỉ. Vui lòng thêm địa chỉ.</p>
            )}
          </div>

          <div className="mt-4">
            <h3 className="font-semibold">Phương thức thanh toán</h3>
            <div className="mt-2 space-y-2">
              {paymentMethods.length > 0 ? (
                paymentMethods.map((method) => (
                  <label
                    key={method.id}
                    className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer"
                  >
                    <input
                      type="radio"
                      name="payment"
                      value={method.id} // Sử dụng ID từ API
                      checked={paymentMethod === method.id.toString()} // So sánh với ID
                      onChange={(e) => setPaymentMethod(e.target.value)}
                    />
                    <span>
                      {method.name === "Tiền mặt"
                        ? "Thanh toán khi nhận hàng"
                        : method.name}
                    </span>{" "}
                    {/* Giả sử API trả về field "name" */}
                  </label>
                ))
              ) : (
                <p>Đang tải phương thức thanh toán...</p>
              )}
            </div>
          </div>

          {error && <div className="mt-4 text-red-500 text-sm">{error}</div>}

          <div className="mt-6 flex justify-between items-center">
            <Link to="/products" className="text-sm text-blue-600 hover:text-blue-800">
              Tiếp tục mua sắm
            </Link>
            <button
              onClick={handlePaymentBuyNow}
              disabled={loading || !selectedAddress || !paymentMethod}
              className="bg-black text-white px-8 py-3 rounded-md hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? "Đang xử lý..." : "Đặt Hàng"}
            </button>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-md border">
          <h3 className="font-semibold">Đơn hàng của bạn</h3>
          <div className="mt-4">
            <div className="flex item-center">
              <input
                type="text"
                value={discountCode}
                onChange={(e) => setDiscountCode(e.target.value)}
                placeholder="Mã giảm giá"
                className="flex-1 px-4 py-2.5 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-300"
                disabled={isVerifying}
              />
              <button
                onClick={handleApplyDiscount}
                disabled={isVerifying || !discountCode}
                className="relative px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-r-xl overflow-hidden transform hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
              >
                <span className="absolute inset-0 w-full h-full bg-white opacity-0 hover:opacity-10 transition-opacity duration-300"></span>
                <span className="relative flex items-center gap-2">
                  {isVerifying ? (
                    <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24">
                      <circle
                        className="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        strokeWidth="4"
                        fill="none"
                      />
                      <path
                        className="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      />
                    </svg>
                  ) : (
                    "Áp dụng"
                  )}
                </span>
              </button>
            </div>
            {discountError && (
              <div className="text-red-500 text-sm mt-2">{discountError}</div>
            )}
          </div>
          <div className="mt-4 space-y-4">
            {selectedProducts.map((item) => {
              const price = item.product_variant
                ? item.product_variant.discount_price ||
                item.product_variant.price ||
                0
                : item.product.discount_price || item.product.price || 0;
              return (
                <div
                  key={`${item.product.id}-${JSON.stringify(
                    item.product_variant?.variant_details
                  )}`}
                  className="flex items-center space-x-4"
                >
                  <div className="relative w-16 h-16 bg-gray-200 rounded-lg overflow-hidden">
                    <img
                      src={`http://localhost:8000/storage/${item.product.image_thumbnail}`}
                      alt={item.product.name}
                      className="w-full h-full object-cover"
                      onError={(e) => {
                        e.target.src =
                          "https://via.placeholder.com/80x80?text=No+Image";
                      }}
                    />
                  </div>
                  <div className="flex-1">
                    <p className="font-medium">{item.product.name}</p>
                    <div className="mt-1 text-sm text-gray-500">
                      {formatPrice(price)} x {item.quantity}
                    </div>
                  </div>
                  <div className="font-medium">
                    {formatPrice(item.total_price)}
                  </div>
                </div>
              );
            })}
          </div>

          <div className="mt-6 border-t pt-4">
            <div className="flex justify-between text-gray-600">
              <span>Tạm tính</span>
              <span>{formatPrice(calculateSubtotal())}</span>
            </div>
            {discountAmount > 0 && (
              <div className="flex justify-between text-green-600 mt-2">
                <span>Giảm giá</span>
                <span>-{formatPrice(discountAmount)}</span>
              </div>
            )}
            <div className="flex justify-between text-gray-600 mt-2">
              <span>Phí vận chuyển</span>
              <span>free</span>
            </div>
            <div className="flex justify-between font-bold text-lg mt-4 pt-4 border-t">
              <span>Tổng cộng</span>
              <span>{formatPrice(calculateTotal())}</span>
            </div>
          </div>
        </div>
      </div>

      {showAddressModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            {/* Header Modal */}
            <div className="flex justify-between items-center p-4 border-b">
              <h2 className="text-lg font-semibold">
                {modalMode === 'select' && 'Danh sách địa chỉ'}
                {modalMode === 'add' && 'Thêm địa chỉ mới'}
                {modalMode === 'edit' && 'Chỉnh sửa địa chỉ'}
              </h2>
              <button onClick={() => setShowAddressModal(false)} className="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>

            {/* Body Modal */}
            <div className="p-6">
              {/* Chế độ Chọn địa chỉ */}
              {modalMode === 'select' && (
                <div>
                  {userAddresses.length > 0 ? (
                    <div className="space-y-4">
                      {userAddresses.map(addr => (
                        <div
                          key={addr.id}
                          className={`border rounded-md p-4 cursor-pointer hover:border-blue-500 ${selectedAddress?.id === addr.id ? 'border-blue-500 bg-blue-50' : 'border-gray-300'}`}
                          onClick={() => handleSelectAddress(addr)}
                        >
                          <div className="flex justify-between items-start mb-1">
                            <div className="font-medium">
                              {addr.full_name} - {addr.phone}
                              {addr.is_default && (
                                <span className="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full ml-2">Mặc định</span>
                              )}
                            </div>
                            <button
                              onClick={(e) => { e.stopPropagation(); openEditAddressModal(addr); }}
                              className="text-xs text-blue-600 hover:underline ml-4"
                            >
                              Sửa
                            </button>
                          </div>
                          <p className="text-sm text-gray-600">{`${addr.street_address}, ${addr.ward}, ${addr.district}, ${addr.province}`}</p>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p className="text-center text-gray-500">Bạn chưa có địa chỉ nào được lưu.</p>
                  )}
                  <div className="mt-6 text-center">
                    <button
                      onClick={openAddAddressModal}
                      className="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800"
                    >
                      Thêm địa chỉ mới
                    </button>
                  </div>
                </div>
              )}

              {/* Chế độ Thêm/Sửa địa chỉ */}
              {(modalMode === 'add' || modalMode === 'edit') && (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {/* Cột trái: Thông tin liên hệ */}
                  <div className="md:col-span-1 space-y-4">
                    <h4 className="font-semibold text-gray-700 mb-2">Thông Tin Liên Hệ</h4>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium mb-1">Họ *</label>
                        <input type="text" name="first_name" value={addressFormData.first_name} onChange={handleAddressFormChange} className="w-full border rounded-md p-2" />
                      </div>
                      <div>
                        <label className="block text-sm font-medium mb-1">Tên *</label>
                        <input type="text" name="last_name" value={addressFormData.last_name} onChange={handleAddressFormChange} className="w-full border rounded-md p-2" />
                      </div>
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Số điện thoại *</label>
                      <input type="tel" name="phone" value={addressFormData.phone} onChange={handleAddressFormChange} className="w-full border rounded-md p-2" />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Email (Tùy chọn)</label>
                      <input type="email" name="email" value={addressFormData.email} onChange={handleAddressFormChange} className="w-full border rounded-md p-2" />
                    </div>
                    {/* Nút đặt làm mặc định */}
                    <div className="flex items-center pt-2">
                      <label className="flex items-center cursor-pointer">
                        <div className="relative">
                          <input type="checkbox" name="is_default" checked={addressFormData.is_default} onChange={handleAddressFormChange} className="sr-only" />
                          <div className={`block w-10 h-6 rounded-full transition ${addressFormData.is_default ? 'bg-blue-500' : 'bg-gray-300'}`}></div>
                          <div className={`dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform ${addressFormData.is_default ? 'translate-x-4' : ''}`}></div>
                        </div>
                        <div className="ml-3 text-sm text-gray-700">Đặt làm địa chỉ mặc định</div>
                      </label>
                    </div>
                  </div>

                  {/* Cột phải: Địa chỉ giao hàng */}
                  <div className="md:col-span-1 space-y-4">
                    <h4 className="font-semibold text-gray-700 mb-2">Địa Chỉ Giao Hàng</h4>
                    <div>
                      <label className="block text-sm font-medium mb-1">Tên địa chỉ (VD: Nhà riêng, Công ty)</label>
                      <input type="text" name="address_name" value={addressFormData.address_name} onChange={handleAddressFormChange} className="w-full border rounded-md p-2" placeholder="Tùy chọn" />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Quốc gia</label>
                      <input type="text" name="country" value={addressFormData.country} onChange={handleAddressFormChange} className="w-full border rounded-md p-2 bg-gray-100" readOnly />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Tỉnh / Thành phố *</label>
                      <select name="province" value={provinces.find(p => p.name === addressFormData.province)?.code || ""} onChange={handleModalProvinceChange} className="w-full border rounded-md p-2">
                        <option value="">Chọn tỉnh/thành</option>
                        {provinces.map(p => <option key={p.code} value={p.code}>{p.name}</option>)}
                      </select>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium mb-1">Quận / Huyện *</label>
                        <select name="district" value={districts.find(d => d.name === addressFormData.district)?.code || ""} onChange={handleModalDistrictChange} className="w-full border rounded-md p-2" disabled={!addressFormData.province || districts.length === 0}>
                          <option value="">Chọn quận/huyện</option>
                          {districts.map(d => <option key={d.code} value={d.code}>{d.name}</option>)}
                        </select>
                      </div>
                      <div>
                        <label className="block text-sm font-medium mb-1">Xã / Phường *</label>
                        <select name="ward" value={wards.find(w => w.name === addressFormData.ward)?.code || ""} onChange={handleModalWardChange} className="w-full border rounded-md p-2" disabled={!addressFormData.district || wards.length === 0}>
                          <option value="">Chọn phường/xã</option>
                          {wards.map(w => <option key={w.code} value={w.code}>{w.name}</option>)}
                        </select>
                      </div>
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Địa chỉ đường *</label>
                      <input type="text" name="street_address" value={addressFormData.street_address} onChange={handleAddressFormChange} className="w-full border rounded-md p-2" placeholder="Số nhà, tên đường..." />
                    </div>
                  </div>
                </div>
              )}
            </div>{/*  Đóng thẻ div Body Modal */}

            {/* Footer Modal (Chỉ hiển thị khi thêm/sửa) */}
            {(modalMode === 'add' || modalMode === 'edit') && (
              <div className="flex justify-end space-x-3 p-4 border-t">
                <button
                  onClick={() => userAddresses.length > 0 ? setModalMode('select') : setShowAddressModal(false)} // Quay lại Select nếu có địa chỉ, nếu không thì đóng
                  className="px-4 py-2 border rounded-md"
                >
                  Hủy
                </button>
                <button
                  onClick={handleSaveAddress}
                  className="px-6 py-2 bg-black text-white rounded-md"
                  disabled={loading}
                >
                  {loading ? "Đang lưu..." : "Lưu lại"}
                </button>
              </div>
            )}
            {/* Footer Modal (Chỉ hiển thị khi chọn) */}
            {modalMode === 'select' && (
              <div className="flex justify-end p-4 border-t">
                <button
                  onClick={() => setShowAddressModal(false)}
                  className="px-6 py-2 bg-gray-200 text-gray-700 rounded-md"
                >
                  Đóng
                </button>
              </div>
            )}
          </div> {/* Đóng thẻ div cho modal content */}
        </div> // Đóng thẻ div cho modal overlay
      )}
    </div>
  );
};

export default PaymentBuyNow;
