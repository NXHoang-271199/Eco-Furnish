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
import { Avatar, AvatarFallback, AvatarImage } from "../../../../components/ui/avatar";
import { Separator } from "../../../../components/ui/separator";
import { Input } from "../../../../components/ui/input";
import { Label } from "../../../../components/ui/label";
import { Switch } from "../../../../components/ui/switch";
import OrderHistory from "../OrderHistory/OrderHistory";

const Account = () => {
  const [user, setUser] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  const updateUserAvatar = (newAvatarUrl) => {
    if (user && newAvatarUrl) {
      setUser(prevUser => ({
        ...prevUser,
        avatar: newAvatarUrl
      }));

      // Cập nhật dữ liệu người dùng trong localStorage
      try {
        const userData = JSON.parse(localStorage.getItem("userData"));
        if (userData) {
          userData.avatar = newAvatarUrl;
          localStorage.setItem("userData", JSON.stringify(userData));
          console.log("Đã cập nhật avatar trong localStorage từ component chính:", newAvatarUrl);
        }
      } catch (error) {
        console.error("Lỗi khi cập nhật avatar trong localStorage:", error);
      }
    }
  };

  const updateUserInfo = (updatedInfo) => {
    if (user && updatedInfo) {
      setUser(prevUser => ({
        ...prevUser,
        ...updatedInfo
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
        const response = await axios.get(
          `http://localhost:8000/api/users/${userData.id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );
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
          joinDate: apiUserData.joined_date || new Date().toLocaleDateString("vi-VN"),
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
          <Tabs defaultValue="profile" className="w-full">
            <TabsList className="grid grid-cols-3 md:w-[300px] mb-8">
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
            </TabsList>

            <TabsContent value="profile" className="space-y-6">
              <ProfileSection user={user} updateUserAvatar={updateUserAvatar} updateUserInfo={updateUserInfo} />
            </TabsContent>

            <TabsContent value="security" className="space-y-6">
              <SecuritySection />
            </TabsContent>

            <TabsContent value="billing" className="space-y-6">
              <BillingSection />
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

      const response = await axios.post(
        `http://localhost:8000/api/users/upload-avatar/${userData.id}`,
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
      } else if (response.data && response.data.data && response.data.data.avatar) {
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
        window.dispatchEvent(new CustomEvent("avatar-updated", {
          detail: { avatar: avatarUrl }
        }));

        console.log("Đã cập nhật avatar trong localStorage:", avatarUrl);
      } else {
        // Sử dụng URL tạm thời nếu không có URL từ API
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
      setUploadError(error.response?.data?.message || "Không thể tải lên avatar. Vui lòng thử lại!");
      toast.error("Không thể tải lên avatar. Vui lòng thử lại!");
    }
  };

  const handleInputChange = (e) => {
    const { id, value } = e.target;
    setUserInfo(prev => ({
      ...prev,
      [id]: value
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
                      <AvatarImage src={avatar} alt={user.name} />
                      <AvatarFallback className="text-2xl">{user.name ? user.name.charAt(0) : "U"}</AvatarFallback>
                    </>
                  )}
                </Avatar>
                <div
                  className="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer"
                  onClick={handleAvatarClick}
                >
                  <Button size="sm" variant="ghost" className="text-white h-8 w-8 p-0">
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
            <Button
              onClick={handleSaveChanges}
              disabled={isSaving}
            >
              {isSaving ? (
                <>
                  <div className="animate-spin mr-2 h-4 w-4 border-2 border-b-transparent border-white rounded-full"></div>
                  Đang lưu...
                </>
              ) : "Lưu thay đổi"}
            </Button>
          </CardFooter>
        </Card>
      </motion.div>

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4, delay: 0.1 }}
      >
        {/* <Card>
          <CardHeader>
            <CardTitle>Mạng xã hội</CardTitle>
            <CardDescription>
              Kết nối tài khoản mạng xã hội của bạn với hồ sơ
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="facebook">Facebook</Label>
                <Input id="facebook" placeholder="facebook.com/username" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="instagram">Instagram</Label>
                <Input id="instagram" placeholder="@username" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="twitter">Twitter</Label>
                <Input id="twitter" placeholder="@username" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="website">Website</Label>
                <Input id="website" placeholder="https://example.com" />
              </div>
            </div>
          </CardContent>
          <CardFooter className="flex justify-end gap-2">
            <Button variant="outline">Hủy bỏ</Button>
            <Button>Lưu thay đổi</Button>
          </CardFooter>
        </Card> */}
      </motion.div>
    </>
  );
};

const SecuritySection = () => {
  const [passwordData, setPasswordData] = useState({
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);

  const handleInputChange = (e) => {
    const { id, value } = e.target;
    setPasswordData((prev) => ({
      ...prev,
      [id === "current-password" ? "currentPassword" :
        id === "new-password" ? "newPassword" :
          id === "confirm-password" ? "confirmPassword" : id]: value,
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

  return (
    <>
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4 }}
      >
        <Card>
          <CardHeader>
            <CardTitle>Mật khẩu</CardTitle>
            <CardDescription>
              Cập nhật mật khẩu để bảo mật tài khoản của bạn
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="current-password">Mật khẩu hiện tại</Label>
              <Input
                id="current-password"
                type="password"
                value={passwordData.currentPassword}
                onChange={handleInputChange}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="new-password">Mật khẩu mới</Label>
              <Input
                id="new-password"
                type="password"
                value={passwordData.newPassword}
                onChange={handleInputChange}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="confirm-password">Xác nhận mật khẩu mới</Label>
              <Input
                id="confirm-password"
                type="password"
                value={passwordData.confirmPassword}
                onChange={handleInputChange}
              />
            </div>
          </CardContent>
          <CardFooter className="flex justify-end gap-2">
            <Button variant="outline" onClick={resetForm}>Hủy bỏ</Button>
            <Button
              onClick={handleUpdatePassword}
              disabled={loading}
            >
              {loading ? (
                <>
                  <div className="animate-spin mr-2 h-4 w-4 border-2 border-b-transparent border-white rounded-full"></div>
                  Đang cập nhật...
                </>
              ) : "Cập nhật mật khẩu"}
            </Button>
          </CardFooter>
        </Card>
      </motion.div>

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4, delay: 0.1 }}
      >

      </motion.div>
    </>
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

export default Account;