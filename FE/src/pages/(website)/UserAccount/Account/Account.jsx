import React, { useEffect, useState, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { motion } from "framer-motion";
import axios from "axios";
import {
  ChevronDown,
  User,
  Shield,
  CreditCard,
  Bolt,
  Edit,
  Key,
  LogOut,
  BookOpen,
  CircleUserRound,
  Upload,
  AlertCircle,
  Eye,
  EyeOff,
  Wallet,
  Landmark,
} from "lucide-react";
import { toast, Toaster } from "react-hot-toast";

import { Button } from "../../../../components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "../../../../components/ui/card";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "../../../../components/ui/dropdown-menu";
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from "../../../../components/ui/tabs";
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from "../../../../components/ui/avatar";
import { Separator } from "../../../../components/ui/separator";
import { Input } from "../../../../components/ui/input";
import { Label } from "../../../../components/ui/label";
import { Switch } from "../../../../components/ui/switch";
import OrderHistory from "../OrderHistory/OrderHistory";
import axiosInstance from "../../../../utils/axiosConfig";
import WalletPage from "../Wallet/WalletPage";
import BankInfo from "../Bank/BankInfo";

const Account = () => {
  const [user, setUser] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const location = window.location;

  const updateUserAvatar = (newAvatarUrl) => {
    if (user && newAvatarUrl) {
      setUser((prevUser) => ({
        ...prevUser,
        avatar: newAvatarUrl,
      }));

      // Cập nhật dữ liệu người dùng trong localStorage
      try {
        const userData = JSON.parse(localStorage.getItem("userData"));
        if (userData) {
          userData.avatar = newAvatarUrl;
          localStorage.setItem("userData", JSON.stringify(userData));
          console.log(
            "Đã cập nhật avatar trong localStorage từ component chính:",
            newAvatarUrl
          );
        }
      } catch (error) {
        console.error("Lỗi khi cập nhật avatar trong localStorage:", error);
      }
    }
  };

  const updateUserInfo = (updatedInfo) => {
    if (user && updatedInfo) {
      setUser((prevUser) => ({
        ...prevUser,
        ...updatedInfo,
      }));
    }
  };

  useEffect(() => {
    const fetchUserData = async () => {
      try {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        const token = localStorage.getItem("authToken");
        console.log("Token từ localStorage:", token);

        if (!token || token === "undefined") {
          console.log("Token không hợp lệ, chuyển hướng đến trang đăng nhập");
          localStorage.removeItem("authToken");
          localStorage.removeItem("userData");
          navigate("/sign-in");
          return;
        }

        // Lấy thông tin người dùng từ localStorage
        const userDataStr = localStorage.getItem("userData");
        console.log("userData từ localStorage:", userDataStr);

        if (!userDataStr || userDataStr === "undefined") {
          console.log("Không tìm thấy dữ liệu người dùng trong localStorage");
          throw new Error("Dữ liệu người dùng không hợp lệ");
        }

        // Phân tích dữ liệu người dùng
        const userData = JSON.parse(userDataStr);

        // Kiểm tra xem có email không
        if (!userData.email) {
          console.error(
            "Không tìm thấy email trong dữ liệu người dùng:",
            userData
          );
          throw new Error("Không thể xác định ID người dùng");
        }

        // Gọi API để lấy thông tin chi tiết của người dùng
        const response = await axiosInstance.get(`/users/${userData.id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        });
        console.log("Dữ liệu người dùng từ API:", response.data);

        // Lưu trữ dữ liệu người dùng từ API
        const apiUserData = response.data.data || response.data;
        console.log("API User Data:", apiUserData);

        // Đảm bảo avatar được lấy từ API
        const apiAvatar = apiUserData.avatar;
        console.log("Avatar từ API:", apiAvatar);

        // Cập nhật state với dữ liệu người dùng
        setUser({
          ...apiUserData,
          avatar: apiAvatar || "https://via.placeholder.com/100",
          role: "Khách hàng",
          joinDate:
            apiUserData.joined_date || new Date().toLocaleDateString("vi-VN"),
        });

        // Cập nhật userData trong localStorage
        userData.avatar = apiAvatar;
        localStorage.setItem("userData", JSON.stringify(userData));

        setLoading(false);
      } catch (error) {
        console.error("Lỗi khi lấy thông tin người dùng:", error);

        if (error.response) {
          console.error(
            "Lỗi từ máy chủ:",
            error.response.status,
            error.response.data
          );

          if (error.response.status === 401) {
            console.log(
              "Token không hợp lệ hoặc hết hạn, chuyển hướng đến trang đăng nhập"
            );
            localStorage.removeItem("userData");
            localStorage.removeItem("authToken");

            navigate("/sign-in");
          }

          setError(
            error.response.data?.message ||
            `Lỗi từ máy chủ: ${error.response.status}`
          );
        } else if (error.request) {
          console.error("Không nhận được phản hồi từ máy chủ");
          setError(
            "Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng."
          );
        } else {
          console.error("Lỗi khi thiết lập yêu cầu:", error.message);
          setError(error.message || "Đã xảy ra lỗi khi gửi yêu cầu.");
        }

        setLoading(false);
      }
    };

    fetchUserData();
  }, [navigate]);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="p-6 text-center">
        <div className="text-red-500 font-medium">{error}</div>
        <Button className="mt-4" onClick={() => navigate("/sign-in")}>
          Quay lại trang đăng nhập
        </Button>
      </div>
    );
  }

  // Xác định tab mặc định từ URL query parameter
  const getDefaultTabFromUrl = () => {
    const searchParams = new URLSearchParams(location.search);
    const tab = searchParams.get("tab");
    if (tab === "level2password") {
      return "security";
    }
    return "profile";
  };

  return (
    <div className="container mx-auto pt-0 pb-10 px-4 md:px-6 w-full">
      <Toaster position="top-right" />
      <div className="flex flex-col gap-8">
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <motion.h1
              className="text-3xl font-bold tracking-tight"
              initial={{ opacity: 0, y: -20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
            >
              Cài đặt tài khoản
            </motion.h1>
            <motion.p
              className="text-muted-foreground mt-1"
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.1 }}
            >
              Quản lý tài khoản và thiết lập cá nhân của bạn
            </motion.p>
          </div>
        </div>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.2 }}
        >
          <Tabs defaultValue={getDefaultTabFromUrl()} className="w-full">
            <TabsList className="grid grid-cols-5 md:w-[595px] mb-8">
              <TabsTrigger value="profile" className="flex items-center gap-2">
                <User size={16} />
                <span className="hidden sm:inline">Hồ sơ</span>
              </TabsTrigger>
              <TabsTrigger value="security" className="flex items-center gap-2">
                <Shield size={16} />
                <span className="hidden sm:inline">Bảo mật</span>
              </TabsTrigger>
              <TabsTrigger value="billing" className="flex items-center gap-2">
                <CreditCard size={16} />
                <span className="hidden sm:inline">Đơn hàng</span>
              </TabsTrigger>
              <TabsTrigger value="wallet" className="flex items-center gap-2">
                <Wallet size={16} />
                <span className="hidden sm:inline">Ví tiền</span>
              </TabsTrigger>
              <TabsTrigger value="landmark" className="flex items-center gap-2">
                <Landmark size={16} />
                <span className="hidden sm:inline">Ngân hàng</span>
              </TabsTrigger>
            </TabsList>

            <TabsContent value="profile" className="space-y-6">
              <ProfileSection
                user={user}
                updateUserAvatar={updateUserAvatar}
                updateUserInfo={updateUserInfo}
              />
            </TabsContent>

            <TabsContent value="security" className="space-y-6">
              <SecuritySection />
            </TabsContent>

            <TabsContent value="billing" className="space-y-6">
              <BillingSection />
            </TabsContent>

            <TabsContent value="wallet" className="space-y-6">
              <WalletSection />
            </TabsContent>

            <TabsContent value="landmark" className="space-y-6">
              <BankSection />
            </TabsContent>
          </Tabs>
        </motion.div>
      </div>
    </div>
  );
};

const ProfileSection = ({ user, updateUserAvatar, updateUserInfo }) => {
  const [avatar, setAvatar] = useState(user.avatar);
  const [isUploading, setIsUploading] = useState(false);
  const [uploadError, setUploadError] = useState(null);
  const fileInputRef = useRef(null);
  const [userInfo, setUserInfo] = useState({
    name: user.name || "",
    email: user.email || "",
    phone: user.phone || "",
  });
  const [isSaving, setIsSaving] = useState(false);
  const [saveError, setSaveError] = useState(null);
  const [saveSuccess, setSaveSuccess] = useState(false);

  const handleAvatarClick = () => {
    fileInputRef.current.click();
  };

  const handleAvatarChange = async (file) => {
    try {
      // Lấy dữ liệu người dùng và token từ localStorage
      const userData = JSON.parse(localStorage.getItem("userData"));
      const token = localStorage.getItem("authToken");

      if (!userData || !userData.id) {
        console.error("Không tìm thấy thông tin người dùng");
        setUploadError("Không tìm thấy thông tin người dùng");
        return;
      }

      setIsUploading(true);
      setUploadError(null);

      const formData = new FormData();
      formData.append("avatar", file);

      console.log("Đang tải lên avatar cho người dùng ID:", userData.id);

      const response = await axiosInstance.post(
        `/users/upload-avatar/${userData.id}`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "multipart/form-data",
          },
        }
      );

      console.log("Phản hồi tải lên avatar:", response.data);

      // Lấy URL avatar từ phản hồi API
      let avatarUrl = null;
      if (response.data && response.data.avatar_url) {
        avatarUrl = response.data.avatar_url;
      } else if (
        response.data &&
        response.data.data &&
        response.data.data.avatar
      ) {
        avatarUrl = response.data.data.avatar;
      }

      console.log("URL avatar mới:", avatarUrl);

      if (avatarUrl) {
        // Cập nhật state với avatar mới
        setAvatar(avatarUrl);

        // Cập nhật avatar trong localStorage và component cha
        userData.avatar = avatarUrl;
        localStorage.setItem("userData", JSON.stringify(userData));

        // Cập nhật avatar trong component cha
        updateUserAvatar(avatarUrl);

        // Kích hoạt sự kiện để Header cập nhật avatar
        window.dispatchEvent(
          new CustomEvent("avatar-updated", {
            detail: { avatar: avatarUrl },
          })
        );

        console.log("Đã cập nhật avatar trong localStorage:", avatarUrl);
      } else {
        // Sử dụng URL tạm thởi nếu không có URL từ API
        const tempUrl = URL.createObjectURL(file);
        setAvatar(tempUrl);

        // Cập nhật avatar trong component cha
        updateUserAvatar(tempUrl);

        console.warn("Không nhận được URL avatar từ API, sử dụng URL tạm thời");
      }

      setIsUploading(false);
      toast.success("Cập nhật avatar thành công!");
    } catch (error) {
      console.error("Lỗi khi tải lên avatar:", error);
      setIsUploading(false);
      setUploadError(
        error.response?.data?.message ||
        "Không thể tải lên avatar. Vui lòng thử lại!"
      );
      toast.error("Không thể tải lên avatar. Vui lòng thử lại!");
    }
  };

  const handleInputChange = (e) => {
    const { id, value } = e.target;
    setUserInfo((prev) => ({
      ...prev,
      [id]: value,
    }));
  };

  const handleSaveChanges = async () => {
    setIsSaving(true);
    setSaveError(null);
    setSaveSuccess(false);

    try {
      const token = localStorage.getItem("authToken");
      if (!token) {
        throw new Error("Bạn cần đăng nhập để thực hiện chức năng này");
      }

      const userData = JSON.parse(localStorage.getItem("userData"));
      if (!userData || !userData.id) {
        throw new Error("Không tìm thấy thông tin người dùng");
      }

      // Gọi API cập nhật thông tin người dùng
      const response = await axios.put(
        `http://localhost:8000/api/users/update/${userData.id}`,
        {
          name: userInfo.name,
          phone: userInfo.phone,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );

      console.log("Phản hồi API cập nhật thông tin:", response.data);

      // Cập nhật userData trong localStorage
      const currentUserData = JSON.parse(localStorage.getItem("userData"));
      if (currentUserData) {
        currentUserData.name = userInfo.name;
        currentUserData.phone = userInfo.phone;
        localStorage.setItem("userData", JSON.stringify(currentUserData));
      }

      // Hiển thị thông báo thành công
      toast.success("Cập nhật thông tin thành công!");
      setSaveSuccess(true);

      // Cập nhật thông tin người dùng trong component cha
      updateUserInfo(userInfo);
    } catch (error) {
      console.error("Lỗi khi cập nhật thông tin:", error);
      setSaveError(
        error.response?.data?.message ||
        "Không thể cập nhật thông tin. Vui lòng thử lại sau."
      );
      toast.error("Không thể cập nhật thông tin. Vui lòng thử lại sau.");
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <>
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4 }}
      >
        <Card>
          <CardHeader>
            <CardTitle>Thông tin cá nhân</CardTitle>
            <CardDescription>
              Cập nhật thông tin cá nhân và hồ sơ của bạn
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="flex flex-col md:flex-row gap-6 items-start">
              <div className="relative group">
                <input
                  type="file"
                  ref={fileInputRef}
                  className="hidden"
                  accept="image/*"
                  onChange={(e) => {
                    if (e.target.files && e.target.files.length > 0) {
                      handleAvatarChange(e.target.files[0]);
                    }
                  }}
                />
                <Avatar className="h-24 w-24 border-4 border-background shadow-md">
                  {isUploading ? (
                    <div className="h-full w-full flex items-center justify-center bg-gray-200">
                      <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>
                  ) : (
                    <>
                      <AvatarImage
                        src={
                          avatar && !avatar.includes("placeholder.com")
                            ? avatar
                            : "/images/avatarEmpty/avatarUser.png"
                        }
                        alt={user.name}
                      />
                      <AvatarFallback className="text-2xl">
                        {user.name ? user.name.charAt(0) : "U"}
                      </AvatarFallback>
                    </>
                  )}
                </Avatar>
                <div
                  className="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer"
                  onClick={handleAvatarClick}
                >
                  <Button
                    size="sm"
                    variant="ghost"
                    className="text-white h-8 w-8 p-0"
                  >
                    <Upload size={16} />
                  </Button>
                </div>
              </div>
              {uploadError && (
                <div className="text-red-500 text-sm flex items-center gap-1 mt-1">
                  <AlertCircle size={14} />
                  <span>{uploadError}</span>
                </div>
              )}
              <div className="space-y-4 flex-1">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="name">Họ và tên</Label>
                    <Input
                      id="name"
                      value={userInfo.name}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="email">Địa chỉ email</Label>
                    <Input
                      id="email"
                      type="email"
                      value={userInfo.email}
                      disabled
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="phone">Số điện thoại</Label>
                    <Input
                      id="phone"
                      value={userInfo.phone}
                      onChange={handleInputChange}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="joined">Ngày tham gia</Label>
                    <Input id="joined" defaultValue={user.joinDate} disabled />
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
          <CardFooter className="flex justify-end gap-2">
            <Button
              variant="outline"
              onClick={() => {
                setUserInfo({
                  name: user.name || "",
                  email: user.email || "",
                  phone: user.phone || "",
                });
                setSaveError(null);
                setSaveSuccess(false);
              }}
            >
              Hủy bỏ
            </Button>
            <Button onClick={handleSaveChanges} disabled={isSaving}>
              {isSaving ? (
                <>
                  <div className="animate-spin mr-2 h-4 w-4 border-2 border-b-transparent border-white rounded-full"></div>
                  Đang lưu...
                </>
              ) : (
                "Lưu thay đổi"
              )}
            </Button>
          </CardFooter>
        </Card>
      </motion.div>

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4, delay: 0.1 }}
      ></motion.div>
    </>
  );
};

const SecuritySection = () => {
  const [passwordData, setPasswordData] = useState({
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
  });
  const [level2PasswordData, setLevel2PasswordData] = useState({
    currentPassword: "",
    level2Password: "",
    confirmLevel2Password: "",
    currentLevel2Password: "",
    newLevel2Password: "",
    confirmNewLevel2Password: "",
  });
  const [loading, setLoading] = useState(false);
  const [level2Loading, setLevel2Loading] = useState(false);
  const [error, setError] = useState(null);
  const [level2Error, setLevel2Error] = useState(null);
  const [success, setSuccess] = useState(false);
  const [level2Success, setLevel2Success] = useState(false);
  const [showCurrentPassword, setShowCurrentPassword] = useState(false);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [showLevel2Password, setShowLevel2Password] = useState(false);
  const [showConfirmLevel2Password, setShowConfirmLevel2Password] =
    useState(false);
  const [showCurrentLevel2Password, setShowCurrentLevel2Password] =
    useState(false);
  const [showNewLevel2Password, setShowNewLevel2Password] = useState(false);
  const [showConfirmNewLevel2Password, setShowConfirmNewLevel2Password] =
    useState(false);
  const [isCurrentPasswordFocused, setIsCurrentPasswordFocused] =
    useState(false);
  const [isNewPasswordFocused, setIsNewPasswordFocused] = useState(false);
  const [isConfirmPasswordFocused, setIsConfirmPasswordFocused] =
    useState(false);
  const [isLevel2PasswordFocused, setIsLevel2PasswordFocused] = useState(false);
  const [isConfirmLevel2PasswordFocused, setIsConfirmLevel2PasswordFocused] =
    useState(false);
  const [isCurrentLevel2PasswordFocused, setIsCurrentLevel2PasswordFocused] =
    useState(false);
  const [isNewLevel2PasswordFocused, setIsNewLevel2PasswordFocused] =
    useState(false);
  const [
    isConfirmNewLevel2PasswordFocused,
    setIsConfirmNewLevel2PasswordFocused,
  ] = useState(false);
  const [hasLevel2Password, setHasLevel2Password] = useState(false);
  const [isOAuthUser, setIsOAuthUser] = useState(false);
  const [activeTab, setActiveTab] = useState("level2password");
  const [isLoading, setIsLoading] = useState(true);
  const location = window.location;
  const navigate = useNavigate();

  useEffect(() => {
    // Kiểm tra trạng thái mật khẩu cấp 2
    const checkLevel2PasswordStatus = async () => {
      try {
        setIsLoading(true); // Bắt đầu loading
        const token = localStorage.getItem("authToken");
        if (!token) {
          setIsLoading(false);
          return;
        }

        const response = await axiosInstance.get(
          "/users/level2-password/status"
        );
        setHasLevel2Password(response.data.data.has_level2_password);
        setIsOAuthUser(response.data.data.is_oauth_user);
        
        // Nếu không phải tài khoản OAuth và chưa có query parameter tab, đặt tab mặc định là "password"
        if (!response.data.data.is_oauth_user && !location.search.includes('tab=')) {
          setActiveTab("password");
        }
        
        setIsLoading(false); // Kết thúc loading
      } catch (error) {
        console.error("Lỗi khi kiểm tra trạng thái mật khẩu cấp 2:", error);
        setIsLoading(false); // Kết thúc loading nếu có lỗi
      }
    };

    checkLevel2PasswordStatus();

    // Kiểm tra URL query parameter để mở tab mật khẩu cấp 2 nếu cần
    const searchParams = new URLSearchParams(location.search);
    const tab = searchParams.get("tab");
    if (tab === "level2password") {
      setActiveTab("level2password");
    }
  }, [location.search]);

  const handleInputChange = (e) => {
    const { id, value } = e.target;
    setPasswordData((prev) => ({
      ...prev,
      [id === "current-password"
        ? "currentPassword"
        : id === "new-password"
          ? "newPassword"
          : id === "confirm-password"
            ? "confirmPassword"
            : id]: value,
    }));
  };

  const handleLevel2InputChange = (e) => {
    const { id, value } = e.target;
    setLevel2PasswordData((prev) => ({
      ...prev,
      [id === "current-password-level2"
        ? "currentPassword"
        : id === "level2-password"
          ? "level2Password"
          : id === "confirm-level2-password"
            ? "confirmLevel2Password"
            : id === "current-level2-password"
              ? "currentLevel2Password"
              : id === "new-level2-password"
                ? "newLevel2Password"
                : id === "confirm-new-level2-password"
                  ? "confirmNewLevel2Password"
                  : id]: value,
    }));
  };

  const resetForm = () => {
    setPasswordData({
      currentPassword: "",
      newPassword: "",
      confirmPassword: "",
    });
    setError(null);
    setSuccess(false);
  };

  const resetLevel2Form = () => {
    setLevel2PasswordData({
      currentPassword: "",
      level2Password: "",
      confirmLevel2Password: "",
      currentLevel2Password: "",
      newLevel2Password: "",
      confirmNewLevel2Password: "",
    });
    setLevel2Error(null);
    setLevel2Success(false);
  };

  const handleUpdatePassword = async () => {
    // Xác thực dữ liệu
    if (!passwordData.currentPassword) {
      setError("Vui lòng nhập mật khẩu hiện tại");
      return;
    }

    if (!passwordData.newPassword) {
      setError("Vui lòng nhập mật khẩu mới");
      return;
    }

    if (passwordData.newPassword.length < 5) {
      setError("Mật khẩu mới phải có ít nhất 5 ký tự");
      return;
    }

    if (passwordData.newPassword !== passwordData.confirmPassword) {
      setError("Mật khẩu mới và xác nhận mật khẩu không khớp");
      return;
    }

    setLoading(true);
    setError(null);
    setSuccess(false);

    try {
      const token = localStorage.getItem("authToken");
      if (!token) {
        throw new Error("Bạn cần đăng nhập để thực hiện chức năng này");
      }

      const userData = JSON.parse(localStorage.getItem("userData"));
      if (!userData || !userData.id) {
        throw new Error("Không tìm thấy thông tin người dùng");
      }

      // Gọi API cập nhật mật khẩu
      const response = await axios.put(
        `http://localhost:8000/api/users/update/${userData.id}`,
        {
          current_password: passwordData.currentPassword,
          new_password: passwordData.newPassword,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );

      console.log("Phản hồi API cập nhật mật khẩu:", response.data);

      // Cập nhật trạng thái thành công và hiển thị thông báo
      setSuccess(true);
      toast.success("Cập nhật mật khẩu thành công!");
      resetForm();
    } catch (error) {
      console.error("Lỗi khi cập nhật mật khẩu:", error);

      if (error.response && error.response.status === 400) {
        setError("Mật khẩu hiện tại không đúng");
      } else {
        setError(
          error.response?.data?.message ||
          "Không thể cập nhật mật khẩu. Vui lòng thử lại sau."
        );
      }

      toast.error("Không thể cập nhật mật khẩu");
    } finally {
      setLoading(false);
    }
  };

  const handleSetLevel2Password = async () => {
    // Xác thực dữ liệu
    if (!isOAuthUser && !level2PasswordData.currentPassword) {
      setLevel2Error("Vui lòng nhập mật khẩu cấp 1");
      return;
    }

    if (!level2PasswordData.level2Password) {
      setLevel2Error("Vui lòng nhập mật khẩu cấp 2");
      return;
    }

    if (level2PasswordData.level2Password.length < 5) {
      setLevel2Error("Mật khẩu cấp 2 phải có ít nhất 5 ký tự");
      return;
    }

    if (
      level2PasswordData.level2Password !==
      level2PasswordData.confirmLevel2Password
    ) {
      setLevel2Error("Mật khẩu cấp 2 và xác nhận mật khẩu cấp 2 không khớp");
      return;
    }

    // Kiểm tra mật khẩu cấp 2 không được giống mật khẩu cấp 1
    if (!isOAuthUser && level2PasswordData.currentPassword === level2PasswordData.level2Password) {
      setLevel2Error("Mật khẩu cấp 2 không được giống mật khẩu cấp 1");
      return;
    }

    setLevel2Loading(true);
    setLevel2Error(null);
    setLevel2Success(false);

    try {
      const token = localStorage.getItem("authToken");
      if (!token) {
        throw new Error("Bạn cần đăng nhập để thực hiện chức năng này");
      }

      // Chuẩn bị dữ liệu dựa trên loại tài khoản
      const requestData = isOAuthUser 
        ? {
            level2_password: level2PasswordData.level2Password,
            confirm_level2_password: level2PasswordData.confirmLevel2Password,
          }
        : {
            current_password: level2PasswordData.currentPassword,
            level2_password: level2PasswordData.level2Password,
            confirm_level2_password: level2PasswordData.confirmLevel2Password,
          };

      // Gọi API thiết lập mật khẩu cấp 2
      const response = await axiosInstance.post(
        "/users/level2-password/set", 
        requestData
      );

      console.log("Phản hồi API thiết lập mật khẩu cấp 2:", response.data);

      // Cập nhật trạng thái thành công và hiển thị thông báo
      setLevel2Success(true);
      setHasLevel2Password(true);

      // Kiểm tra nếu có yêu cầu chuyển hướng quay lại trang thanh toán
      const searchParams = new URLSearchParams(location.search);
      const redirect = searchParams.get("redirect");

      // Chỉ hiển thị toast thành công nếu KHÔNG có chuyển hướng đến trang rút tiền
      if (!redirect || redirect !== "wallet_withdraw") {
        toast.success("Thiết lập mật khẩu cấp 2 thành công!");
      }

      resetLevel2Form();

      // Phát sự kiện để cập nhật trạng thái mật khẩu cấp 2 trong Header
      window.dispatchEvent(
        new CustomEvent("level2password-updated", {
          detail: { hasLevel2Password: true },
        })
      );

      // Kiểm tra nếu có yêu cầu chuyển hướng quay lại trang thanh toán
      if (redirect) {
        // Đợi 1.5 giây để người dùng thấy thông báo thành công
        setTimeout(() => {
          // Lấy dữ liệu trạng thái thanh toán đã lưu
          const pendingPaymentState = JSON.parse(localStorage.getItem("pendingPaymentState") || "{}");

          // Chuyển hướng dựa vào loại thanh toán
          if (redirect === "payment" && pendingPaymentState.type === "normal") {
            // Cập nhật: Chuyển về trang thanh toán thường với toàn bộ dữ liệu sản phẩm
            if (pendingPaymentState.selectedProductsData && pendingPaymentState.selectedProductsData.length > 0) {
              // Nếu có thông tin sản phẩm đầy đủ, sử dụng nó
              navigate("/payment", {
                state: {
                  selectedProducts: pendingPaymentState.selectedProductsData
                }
              });
            } else if (pendingPaymentState.selectedProducts && pendingPaymentState.selectedProducts.length > 0) {
              // Nếu chỉ có ID sản phẩm, lưu vào localStorage để trang Payment tự tải lại
              localStorage.setItem("tempSelectedProducts", JSON.stringify(pendingPaymentState.selectedProducts));
              navigate("/payment");
            } else {
              // Không có thông tin sản phẩm
              navigate("/payment");
            }
            toast.info("Đang chuyển về trang thanh toán...");
          } else if (redirect === "payment_buy_now" && pendingPaymentState.type === "buy_now") {
            // Lấy dữ liệu sản phẩm
            const product = pendingPaymentState.product_id;
            const variant = pendingPaymentState.product_variant_id;
            const quantity = pendingPaymentState.quantity;

            // Chuyển về trang thanh toán mua ngay với thông tin sản phẩm
            navigate("/payment_buy_now", {
              state: {
                selectedProducts: [{
                  product: {
                    id: product,
                    name: pendingPaymentState.product_name,
                    price: pendingPaymentState.product_price,
                    discount_price: pendingPaymentState.product_discount_price,
                    image_thumbnail: pendingPaymentState.product_image_thumbnail
                  },
                  product_variant: variant ? {
                    id: variant,
                    price: pendingPaymentState.variant_price,
                    discount_price: pendingPaymentState.variant_discount_price
                  } : null,
                  quantity: quantity,
                  total_price: pendingPaymentState.total_price || (pendingPaymentState.variant_price || pendingPaymentState.product_price) * quantity
                }]
              }
            });
            toast.info("Đang chuyển về trang thanh toán mua ngay...");
          } else if (redirect === "wallet_withdraw") {
            // Xử lý chuyển hướng về trang rút tiền sau khi thiết lập mật khẩu cấp 2
            const pendingWithdrawState = JSON.parse(localStorage.getItem("pendingWithdrawState") || "{}");

            // Chuyển về trang rút tiền với tham số thông báo thành công
            navigate("/account/wallet/withdraw?from_level2_setup=true");
          }
        }, 1500);
      }
    } catch (error) {
      console.error("Lỗi khi thiết lập mật khẩu cấp 2:", error);

      if (error.response && error.response.status === 400) {
        setLevel2Error(
          error.response.data.message || "Mật khẩu cấp 1 không đúng"
        );
      } else {
        setLevel2Error(
          error.response?.data?.message ||
          "Không thể thiết lập mật khẩu cấp 2. Vui lòng thử lại sau."
        );
      }

      toast.error("Không thể thiết lập mật khẩu cấp 2");
    } finally {
      setLevel2Loading(false);
    }
  };

  const handleUpdateLevel2Password = async () => {
    // Xác thực dữ liệu
    if (!level2PasswordData.currentLevel2Password) {
      setLevel2Error("Vui lòng nhập mật khẩu cấp 2 hiện tại");
      return;
    }

    if (!level2PasswordData.newLevel2Password) {
      setLevel2Error("Vui lòng nhập mật khẩu cấp 2 mới");
      return;
    }

    if (level2PasswordData.newLevel2Password.length < 5) {
      setLevel2Error("Mật khẩu cấp 2 mới phải có ít nhất 5 ký tự");
      return;
    }

    if (
      level2PasswordData.newLevel2Password !==
      level2PasswordData.confirmNewLevel2Password
    ) {
      setLevel2Error(
        "Mật khẩu cấp 2 mới và xác nhận mật khẩu cấp 2 không khớp"
      );
      return;
    }

    // Kiểm tra mật khẩu cấp 2 mới không được giống mật khẩu cấp 1
    // Chúng ta để việc kiểm tra này cho backend xử lý
    // Nếu giống mật khẩu cấp 1, backend sẽ trả về lỗi 400 với message phù hợp

    setLevel2Loading(true);
    setLevel2Error(null);
    setLevel2Success(false);

    try {
      const token = localStorage.getItem("authToken");
      if (!token) {
        throw new Error("Bạn cần đăng nhập để thực hiện chức năng này");
      }

      // Gọi API cập nhật mật khẩu cấp 2
      const response = await axiosInstance.put(
        "/users/level2-password/update",
        {
          current_level2_password: level2PasswordData.currentLevel2Password,
          new_level2_password: level2PasswordData.newLevel2Password,
          confirm_level2_password: level2PasswordData.confirmNewLevel2Password,
        }
      );

      console.log("Phản hồi API cập nhật mật khẩu cấp 2:", response.data);

      // Cập nhật trạng thái thành công và hiển thị thông báo
      setLevel2Success(true);
      toast.success("Cập nhật mật khẩu cấp 2 thành công!");
      resetLevel2Form();

      // Phát sự kiện để cập nhật trạng thái mật khẩu cấp 2 trong Header
      window.dispatchEvent(
        new CustomEvent("level2password-updated", {
          detail: { hasLevel2Password: true },
        })
      );
    } catch (error) {
      console.error("Lỗi khi cập nhật mật khẩu cấp 2:", error);

      if (error.response && error.response.status === 400) {
        setLevel2Error(
          error.response.data.message || "Mật khẩu cấp 2 hiện tại không đúng"
        );
      } else {
        setLevel2Error(
          error.response?.data?.message ||
          "Không thể cập nhật mật khẩu cấp 2. Vui lòng thử lại sau."
        );
      }

      toast.error("Không thể cập nhật mật khẩu cấp 2");
    } finally {
      setLevel2Loading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold tracking-tight">Bảo mật</h1>
        <p className="text-muted-foreground">
          {isOAuthUser 
            ? "Quản lý các thiết lập bảo mật cho tài khoản đăng nhập bằng Google/Facebook của bạn" 
            : "Quản lý mật khẩu và các thiết lập bảo mật khác của tài khoản"}
        </p>
      </div>

      {isOAuthUser && !hasLevel2Password && (
        <motion.div 
          initial={{ opacity: 0, y: 5 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3 }}
          className="bg-blue-50 border border-blue-100 rounded-md p-4"
        >
          <div className="flex items-start">
            <div className="flex-shrink-0 pt-0.5">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 18.3333C14.6024 18.3333 18.3334 14.6024 18.3334 10C18.3334 5.39763 14.6024 1.66667 10 1.66667C5.39765 1.66667 1.66669 5.39763 1.66669 10C1.66669 14.6024 5.39765 18.3333 10 18.3333Z" stroke="#3B82F6" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M10 6.66667V10" stroke="#3B82F6" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M10 13.3333H10.0083" stroke="#3B82F6" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </div>
            <div className="ml-3 flex-1">
              <h3 className="text-sm font-medium text-blue-800">Tài khoản OAuth</h3>
              <div className="mt-2 text-sm text-blue-700">
                <p>Tài khoản của bạn được đăng nhập thông qua Google/Facebook nên không có mật khẩu cấp 1. Bạn cần thiết lập mật khẩu cấp 2 để bảo vệ giao dịch.</p>
              </div>
            </div>
          </div>
        </motion.div>
      )}

      {isLoading ? (
        <div className="flex justify-center py-10">
          <div className="animate-spin h-8 w-8 border-2 border-blue-500 border-t-transparent rounded-full"></div>
        </div>
      ) : (
      <motion.div
        initial={{ opacity: 0, y: 10 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4 }}
      >
        <Tabs defaultValue={activeTab} value={activeTab} onValueChange={setActiveTab} className="w-full">
          <TabsList className={`grid w-full ${isOAuthUser ? 'grid-cols-1' : 'grid-cols-2'}`}>
            {!isOAuthUser && (
              <TabsTrigger value="password">Mật khẩu</TabsTrigger>
            )}
            <TabsTrigger value="level2password">
              {hasLevel2Password ? "Cập nhật mật khẩu cấp 2" : "Thiết lập mật khẩu cấp 2"}
            </TabsTrigger>
          </TabsList>

          {!isOAuthUser && (
            <TabsContent value="password">
              <Card>
                <CardHeader>
                  <CardTitle>Mật khẩu</CardTitle>
                  <CardDescription>
                    Đổi mật khẩu đăng nhập tài khoản của bạn
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  {error && (
                    <div className="text-red-500 text-sm flex items-center gap-1 mb-4">
                      <AlertCircle size={14} />
                      <span>{error}</span>
                    </div>
                  )}
                  <div className="space-y-2 relative">
                    <Label htmlFor="current-password">Mật khẩu hiện tại</Label>
                    <Input
                      id="current-password"
                      type={showCurrentPassword ? "text" : "password"}
                      value={passwordData.currentPassword}
                      onChange={handleInputChange}
                      className="pr-10"
                      onFocus={() => setIsCurrentPasswordFocused(true)}
                      onBlur={() => setIsCurrentPasswordFocused(false)}
                    />
                    {passwordData.currentPassword && isCurrentPasswordFocused && (
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                        onMouseDown={(e) => e.preventDefault()}
                        onClick={() => setShowCurrentPassword((prev) => !prev)}
                      >
                        {showCurrentPassword ? (
                          <EyeOff size={16} />
                        ) : (
                          <Eye size={16} />
                        )}
                      </Button>
                    )}
                  </div>
                  <div className="space-y-2 relative">
                    <Label htmlFor="new-password">Mật khẩu mới</Label>
                    <Input
                      id="new-password"
                      type={showNewPassword ? "text" : "password"}
                      value={passwordData.newPassword}
                      onChange={handleInputChange}
                      className="pr-10"
                      onFocus={() => setIsNewPasswordFocused(true)}
                      onBlur={() => setIsNewPasswordFocused(false)}
                    />
                    {passwordData.newPassword && isNewPasswordFocused && (
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                        onMouseDown={(e) => e.preventDefault()}
                        onClick={() => setShowNewPassword((prev) => !prev)}
                      >
                        {showNewPassword ? (
                          <EyeOff size={16} />
                        ) : (
                          <Eye size={16} />
                        )}
                      </Button>
                    )}
                  </div>
                  <div className="space-y-2 relative">
                    <Label htmlFor="confirm-password">
                      Xác nhận mật khẩu mới
                    </Label>
                    <Input
                      id="confirm-password"
                      type={showConfirmPassword ? "text" : "password"}
                      value={passwordData.confirmPassword}
                      onChange={handleInputChange}
                      className="pr-10"
                      onFocus={() => setIsConfirmPasswordFocused(true)}
                      onBlur={() => setIsConfirmPasswordFocused(false)}
                    />
                    {passwordData.confirmPassword && isConfirmPasswordFocused && (
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                        onMouseDown={(e) => e.preventDefault()}
                        onClick={() => setShowConfirmPassword((prev) => !prev)}
                      >
                        {showConfirmPassword ? (
                          <EyeOff size={16} />
                        ) : (
                          <Eye size={16} />
                        )}
                      </Button>
                    )}
                  </div>
                </CardContent>
                <CardFooter className="flex justify-end gap-2">
                  <Button variant="outline" onClick={resetForm}>
                    Hủy bỏ
                  </Button>
                  <Button onClick={handleUpdatePassword} disabled={loading}>
                    {loading ? (
                      <>
                        <div className="animate-spin mr-2 h-4 w-4 border-2 border-b-transparent border-white rounded-full"></div>
                        Đang cập nhật...
                      </>
                    ) : (
                      "Cập nhật mật khẩu"
                    )}
                  </Button>
                </CardFooter>
              </Card>
            </TabsContent>
          )}

          <TabsContent value="level2password">
            <Card>
              <CardHeader>
                <CardTitle>Mật khẩu cấp 2</CardTitle>
                <CardDescription>
                  {hasLevel2Password
                    ? "Cập nhật mật khẩu cấp 2 để bảo mật giao dịch của bạn"
                    : isOAuthUser 
                      ? "Thiết lập mật khẩu cấp 2 để bảo mật giao dịch (cần thiết cho rút tiền và một số giao dịch quan trọng)"
                      : "Thiết lập mật khẩu cấp 2 để bảo mật giao dịch của bạn"}
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {level2Error && (
                  <div className="text-red-500 text-sm flex items-center gap-1 mb-4">
                    <AlertCircle size={14} />
                    <span>{level2Error}</span>
                  </div>
                )}

                {!hasLevel2Password ? (
                  // Form thiết lập mật khẩu cấp 2
                  <>
                    {!isOAuthUser && (
                      <div className="space-y-2">
                        <Label htmlFor="current-password-level2">
                          Mật khẩu cấp 1
                        </Label>
                        <Input
                          id="current-password-level2"
                          type={showCurrentPassword ? "text" : "password"}
                          value={level2PasswordData.currentPassword}
                          onChange={handleLevel2InputChange}
                          className="pr-10"
                          onFocus={() => setIsCurrentPasswordFocused(true)}
                          onBlur={() => setIsCurrentPasswordFocused(false)}
                        />
                        {level2PasswordData.currentPassword &&
                          isCurrentPasswordFocused && (
                            <Button
                              type="button"
                              variant="ghost"
                              size="sm"
                              className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                              onMouseDown={(e) => e.preventDefault()}
                              onClick={() =>
                                setShowCurrentPassword((prev) => !prev)
                              }
                            >
                              {showCurrentPassword ? (
                                <EyeOff size={16} />
                              ) : (
                                <Eye size={16} />
                              )}
                            </Button>
                          )}
                      </div>
                    )}
                    <div className="space-y-2 relative">
                      <Label htmlFor="level2-password">Mật khẩu cấp 2</Label>
                      <Input
                        id="level2-password"
                        type={showLevel2Password ? "text" : "password"}
                        value={level2PasswordData.level2Password}
                        onChange={handleLevel2InputChange}
                        className="pr-10"
                        onFocus={() => setIsLevel2PasswordFocused(true)}
                        onBlur={() => setIsLevel2PasswordFocused(false)}
                      />
                      {level2PasswordData.level2Password &&
                        isLevel2PasswordFocused && (
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                            onMouseDown={(e) => e.preventDefault()}
                            onClick={() =>
                              setShowLevel2Password((prev) => !prev)
                            }
                          >
                            {showLevel2Password ? (
                              <EyeOff size={16} />
                            ) : (
                              <Eye size={16} />
                            )}
                          </Button>
                        )}
                    </div>
                    <div className="space-y-2 relative">
                      <Label htmlFor="confirm-level2-password">
                        Xác nhận mật khẩu cấp 2
                      </Label>
                      <Input
                        id="confirm-level2-password"
                        type={showConfirmLevel2Password ? "text" : "password"}
                        value={level2PasswordData.confirmLevel2Password}
                        onChange={handleLevel2InputChange}
                        className="pr-10"
                        onFocus={() => setIsConfirmLevel2PasswordFocused(true)}
                        onBlur={() => setIsConfirmLevel2PasswordFocused(false)}
                      />
                      {level2PasswordData.confirmLevel2Password &&
                        isConfirmLevel2PasswordFocused && (
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                            onMouseDown={(e) => e.preventDefault()}
                            onClick={() =>
                              setShowConfirmLevel2Password((prev) => !prev)
                            }
                          >
                            {showConfirmLevel2Password ? (
                              <EyeOff size={16} />
                            ) : (
                              <Eye size={16} />
                            )}
                          </Button>
                        )}
                    </div>
                  </>
                ) : (
                  // Form cập nhật mật khẩu cấp 2
                  <>
                    <div className="space-y-2 relative">
                      <Label htmlFor="current-level2-password">
                        Mật khẩu cấp 2 hiện tại
                      </Label>
                      <Input
                        id="current-level2-password"
                        type={showCurrentLevel2Password ? "text" : "password"}
                        value={level2PasswordData.currentLevel2Password}
                        onChange={handleLevel2InputChange}
                        className="pr-10"
                        onFocus={() => setIsCurrentLevel2PasswordFocused(true)}
                        onBlur={() => setIsCurrentLevel2PasswordFocused(false)}
                      />
                      {level2PasswordData.currentLevel2Password &&
                        isCurrentLevel2PasswordFocused && (
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                            onMouseDown={(e) => e.preventDefault()}
                            onClick={() =>
                              setShowCurrentLevel2Password((prev) => !prev)
                            }
                          >
                            {showCurrentLevel2Password ? (
                              <EyeOff size={16} />
                            ) : (
                              <Eye size={16} />
                            )}
                          </Button>
                        )}
                    </div>
                    <div className="space-y-2 relative">
                      <Label htmlFor="new-level2-password">
                        Mật khẩu cấp 2 mới
                      </Label>
                      <Input
                        id="new-level2-password"
                        type={showNewLevel2Password ? "text" : "password"}
                        value={level2PasswordData.newLevel2Password}
                        onChange={handleLevel2InputChange}
                        className="pr-10"
                        onFocus={() => setIsNewLevel2PasswordFocused(true)}
                        onBlur={() => setIsNewLevel2PasswordFocused(false)}
                      />
                      {level2PasswordData.newLevel2Password &&
                        isNewLevel2PasswordFocused && (
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                            onMouseDown={(e) => e.preventDefault()}
                            onClick={() =>
                              setShowNewLevel2Password((prev) => !prev)
                            }
                          >
                            {showNewLevel2Password ? (
                              <EyeOff size={16} />
                            ) : (
                              <Eye size={16} />
                            )}
                          </Button>
                        )}
                    </div>
                    <div className="space-y-2 relative">
                      <Label htmlFor="confirm-new-level2-password">
                        Xác nhận mật khẩu cấp 2 mới
                      </Label>
                      <Input
                        id="confirm-new-level2-password"
                        type={
                          showConfirmNewLevel2Password ? "text" : "password"
                        }
                        value={level2PasswordData.confirmNewLevel2Password}
                        onChange={handleLevel2InputChange}
                        className="pr-10"
                        onFocus={() =>
                          setIsConfirmNewLevel2PasswordFocused(true)
                        }
                        onBlur={() =>
                          setIsConfirmNewLevel2PasswordFocused(false)
                        }
                      />
                      {level2PasswordData.confirmNewLevel2Password &&
                        isConfirmNewLevel2PasswordFocused && (
                          <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="absolute right-1 top-[2.1rem] h-7 w-7 px-0"
                            onMouseDown={(e) => e.preventDefault()}
                            onClick={() =>
                              setShowConfirmNewLevel2Password((prev) => !prev)
                            }
                          >
                            {showConfirmNewLevel2Password ? (
                              <EyeOff size={16} />
                            ) : (
                              <Eye size={16} />
                            )}
                          </Button>
                        )}
                    </div>
                  </>
                )}
              </CardContent>
              <CardFooter className="flex justify-end gap-2">
                <Button variant="outline" onClick={resetLevel2Form}>
                  Hủy bỏ
                </Button>
                <Button
                  onClick={
                    hasLevel2Password
                      ? handleUpdateLevel2Password
                      : handleSetLevel2Password
                  }
                  disabled={level2Loading}
                >
                  {level2Loading ? (
                    <>
                      <div className="animate-spin mr-2 h-4 w-4 border-2 border-b-transparent border-white rounded-full"></div>
                      Đang xử lý...
                    </>
                  ) : hasLevel2Password ? (
                    "Cập nhật mật khẩu cấp 2"
                  ) : (
                    "Thiết lập mật khẩu cấp 2"
                  )}
                </Button>
              </CardFooter>
            </Card>
          </TabsContent>
        </Tabs>
      </motion.div>
      )}
    </div>
  );
};

const BillingSection = () => {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4 }}
    >
      <OrderHistory />
    </motion.div>
  );
};

const WalletSection = () => {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4 }}
    >
      <WalletPage />
    </motion.div>
  );
};

const BankSection = () => {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4 }}
    >
      <BankInfo />
    </motion.div>
  );
};

export default Account;
