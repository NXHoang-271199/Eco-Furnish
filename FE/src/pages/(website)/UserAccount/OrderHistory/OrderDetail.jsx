import React, { useState, useEffect, useCallback, useRef } from "react";
import { useParams, Link } from "react-router-dom";
import axios from "axios";
import axiosInstance from "../../../../utils/axiosConfig";
import {
  FiArrowLeft,
  FiInfo,
  FiPhone,
  FiMail,
  FiMapPin,
  FiPackage,
  FiClock,
  FiTruck,
  FiCalendar,
  FiCheckCircle,
  FiXCircle,
  FiAlertCircle,
  FiShoppingBag,
  FiRefreshCw,
  FiHome,
  FiUser,
} from "react-icons/fi";
import { motion, AnimatePresence } from "framer-motion";

const OrderDetail = () => {
  const { id } = useParams();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [autoConfirmTimerSet, setAutoConfirmTimerSet] = useState(false);
  const autoConfirmTimerIdRef = useRef(null); // Ref để lưu ID của timer

  // --- Logic Polling ---
  const fetchOrderDetailCallback = useCallback(
    async (isPolling = false) => {
      // Không hiển thị loading toàn trang khi polling
      // if (!isPolling) setLoading(true);

      try {
        const token = localStorage.getItem("authToken");
        const response = await axiosInstance.get(`/orders/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
          withCredentials: true,
        });

        if (response.data.status === "success") {
          setOrder(response.data.data);
          if (!isPolling) setError(null); // Chỉ xóa lỗi chính khi tải lần đầu thành công
        } else {
          // Xử lý trường hợp API trả về status không phải success
          console.warn(
            "API không trả về success khi lấy chi tiết đơn hàng:",
            response.data
          );
          if (!isPolling) setError("Không thể tải thông tin đơn hàng.");
        }
      } catch (error) {
        console.error(
          "Lỗi khi lấy chi tiết đơn hàng (polling: " + isPolling + "):",
          error
        );
        // Chỉ hiển thị lỗi toàn trang khi tải lần đầu thất bại
        if (!isPolling) {
          setError("Không thể tải thông tin đơn hàng. Vui lòng thử lại sau.");
        }
      } finally {
        // Không tắt loading toàn trang khi polling
        // if (!isPolling) setLoading(false);
      }
    },
    [id]
  );

  // useEffect cho lần tải đầu tiên
  useEffect(() => {
    setLoading(true); // Bật loading khi tải lần đầu
    fetchOrderDetailCallback(false).finally(() => setLoading(false)); // Tải lần đầu và tắt loading sau khi xong
  }, [fetchOrderDetailCallback]);

  // useEffect cho việc polling
  useEffect(() => {
    // Điều kiện dừng polling
    const finalOrderStates = ["Đã Nhận", "Hủy Đơn"];
    const finalRefundStates = [
      "completed",
      "rejected",
      "đã hoàn tiền",
      "từ chối",
    ]; // lowercase
    const latestRefundStatus =
      order?.refund_request?.[0]?.status?.toLowerCase();
    const isOrderInFinalState =
      finalOrderStates.includes(order?.order_status) ||
      (latestRefundStatus && finalRefundStates.includes(latestRefundStatus));

    if (!order || isOrderInFinalState) {
      console.log("Polling stopped: Order data missing or in final state.");
      return; // Dừng nếu không có order hoặc đã ở trạng thái cuối
    }

    console.log("Starting polling for order details...");
    const intervalId = setInterval(() => {
      console.log("Polling...");
      fetchOrderDetailCallback(true); // Gọi với isPolling = true
    }, 5000); // 5 giây

    // Hàm dọn dẹp khi component unmount hoặc dependencies thay đổi
    return () => {
      console.log("Clearing polling interval.");
      clearInterval(intervalId);
    };
    // Chạy lại effect này nếu order thay đổi (để kiểm tra lại điều kiện dừng)
  }, [order, fetchOrderDetailCallback]);

  // --- Tự động xác nhận sau 30 phút --- START ---
  // Hàm thực hiện gọi API xác nhận
  const performOrderConfirmation = useCallback(
    async (isAutoConfirm = false) => {
      // setLoading(true); // Cân nhắc hiển thị loading

      try {
        const token = localStorage.getItem("authToken");
        // Lấy trạng thái mới nhất trước khi xác nhận (đề phòng race condition)
        const latestOrderResponse = await axiosInstance.get(`/orders/${id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (
          latestOrderResponse.data.status !== "success" ||
          latestOrderResponse.data.data.order_status !== "Đã Giao"
        ) {
          console.log(
            "Auto-confirmation skipped, status is no longer 'Đã Giao' or failed to fetch latest status."
          );
          // setLoading(false);
          return; // Không xác nhận nếu trạng thái không còn là Đã Giao
        }

        // Chỉ hỏi nếu là xác nhận thủ công
        if (
          !isAutoConfirm &&
          !window.confirm("Bạn đã nhận được hàng và muốn xác nhận?")
        ) {
          // setLoading(false);
          return;
        }

        const response = await axiosInstance.post(
          `/orders/${id}/confirm`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data.status === "success") {
          if (!isAutoConfirm) {
            alert("Đã xác nhận nhận hàng thành công!");
          } else {
            console.log("Đơn hàng tự động xác nhận sau 30 phút.");
            // Có thể thêm thông báo nhẹ nhàng hơn alert
          }
          // Cập nhật lại thông tin đơn hàng cục bộ
          setOrder(response.data.data); // Sử dụng data trả về từ API confirm
        } else {
          // Xử lý lỗi từ API confirm
          if (!isAutoConfirm) {
            alert(response.data.message || "Không thể xác nhận đơn hàng.");
          } else {
            console.error("Lỗi tự động xác nhận:", response.data.message);
          }
        }
      } catch (error) {
        console.error(
          "Lỗi khi xác nhận đơn hàng:",
          error.response?.data || error.message
        );
        if (!isAutoConfirm) {
          alert("Không thể xác nhận đơn hàng. Vui lòng thử lại sau.");
        } // Lỗi tự động thì log
      } finally {
        // setLoading(false);
      }
    },
    [id]
  ); // Thêm id vào dependencies

  // --- Tự động xác nhận sau 24 giờ --- START ---
  // useEffect để thiết lập timer tự động xác nhận
  useEffect(() => {
    // Hàm cleanup để xóa timer cũ
    const clearExistingTimer = () => {
      if (autoConfirmTimerIdRef.current) {
        clearTimeout(autoConfirmTimerIdRef.current);
        autoConfirmTimerIdRef.current = null;
        console.log("Cleared previous auto-confirm timer.");
      }
    };

    if (order && order.order_status === "Đã Giao" && !autoConfirmTimerSet) {
      // console.log("Order is 'Đã Giao'. Checking for auto-confirmation.");
      // Giả định updated_at là thời điểm chuyển sang "Đã Giao"
      const deliveredTime = new Date(order.updated_at).getTime();
      const currentTime = new Date().getTime();
      const timeSinceDelivered = currentTime - deliveredTime;
      const autoConfirmDelayMs = 24 * 60 * 60 * 1000; // 24 giờ

      if (timeSinceDelivered >= autoConfirmDelayMs) {
        console.log(
          "Order delivered more than 30 mins ago. Triggering auto-confirm."
        );
        // Dùng setTimeout 0 để đẩy ra khỏi luồng render hiện tại
        clearExistingTimer(); // Xóa timer cũ nếu có
        // Không cần set timer, gọi trực tiếp (hoặc qua setTimeout 0)
        performOrderConfirmation(true); // Gọi xác nhận tự động
        setAutoConfirmTimerSet(true); // Đánh dấu đã xử lý
      } else {
        const remainingDelay = autoConfirmDelayMs - timeSinceDelivered;
        console.log(
          `Setting auto-confirm timer for ${Math.round(
            remainingDelay / 1000
          )} seconds.`
        );
        clearExistingTimer(); // Xóa timer cũ trước khi set timer mới
        autoConfirmTimerIdRef.current = setTimeout(() => {
          performOrderConfirmation(true); // Gọi xác nhận tự động
        }, remainingDelay);
        setAutoConfirmTimerSet(true); // Đánh dấu đã set timer
      }
    } else if (order && order.order_status !== "Đã Giao") {
      // Nếu trạng thái không còn là Đã Giao, xóa timer và reset cờ
      clearExistingTimer();
      if (autoConfirmTimerSet) {
        setAutoConfirmTimerSet(false);
      }
    }

    // Hàm cleanup chính của useEffect
    return () => {
      clearExistingTimer();
    };
    // Thêm performOrderConfirmation vào dependencies
  }, [order, autoConfirmTimerSet, performOrderConfirmation]);
  // --- Tự động xác nhận sau 30 phút --- END ---

  // Chuyển đổi mã trạng thái thanh toán thành text và màu sắc
  const getPaymentStatusInfo = (statusCode) => {
    switch (statusCode) {
      case 0:
        return {
          text: "Chưa thanh toán",
          color: "text-yellow-600",
          bgColor: "bg-yellow-100",
          icon: <FiClock className="w-5 h-5" />,
        };
      case 1:
        return {
          text: "Đã thanh toán",
          color: "text-green-600",
          bgColor: "bg-green-100",
          icon: <FiCheckCircle className="w-5 h-5" />,
        };
      case 2:
        return {
          text: "Đang chờ thanh toán",
          color: "text-blue-600",
          bgColor: "bg-blue-100",
          icon: <FiRefreshCw className="w-5 h-5" />,
        };
      default:
        return {
          text: "Không xác định",
          color: "text-gray-600",
          bgColor: "bg-gray-100",
          icon: <FiAlertCircle className="w-5 h-5" />,
        };
    }
  };

  // Lấy thông tin về trạng thái đơn hàng
  const getOrderStatusInfo = (status) => {
    switch (status) {
      case "Đang Xử Lý":
        return {
          color: "text-blue-600",
          bgColor: "bg-blue-100",
          icon: <FiRefreshCw className="w-5 h-5" />,
        };
      case "Chưa Xác Nhận":
        return {
          color: "text-yellow-600",
          bgColor: "bg-yellow-100",
          icon: <FiClock className="w-5 h-5" />,
        };
      case "Đã Xác Nhận":
        return {
          color: "text-purple-600",
          bgColor: "bg-purple-100",
          icon: <FiCheckCircle className="w-5 h-5" />,
        };
      case "Đang Chuẩn Bị Hàng":
        return {
          color: "text-blue-600",
          bgColor: "bg-blue-100",
          icon: <FiPackage className="w-5 h-5" />,
        };
      case "Đang Giao":
        return {
          color: "text-amber-600",
          bgColor: "bg-amber-100",
          icon: <FiTruck className="w-5 h-5" />,
        };
      case "Đã Giao":
        return {
          color: "text-green-600",
          bgColor: "bg-green-100",
          icon: <FiTruck className="w-5 h-5" />,
        };
      case "Đã Nhận":
        return {
          color: "text-green-600",
          bgColor: "bg-green-100",
          icon: <FiCheckCircle className="w-5 h-5" />,
        };
      case "Hủy Đơn":
        return {
          color: "text-red-600",
          bgColor: "bg-red-100",
          icon: <FiXCircle className="w-5 h-5" />,
        };
      case "Hoàn Hàng":
        return {
          color: "text-orange-600",
          bgColor: "bg-orange-100",
          icon: <FiPackage className="w-5 h-5" />,
        };
      default:
        return {
          color: "text-gray-600",
          bgColor: "bg-gray-100",
          icon: <FiInfo className="w-5 h-5" />,
        };
    }
  };

  // Hiển thị các thuộc tính của biến thể sản phẩm
  const renderVariantDetails = (variant) => {
    if (
      !variant ||
      !variant.variant_details ||
      variant.variant_details.length === 0
    ) {
      return null;
    }

    return variant.variant_details.map((detail, index) => (
      <div
        key={index}
        className="text-sm text-gray-600 inline-flex items-center mr-3"
      >
        <span className="font-medium mr-1">{detail.name}:</span> {detail.value}
      </div>
    ));
  };

  // Xử lý hủy đơn hàng
  const handleCancelOrder = async () => {
    if (!window.confirm("Bạn có chắc chắn muốn hủy đơn hàng này?")) {
      return;
    }

    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post(
        `/orders/${id}/cancel`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.status === "success") {
        alert("Đơn hàng đã được hủy thành công!");
        // Cập nhật lại thông tin đơn hàng
        const updatedOrder = { ...order, order_status: "Hủy Đơn" };
        setOrder(updatedOrder);
      }
    } catch (error) {
      console.error("Lỗi khi hủy đơn hàng:", error);
      alert("Không thể hủy đơn hàng. Vui lòng thử lại sau.");
    } finally {
      setLoading(false);
    }
  };

  // Xử lý xác nhận đã nhận hàng (thủ công)
  const handleConfirmOrder = async () => {
    await performOrderConfirmation(false); // false = xác nhận thủ công
  };

  // Xử lý yêu cầu hoàn hàng
  const handleRequestRefund = async () => {
    const reason = prompt("Vui lòng nhập lý do hoàn hàng:");
    if (reason === null) return; // Người dùng nhấn hủy

    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post(
        `/orders/${id}/request-refund`,
        { reason },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.status === "success") {
        alert("Yêu cầu hoàn hàng đã được gửi!");
        // Cập nhật state với yêu cầu hoàn hàng (dưới dạng mảng)
        setOrder({
          ...order,
          refund_request: [
            {
              status: "pending", // Giả sử trạng thái ban đầu là pending
              reason: reason,
              created_at: new Date().toISOString(),
            },
          ],
        });
      }
    } catch (error) {
      console.error("Lỗi khi yêu cầu hoàn hàng:", error);
      alert(
        error.response?.data?.message ||
          "Không thể gửi yêu cầu hoàn hàng. Vui lòng thử lại sau."
      );
    } finally {
      setLoading(false);
    }
  };

  // Xử lý thanh toán lại
  const handleRetryPayment = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post(
        `/payment-method/retry/${id}`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      console.log("Response từ API thanh toán lại:", response);

      // Kiểm tra phản hồi và chuyển hướng
      if (response.data.status === "success" && response.data.payUrl) {
        // Nếu backend trả về payUrl
        window.location.href = response.data.payUrl;
      } else if (response.data.message === "success" && response.data.data) {
        // Nếu backend trả về data (trường hợp VNPAY)
        window.location.href = response.data.data;
      } else if (response.data.payUrl) {
        // Nếu backend chỉ trả về payUrl (trường hợp MoMo có thể)
        window.location.href = response.data.payUrl;
      } else {
        // Hiển thị thông báo lỗi cụ thể hơn nếu có
        alert(
          response.data.message ||
            "Không thể lấy link thanh toán lại. Vui lòng kiểm tra console."
        );
        console.error(
          "API response không chứa URL thanh toán hợp lệ:",
          response.data
        );
      }
    } catch (error) {
      console.error("Lỗi khi thanh toán lại:", error);
      alert("Không thể thanh toán lại. Vui lòng thử lại sau.");
    } finally {
      setLoading(false);
    }
  };

  // Hiển thị tiến trình hoàn tiền
  const renderRefundProgress = (refundStatus) => {
    const refundSteps = [
      { id: 1, name: "Gửi yêu cầu", icon: <FiRefreshCw /> },
      { id: 2, name: "Được chấp nhận", icon: <FiCheckCircle /> },
      { id: 3, name: "Đã hoàn tiền", icon: <FiCheckCircle /> },
    ];

    let currentStep = 1; // Mặc định là bước 1

    // Xác định bước hiện tại dựa trên trạng thái hoàn hàng
    const refundStatusLower = refundStatus?.toLowerCase(); // Chuyển sang chữ thường để không phân biệt hoa/thường

    // Xử lý trường hợp bị từ chối riêng
    if (refundStatusLower === "rejected" || refundStatusLower === "từ chối") {
      return (
        <div className="my-6 bg-red-100 text-red-700 p-4 rounded-lg flex items-center justify-center">
          <FiXCircle className="w-5 h-5 mr-2" />
          <span>Yêu cầu hoàn hàng của bạn đã bị từ chối.</span>
          {/* Optional: Thêm lý do từ chối nếu có */}
          {/* {order.refund_request[0]?.reject_reason && <p className="mt-2 text-sm">Lý do: {order.refund_request[0].reject_reason}</p>} */}
        </div>
      );
    }

    // Xác định bước cho các trạng thái khác
    switch (refundStatusLower) {
      case "pending":
        currentStep = 1;
        break;
      case "đã duyệt":
        currentStep = 2;
        break;
      case "completed":
      case "refunded":
      case "đã hoàn tiền":
        currentStep = 3;
        break;
      default:
        currentStep = 1; // Mặc định nếu trạng thái không xác định
    }

    return (
      <div className="my-6">
        <div className="relative">
          <div className="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
            <motion.div
              initial={{ width: 0 }}
              animate={{
                width: `${Math.min(
                  100,
                  (currentStep / refundSteps.length) * 100
                )}%`,
              }}
              transition={{ duration: 0.8, ease: "easeOut" }}
              className="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-orange-500"
            ></motion.div>
          </div>
          <div className="flex justify-between">
            {refundSteps.map((step, index) => (
              <div
                key={step.id}
                className={`flex flex-col items-center ${
                  currentStep >= step.id ? "text-orange-600" : "text-gray-400"
                }`}
              >
                <div
                  className={`
                  rounded-full h-8 w-8 flex items-center justify-center border-2 mb-1
                  ${
                    currentStep >= step.id
                      ? "border-orange-500 bg-orange-100"
                      : "border-gray-300"
                  }
                `}
                >
                  {step.icon}
                </div>
                <span className="text-xs font-medium text-center">
                  {step.name}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  };

  // Hiển thị tiến trình đơn hàng
  const renderOrderProgress = (status) => {
    // Nếu có yêu cầu hoàn tiền (là mảng và không rỗng), hiển thị thanh tiến trình hoàn tiền
    if (
      Array.isArray(order.refund_request) &&
      order.refund_request.length > 0
    ) {
      // Lấy trạng thái từ yêu cầu hoàn tiền đầu tiên (giả định chỉ có 1 yêu cầu active)
      const currentRefundStatus = order.refund_request[0]?.status || "pending";
      return renderRefundProgress(currentRefundStatus);
    }

    const steps = [
      { id: 1, name: "Chưa Xác Nhận", icon: <FiClock /> },
      { id: 2, name: "Đã Xác Nhận", icon: <FiCheckCircle /> },
      { id: 3, name: "Đang Chuẩn Bị Hàng", icon: <FiPackage /> },
      { id: 4, name: "Đang Giao", icon: <FiTruck /> },
      { id: 5, name: "Đã Giao", icon: <FiTruck /> },
      { id: 6, name: "Đã Nhận", icon: <FiCheckCircle /> },
    ];

    let currentStep = 0;

    switch (status) {
      case "Chưa Xác Nhận":
        currentStep = 1;
        break;
      case "Đã Xác Nhận":
        currentStep = 2;
        break;
      case "Đang Chuẩn Bị Hàng":
        currentStep = 3;
        break;
      case "Đang Giao":
        currentStep = 4;
        break;
      case "Đã Giao":
        currentStep = 5;
        break;
      case "Đã Nhận":
        currentStep = 6;
        break;
      case "Đang Xử Lý":
        currentStep = 1;
        break;
      case "Hủy Đơn":
      case "Hoàn Hàng":
        currentStep = -1;
        break;
      default:
        currentStep = 0;
    }

    return (
      <div className="my-6">
        {currentStep === -1 ? (
          <div className="bg-red-100 text-red-700 p-4 rounded-lg flex items-center justify-center">
            <FiAlertCircle className="w-5 h-5 mr-2" />
            <span>
              {status === "Hủy Đơn"
                ? "Đơn hàng đã bị hủy."
                : "Đơn hàng đang trong quá trình hoàn hàng hoặc đã hoàn."}
            </span>
          </div>
        ) : (
          <div className="relative">
            <div className="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
              <motion.div
                initial={{ width: 0 }}
                animate={{
                  width: `${Math.min(
                    100,
                    (currentStep / steps.length) * 100
                  )}%`,
                }}
                transition={{ duration: 0.8, ease: "easeOut" }}
                className="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-amber-500"
              ></motion.div>
            </div>
            <div className="flex justify-between">
              {steps.map((step, index) => (
                <div
                  key={step.id}
                  className={`flex flex-col items-center ${
                    currentStep >= step.id ? "text-amber-600" : "text-gray-400"
                  }`}
                >
                  <div
                    className={`
                    rounded-full h-8 w-8 flex items-center justify-center border-2 mb-1
                    ${
                      currentStep >= step.id
                        ? "border-amber-500 bg-amber-100"
                        : "border-gray-300"
                    }
                  `}
                  >
                    {step.icon}
                  </div>
                  <span className="text-xs font-medium">{step.name}</span>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    );
  };

  // Format giá tiền thành dạng xxx.xxx đ
  const formatCurrency = (amount) => {
    if (amount == null) return ""; // Handle null or undefined
    let amountStr = amount.toString();
    // Remove .00 if it exists
    if (amountStr.endsWith(".00")) {
      amountStr = amountStr.slice(0, -3);
    }
    return amountStr.replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " đ";
  };

  if (loading) {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen p-4">
        <motion.div
          initial={{ rotate: 0 }}
          animate={{ rotate: 360 }}
          transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
          className="rounded-full h-12 w-12 border-t-2 border-b-2 border-amber-500 mb-4"
        ></motion.div>
        <p className="text-gray-600 animate-pulse">
          Đang tải thông tin đơn hàng...
        </p>
      </div>
    );
  }

  if (error || !order) {
    return (
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="bg-gray-50 min-h-screen py-8 px-4"
      >
        <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8 text-center">
          <FiAlertCircle className="h-16 w-16 text-red-500 mx-auto mb-4" />
          <p className="text-red-500 text-lg mb-6">
            {error || "Không tìm thấy đơn hàng"}
          </p>
          <Link
            to="/account/list_order"
            className="inline-flex items-center px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition duration-200"
          >
            <FiArrowLeft className="mr-2" />
            Quay lại danh sách đơn hàng
          </Link>
        </div>
      </motion.div>
    );
  }

  // Lấy thông tin trạng thái thanh toán và đơn hàng
  const paymentStatusInfo = getPaymentStatusInfo(order.payment_status);

  // Xác định trạng thái hiển thị hiệu lực
  let effectiveOrderStatus = order.order_status;
  let isRefundActive = false;
  let isRefundRejected = false;

  if (Array.isArray(order.refund_request) && order.refund_request.length > 0) {
    const latestRefundStatus = order.refund_request[0]?.status?.toLowerCase();
    if (latestRefundStatus === "rejected" || latestRefundStatus === "từ chối") {
      isRefundRejected = true;
    } else if (latestRefundStatus) {
      // Các trạng thái khác (pending, accepted, completed)
      isRefundActive = true;
    }
  }

  if (isRefundActive && order.order_status !== "Hủy Đơn") {
    effectiveOrderStatus = "Hoàn Hàng";
  } // Nếu isRefundRejected = true, effectiveOrderStatus sẽ giữ nguyên giá trị gốc (ví dụ: "Đã Giao")

  const orderStatusInfo = getOrderStatusInfo(effectiveOrderStatus);

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.5 }}
      className="bg-gray-50 min-h-screen py-8 px-4"
    >
      <div className="max-w-5xl mx-auto">
        <div className="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <Link
            to="/account/list_order"
            className="inline-flex items-center text-amber-500 hover:text-amber-600 transition-colors mb-4 sm:mb-0"
          >
            <FiArrowLeft className="mr-2" />
            <span>Quay lại danh sách đơn hàng</span>
          </Link>

          <div className="flex items-center space-x-2">
            <FiHome className="text-gray-500" />
            <span className="text-gray-500">/</span>
            <Link to="/account" className="text-gray-500 hover:text-amber-500">
              Tài khoản
            </Link>
            <span className="text-gray-500">/</span>
            <Link
              to="/account/list_order"
              className="text-gray-500 hover:text-amber-500"
            >
              Đơn hàng
            </Link>
            <span className="text-gray-500">/</span>
            <span className="text-gray-700">Chi tiết</span>
          </div>
        </div>

        {/* Header Card */}
        <motion.div
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ delay: 0.1 }}
          className="bg-white rounded-lg shadow-sm p-6 mb-6"
        >
          <div className="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
            <div>
              <h1 className="text-2xl font-bold text-gray-800 mb-2">
                Đơn hàng #{order.order_code}
              </h1>
              <div className="flex items-center text-gray-600">
                <FiCalendar className="mr-2" />
                <span>
                  Ngày đặt:{" "}
                  {new Date(order.created_at).toLocaleDateString("vi-VN", {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                  })}
                </span>
              </div>
            </div>
            <div
              className={`mt-4 lg:mt-0 px-4 py-2 rounded-full ${orderStatusInfo.bgColor} ${orderStatusInfo.color} flex items-center`}
            >
              {orderStatusInfo.icon}
              <span className="ml-2 font-medium">{effectiveOrderStatus}</span>
            </div>
          </div>

          {/* Progress bar */}
          {renderOrderProgress(order.order_status)}

          {/* Action buttons */}
          <div className="flex flex-wrap gap-3 mt-6 justify-end">
            {["Chưa Xác Nhận", "Đã Xác Nhận"].includes(order.order_status) && (
              <button
                onClick={handleCancelOrder}
                className="px-4 py-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors flex items-center"
              >
                <FiXCircle className="mr-2" />
                Hủy đơn hàng
              </button>
            )}
            {order?.order_status === "Đã Giao" &&
              !isRefundActive &&
              !isRefundRejected && (
                <button
                  onClick={handleConfirmOrder}
                  className="px-4 py-2 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors flex items-center"
                >
                  <FiCheckCircle className="mr-2" />
                  Xác nhận đã nhận hàng
                </button>
              )}
            {order?.order_status === "Đã Giao" &&
              !isRefundActive &&
              !isRefundRejected && (
                <button
                  onClick={handleRequestRefund}
                  className="px-4 py-2 bg-orange-100 text-orange-600 hover:bg-orange-200 rounded-lg transition-colors flex items-center"
                >
                  <FiRefreshCw className="mr-2" />
                  Yêu cầu hoàn hàng
                </button>
              )}
          </div>
        </motion.div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left column */}
          <div className="lg:col-span-2 space-y-6">
            {/* Order Information */}
            <motion.div
              initial={{ y: 20, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              transition={{ delay: 0.2 }}
              className="bg-white rounded-lg shadow-sm overflow-hidden"
            >
              <div className="border-b border-gray-100 px-6 py-4">
                <h2 className="text-xl font-semibold text-gray-800 flex items-center">
                  <FiInfo className="mr-2 text-amber-500" />
                  Thông tin đơn hàng
                </h2>
              </div>

              <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <div className="mb-4">
                    <h3 className="text-sm font-medium text-gray-500 mb-2">
                      PHƯƠNG THỨC THANH TOÁN
                    </h3>
                    <p className="flex items-center">
                      <span
                        className={`inline-block w-3 h-3 rounded-full mr-2 ${paymentStatusInfo.bgColor}`}
                      ></span>
                      <span>
                        {order.payment_method.name} - {paymentStatusInfo.text}
                      </span>
                    </p>
                  </div>

                  <div>
                    <h3 className="text-sm font-medium text-gray-500 mb-2">
                      TRẠNG THÁI ĐƠN HÀNG
                    </h3>
                    <p>{order.order_status}</p>
                  </div>
                </div>

                <div>
                  <h3 className="text-sm font-medium text-gray-500 mb-2">
                    THÔNG TIN GIAO HÀNG
                  </h3>
                  <div className="space-y-3">
                    <div className="flex">
                      <FiUser className="text-gray-400 mt-1 mr-3 flex-shrink-0" />
                      <p className="font-medium">{order.user_name}</p>
                    </div>
                    <div className="flex">
                      <FiPhone className="text-gray-400 mt-1 mr-3 flex-shrink-0" />
                      <p>{order.user_phone}</p>
                    </div>
                    <div className="flex">
                      <FiMail className="text-gray-400 mt-1 mr-3 flex-shrink-0" />
                      <p>{order.user_email}</p>
                    </div>
                    <div className="flex">
                      <FiMapPin className="text-gray-400 mt-1 mr-3 flex-shrink-0" />
                      <p>{order.user_address}</p>
                    </div>
                  </div>
                </div>
              </div>
            </motion.div>

            {/* Order Items */}
            <motion.div
              initial={{ y: 20, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              transition={{ delay: 0.3 }}
              className="bg-white rounded-lg shadow-sm overflow-hidden"
            >
              <div className="border-b border-gray-100 px-6 py-4">
                <h2 className="text-xl font-semibold text-gray-800 flex items-center">
                  <FiShoppingBag className="mr-2 text-amber-500" />
                  Sản phẩm đã đặt
                </h2>
              </div>

              <div className="divide-y divide-gray-100">
                {order.order_items.map((item) => (
                  <div key={item.id} className="p-6 flex flex-col sm:flex-row">
                    <div className="sm:w-20 sm:h-20 h-32 w-full mb-4 sm:mb-0 sm:mr-4 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
                      <img
                        src={
                          item.image_url
                            ? `http://localhost:8000/storage/${item.image_url}`
                            : "https://via.placeholder.com/80"
                        }
                        alt={item.product_name}
                        className="w-full h-full object-cover object-center"
                        onError={(e) => {
                          e.target.onerror = null;
                          e.target.src = "https://via.placeholder.com/80";
                        }}
                      />
                    </div>
                    <div className="flex-grow">
                      <div className="flex flex-col sm:flex-row sm:justify-between">
                        <div>
                          <Link
                            to={`/product-detail/${item.product_id}`}
                            className="text-lg font-medium text-gray-800 hover:text-amber-500 transition-colors"
                          >
                            {item.product_name}
                          </Link>
                          {item.product_variant && (
                            <div className="mt-2 flex flex-wrap">
                              {renderVariantDetails(item.product_variant)}
                            </div>
                          )}
                          <div className="mt-1 text-gray-600">
                            Số lượng: {item.quantity} ×{" "}
                            {formatCurrency(item.price)}
                          </div>
                        </div>
                        <div className="mt-2 sm:mt-0 text-lg font-semibold text-amber-600">
                          {formatCurrency(item.quantity * item.price)}
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </motion.div>
          </div>

          {/* Right column - Order Summary */}
          <motion.div
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.4 }}
            className="bg-white rounded-lg shadow-sm overflow-hidden h-fit"
          >
            <div className="border-b border-gray-100 px-6 py-4">
              <h2 className="text-xl font-semibold text-gray-800">Tổng cộng</h2>
            </div>

            <div className="p-6">
              <div className="space-y-3 text-gray-600">
                <div className="flex justify-between">
                  <span>Tạm tính:</span>
                  <span>{formatCurrency(order.total_price)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Phí vận chuyển:</span>
                  <span>{formatCurrency(0)}</span>
                </div>
                {order.voucher && (
                  <div className="flex justify-between text-green-600">
                    <span>Giảm giá (voucher):</span>
                    <span>-{formatCurrency(order.discount_amount || 0)}</span>
                  </div>
                )}
              </div>

              <div className="mt-4 pt-4 border-t border-gray-100">
                <div className="flex justify-between items-center">
                  <span className="text-lg font-medium">Tổng cộng:</span>
                  <span className="text-xl font-bold text-amber-600">
                    {formatCurrency(
                      order.total_price - (order.discount_amount || 0)
                    )}
                  </span>
                </div>
              </div>

              {order.payment_status !== 1 &&
                (order.payment_method.name === "MoMo" ||
                  order.payment_method.name === "VNPAY") && (
                  <div className="mt-6">
                    <button
                      className="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors flex items-center justify-center"
                      onClick={handleRetryPayment}
                    >
                      <FiRefreshCw className="mr-2" />
                      Thanh toán lại
                    </button>
                  </div>
                )}
            </div>
          </motion.div>
        </div>
      </div>
    </motion.div>
  );
};

export default OrderDetail;
