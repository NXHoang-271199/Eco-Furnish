import { useState, useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import axios from "axios";

const API_URL = "http://localhost:8000/api";
const BlogsDetail = () => {
  const { slug } = useParams();
  const [post, setPost] = useState(null);
  const [relatedPosts, setRelatedPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchPostDetail = async () => {
      try {
        setLoading(true);
        const response = await axios.get(`${API_URL}/posts/${slug}`);
        console.log("Chi tiết bài viết:", response.data);

        if (response.data && response.data.status === "success") {
          setPost(response.data.data);
          if (response.data.data && response.data.data.category_id) {
            const relatedResponse = await axios.get(
              `${API_URL}/posts/category/${response.data.data.category_id}`
            );
            console.log("Bài viết liên quan:", relatedResponse.data);

            if (
              relatedResponse.data &&
              relatedResponse.data.status === "success"
            ) {
              const filteredRelatedPosts = relatedResponse.data.data.filter(
                (relatedPost) => relatedPost.id !== response.data.data.id
              );
              setRelatedPosts(filteredRelatedPosts.slice(0, 5));
            }
          }
        } else {
          setError("Không thể tải thông tin bài viết");
        }
        setLoading(false);
      } catch (err) {
        console.error("Lỗi khi tải chi tiết bài viết:", err);
        setError("Đã xảy ra lỗi khi tải bài viết. Vui lòng thử lại sau.");
        setLoading(false);
      }
    };

    if (slug) {
      fetchPostDetail();
    } else {
      setError("Không tìm thấy bài viết với đường dẫn này");
      setLoading(false);
    }
  }, [slug]);
  const formatDate = (dateString) => {
    if (!dateString) return "";
    const options = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    };
    return new Date(dateString).toLocaleDateString("vi-VN", options);
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-[50vh]">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error || !post) {
    return (
      <div className="text-center py-12 px-4">
        <h1 className="text-2xl font-bold text-red-500 mb-4">
          {error || "Không tìm thấy bài viết"}
        </h1>
        <Link
          to="/blogs"
          className="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Quay lại trang blog
        </Link>
      </div>
    );
  }

  return (
    <div className="font-roboto">
      <div className="bg-blue-100 text-center py-2 px-4">
        <p className="text-blue-600 text-sm md:text-base">
          Chúc mừng giáng sinh! – Chúc bạn một mùa sinh an lành{" "}
          <a className="text-blue-800 underline ml-1" href="#">
            Mua Đồ Ngay
          </a>
        </p>
      </div>
      <div className="container mt-4 md:mt-10 mx-auto px-4 py-2 md:py-4 overflow-x-auto">
        <nav className="text-gray-600 text-xs md:text-sm whitespace-nowrap">
          <Link className="hover:underline" to="/">
            Trang chủ
          </Link>
          {" > "}
          <Link className="hover:underline" to="/blogs">
            Blog
          </Link>
          {" > "}
          <span className="text-gray-800">{post.title}</span>
        </nav>
      </div>
      <div className="container mx-auto px-4 md:px-6 lg:px-8">
        <h1 className="text-2xl md:text-3xl font-bold mb-3 md:mb-4">
          {post.title}
        </h1>

        <div className="flex flex-wrap items-center text-gray-600 mb-3 md:mb-4 text-sm">
          <span className="mr-2 mb-1 md:mb-0">
            {post.author_name || "Eco-Furnish"}
          </span>
          <span className="mb-1 md:mb-0">
            <i className="fas fa-calendar-alt mr-1"></i>
            {formatDate(post.created_at)}
          </span>
        </div>

        <div className="mb-4 overflow-hidden rounded-lg">
          <img
            alt={post.title}
            className="w-full transition-transform duration-300 hover:scale-95 object-cover"
            src={
              post.thumbnail
                ? post.thumbnail.startsWith("http")
                  ? post.thumbnail
                  : post.thumbnail.startsWith("/")
                  ? `http://localhost:8000${post.thumbnail}`
                  : `http://localhost:8000/${post.thumbnail}`
                : "http://localhost:5173/src/assets/img/banners/homepage01-slide2.jpg"
            }
            onError={(e) => {
              console.log("Lỗi khi tải ảnh:", post.thumbnail);
              e.target.src =
                "http://localhost:5173/src/assets/img/banners/homepage01-slide2.jpg";
            }}
            width="1200"
            height="600"
          />
        </div>

        <div className="prose max-w-none">
          <div
            className="text-gray-700 mb-4 text-base md:text-lg"
            dangerouslySetInnerHTML={{ __html: post.content }}
          />
        </div>

        <Link
          className="inline-block text-blue-600 hover:underline font-medium mb-8"
          to="/blogs"
        >
          Xem thêm bài viết
        </Link>
      </div>
      {relatedPosts.length > 0 && (
        <div className="bg-gray-50 py-8">
          <div className="container mx-auto px-4 md:px-6 lg:px-8">
            <h2 className="text-xl md:text-2xl font-bold mb-4 md:mb-6">
              Bài viết bạn có thể thích
            </h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-5">
              {relatedPosts.map((relatedPost) => (
                <div
                  key={relatedPost.id}
                  className="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300"
                >
                  <Link to={`/blog-detail/${relatedPost.slug}`}>
                    <div className="overflow-hidden">
                      <img
                        alt={relatedPost.title}
                        className="w-full h-48 object-cover transition-transform duration-300 hover:scale-110"
                        src={
                          relatedPost.thumbnail
                            ? relatedPost.thumbnail.startsWith("http")
                              ? relatedPost.thumbnail
                              : relatedPost.thumbnail.startsWith("/")
                              ? `http://localhost:8000${relatedPost.thumbnail}`
                              : `http://localhost:8000/${relatedPost.thumbnail}`
                            : "http://localhost:5173/src/assets/img/banners/homepage01-slide2.jpg"
                        }
                        onError={(e) => {
                          console.log(
                            "Lỗi khi tải ảnh:",
                            relatedPost.thumbnail
                          );
                          e.target.src =
                            "http://localhost:5173/src/assets/img/banners/homepage01-slide2.jpg";
                        }}
                      />
                    </div>
                    <div className="p-4">
                      <h3 className="text-base md:text-lg font-bold mb-2 line-clamp-2">
                        {relatedPost.title}
                      </h3>
                      <p className="text-gray-600 text-sm">
                        {formatDate(relatedPost.created_at)}
                      </p>
                    </div>
                  </Link>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
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
