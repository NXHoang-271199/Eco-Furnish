import React, { useState, useEffect } from "react";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import { clearSelectedItems } from "../../../store/cartSlice";
import axios from "axios";
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

  // State mới cho danh sách voucher
  const [availableVouchers, setAvailableVouchers] = useState([]);
  const [showVoucherDropdown, setShowVoucherDropdown] = useState(false);
  const [selectedVoucher, setSelectedVoucher] = useState(null);

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

  // Thêm state để lưu số dư ví và trạng thái loading của ví
  const [walletBalance, setWalletBalance] = useState(0);
  const [loadingWallet, setLoadingWallet] = useState(false);

  // Thêm các state cho mật khẩu cấp 2
  const [hasLevel2Password, setHasLevel2Password] = useState(false);
  const [showLevel2PasswordModal, setShowLevel2PasswordModal] = useState(false);
  const [level2Password, setLevel2Password] = useState("");
  const [level2PasswordError, setLevel2PasswordError] = useState("");
  const [checkingLevel2Password, setCheckingLevel2Password] = useState(false);

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
      navigate("/cart");
    }

    getPaymentMethod();
    fetchProvinces();
    getWalletBalance(); // Thêm gọi hàm lấy số dư ví
    checkLevel2PasswordStatus(); // Thêm gọi hàm kiểm tra trạng thái mật khẩu cấp 2
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
    // Lấy email mặc định từ localStorage
    const userData = JSON.parse(localStorage.getItem("userData")) || {};
    setAddressFormData({
      first_name: "",
      last_name: "",
      phone: "",
      email: userData.email || "",
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

  // Thêm useEffect để lấy danh sách voucher khả dụng khi component mount
  useEffect(() => {
    const fetchAvailableVouchers = async () => {
      try {
        const token = localStorage.getItem("authToken");
        if (!token) return;

        const response = await axiosInstance.get("/vouchers", {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        if (response.data.status === "success") {
          setAvailableVouchers(response.data.data || []);
        }
      } catch (error) {
        console.error("Lỗi khi lấy danh sách voucher:", error);
      }
    };

    fetchAvailableVouchers();
  }, []);

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
        // Cập nhật voucher đã chọn
        const selected = availableVouchers.find(v => v.id === response.data.voucher_id);
        setSelectedVoucher(selected || null);
      } else {
        setDiscountError(response.data.message || "Mã giảm giá không hợp lệ");
        setDiscountAmount(0);
        setVoucherId(null);
        setSelectedVoucher(null);
      }
    } catch (err) {
      console.error("Lỗi khi áp dụng mã giảm giá:", err);
      setDiscountError(
        err.response?.data?.message || "Có lỗi xảy ra khi áp dụng mã giảm giá"
      );
      setDiscountAmount(0);
      setVoucherId(null);
      setSelectedVoucher(null);
    } finally {
      setIsVerifying(false);
    }
  };

  // Hàm mới để áp dụng voucher khi click vào voucher từ dropdown
  const handleSelectVoucher = async (voucher) => {
    // Đóng dropdown
    setShowVoucherDropdown(false);

    // Kiểm tra nếu giá trị đơn hàng không đủ để áp dụng voucher
    if (calculateSubtotal() < voucher.min_order_value) {
      setDiscountError(`Giá trị đơn hàng tối thiểu phải từ ${formatPrice(voucher.min_order_value)}`);
      return;
    }

    // Đặt mã giảm giá vào ô input
    setDiscountCode(voucher.code);

    // Áp dụng voucher
    try {
      setIsVerifying(true);
      setDiscountError("");

      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post(
        "/check-voucher",
        {
          voucher_code: voucher.code,
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
        setSelectedVoucher(voucher);
      } else {
        setDiscountError(response.data.message || "Mã giảm giá không hợp lệ");
        setDiscountAmount(0);
        setVoucherId(null);
        setSelectedVoucher(null);
      }
    } catch (err) {
      console.error("Lỗi khi áp dụng mã giảm giá:", err);
      setDiscountError(
        err.response?.data?.message || "Có lỗi xảy ra khi áp dụng mã giảm giá"
      );
      setDiscountAmount(0);
      setVoucherId(null);
      setSelectedVoucher(null);
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

    // Xử lý thanh toán bằng ví
    const selectedMethodObj = paymentMethods.find((method) => method.id === Number(paymentMethod));
    if (selectedMethodObj && selectedMethodObj.name === "Ví") {
      // Kiểm tra nếu chưa có mật khẩu cấp 2
      if (!hasLevel2Password) {
        // Chuyển hướng đến trang thiết lập mật khẩu cấp 2 với tham số redirect để quay lại
        // Lưu trạng thái hiện tại vào localStorage để có thể khôi phục sau khi thiết lập mật khẩu cấp 2
        localStorage.setItem("pendingPaymentState", JSON.stringify({
          product_id: selectedProducts[0].product.id,
          product_variant_id: selectedProducts[0].product_variant?.id || null,
          quantity: selectedProducts[0].quantity,
          selectedAddress: selectedAddress?.id,
          paymentMethod,
          voucherId,
          discountAmount,
          type: "buy_now",
          // Thêm thông tin chi tiết về sản phẩm
          product_name: selectedProducts[0].product.name,
          product_price: selectedProducts[0].product.price,
          product_discount_price: selectedProducts[0].product.discount_price,
          product_image_thumbnail: selectedProducts[0].product.image_thumbnail,
          // Thêm thông tin chi tiết về biến thể nếu có
          variant_price: selectedProducts[0].product_variant?.price,
          variant_discount_price: selectedProducts[0].product_variant?.discount_price,
          // Thêm thông tin tổng giá trị sản phẩm
          total_price: calculateSubtotal()
        }));

        navigate("/account?tab=level2password&redirect=payment_buy_now");
        toast.info("Vui lòng thiết lập mật khẩu cấp 2 để thanh toán bằng ví.");
        return;
      }

      // Nếu đã có mật khẩu cấp 2, hiển thị modal xác nhận
      setShowLevel2PasswordModal(true);
      setLevel2Password("");
      setLevel2PasswordError("");
      return;
    }

    // Tiếp tục xử lý đặt hàng nếu không phải thanh toán bằng ví
    processOrder();
  };

  // Tách logic xử lý đặt hàng
  const processOrder = async (level2PasswordInput = "") => {
    setLoading(true);
    setError("");

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

      // Thêm mật khẩu cấp 2 nếu thanh toán bằng ví
      const selectedMethod = paymentMethods.find((method) => method.id === Number(paymentMethod));
      if (selectedMethod && selectedMethod.name === "Ví" && level2PasswordInput) {
        orderData.level2_password = level2PasswordInput;
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

      // Kiểm tra xem có phải lỗi mật khẩu cấp 2 không chính xác không
      if (err.response?.data?.message === "Mật khẩu cấp 2 không chính xác") {
        toast.error("Mật khẩu cấp 2 không chính xác");
        // Reset lại trạng thái kiểm tra mật khẩu cấp 2 để có thể thử lại
        setCheckingLevel2Password(false);
        // Giữ modal mở để người dùng có thể nhập lại
      } else {
        // Các lỗi khác vẫn hiển thị như cũ
        setError(
          err.response?.data?.message || "Có lỗi xảy ra khi xử lý đơn hàng"
        );
        // Đóng modal cho lỗi không phải mật khẩu cấp 2
        setShowLevel2PasswordModal(false);
      }
    } finally {
      setLoading(false);
    }
  };

  // Hàm xử lý khi nhấn nút xác nhận mật khẩu cấp 2
  const handleConfirmLevel2Password = () => {
    // Kiểm tra xem đã nhập mật khẩu cấp 2 chưa
    if (!level2Password) {
      setLevel2PasswordError("Vui lòng nhập mật khẩu cấp 2");
      return;
    }

    // Xóa lỗi cũ nếu có
    setLevel2PasswordError("");
    setError("");

    // Gọi hàm xử lý đặt hàng với mật khẩu cấp 2
    setCheckingLevel2Password(true);
    processOrder(level2Password);
  };

  // Thêm hàm kiểm tra trạng thái mật khẩu cấp 2
  const checkLevel2PasswordStatus = async () => {
    try {
      const token = localStorage.getItem("authToken");
      if (!token) return;

      const response = await axiosInstance.get("/users/level2-password/status", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.data.status === "success") {
        setHasLevel2Password(response.data.data.has_level2_password);
      }
    } catch (error) {
      console.error("Lỗi khi kiểm tra trạng thái mật khẩu cấp 2:", error);
    }
  };

  // Thêm hàm để lấy số dư ví
  const getWalletBalance = async () => {
    const token = localStorage.getItem("authToken");
    if (token) {
      try {
        setLoadingWallet(true);
        const response = await axiosInstance.get("/wallet/balance", {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        setWalletBalance(response.data.balance || 0);
      } catch (error) {
        console.error("Lỗi khi lấy số dư ví:", error);
        setWalletBalance(0);
      } finally {
        setLoadingWallet(false);
      }
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

          <div className="mt-6 bg-white p-5 rounded-lg shadow-sm border">
            <h3 className="font-semibold text-lg mb-4 text-gray-800 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                <path fillRule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clipRule="evenodd" />
              </svg>
              Phương thức thanh toán
            </h3>
            <div className="space-y-3">
              {paymentMethods.length > 0 ? (
                paymentMethods.map((method) => {
                  // Kiểm tra xem phương thức thanh toán có phải là Ví không
                  const isWalletMethod = method.name === "Ví";
                  // Kiểm tra xem số dư ví có đủ để thanh toán không
                  const insufficientBalance = isWalletMethod && walletBalance < calculateTotal();
                  // Quyết định disabled dựa trên điều kiện số dư
                  const isDisabled = isWalletMethod && insufficientBalance;

                  return (
                    <label
                      key={method.id}
                      className={`relative flex items-center justify-between p-4 rounded-xl transition-all duration-200 ${paymentMethod === method.id.toString() && !isDisabled
                        ? "bg-blue-50 border-2 border-blue-500"
                        : "border border-gray-200 hover:border-blue-400"
                        } ${isDisabled
                          ? "opacity-60 cursor-not-allowed bg-gray-50"
                          : "cursor-pointer"
                        }`}
                    >
                      <div className="flex items-center space-x-4">
                        <input
                          type="radio"
                          name="payment"
                          value={method.id}
                          checked={paymentMethod === method.id.toString()}
                          onChange={(e) => setPaymentMethod(e.target.value)}
                          disabled={isDisabled}
                          className="form-radio h-5 w-5 text-blue-600"
                        />
                        {method.image ? (
                          <div className="w-12 h-12 flex items-center justify-center rounded-lg overflow-hidden bg-white p-1 border border-gray-100 shadow-sm">
                            <img
                              src={`http://localhost:8000/storage/${method.image}`}
                              alt={method.name}
                              className="h-8 object-contain"
                            />
                          </div>
                        ) : (
                          <div className="w-12 h-12 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                              <path fillRule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clipRule="evenodd" />
                            </svg>
                          </div>
                        )}
                        <div className="flex flex-col">
                          <span className="font-medium text-gray-800">
                            {method.name === "Tiền mặt"
                              ? "Thanh toán khi nhận hàng"
                              : method.name}
                          </span>
                          {isWalletMethod && (
                            <span className={`text-sm ${insufficientBalance ? "text-red-500" : "text-green-600"}`}>
                              Số dư: {formatPrice(walletBalance)}
                            </span>
                          )}
                          {method.name === "MoMo" && (
                            <span className="text-sm text-gray-500">Thanh toán qua ví điện tử MoMo</span>
                          )}
                          {method.name === "VNPAY" && (
                            <span className="text-sm text-gray-500">Thanh toán qua cổng VNPAY</span>
                          )}
                          {method.name === "Tiền mặt" && (
                            <span className="text-sm text-gray-500">Thanh toán khi nhận được hàng</span>
                          )}
                        </div>
                      </div>

                      {/* Phù hợp nhất / Không đủ số dư */}
                      {(paymentMethod === method.id.toString() && !isDisabled) && (
                        <span className="absolute top-2 right-2 bg-blue-500 text-white text-xs font-medium px-2 py-1 rounded-full">
                          Đã chọn
                        </span>
                      )}
                      {isWalletMethod && insufficientBalance && (
                        <span className="text-xs text-red-500 font-medium bg-red-50 px-2 py-1 rounded-full">
                          Số dư không đủ
                        </span>
                      )}
                    </label>
                  );
                })
              ) : (
                <div className="flex items-center justify-center p-6 text-gray-500">
                  <svg className="animate-spin mr-2 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Đang tải phương thức thanh toán...
                </div>
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

            {/* Hiển thị Voucher khả dụng */}
            <div className="relative mt-2">
              <button
                onClick={() => setShowVoucherDropdown(!showVoucherDropdown)}
                className="text-blue-600 text-sm font-medium hover:text-blue-800 cursor-pointer flex items-center"
              >
                {showVoucherDropdown ? "Ẩn" : "Hiển thị"} mã giảm giá khả dụng
                <svg
                  className={`ml-1 w-4 h-4 transition-transform ${showVoucherDropdown ? 'rotate-180' : ''}`}
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>

              {showVoucherDropdown && (
                <div className="absolute z-10 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg">
                  <div className="p-2 max-h-60 overflow-y-auto">
                    {availableVouchers.length > 0 ? (
                      availableVouchers.map((voucher) => {
                        const isApplicable = calculateSubtotal() >= voucher.min_order_value;
                        return (
                          <div
                            key={voucher.id}
                            onClick={() => isApplicable && handleSelectVoucher(voucher)}
                            className={`p-3 border-b border-gray-100 last:border-b-0 ${isApplicable ? 'cursor-pointer hover:bg-gray-50' : 'opacity-50 cursor-not-allowed'
                              } ${selectedVoucher?.id === voucher.id ? 'bg-amber-50' : ''}`}
                          >
                            <div className="flex justify-between items-start">
                              <div>
                                <span className="font-medium text-gray-800">{voucher.code}</span>
                                <p className="text-xs text-gray-500 mt-1">
                                  Giảm {voucher.discount_percentage}% tối đa {formatPrice(voucher.max_discount_amount)}
                                </p>
                              </div>
                              <div className="text-right">
                                <span className={`text-xs px-2 py-1 rounded-full ${isApplicable ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                  }`}>
                                  {isApplicable ? 'Có thể dùng' : 'Chưa đủ điều kiện'}
                                </span>
                                <p className="text-xs text-gray-500 mt-1">
                                  Đơn tối thiểu {formatPrice(voucher.min_order_value)}
                                </p>
                              </div>
                            </div>
                          </div>
                        );
                      })
                    ) : (
                      <div className="p-3 text-center text-gray-500">Không có mã giảm giá khả dụng</div>
                    )}
                  </div>
                </div>
              )}
            </div>
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
                      <label className="block text-sm font-medium mb-1">Email *</label>
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
            </div>

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

      {/* Modal xác nhận mật khẩu cấp 2 */}
      {showLevel2PasswordModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg w-full max-w-md p-6">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-lg font-semibold">Xác nhận thanh toán</h2>
              <button
                onClick={() => setShowLevel2PasswordModal(false)}
                className="text-gray-500 hover:text-gray-700 text-2xl"
                disabled={checkingLevel2Password}
              >
                &times;
              </button>
            </div>

            <p className="mb-4 text-gray-700">Để bảo mật giao dịch, vui lòng nhập mật khẩu cấp 2 của bạn.</p>

            <div className="mb-4">
              <input
                type="password"
                value={level2Password}
                onChange={(e) => setLevel2Password(e.target.value)}
                placeholder="Nhập mật khẩu cấp 2"
                className="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                disabled={checkingLevel2Password}
              />
              {level2PasswordError && (
                <p className="mt-1 text-red-500 text-sm">{level2PasswordError}</p>
              )}
            </div>

            <div className="flex justify-end gap-3">
              <button
                onClick={() => setShowLevel2PasswordModal(false)}
                className="px-4 py-2 border rounded-md"
                disabled={checkingLevel2Password}
              >
                Hủy
              </button>
              <button
                onClick={handleConfirmLevel2Password}
                className="px-4 py-2 bg-black text-white rounded-md"
                disabled={checkingLevel2Password}
              >
                {checkingLevel2Password ? (
                  <span className="flex items-center">
                    <svg className="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Đang xác thực
                  </span>
                ) : "Xác nhận"}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default PaymentBuyNow;
