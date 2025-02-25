import React from "react";

const BlogDetail = () => {
  return (
    <div className="font-roboto">
      {/* Announcement */}
      <div className="bg-blue-100 text-center py-2">
        <p className="text-blue-600">
          Chúc mừng giáng sinh! – Chúc bạn một mùa sinh an lành
          <a className="text-blue-800 underline" href="#">
            Mua Đồ Ngay
          </a>
        </p>
      </div>
      {/* Breadcrumb */}
      <div className="container mt-10 mx-auto px-4 py-4">
        <nav className="text-gray-600 text-sm">
          <a className="hover:underline" href="#">
            Trang chủ
          </a>
          &gt;
          <a className="hover:underline" href="#">
            Blog
          </a>
          &gt;
          <a className="hover:underline" href="#">
            Nội Thất Phòng Khách Tối Giản - Vẻ Đẹp Từ Sự Giản Đơn
          </a>
        </nav>
      </div>
      {/* Main Content */}
      <div className="container mx-auto px-4">
        <h1 className="text-3xl font-bold mb-4">
          Nội Thất Phòng Khách Tối Giản - Vẻ Đẹp Từ Sự Giản Đơn
        </h1>
        <div className="flex items-center text-gray-600 mb-4">
          <span className="mr-2">Nội thất River</span>
          <span className="mr-2">
            <i className="fas fa-calendar-alt"></i>
            Thứ Ba, 10 tháng 12, 2024
          </span>
        </div>
        {/* transition-transform duration-300 hover:scale-95 */}
        <img
          alt="Minimalist living room with modern furniture"
          className="w-full mb-4 rounded-lg "
          height="600"
          src="https://storage.googleapis.com/a1aa/image/_0Fo2rLT5qb31Kx7O68dFLCwrMQWvFnbqIC0dpjvnIw.jpg"
          width="1200"
        />
        <p className="text-gray-700 mb-4">
          Phong cách tối giản trong thiết kế phòng khách không chỉ mang đến
          không gian sống gọn gàng mà vẫn có thể thẩm mỹ tinh tế của gia chủ.
          Thiết kế này tập trung vào việc sử dụng các thiết kế nội thất nữ tính
          để căn thiết kế và giảm thiểu sự phức tạp trong không gian. Nội thất
          phòng khách tối giản thường bao gồm ghế sofa bọc da, bàn cà phê và kệ
          đựng đồ đơn giản để tạo sự trẻ trung và hiện đại. Các gam màu chủ đạo
          như trắng, xám, đen, nâu được các nhà thiết kế ưa chuộng vì mang lại
          cảm giác nhẹ nhàng và thoải mái. Tính linh hoạt trong không gian cũng
          là một yếu tố quan trọng. Công việc sắp xếp nội thất không kém phần
          sáng tạo, làm cho không gian trở nên thú vị và dễ chịu. Phòng khách
          tối giản là sự lựa chọn lý tưởng cho những ai yêu thích sự tinh tế và
          tiện nghi nhưng đầy đủ và hiện đại.
        </p>
        <p className="text-gray-700 mb-4">
          Phòng khách tối giản không chỉ là một xu hướng mà còn là một phong
          cách sống. Khi bạn chọn một không gian tối giản, bạn đang chọn một lối
          sống gọn gàng và tinh tế. Ánh sáng tự nhiên luôn được ưu tiên để giúp
          không gian phòng khách trở nên thông thoáng. Ngoài ra, nên chọn sử
          dụng những món đồ nội thất đa dụng để tối ưu hóa không gian và tiện
          ích cho người sử dụng. Phòng khách tối giản là sự lựa chọn lý tưởng
          cho những ai yêu thích sự tinh tế và tiện nghi nhưng đầy đủ và hiện
          đại.
        </p>
        <a className="text-blue-600 hover:underline" href="#">
          Xem thêm bài viết
        </a>
      </div>
      {/* Related Posts */}
      <div className="container mx-auto px-6 py-8 ">
        <h2 className="text-2xl font-bold mb-4">Bài viết bạn có thể thích</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
          {[...Array(5)].map((_, index) => (
            <div className="bg-white shadow rounded overflow-hidden">
              <img
                alt="Modern living room with comfortable sofa"
                className="w-full rounded-lg transition-transform duration-300 hover:scale-110"
                height="300"
                src="https://storage.googleapis.com/a1aa/image/vyVG2kxxwpC24KZA-rQ0QCKdXySwdd0G4xznX8ZcoBM.jpg"
                width="400"
              />
              <div className="p-4">
                <h3 className="text-lg font-bold mb-2">
                  Khám Phá Sự Hoàn Hảo: Top Hiện Đại Mang Lại Sự Thoải Mái Và
                  Phong Cách Cho Ngôi Nhà Bạn
                </h3>
                <p className="text-gray-600">
                  Thứ Tư Năm, 19 tháng 12 năm 2024
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
      {/* Newsletter */}
      <div className="bg-gray-100 py-8">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-2xl font-bold mb-4">
            Tham khảo bản tin của chúng tôi
          </h2>
          <p className="text-gray-700 mb-4">
            Đăng ký để nhận ưu đãi, sản phẩm mới và chương trình khuyến mãi!
          </p>
          <form className="flex justify-center">
            <input
              className="w-1/2 p-2 border border-gray-300 rounded-l"
              placeholder="Địa chỉ email"
              type="email"
            />
            <button
              className="bg-blue-600 text-white p-2 rounded-r"
              type="submit"
            >
              Đăng Ký
            </button>
          </form>
        </div>
      </div>
    </div>
  );
};

export default BlogDetail;
