import React, { useEffect, useRef } from "react";
import { TbTargetArrow } from "react-icons/tb";
import { PiRoadHorizonFill } from "react-icons/pi";
import { BiSolidBookHeart } from "react-icons/bi";
import { motion, useAnimation } from "framer-motion";
import { useInView } from "react-intersection-observer";

const FadeInAnimation = ({ children, delay = 0, direction = null }) => {
  const controls = useAnimation();
  const [ref, inView] = useInView({
    triggerOnce: true,
    threshold: 0.2,
  });

  const getDirectionVariants = () => {
    switch (direction) {
      case "left":
        return {
          hidden: { x: -50, opacity: 0 },
          visible: { x: 0, opacity: 1 },
        };
      case "right":
        return {
          hidden: { x: 50, opacity: 0 },
          visible: { x: 0, opacity: 1 },
        };
      case "up":
        return {
          hidden: { y: 50, opacity: 0 },
          visible: { y: 0, opacity: 1 },
        };
      case "down":
        return {
          hidden: { y: -50, opacity: 0 },
          visible: { y: 0, opacity: 1 },
        };
      default:
        return {
          hidden: { opacity: 0 },
          visible: { opacity: 1 },
        };
    }
  };

  useEffect(() => {
    if (inView) {
      controls.start("visible");
    }
  }, [controls, inView]);

  return (
    <motion.div
      ref={ref}
      initial="hidden"
      animate={controls}
      variants={getDirectionVariants()}
      transition={{ duration: 0.6, delay: delay, ease: "easeOut" }}
    >
      {children}
    </motion.div>
  );
};

