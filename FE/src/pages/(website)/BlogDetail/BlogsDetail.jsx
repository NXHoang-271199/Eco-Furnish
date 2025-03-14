import React from "react";

const BlogsDetail = () => {
  return (
    <div className="font-roboto">
      {/* Announcement */}
      <div className="bg-blue-100 text-center py-2 px-4">
        <p className="text-blue-600 text-sm md:text-base">
          Chúc mừng giáng sinh! – Chúc bạn một mùa sinh an lành{" "}
          <a className="text-blue-800 underline ml-1" href="#">
            Mua Đồ Ngay
          </a>
        </p>
      </div>
      
      {/* Breadcrumb */}
      <div className="container mt-4 md:mt-10 mx-auto px-4 py-2 md:py-4 overflow-x-auto">
        <nav className="text-gray-600 text-xs md:text-sm whitespace-nowrap">
          <a className="hover:underline" href="#">
            Trang chủ
          </a>
          {" > "}
          <a className="hover:underline" href="#">
            Blog
          </a>
          {" > "}
          <a className="hover:underline" href="#">
            Nội Thất Phòng Khách Tối Giản - Vẻ Đẹp Từ Sự Giản Đơn
          </a>
        </nav>
      </div>
      
      {/* Main Content */}
      <div className="container mx-auto px-4 md:px-6 lg:px-8">
        <h1 className="text-2xl md:text-3xl font-bold mb-3 md:mb-4">
          Nội Thất Phòng Khách Tối Giản - Vẻ Đẹp Từ Sự Giản Đơn
        </h1>
        
        <div className="flex flex-wrap items-center text-gray-600 mb-3 md:mb-4 text-sm">
          <span className="mr-2 mb-1 md:mb-0">Nội thất River</span>
          <span className="mb-1 md:mb-0">
            <i className="fas fa-calendar-alt mr-1"></i>
            Thứ Ba, 10 tháng 12, 2024
          </span>
        </div>
        
        <div className="mb-4 overflow-hidden rounded-lg">
          <img
            alt="Minimalist living room with modern furniture"
            className="w-full transition-transform duration-300 hover:scale-95 object-cover"
            src="https://storage.googleapis.com/a1aa/image/_0Fo2rLT5qb31Kx7O68dFLCwrMQWvFnbqIC0dpjvnIw.jpg"
            width="1200"
            height="600"
          />
        </div>
        
        <div className="prose max-w-none">
          <p className="text-gray-700 mb-4 text-base md:text-lg">
            Phong cách tối giản trong thiết kế phòng khách không chỉ mang đến
            không gian sống gọn gàng mà vẫn có thể thẩm mỹ tinh tế của gia chủ.
            Thiết kế này tập trung vào việc sử dụng các thiết kế nội thất nữ tính
            để căn thiết kế và giảm thiểu sự phức tạp trong không gian. Nội thất
            phòng khách tối giản thường bao gồm ghế sofa bọc da, bàn cà phê và kệ
            đựng đồ đơn giản để tạo sự trẻ trung và hiện đại. Các gam màu chủ đạo
            như trắng, xám, đen, nâu được các nhà thiết kế ưa chuộng vì mang lại
            cảm giác nhẹ nhàng và thoải mái.
</p>
          
          <p className="text-gray-700 mb-4 text-base md:text-lg">
            Phòng khách tối giản không chỉ là một xu hướng mà còn là một phong
            cách sống. Khi bạn chọn một không gian tối giản, bạn đang chọn một lối
            sống gọn gàng và tinh tế. Ánh sáng tự nhiên luôn được ưu tiên để giúp
            không gian phòng khách trở nên thông thoáng. Ngoài ra, nên chọn sử
            dụng những món đồ nội thất đa dụng để tối ưu hóa không gian và tiện
            ích cho người sử dụng. Phòng khách tối giản là sự lựa chọn lý tưởng
            cho những ai yêu thích sự tinh tế và tiện nghi nhưng đầy đủ và hiện
            đại.
          </p>
        </div>
        
        <a className="inline-block text-blue-600 hover:underline font-medium mb-8" href="#">
          Xem thêm bài viết
        </a>
      </div>
      
      {/* Related Posts */}
      <div className="bg-gray-50 py-8">
        <div className="container mx-auto px-4 md:px-6 lg:px-8">
          <h2 className="text-xl md:text-2xl font-bold mb-4 md:mb-6">Bài viết bạn có thể thích</h2>
          
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-5">
            {[...Array(5)].map((_, index) => (
              <div key={index} className="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div className="overflow-hidden">
                  <img
                    alt="Modern living room with comfortable sofa"
                    className="w-full h-48 object-cover transition-transform duration-300 hover:scale-110"
                    src="https://storage.googleapis.com/a1aa/image/vyVG2kxxwpC24KZA-rQ0QCKdXySwdd0G4xznX8ZcoBM.jpg"
                  />
                </div>
                <div className="p-4">
                  <h3 className="text-base md:text-lg font-bold mb-2 line-clamp-2">
                    Khám Phá Sự Hoàn Hảo: Top Hiện Đại Mang Lại Sự Thoải Mái Và
                    Phong Cách Cho Ngôi Nhà Bạn
                  </h3>
                  <p className="text-gray-600 text-sm">Thứ Tư, 19 tháng 12 năm 2024</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
      
      {/* Newsletter */}
      <div className="bg-gray-100 py-8">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-xl md:text-2xl font-bold mb-3 md:mb-4">
            Tham khảo bản tin của chúng tôi
          </h2>
          <p className="text-gray-700 mb-4 max-w-lg mx-auto">
            Đăng ký để nhận ưu đãi, sản phẩm mới và chương trình khuyến mãi!
          </p>
<form className="flex flex-col sm:flex-row max-w-md mx-auto justify-center">
            <input
              className="p-2 border border-gray-300 rounded-l sm:w-2/3 mb-2 sm:mb-0 rounded sm:rounded-r-none"
              placeholder="Địa chỉ email"
              type="email"
            />
            <button
              className="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-r transition-colors sm:w-1/3 rounded sm:rounded-l-none"
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

export default BlogsDetail;
