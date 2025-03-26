import { useState, useEffect, useRef } from "react";
import { Link } from "react-router-dom";
import {
  motion,
  useScroll,
  useTransform,
  useSpring,
  useMotionValue,
  useInView,
  useAnimation,
} from "framer-motion";
import api from "../../../service/api";

const Homes = () => {
  const [products, setProducts] = useState([]);
  const [posts, setPosts] = useState([]);
  const bannerRef = useRef(null);
  const bannerInView = useInView(bannerRef, { once: false, amount: 0.5 });
  const bannerControls = useAnimation();
  const { scrollY } = useScroll();

  // Hiệu ứng parallax cho banner
  const y = useTransform(scrollY, [0, 1000], [0, 300]);
  const opacity = useTransform(scrollY, [0, 300], [1, 0.3]);
  const scale = useTransform(scrollY, [0, 500], [1, 1.2]);
  const springY = useSpring(y, { stiffness: 50, damping: 15 });
  const springOpacity = useSpring(opacity, { stiffness: 50, damping: 15 });
  const springScale = useSpring(scale, { stiffness: 50, damping: 15 });

  // Hiệu ứng vị trí mặt trời (gradient)
  const mouseX = useMotionValue(0);
  const mouseY = useMotionValue(0);
  const rotateX = useTransform(mouseY, [-300, 300], [5, -5]);
  const rotateY = useTransform(mouseX, [-300, 300], [-5, 5]);
  const gradientX = useTransform(mouseX, [-300, 300], ["30%", "70%"]);
  const gradientY = useTransform(mouseY, [-300, 300], ["30%", "70%"]);

  useEffect(() => {
    if (bannerInView) {
      bannerControls.start({
        scale: 1,
        opacity: 1,
        transition: {
          type: "spring",
          stiffness: 60,
          damping: 20,
          delay: 0.2,
        },
      });
    }
  }, [bannerInView, bannerControls]);

  const handleMouseMove = (e) => {
    const { clientX, clientY } = e;
    const boundingRect = e.currentTarget.getBoundingClientRect();
    const centerX = boundingRect.width / 2;
    const centerY = boundingRect.height / 2;
    mouseX.set(clientX - boundingRect.left - centerX);
    mouseY.set(clientY - boundingRect.top - centerY);
  };

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const response = await api.get("products");
        if (
          response.data.status === "success" &&
          Array.isArray(response.data.data.data)
        ) {
          setProducts(response.data.data.data);
        } else {
          console.log("Dữ liệu không phải là mảng hoặc API trả về lỗi");
        }
      } catch (error) {
        console.log("Lỗi khi gọi API sản phẩm:", error);
      }
    };

    const fetchPosts = async () => {
      try {
        const response = await api.get("posts");
        if (
          response.data.status === "success" &&
          Array.isArray(response.data.data)
        ) {
          console.log("Dữ liệu bài viết:", response.data.data);
          setPosts(response.data.data);
        } else {
          console.log(
            "Dữ liệu bài viết không phải là mảng hoặc API trả về lỗi"
          );
        }
      } catch (error) {
        console.log("Lỗi khi gọi API bài viết:", error);
      }
    };

    fetchProducts();
    fetchPosts();
  }, []);

  // Animation variants
  const fadeInUp = {
    hidden: { opacity: 0, y: 60 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 1.2,
        ease: [0.22, 0.03, 0.21, 1], // chuyển động cực kỳ mượt
      },
    },
  };

  const staggerContainer = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.5,
        delayChildren: 0.6,
      },
    },
  };

  const scaleIn = {
    hidden: { scale: 0.92, opacity: 0 },
    visible: {
      scale: 1,
      opacity: 1,
      transition: {
        duration: 1.2,
        ease: [0.22, 1.2, 0.36, 1], // easeOutBack với nhẹ hơn
      },
    },
  };

  // Banner text variants
  const bannerTextVariants = {
    hidden: {
      opacity: 0,
      y: 100,
    },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 1.5,
        ease: "easeOut",
        delay: 0.3,
      },
    },
  };

  // Hiệu ứng shine cho banner texture
  const shineVariants = {
    initial: {
      background:
        "linear-gradient(45deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0) 75%, rgba(255,255,255,0.5) 90%, rgba(255,255,255,0) 100%)",
      backgroundSize: "200% 200%",
      backgroundPosition: "0% 0%",
    },
    animate: {
      backgroundPosition: "200% 200%",
      transition: {
        duration: 15,
        ease: "linear",
        repeat: Infinity,
      },
    },
  };

  return (
    <>
      <motion.div
        ref={bannerRef}
        className="relative w-full overflow-hidden h-[90vh]"
        initial={{ opacity: 0, scale: 0.95 }}
        animate={bannerControls}
        onMouseMove={handleMouseMove}
      >
        {/* Parallax banner image */}
        <motion.div
          className="absolute inset-0 w-full h-full"
          style={{
            y: springY,
            scale: springScale,
            opacity: springOpacity,
          }}
        >
          <img
            src=".\src\assets\img\slider-banner\homepage01-slide1.jpg"
            alt="Eco-Furnish Banner"
            className="w-full h-full object-cover"
          />
        </motion.div>

        {/* Overlay gradient effect */}
        <motion.div
          className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent z-10"
          style={{
            rotateX,
            rotateY,
            background: `radial-gradient(circle at ${gradientX} ${gradientY}, rgba(255,200,64,0.4) 0%, rgba(0,0,0,0.4) 70%)`,
          }}
        />

        {/* Shine effect layer */}
        <motion.div
          className="absolute inset-0 z-10 opacity-30"
          variants={shineVariants}
          initial="initial"
          animate="animate"
        />

        {/* Banner content */}
        <div className="absolute inset-0 flex flex-col items-center justify-center z-20 px-4">
          <motion.div
            className="text-center text-white"
            variants={bannerTextVariants}
            initial="hidden"
            animate="visible"
          >
            <motion.h1
              className="text-6xl font-bold mb-6 text-white drop-shadow-lg"
              initial={{ opacity: 0, y: 50 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 1.2, delay: 0.3 }}
            >
              Eco-Furnish
            </motion.h1>
            <motion.p
              className="text-xl mb-8 max-w-2xl mx-auto text-white/90 drop-shadow-md"
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 1.2, delay: 0.5 }}
            >
              Không gian sống xanh - Thiết kế hiện đại - Chất liệu bền vững
            </motion.p>
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 1.2, delay: 0.7 }}
            >
              <Link
                to="/products"
                className="inline-flex items-center bg-amber-300 hover:bg-amber-400 text-white font-medium py-3 px-8 rounded-full transition-all duration-300 shadow-lg hover:shadow-amber-500/30 transform hover:translate-y-[-3px]"
              >
                <span>Khám phá ngay</span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="h-5 w-5 ml-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                  />
                </svg>
              </Link>
            </motion.div>
          </motion.div>
        </div>

        {/* Decorative elements */}
        <motion.div
          className="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-white to-transparent z-20"
          animate={{
            opacity: [0.5, 0.8, 0.5],
            y: [0, -5, 0],
          }}
          transition={{
            duration: 6,
            repeat: Infinity,
            ease: "easeInOut",
          }}
        />
      </motion.div>

      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
      >
        <div className="max-w-6xl mx-auto mt-20 my-5">
          <div>
            <div>
              <motion.div className="text-center m-auto" variants={fadeInUp}>
                <motion.h2
                  className="mb-6 text-4xl font-semibold"
                  initial={{ opacity: 0, scale: 0.9 }}
                  whileInView={{ opacity: 1, scale: 1 }}
                  transition={{ duration: 1.2, ease: "easeOut" }}
                >
                  <span className="bg-clip-text text-transparent bg-gradient-to-r from-amber-300 to-amber-500">
                    New Product
                  </span>
                </motion.h2>
                <motion.p
                  className="w-[65%] m-auto"
                  initial={{ opacity: 0 }}
                  whileInView={{ opacity: 1 }}
                  transition={{ duration: 1.2, delay: 0.2 }}
                >
                  Our traditional dining tables, chairs, case pieces and other
                  traditional dining furniture are geared toward those who
                  appreciate the simplicity and true craftsmanship.
                </motion.p>
              </motion.div>
            </div>
            <motion.div
              className="grid grid-cols-4 grid-rows-1 gap-4 my-12"
              variants={staggerContainer}
              initial="hidden"
              whileInView="visible"
              viewport={{ once: true, amount: 0.1 }}
            >
              {products.length > 0 ? (
                products.slice(0, 4).map((product, index) => (
                  <motion.div
                    key={product.id}
                    variants={fadeInUp}
                    custom={index}
                    whileHover={{
                      scale: 1.03,
                      y: -10,
                      boxShadow: "0 25px 50px -12px rgba(0, 0, 0, 0.1)",
                    }}
                    transition={{
                      type: "spring",
                      stiffness: 300,
                      damping: 20,
                    }}
                    className="rounded-xl overflow-hidden bg-white p-2"
                  >
                    <Link to={`product/${product.id}`}>
                      <motion.div
                        className="mb-2 relative overflow-hidden rounded-lg"
                        whileHover={{ scale: 1.05 }}
                        transition={{ duration: 0.8 }}
                      >
                        <motion.div
                          className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 z-10"
                          whileHover={{ opacity: 1 }}
                          transition={{ duration: 0.5 }}
                        />
                        <motion.img
                          src={`http://localhost:8000/storage/${product.image_thumnail}`}
                          alt={product.name}
                          className="rounded-lg w-full object-cover aspect-[2/3]"
                          initial={{ scale: 1.2, y: 20 }}
                          animate={{ scale: 1, y: 0 }}
                          transition={{ duration: 0.8, delay: index * 0.1 }}
                        />
                        <motion.div
                          className="absolute top-2 right-2 bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full z-20"
                          initial={{ opacity: 0, scale: 0.5 }}
                          whileInView={{ opacity: 1, scale: 1 }}
                          transition={{
                            type: "spring",
                            stiffness: 500,
                            delay: 0.5 + index * 0.1,
                          }}
                        >
                          NEW
                        </motion.div>
                      </motion.div>
                      <div className="px-3 py-2">
                        <motion.h3
                          className="text-lg font-semibold truncate mb-1"
                          initial={{ opacity: 0, y: 10 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{
                            duration: 0.5,
                            delay: 0.2 + index * 0.1,
                          }}
                        >
                          {product.name}
                        </motion.h3>
                        <motion.p
                          className="text-sm text-gray-500 mb-2 line-clamp-2 min-h-[40px]"
                          initial={{ opacity: 0, y: 10 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{
                            duration: 0.5,
                            delay: 0.3 + index * 0.1,
                          }}
                        >
                          Sản phẩm nội thất chất lượng cao
                        </motion.p>
                        <motion.p
                          className="font-medium text-amber-600 text-lg"
                          initial={{ opacity: 0, y: 10 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{
                            duration: 0.5,
                            delay: 0.4 + index * 0.1,
                          }}
                        >
                          {new Intl.NumberFormat("vi-VN", {
                            style: "currency",
                            currency: "VND",
                          }).format(product.price)}
                        </motion.p>
                      </div>
                    </Link>
                  </motion.div>
                ))
              ) : (
                <>
                  {[1, 2, 3, 4].map((index) => (
                    <motion.div
                      key={index}
                      variants={fadeInUp}
                      custom={index}
                      className="bg-white p-3 rounded-xl"
                    >
                      <div className="mb-2">
                        <div className="bg-gray-300 w-full rounded-lg animate-pulse aspect-[1/2]"></div>
                      </div>
                      <div>
                        <div className="h-4 bg-gray-300 rounded w-3/4 mb-2 animate-pulse"></div>
                        <div className="h-4 bg-gray-300 rounded w-full mb-2 animate-pulse"></div>
                        <div className="h-4 bg-gray-300 rounded w-1/2 animate-pulse"></div>
                      </div>
                    </motion.div>
                  ))}
                </>
              )}
            </motion.div>
          </div>
        </div>
      </motion.section>

      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
      >
        <div className="max-w-6xl mx-auto mt-20 my-5 relative">
          {/* Bỏ bớt decorative elements */}

          <motion.div
            className="grid grid-rows-6 grid-cols-3 gap-4 relative z-10"
            variants={staggerContainer}
          >
            <motion.div
              className="row-span-4 overflow-hidden rounded-2xl"
              variants={scaleIn}
              whileHover={{
                scale: 1.02,
              }}
              transition={{ duration: 0.5 }}
            >
              <div className="h-full w-full relative">
                <motion.a
                  href=""
                  className="block h-full w-full relative overflow-hidden rounded-2xl"
                >
                  <motion.div
                    className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 z-10 flex items-end p-6"
                    whileHover={{ opacity: 1 }}
                    transition={{ duration: 0.5 }}
                  >
                    <h3 className="text-white text-xl font-bold">
                      Thiết kế cổ điển
                    </h3>
                  </motion.div>
                  <img
                    src=".\src\assets\img\banners\banner-homepage2_1.png"
                    alt="Thiết kế cổ điển"
                    className="h-full w-full object-cover rounded-2xl"
                  />
                </motion.a>
              </div>
            </motion.div>

            <motion.div
              className="col-span-2 row-span-2 overflow-hidden rounded-2xl"
              variants={scaleIn}
              whileHover={{
                scale: 1.02,
              }}
              transition={{ duration: 0.5 }}
            >
              <div className="h-full w-full relative">
                <motion.a
                  href=""
                  className="block h-full w-full relative overflow-hidden rounded-2xl"
                >
                  <motion.div
                    className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 z-10 flex items-end p-6"
                    whileHover={{ opacity: 1 }}
                    transition={{ duration: 0.5 }}
                  >
                    <h3 className="text-white text-xl font-bold">
                      Không gian sống hiện đại
                    </h3>
                  </motion.div>
                  <img
                    src=".\src\assets\img\banners\banner-homepage2_3.png"
                    alt="Không gian sống hiện đại"
                    className="h-full w-full object-cover rounded-2xl"
                  />
                </motion.a>
              </div>
            </motion.div>

            <motion.div
              className="col-span-2 row-span-4 overflow-hidden rounded-2xl"
              variants={scaleIn}
              whileHover={{
                scale: 1.02,
              }}
              transition={{ duration: 0.5 }}
            >
              <div className="h-full w-full relative pt-3">
                <motion.a
                  href=""
                  className="block h-full w-full relative overflow-hidden rounded-2xl"
                >
                  <motion.div
                    className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 z-10 flex items-end p-6"
                    whileHover={{ opacity: 1 }}
                    transition={{ duration: 0.5 }}
                  >
                    <h3 className="text-white text-xl font-bold">
                      Phòng khách sang trọng
                    </h3>
                  </motion.div>
                  <img
                    src=".\src\assets\img\banners\banner-homepage2_4.png"
                    alt="Phòng khách sang trọng"
                    className="h-full w-full object-cover rounded-2xl"
                  />
                </motion.a>
              </div>
            </motion.div>

            <motion.div
              className="row-span-2 overflow-hidden rounded-2xl"
              variants={scaleIn}
              whileHover={{
                scale: 1.02,
              }}
              transition={{ duration: 0.5 }}
            >
              <div className="h-full w-full relative">
                <motion.a
                  href=""
                  className="block h-full w-full relative overflow-hidden rounded-2xl"
                >
                  <motion.div
                    className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 z-10 flex items-end p-6"
                    whileHover={{ opacity: 1 }}
                    transition={{ duration: 0.5 }}
                  >
                    <h3 className="text-white text-xl font-bold">
                      Thiết kế phòng ngủ
                    </h3>
                  </motion.div>
                  <img
                    src=".\src\assets\img\banners\banner-homepage2_2.png"
                    alt="Thiết kế phòng ngủ"
                    className="h-full w-full object-cover rounded-2xl"
                  />
                </motion.a>
              </div>
            </motion.div>
          </motion.div>
        </div>
      </motion.section>

      {/* list products */}

      {/*  */}
      <motion.section
        className="py-16 bg-gray-50 relative overflow-hidden"
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
      >
        {/* Decorative Elements */}
        <motion.div
          className="absolute top-0 right-0 w-72 h-72 rounded-full bg-amber-200 filter blur-3xl opacity-20"
          animate={{
            x: [50, -50, 50],
            y: [20, -20, 20],
            scale: [1, 1.1, 1],
          }}
          transition={{ duration: 20, repeat: Infinity, ease: "easeInOut" }}
        />
        <motion.div
          className="absolute bottom-0 left-20 w-64 h-64 rounded-full bg-amber-400 filter blur-3xl opacity-10"
          animate={{
            x: [-30, 30, -30],
            y: [30, 0, 30],
            scale: [1, 1.2, 1],
          }}
          transition={{ duration: 25, repeat: Infinity, ease: "easeInOut" }}
        />

        <div className="max-w-6xl mx-auto px-4 relative z-10">
          <motion.div className="mb-16" variants={fadeInUp}>
            <div className="text-center">
              <motion.span
                className="text-amber-500 font-medium mb-2 block"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 1.2, delay: 0.3 }}
                viewport={{ once: true }}
              >
                Tin tức & ý tưởng
              </motion.span>
              <motion.h2
                className="text-4xl font-bold mb-4 relative"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 1.2, delay: 0.6 }}
                viewport={{ once: true }}
              >
                <span className="relative inline-block">
                  Bài Viết Mới Nhất
                  <motion.span
                    className="absolute -bottom-2 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-600"
                    initial={{ width: 0 }}
                    whileInView={{ width: "100%" }}
                    transition={{ duration: 1.2, delay: 1.2 }}
                    viewport={{ once: true }}
                  />
                </span>
              </motion.h2>
              <motion.p
                className="text-gray-600 max-w-2xl mx-auto"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 1.2, delay: 0.9 }}
                viewport={{ once: true }}
              >
                Khám phá những ý tưởng thiết kế nội thất mới nhất và các bí
                quyết để tạo nên không gian sống hoàn hảo cho ngôi nhà của bạn.
              </motion.p>
            </div>
          </motion.div>

          <motion.div
            className="grid grid-cols-1 md:grid-cols-3 gap-8"
            variants={staggerContainer}
          >
            {posts.length > 0 ? (
              posts.slice(0, 3).map((post, index) => (
                <motion.div
                  key={post.id}
                  className="group"
                  variants={fadeInUp}
                  custom={index}
                  whileHover={{ y: -10 }}
                  transition={{ duration: 0.4 }}
                >
                  <Link
                    to={`/blog-detail/${post.slug}`}
                    className="block h-full"
                  >
                    <motion.article
                      className="bg-white rounded-2xl overflow-hidden shadow-lg h-full transform transition-all duration-300 border border-gray-100"
                      initial={{
                        boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
                      }}
                      whileHover={{
                        y: -5,
                        boxShadow:
                          "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                      }}
                      transition={{ duration: 0.6 }}
                    >
                      <div className="relative overflow-hidden aspect-[16/10]">
                        <motion.div
                          className="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-purple-600/30 z-10 opacity-0 mix-blend-overlay"
                          whileHover={{ opacity: 1 }}
                          transition={{ duration: 0.5 }}
                        />
                        <motion.img
                          src={
                            post.thumbnail
                              ? post.thumbnail.startsWith("http")
                                ? post.thumbnail
                                : post.thumbnail.startsWith("/")
                                ? `http://localhost:8000${post.thumbnail}`
                                : `http://localhost:8000/${post.thumbnail}`
                              : "http://localhost:5173/src/assets/img/blog/blog-1.jpg"
                          }
                          alt={post.title}
                          className="w-full h-full object-cover"
                          whileHover={{ scale: 1.08 }}
                          transition={{ duration: 1.8 }}
                          onError={(e) => {
                            console.log("Lỗi khi tải ảnh:", post.thumbnail);
                            e.target.src =
                              "http://localhost:5173/src/assets/img/blog/blog-1.jpg";
                          }}
                        />
                        <motion.div
                          className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                          initial={{ opacity: 0 }}
                          whileHover={{ opacity: 1 }}
                          transition={{ duration: 1.2 }}
                        ></motion.div>

                        {/* Thẻ danh mục */}
                        <motion.div
                          className="absolute top-4 left-4 z-20"
                          initial={{ opacity: 0, x: -20 }}
                          whileInView={{ opacity: 1, x: 0 }}
                          transition={{ delay: 0.4 + index * 0.2 }}
                        >
                          <span className="bg-amber-500 text-white text-xs font-medium px-3 py-1 rounded-full shadow-lg backdrop-blur-sm bg-opacity-80">
                            {post.category.title}
                          </span>
                        </motion.div>
                      </div>

                      <div className="p-6 relative">
                        {/* Background decorative element */}
                        <div className="absolute top-0 right-0 -mt-8 -mr-8 w-16 h-16 bg-amber-50 rounded-full opacity-70" />

                        {/* Thông tin thời gian */}
                        <div className="flex items-center text-gray-500 text-sm mb-3 relative z-10">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-4 w-4 mr-1 text-amber-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                          </svg>
                          <span>{post.created_at}</span>
                        </div>

                        {/* Tiêu đề */}
                        <motion.h3
                          className="font-bold text-xl mb-3 text-gray-800 group-hover:text-amber-600 transition-colors duration-200 line-clamp-2 relative z-10"
                          initial={{ opacity: 0, y: 10 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{
                            duration: 0.8,
                            delay: 0.2 + index * 0.1,
                          }}
                        >
                          {post.title}
                        </motion.h3>

                        {/* Mô tả ngắn */}
                        <motion.p
                          className="text-gray-600 mb-5 line-clamp-3 relative z-10"
                          initial={{ opacity: 0, y: 10 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{
                            duration: 0.8,
                            delay: 0.3 + index * 0.1,
                          }}
                        >
                          {post.short_content}
                        </motion.p>

                        {/* Nút đọc thêm */}
                        <motion.div
                          className="flex items-center text-amber-500 font-medium transition-all duration-200 group-hover:text-amber-600 relative z-10"
                          whileHover={{ x: 5 }}
                          transition={{ duration: 0.8 }}
                        >
                          <span>Đọc tiếp</span>
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-5 w-5 ml-2 transform transition-transform duration-300 group-hover:translate-x-1"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M17 8l4 4m0 0l-4 4m4-4H3"
                            />
                          </svg>
                        </motion.div>
                      </div>
                    </motion.article>
                  </Link>
                </motion.div>
              ))
            ) : (
              <>
                {[1, 2, 3].map((index) => (
                  <motion.div
                    key={index}
                    className="animate-pulse bg-white rounded-xl overflow-hidden shadow-sm"
                    variants={fadeInUp}
                    custom={index}
                  >
                    <div className="bg-gray-300 h-48 w-full"></div>
                    <div className="p-6">
                      <div className="h-4 bg-gray-300 rounded w-1/4 mb-3"></div>
                      <div className="h-6 bg-gray-300 rounded w-3/4 mb-3"></div>
                      <div className="h-4 bg-gray-300 rounded w-full mb-2"></div>
                      <div className="h-4 bg-gray-300 rounded w-full mb-2"></div>
                      <div className="h-4 bg-gray-300 rounded w-2/3 mb-4"></div>
                      <div className="h-4 bg-gray-300 rounded w-1/4"></div>
                    </div>
                  </motion.div>
                ))}
              </>
            )}
          </motion.div>

          {/* Nút xem tất cả bài viết */}
          <motion.div
            className="text-center mt-12"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 1.2, delay: 1 }}
            viewport={{ once: true }}
          >
            <Link
              to="/blogs"
              className="relative inline-flex items-center bg-amber-300 hover:bg-amber-400 text-white font-medium py-3 px-8 rounded-full transition-all duration-300 overflow-hidden group"
            >
              <motion.span
                className="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-amber-400 to-amber-600 opacity-0 group-hover:opacity-100"
                transition={{ duration: 0.5 }}
              />
              <span className="relative z-10">Xem tất cả bài viết</span>
              <motion.svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-5 w-5 ml-2 relative z-10"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                whileHover={{ x: 5 }}
                transition={{ duration: 0.3 }}
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M17 8l4 4m0 0l-4 4m4-4H3"
                />
              </motion.svg>
            </Link>
          </motion.div>
        </div>
      </motion.section>
      {/* end blog */}

      {/* brand */}
      <motion.section
        className="bg-gray-100 py-20"
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
      >
        <div className="w-full max-w-6xl mx-auto px-6">
          <motion.div
            className="text-center mb-12"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 1.2 }}
            viewport={{ once: true }}
          >
            <h3 className="text-2xl font-semibold mb-2">
              Các thương hiệu đối tác
            </h3>
            <div className="h-1 w-20 bg-amber-500 mx-auto rounded-full" />
          </motion.div>

          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 items-center">
            {[2, 3, 4, 6, 7, 11].map((num, index) => (
              <motion.div
                key={num}
                variants={fadeInUp}
                custom={index * 0.1}
                className="bg-white p-5 rounded-lg shadow-sm flex items-center justify-center h-20"
              >
                <img
                  src={`.\\src\\assets\\img\\brands\\brand-${num}.png`}
                  alt={`Brand ${index + 1}`}
                  className="max-h-10 w-auto filter grayscale hover:grayscale-0 transition-all duration-300"
                />
              </motion.div>
            ))}
          </div>
        </div>
      </motion.section>
    </>
  );
};

export default Homes;
