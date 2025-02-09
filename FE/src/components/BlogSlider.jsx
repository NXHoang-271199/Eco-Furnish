import React from "react";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import Slider from "react-slick";
import "../App.css";
const BlogSlider = () => {
  const settings = {
    dots: false,
    infinite: true,
    speed: 500,
    slidesToShow: 3,
    slidesToScroll: 3,
    arrows: true,
    autoplay: false,
    autoplaySpeed: 5000,
    responsive: [
      { breakpoint: 1501, settings: { slidesToShow: 3, arrows: false } },
      { breakpoint: 1199, settings: { slidesToShow: 3, arrows: false } },
      {
        breakpoint: 991,
        settings: { slidesToShow: 2, arrows: false, slidesToScroll: 2 },
      },
      {
        breakpoint: 767,
        settings: { slidesToShow: 1, arrows: false, slidesToScroll: 1 },
      },
      {
        breakpoint: 575,
        settings: { slidesToShow: 1, arrows: false, slidesToScroll: 1 },
      },
      {
        breakpoint: 479,
        settings: { slidesToShow: 1, arrows: false, slidesToScroll: 1 },
      },
    ],
  };

  return (
    <div>
      <div className="blog-slider-area">
        <div className="container">
          <div className="row">
            <div className="col-lg-12 text-center">
              <h2 className="section-title">From Our Blog</h2>
            </div>
          </div>
          <Slider
            {...settings}
            className="blog-slider-wrapper theme-slick-slider"
          >
            <div className="single-slider-blog-post ">
              <div className="single-slider-blog-post__image mx-2">
                <a href="blog-post-left-sidebar.html">
                  <img
                    src="/src/assets/img/blog/slider/one-550x360.jpg"
                    className="img-fluid"
                    alt=""
                  />
                </a>
              </div>
              <div className="single-slider-blog-post__content mx-2">
                <h3 className="post-title">
                  <a href="blog-post-left-sidebar.html">
                    The Difference Between Green Furniture and Sustainable
                    Furniture
                  </a>
                </h3>
                <p className="post-meta">
                  By <a href="#">admin</a> | <a href="#">January 21, 2019</a>
                </p>
                <p className="post-excerpt">
                  Many furniture companies claim their products are “green”...
                </p>
                <a
                  href="blog-post-left-sidebar.html"
                  className="blog-readmore-link"
                >
                  Read more <i className="fa fa-caret-right"></i>
                </a>
              </div>
            </div>

            <div className="single-slider-blog-post ">
              <div className="single-slider-blog-post__image  mx-2">
                <a href="blog-post-left-sidebar.html">
                  <img
                    src="/src/assets/img/blog/slider/two-550x360.jpg"
                    className="img-fluid"
                    alt=""
                  />
                </a>
              </div>
              <div className="single-slider-blog-post__content  mx-2">
                <h3 className="post-title">
                  <a href="blog-post-left-sidebar.html">
                    A Busy Person Guide To Getting Organized Room
                  </a>
                </h3>
                <p className="post-meta">
                  By <a href="#">admin</a> | <a href="#">January 21, 2019</a>
                </p>
                <p className="post-excerpt">
                  Many furniture companies claim their products are “green”...
                </p>
                <a
                  href="blog-post-left-sidebar.html"
                  className="blog-readmore-link"
                >
                  Read more <i className="fa fa-caret-right"></i>
                </a>
              </div>
            </div>
          </Slider>
        </div>
      </div>
    </div>
  );
};

export default BlogSlider;
