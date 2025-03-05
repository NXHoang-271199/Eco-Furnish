import React from "react";
import { FaArrowRight } from "react-icons/fa6";
const Contact = () => {
  return (
    <div className="font-roboto">
      <main className="container mx-auto px-4 py-16">
        <div className="text-left pr-96">
          <h1 className="text-4xl font-bold text-gray-900 pt-8 mb-4">
            Chúng tôi tin vào thiết kế nội thất bền vững và đam mê tạo ra không
            gian sống đẹp, thoải mái cho ngôi nhà của bạn.
          </h1>
          <p className="text-gray-600 mb-8 font-medium text-2xl">
            Cửa hàng của chúng tôi cung cấp các sản phẩm mới nhất mang phong
            cách cổ điển, với chất liệu tự nhiên, đường cong, góc cạnh và thiết
            kế cổ điển, có thể phù hợp với bất kỳ dự án trang trí nào.
          </p>
        </div>
        <div className="flex flex-col md:flex-row items-center md:space-x-8 bg-gray-100">
          <img
            src="https://storage.googleapis.com/a1aa/image/17RKU44o-p0wL_aV7UMwpptAv70oSYYp01uylGBfbOM.jpg"
            alt="Decorative wooden floor"
            className="w-full md:w-1/2 h-[600px] "
            // width="600"
            // height="400"
          />
          <div className="bg-gray-100 pr-12 pl-12 pt-56 h-[600px]">
            <h2 className="text-2xl font-bold text-gray-900 mb-4">
              Về chúng tôi
            </h2>
            <p className="text-gray-600 mb-4 w-[65%] font-semibold">
              Nội thất River là cửa hàng quà tặng và trang trí trụ sở tại Thành
              phố Hà Nội, Việt Nam. Thành lập từ năm 2025.
            </p>
            <a
              className="text-black font-medium underline hover:translate-x-4 duration-700  hover:ease-in flex"
              href="products"
            >
              Mua ngay <FaArrowRight className="mt-2 ml-2 size-[15px] " />
            </a>
          </div>
        </div>
      </main>
      <div className=" mx-auto px-4 text-center  py-8">
        <h2 className="text-3xl font-bold text-gray-900">
          Liên hệ với chúng tôi
        </h2>
      </div>
      <section className="bg-gray-50 py-16">
        <div className="container mx-auto px-4 text-center">
          {/* <h2 className="text-3xl font-bold text-gray-900 mb-8">
            Liên hệ với chúng tôi
          </h2> */}
          <div className="flex flex-col md:flex-row justify-between items-center md:space-x-8 mb-8">
            <div className="flex items-center space-x-4 mb-8 md:mb-0 w-64">
              <i className="fas fa-map-marker-alt text-2xl text-gray-500"></i>
              <div>
                <h3 className="text-lg font-bold text-gray-900 ">ĐỊA CHỈ</h3>
                <p className="text-gray-600">
                  Tòa nhà FPT Polytechnic, 13 phố Trịnh Văn Bô, Hà Nội
                </p>
              </div>
            </div>
            <div className="flex items-center space-x-4 mb-8 md:mb-0">
              <i className="fas fa-map-marker-alt text-2xl text-gray-500"></i>
              <div>
                <h3 className="text-lg font-bold text-gray-900">SỐ ĐIỆN THOẠI</h3>
                <p className="text-gray-600">0901121111</p>
              </div>
            </div>
            <div className="flex items-center space-x-4 mb-8 md:mb-0">
              <i className="fas fa-phone-alt text-2xl text-gray-500"></i>
              <div>
                <h3 className="text-lg font-bold text-gray-900">LIÊN HỆ</h3>
                <p className="text-gray-600">tuandnph33203@fpt.edu.vn</p>
              </div>
            </div>
          </div>
          <div className="flex flex-col md:flex-row justify-between items-start md:space-x-8 ">
            <form className="w-full md:w-1/2 mb-8 md:mb-0 bg-gray-100 pr-12 pl-12 ]">
              <div className="mb-4">
                <label className="block text-gray-700" htmlFor="name">
                  Họ tên
                </label>
                <input
                  className="w-full px-4 py-2 border border-gray-300 rounded-md"
                  id="name"
                  placeholder="Họ tên..."
                  type="text"
                />
              </div>
              <div className="my-3">
                <label className="block text-gray-700" htmlFor="email">
                  E-mail
                </label>
                <input
                  className="w-full px-4 py-2 border border-gray-300 rounded-md"
                  id="email"
                  placeholder="E-mail..."
                  type="email"
                />
              </div>
              <div className="my-3">
                <label className="block text-gray-700" htmlFor="message">
                  Tin nhắn
                </label>
                <textarea
                  className="w-full px-4 py-2 border border-gray-300 rounded-md"
                  id="message"
                  placeholder="Tin nhắn..."
                  rows="4"
                ></textarea>
              </div>
              <button
                className="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700 mb-4"
                type="submit"
              >
                Gửi
              </button>
            </form>
            <div className="w-full md:w-1/2 h-96">
              <iframe
                className="w-full md:h-full rounded-md"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.1234567890123!2d105.78000000000001!3d21.02851111111111!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab1234567890%3A0x1234567890abcdef!2sFPT%20Polytechnic!5e0!3m2!1sen!2s!4v1611234567890!5m2!1sen!2s"
                style={{ border: 0 }}
                allowFullScreen=""
                aria-hidden="false"
                tabIndex="0"
              ></iframe>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Contact;
