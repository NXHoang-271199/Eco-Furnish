import { useState, useEffect, useCallback } from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import axios from "axios";

const API_URL = "http://localhost:8000/api";

const Blogs = () => {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [activeCategory, setActiveCategory] = useState("all");
  const [categories, setCategories] = useState([]);

  const fetchAllPosts = useCallback(async () => {
    setLoading(true);
    setActiveCategory("all");
    setError(null);
    try {
      const response = await axios.get(`${API_URL}/posts`);
      console.log("Dữ liệu tất cả bài viết:", response.data);

      if (response.data && response.data.status === "success") {
        const postsData = response.data.data || [];
        console.log("Số lượng bài viết tải được:", postsData.length);
        setPosts(postsData);
      } else {
        console.error("API trả về cấu trúc dữ liệu không đúng:", response.data);
        setPosts([]);
      }
      setLoading(false);
    } catch (err) {
      console.error("Lỗi khi lấy tất cả bài viết:", err);
      setError("Không thể tải bài viết. Vui lòng thử lại sau.");
      setPosts([]);
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchAllPosts();

    const fetchCategories = async () => {
      try {
        const response = await axios.get(`${API_URL}/category-posts`);
        console.log("Dữ liệu categories từ API:", response.data);
        if (response.data && response.data.status === "success") {
          const categoriesData = response.data.data || [];
          console.log("Chi tiết categories:", categoriesData);
          setCategories(categoriesData);
        } else {
          console.error(
            "API categories trả về cấu trúc không đúng:",
            response.data
          );
          setCategories([]);
        }
      } catch (err) {
        console.error("Lỗi khi lấy danh mục bài viết:", err);
        setCategories([]);
      }
    };

    fetchCategories();
  }, [fetchAllPosts]);

  const handleCategoryClick = useCallback(
    async (categorySlug) => {
      setLoading(true);
      setActiveCategory(categorySlug);
      setError(null);
      try {
        if (categorySlug === "all") {
          await fetchAllPosts();
          return;
        }

        const categoryInfo = categories.find((c) => c.slug === categorySlug);
        if (!categoryInfo) {
          setError(`Không tìm thấy thông tin danh mục "${categorySlug}"`);
          setPosts([]);
          setLoading(false);
          return;
        }

        console.log(
          "Thực hiện lọc danh mục ID:",
          categoryInfo.id,
          "Tên:",
          categoryInfo.title
        );

        try {
          // Lấy tất cả bài viết
          const response = await axios.get(`${API_URL}/posts`);
          console.log("Đã lấy tất cả bài viết:", response.data);

          if (response.data && response.data.status === "success") {
            const allPosts = response.data.data || [];

            // Lưu lại danh sách tất cả bài viết để debug
            console.log("Tổng số bài viết:", allPosts.length);

            // Đặt flag đang xử lý
            setPosts([]);
            setError(null);

            // Mảng lưu bài viết đã lọc
            let filteredPosts = [];

            // Lọc bài viết theo danh mục
            for (let i = 0; i < allPosts.length; i++) {
              const post = allPosts[i];
              try {
                console.log(
                  `Kiểm tra bài viết ${i + 1}/${allPosts.length}: ${post.title}`
                );

                // Lấy chi tiết bài viết để kiểm tra danh mục
                const detailResponse = await axios.get(
                  `${API_URL}/posts/${post.slug}`
                );

                if (
                  detailResponse.data &&
                  detailResponse.data.status === "success"
                ) {
                  const postDetail = detailResponse.data.data;

                  // Kiểm tra trường category trong chi tiết bài viết
                  if (postDetail.category && postDetail.category.id) {
                    console.log(
                      `Bài viết '${post.title}' thuộc danh mục: ${postDetail.category.title} (ID: ${postDetail.category.id})`
                    );

                    // So sánh với ID danh mục hiện tại
                    if (
                      parseInt(postDetail.category.id) ===
                      parseInt(categoryInfo.id)
                    ) {
                      console.log(
                        `Thêm bài viết '${post.title}' vào danh sách hiển thị`
                      );
                      filteredPosts.push(post);
                    }
                  } else {
                    console.log(
                      `Bài viết '${post.title}' không có thông tin danh mục`
                    );
                  }
                }
              } catch (error) {
                console.error(
                  `Lỗi khi lấy chi tiết bài viết ${post.slug}:`,
                  error
                );
              }
            }

            console.log(
              `Đã tìm thấy ${filteredPosts.length} bài viết thuộc danh mục ${categoryInfo.title}`
            );

            if (filteredPosts.length === 0) {
              setError(
                `Không có bài viết nào trong danh mục "${categoryInfo.title}"`
              );
            }

            setPosts(filteredPosts);
          } else {
            console.error("API trả về cấu trúc dữ liệu không đúng");
            setPosts([]);
            setError("Không thể tải bài viết. Vui lòng thử lại sau.");
          }
        } catch (err) {
          console.error("Lỗi khi lọc bài viết theo danh mục:", err);
          setPosts([]);
          setError("Lỗi kết nối API. Vui lòng thử lại sau.");
        }

        setLoading(false);
      } catch (err) {
        console.error("Lỗi tổng quát khi xử lý danh mục:", err);
        setError("Không thể tải bài viết. Vui lòng thử lại sau.");
        setPosts([]);
        setLoading(false);
      }
    },
    [fetchAllPosts, categories]
  );

  // Hàm format ngày tháng
  const formatDate = (dateString) => {
    const options = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    };
    return new Date(dateString).toLocaleDateString("vi-VN", options);
  };

  return (
    <div className="bg-gray-100 mt-10">
      <main className="max-w-6xl mx-auto px-4 py-8">
        {/* <section className="relative">
          
        {/* Tiêu đề và mô tả trang */}
        <div className="mt-10 mb-8 text-center">
          <h1 className="text-3xl font-bold mb-4">Blog Nội Thất</h1>
          <p className="text-gray-600 max-w-3xl mx-auto">
            {activeCategory === "all" &&
              "Khám phá những ý tưởng và xu hướng mới nhất về thiết kế nội thất cho ngôi nhà của bạn."}
            {activeCategory !== "all" &&
              (categories.find((c) => c.slug === activeCategory)?.description ||
                "Danh mục bài viết")}
          </p>
        </div>

        {/* Section danh mục và bài viết */}
        <section className="mt-16 my-6">
          {/* Danh mục bài viết - hiển thị ngang ở trên */}
          <div className="w-full mb-8">
            <h2 className="font-medium text-xl mb-5 text-center">
              Danh mục bài viết
            </h2>
            <div className="flex flex-wrap justify-center gap-4">
              <button
                className={`px-4 py-2 rounded-full font-medium transition-all duration-200 ${
                  activeCategory === "all"
                    ? "bg-amber-300 text-white shadow-md"
                    : "bg-white text-gray-600 hover:bg-amber-100"
                }`}
                onClick={() => handleCategoryClick("all")}
              >
                Tất cả bài viết
              </button>

              {categories.map((category) => (
                <button
                  key={category.id}
                  className={`px-4 py-2 rounded-full font-medium transition-all duration-200 ${
                    activeCategory === category.slug
                      ? "bg-amber-300 text-white shadow-md"
                      : "bg-white text-gray-600 hover:bg-amber-100"
                  }`}
                  onClick={() => handleCategoryClick(category.slug)}
                >
                  {category.title}
                </button>
              ))}
            </div>
          </div>

          {/* Phần bài viết chính - chiếm toàn bộ chiều rộng */}
          <div className="w-full mt-6">
            {loading ? (
              <div className="flex flex-col justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                <p className="text-gray-600">Đang tải dữ liệu bài viết...</p>
              </div>
            ) : error ? (
              <div className="text-center py-10">
                <h3 className="text-xl font-semibold mb-2">
                  {activeCategory !== "all" &&
                    `Danh mục: ${
                      categories.find((c) => c.slug === activeCategory)
                        ?.title || activeCategory
                    }`}
                </h3>
                <p className="text-red-500">{error}</p>
              </div>
            ) : (
              <div>
                {activeCategory !== "all" && (
                  <h3 className="text-xl font-semibold mb-6 pb-2 border-b border-gray-200 text-center">
                    Danh mục:{" "}
                    <span className="text-amber-500">
                      {categories.find((c) => c.slug === activeCategory)
                        ?.title || activeCategory}
                    </span>
                  </h3>
                )}

                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-8">
                  {posts.length > 0 ? (
                    posts.map((post) => (
                      <Link
                        to={`/blog-detail/${post.slug}`}
                        key={post.id || post.slug}
                        className="block group"
                      >
                        <div className="bg-white rounded-xl shadow-sm h-full hover:shadow-md transition-all duration-300 overflow-hidden group-hover:translate-y-[-5px]">
                          <div className="relative overflow-hidden rounded-t-xl">

                            {/* Ảnh thumbnail */}
                            <div className="aspect-w-16 aspect-h-9 relative">
                              <img
                                src={
                                  post.thumbnail
                                    ? post.thumbnail.startsWith("http")
                                      ? post.thumbnail
                                      : post.thumbnail.startsWith("/")
                                      ? `http://localhost:8000${post.thumbnail}`
                                      : `http://localhost:8000/${post.thumbnail}`
                                    : "http://localhost:5173/src/assets/img/banners/homepage01-slide2.jpg"
                                }
                                alt={post.title}
                                className="w-full h-56 object-cover transition-transform duration-500 group-hover:scale-110"
                                onError={(e) => {
                                  console.log(
                                    "Lỗi khi tải ảnh:",
                                    post.thumbnail
                                  );
                                  e.target.src =
                                    "http://localhost:5173/src/assets/img/banners/homepage01-slide2.jpg";
                                }}
                              />
                              <div className="absolute inset-0 bg-gradient-to-b from-transparent to-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                          </div>

                          <div className="p-5">
                            {/* Ngày đăng */}
                            <div className="flex items-center mb-3 text-gray-500 text-sm">
                              <i className="far fa-clock mr-2"></i>
                              <span>{formatDate(post.created_at)}</span>
                            </div>

                            {/* Tiêu đề bài viết */}
                            <h2 className="text-lg font-bold text-gray-800 line-clamp-2 group-hover:text-amber-500 transition-colors duration-200">
                              {post.title}
                            </h2>

                            {/* Mô tả ngắn (nếu có) */}
                            {post.description && (
                              <p className="mt-2 text-gray-600 text-sm line-clamp-3">
                                {post.description}
                              </p>
                            )}

                            {/* Nút đọc thêm */}
                            <div className="mt-4 flex justify-end">
                              <span className="inline-flex items-center text-amber-500 text-sm font-medium group-hover:text-amber-600">
                                Xem thêm
                                <i className="fas fa-arrow-right ml-2 transition-transform duration-300 group-hover:translate-x-1"></i>
                              </span>
                            </div>
                          </div>
                        </div>
                      </Link>
                    ))
                  ) : (
                    <div className="col-span-3 py-16 px-4">
                      <div className="text-center max-w-lg mx-auto bg-white rounded-xl p-8 shadow-sm border border-gray-100">
                        <i className="fas fa-newspaper text-5xl text-gray-300 mb-4"></i>
                        <h3 className="text-xl font-semibold text-gray-800 mb-2">
                          Chưa có bài viết nào
                        </h3>
                        <p className="text-gray-500">
                          {activeCategory === "all"
                            ? "Hiện tại chưa có bài viết nào được đăng tải. Vui lòng quay lại sau."
                            : `Chưa có bài viết nào thuộc danh mục "${
                                categories.find(
                                  (c) => c.slug === activeCategory
                                )?.title || activeCategory
                              }". Vui lòng chọn danh mục khác.`}
                        </p>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )}

            {posts.length > 0 && (
              <div className="flex justify-center mt-12">
                <button className="bg-white text-gray-800 font-medium px-8 py-3 rounded-lg border border-gray-200 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-600 transition-colors shadow-sm">
                  <span className="flex items-center">
                    <span>Xem thêm bài viết</span>
                    <i className="fas fa-chevron-down ml-2"></i>
                  </span>
                </button>
              </div>
            )}
          </div>
        </section>
      </main>
    </div>
  );
};

export default Blogs;