const About = () => {
  return (
    <div className="bg-neutral-50">
      <main className="mb-16">
        {/* Hero Banner */}
        <div className="relative h-[70vh] overflow-hidden">
          <motion.div
            initial={{ scale: 1.1 }}
            animate={{ scale: 1 }}
            transition={{ duration: 1.5 }}
            className="absolute inset-0"
          >
            <img
              src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1920&q=80"
              alt="Eco-Friendly Furniture"
              className="w-full h-full object-cover"
            />
            <div className="absolute inset-0 bg-black bg-opacity-40"></div>
          </motion.div>

          <div className="absolute inset-0 flex flex-col items-center justify-center text-white">
            <motion.h1
              initial={{ y: 30, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              transition={{ duration: 0.8 }}
              className="text-5xl md:text-6xl font-bold mb-4 text-center"
            >
              Về Eco-Furnish
            </motion.h1>
            <motion.div
              initial={{ y: 30, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="h-1 w-24 bg-orange-400 mb-6"
            ></motion.div>
            <motion.p
              initial={{ y: 30, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              transition={{ duration: 0.8, delay: 0.4 }}
              className="text-xl md:text-2xl max-w-2xl text-center px-4"
            >
              Chúng tôi luôn kiên định với sứ mệnh tạo ra những sản phẩm nội
              thất bền vững, thân thiện với môi trường
            </motion.p>
          </div>
        </div>

        {/* Content */}
        <div className="max-w-6xl mx-auto px-4 pt-20">
          {/* Mission, Vision, Values */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-10 mb-20">
            <motion.div
              initial={{ opacity: 0, y: 50 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.2 }}
              viewport={{ once: true }}
              className="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 h-full"
            >
              <div className="flex w-full justify-center mb-6">
                <div className="bg-orange-50 p-5 rounded-full">
                  <TbTargetArrow className="text-orange-500 w-16 h-16" />
                </div>
              </div>
              <h3 className="font-bold text-2xl uppercase text-center mb-4">
                Sứ mệnh
              </h3>
              <p className="text-center text-gray-600">
                Chúng tôi mang đến những sản phẩm nội thất chất lượng cao, được
                sản xuất từ nguyên liệu bền vững, góp phần bảo vệ môi trường và
                nâng cao chất lượng cuộc sống.
              </p>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, y: 50 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.4 }}
              viewport={{ once: true }}
              className="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 h-full"
            >
              <div className="flex w-full justify-center mb-6">
                <div className="bg-orange-50 p-5 rounded-full">
                  <PiRoadHorizonFill className="text-orange-500 w-16 h-16" />
                </div>
              </div>
              <h3 className="font-bold text-2xl uppercase text-center mb-4">
                Tầm nhìn
              </h3>
              <p className="text-center text-gray-600">
                Trở thành thương hiệu nội thất hàng đầu tại Việt Nam trong lĩnh
                vực nội thất bền vững, mang đến những giải pháp thiết kế hiện
                đại và thân thiện với môi trường.
              </p>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, y: 50 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.6 }}
              viewport={{ once: true }}
              className="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 h-full"
            >
              <div className="flex w-full justify-center mb-6">
                <div className="bg-orange-50 p-5 rounded-full">
                  <BiSolidBookHeart className="text-orange-500 w-16 h-16" />
                </div>
              </div>
              <h3 className="font-bold text-2xl uppercase text-center mb-4">
                Giá trị cốt lõi
              </h3>
              <p className="text-center text-gray-600">
                Chất lượng - Sáng tạo - Bền vững - Trách nhiệm - Khách hàng là
                trọng tâm. Những giá trị này định hướng mọi quyết định và hành
                động của chúng tôi.
              </p>
            </motion.div>
          </div>

          {/* About Company */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-12 mb-20 items-center">
            <FadeInAnimation direction="left">
              <div className="overflow-hidden rounded-lg">
                <motion.img
                  whileHover={{ scale: 1.05 }}
                  transition={{ duration: 0.5 }}
                  src="https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=700&q=80"
                  alt="Về chúng tôi"
                  className="w-full h-full object-cover rounded-lg"
                />
              </div>
            </FadeInAnimation>

            <FadeInAnimation direction="right">
              <h2 className="font-bold text-3xl uppercase mb-6 text-gray-800">
                Về <span className="text-orange-500">Eco-Furnish</span>
              </h2>
              <div className="h-1 w-20 bg-orange-400 mb-6"></div>
              <p className="text-gray-600 mb-6 leading-relaxed">
                Được thành lập vào năm 2020, Eco-Furnish là thương hiệu tiên
                phong trong lĩnh vực nội thất bền vững tại Việt Nam. Chúng tôi
                chuyên sản xuất và phân phối các sản phẩm nội thất được làm từ
                gỗ tự nhiên, được khai thác có trách nhiệm từ các khu rừng được
                quản lý bền vững.
              </p>
              <p className="text-gray-600 leading-relaxed">
                Với đội ngũ thiết kế tài năng và đam mê, chúng tôi luôn nỗ lực
                sáng tạo những sản phẩm nội thất không chỉ đẹp về mặt thẩm mỹ mà
                còn bền vững, thân thiện với môi trường và mang lại không gian
                sống lành mạnh cho khách hàng.
              </p>
            </FadeInAnimation>
          </div>

          {/* Products Showcase */}
          <div className="mb-20">
            <motion.div
              initial={{ opacity: 0 }}
              whileInView={{ opacity: 1 }}
              transition={{ duration: 0.8 }}
              viewport={{ once: true }}
            >
              <h2 className="text-center text-3xl font-bold text-gray-800 mb-2">
                CHÚNG TÔI TẠO RA NỘI THẤT GỖ TỰ NHIÊN
              </h2>
              <h3 className="text-center text-3xl font-bold text-gray-800 mb-8">
                CHẤT LƯỢNG CAO
              </h3>
              <div className="h-1 w-20 bg-orange-400 mx-auto mb-10"></div>
            </motion.div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <motion.div
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.1 }}
                viewport={{ once: true }}
                className="group relative overflow-hidden rounded-lg cursor-pointer"
              >
                <motion.img
                  whileHover={{ scale: 1.1 }}
                  transition={{ duration: 0.5 }}
                  src="https://images.unsplash.com/photo-1617103996702-96ff29b1c467?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=396&q=80"
                  alt="Sàn gỗ"
                  className="w-full h-80 object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent group-hover:from-black/80 transition-all duration-300"></div>
                <div className="absolute bottom-0 left-0 right-0 p-6 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                  <h4 className="text-white uppercase text-lg font-semibold mb-2">
                    Sàn gỗ
                  </h4>
                  <p className="text-white/80 text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    Sàn gỗ tự nhiên cao cấp, đa dạng mẫu mã, phù hợp với mọi
                    không gian sống.
                  </p>
                </div>
              </motion.div>

              <motion.div
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.2 }}
                viewport={{ once: true }}
                className="group relative overflow-hidden rounded-lg cursor-pointer"
              >
                <motion.img
                  whileHover={{ scale: 1.1 }}
                  transition={{ duration: 0.5 }}
                  src="https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=396&q=80"
                  alt="Hoàn thiện"
                  className="w-full h-80 object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent group-hover:from-black/80 transition-all duration-300"></div>
                <div className="absolute bottom-0 left-0 right-0 p-6 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                  <h4 className="text-white uppercase text-lg font-semibold mb-2">
                    Hoàn thiện
                  </h4>
                  <p className="text-white/80 text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    Dịch vụ hoàn thiện chuyên nghiệp với các sản phẩm sơn, dầu
                    bảo vệ gỗ thân thiện với môi trường.
                  </p>
                </div>
              </motion.div>

              <motion.div
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.3 }}
                viewport={{ once: true }}
                className="group relative overflow-hidden rounded-lg cursor-pointer"
              >
                <motion.img
                  whileHover={{ scale: 1.1 }}
                  transition={{ duration: 0.5 }}
                  src="https://images.unsplash.com/photo-1615876234886-fd9a39fda97f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1932&q=80"
                  alt="Lắp đặt"
                  className="w-full h-80 object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent group-hover:from-black/80 transition-all duration-300"></div>
                <div className="absolute bottom-0 left-0 right-0 p-6 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                  <h4 className="text-white uppercase text-lg font-semibold mb-2">
                    Lắp đặt
                  </h4>
                  <p className="text-white/80 text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    Dịch vụ lắp đặt chuyên nghiệp, nhanh chóng và tỉ mỉ đến từng
                    chi tiết.
                  </p>
                </div>
              </motion.div>

              <motion.div
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.4 }}
                viewport={{ once: true }}
                className="group relative overflow-hidden rounded-lg cursor-pointer"
              >
                <motion.img
                  whileHover={{ scale: 1.1 }}
                  transition={{ duration: 0.5 }}
                  src="https://images.unsplash.com/photo-1631679706909-1844bbd07221?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1992&q=80"
                  alt="Sưởi ấm sàn"
                  className="w-full h-80 object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent group-hover:from-black/80 transition-all duration-300"></div>
                <div className="absolute bottom-0 left-0 right-0 p-6 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                  <h4 className="text-white uppercase text-lg font-semibold mb-2">
                    Sưởi ấm sàn
                  </h4>
                  <p className="text-white/80 text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    Giải pháp sưởi ấm sàn hiện đại, tiết kiệm năng lượng, mang
                    lại cảm giác ấm áp cho ngôi nhà.
                  </p>
                </div>
              </motion.div>
            </div>
          </div>

          {/* Team Section */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            viewport={{ once: true }}
            className="bg-white p-10 rounded-xl shadow-sm mb-20"
          >
            <h2 className="text-center text-3xl font-bold text-gray-800 mb-2">
              ĐỘI NGŨ CỦA CHÚNG TÔI
            </h2>
            <div className="h-1 w-20 bg-orange-400 mx-auto mb-10"></div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
              <motion.div
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.1 }}
                viewport={{ once: true }}
                className="text-center"
              >
                <div className="mb-4 overflow-hidden rounded-full w-40 h-40 mx-auto">
                  <img
                    src="/public/images/avatarAbout/avatar3.jpg"
                    alt="CEO"
                    className="w-full h-full object-cover"
                  />
                </div>
                <h3 className="font-bold text-xl mb-1">Nguyễn Xuân Hoàng</h3>
                <p className="text-orange-500 mb-3">Giám đốc điều hành</p>
                <p className="text-gray-600">
                  Với hơn 15 năm kinh nghiệm trong lĩnh vực nội thất và thiết kế
                  bền vững.
                </p>
              </motion.div>

              <motion.div
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.2 }}
                viewport={{ once: true }}
                className="text-center"
              >
                <div className="mb-4 overflow-hidden rounded-full w-40 h-40 mx-auto">
                  <img
                    src="/public/images/avatarAbout/avatar1.jpg"
                    alt="Design Director"
                    className="w-full h-full object-cover"
                  />
                </div>
                <h3 className="font-bold text-xl mb-1">Nguyễn Đức Việt</h3>
                <p className="text-orange-500 mb-3">Giám đốc thiết kế và kĩ thuật</p>
                <p className="text-gray-600">
                  Chuyên gia thiết kế với nhiều dự án nổi bật trong nước và quốc
                  tế.
                </p>
              </motion.div>

              <motion.div
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.3 }}
                viewport={{ once: true }}
                className="text-center"
              >
                <div className="mb-4 overflow-hidden rounded-full w-40 h-40 mx-auto">
                  <img
                    src="/public/images/avatarAbout/avatar2.jpg"
                    alt="Marketing Manager"
                    className="w-full h-full object-cover"
                  />
                </div>
                <h3 className="font-bold text-xl mb-1">Nguyễn Huy Hoàng</h3>
                <p className="text-orange-500 mb-3">Giám đốc Marketing</p>
                <p className="text-gray-600">
                  Chuyên gia về chiến lược marketing và phát triển thương hiệu
                  bền vững.
                </p>
              </motion.div>
            </div>
          </motion.div>
        </div>
      </main>
    </div>
  );
};

export default About;
