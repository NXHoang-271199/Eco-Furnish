import React from "react";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import BlogSlider from "../../../components/BlogSlider";

const HomePage = () => {
  return (
    <div>
      <div class="category-area section-space">
        <div class="container">
          <div class="row masonry-layout--category">
            <div class="col-md-6 masonry-item--category">
              <div class="single-category single-category--type-two">
                <div class="single-category--type-two__image">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/categories/category-out-doors.jpg"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-category--type-two__content">
                  <h3 class="title">Outdoor</h3>
                  <span class="count">(3 items)</span>
                </div>
              </div>
            </div>
            <div class="col-md-6 masonry-item--category">
              <div class="single-category single-category--type-two">
                <div class="single-category--type-two__image">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/categories/category-living-room-masonry.jpg"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-category--type-two__content">
                  <h3 class="title">Living Room</h3>
                  <span class="count">(37 items)</span>
                </div>
              </div>
            </div>
            <div class="col-md-9 masonry-item--category">
              <div class="single-category single-category--type-two">
                <div class="single-category--type-two__image">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/categories/category-bathroom.jpg"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-category--type-two__content">
                  <h3 class="title">Bathroom</h3>
                  <span class="count">(2 items)</span>
                </div>
              </div>
            </div>
            <div class="col-md-3 masonry-item--category grid-sizer">
              <div class="single-category single-category--type-two">
                <div class="single-category--type-two__image">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/categories/category-dinning-chairs.jpg"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-category--type-two__content">
                  <h3 class="title">Dining Chairs</h3>
                  <span class="count">(3 items)</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="product-double-row-area">
        <div class="container">
          <div class="row">
            <div class="col-lg-12">
              <div class="section-title-area text-center">
                <h2 class="section-title">Featured Items</h2>
                <p class="section-subtitle">
                  From a welcoming new collection of lounge seating to an
                  executive chair that melds craft with ergonomics, We want to
                  show you some of our featured products here
                </p>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="product-row-wrapper">
                <div class="row">
                  <div class="col-lg-3 col-md-4 col-sm-6 col-custom-sm-6">
                    <div class="single-grid-product">
                      <div class="single-grid-product__image">
                        <div class="product-badge-wrapper">
                          <span class="onsale">-17%</span>
                          <span class="hot">Hot</span>
                        </div>
                        <a href="product-details-basic.html" class="image-wrap">
                          <img
                            src="./src/assets/img/products/product-9-1-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                          <img
                            src="./src/assets/img/products/product-9-2-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                        </a>
                        <div class="product-hover-icon-wrapper">
                          <span class="single-icon single-icon--quick-view">
                            <a
                              class="cd-trigger"
                              href="#qv-1"
                              data-tippy="Quick View"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-search"></i>
                            </a>
                          </span>
                          <span class="single-icon single-icon--add-to-cart">
                            <a
                              href="#"
                              data-tippy="Add to cart"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-shopping-basket"></i>{" "}
                              <span>ADD TO CART</span>
                            </a>
                          </span>
                          <span class="single-icon single-icon--compare">
                            <a
                              href="#"
                              data-tippy="Compare"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-exchange"></i>
                            </a>
                          </span>
                        </div>
                      </div>
                      <div class="single-grid-product__content">
                        <h3 class="title">
                          <a href="product-details-basic.html">Lighting Lamp</a>
                        </h3>
                        <div class="price">
                          <span class="main-price discounted">$145</span>{" "}
                          <span class="discounted-price">$110</span>
                        </div>
                        <div class="rating">
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star"></i>
                        </div>
                        <div class="color">
                          <ul>
                            <li>
                              <a
                                class="active"
                                href="#"
                                data-tippy="Black"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker black"></span>
                              </a>
                            </li>
                            <li>
                              <a
                                href="#"
                                data-tippy="Blue"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker blue"></span>
                              </a>
                            </li>
                            <li>
                              <a
                                href="#"
                                data-tippy="Brown"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker brown"></span>
                              </a>
                            </li>
                          </ul>
                        </div>
                        <a
                          href="#"
                          class="favorite-icon"
                          data-tippy="Add to Wishlist"
                          data-tippy-inertia="true"
                          data-tippy-animation="shift-away"
                          data-tippy-delay="50"
                          data-tippy-arrow="true"
                          data-tippy-theme="sharpborder"
                          data-tippy-placement="left"
                        >
                          <i class="fa fa-heart-o"></i>
                          <i class="fa fa-heart"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-4 col-sm-6 col-custom-sm-6">
                    <div class="single-grid-product">
                      <div class="single-grid-product__image">
                        <a href="product-details-basic.html" class="image-wrap">
                          <img
                            src="./src/assets/img/products/product-10-1-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                        </a>
                        <div class="product-hover-icon-wrapper">
                          <span class="single-icon single-icon--quick-view">
                            <a
                              class="cd-trigger"
                              href="#qv-1"
                              data-tippy="Quick View"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-search"></i>
                            </a>
                          </span>
                          <span class="single-icon single-icon--add-to-cart">
                            <a
                              href="#"
                              data-tippy="Add to cart"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-shopping-basket"></i>{" "}
                              <span>ADD TO CART</span>
                            </a>
                          </span>
                          <span class="single-icon single-icon--compare">
                            <a
                              href="#"
                              data-tippy="Compare"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-exchange"></i>
                            </a>
                          </span>
                        </div>
                      </div>
                      <div class="single-grid-product__content">
                        <h3 class="title">
                          <a href="product-details-basic.html">
                            Jane Lauren Design Chair
                          </a>
                        </h3>
                        <div class="price">
                          <span class="main-price">$98</span>
                        </div>
                        <div class="rating">
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star"></i>
                        </div>

                        <a
                          href="#"
                          class="favorite-icon"
                          data-tippy="Add to Wishlist"
                          data-tippy-inertia="true"
                          data-tippy-animation="shift-away"
                          data-tippy-delay="50"
                          data-tippy-arrow="true"
                          data-tippy-theme="sharpborder"
                          data-tippy-placement="left"
                        >
                          <i class="fa fa-heart-o"></i>
                          <i class="fa fa-heart"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-4 col-sm-6 col-custom-sm-6">
                    <div class="single-grid-product">
                      <div class="single-grid-product__image">
                        <div class="product-badge-wrapper">
                          <span class="hot">Hot</span>
                        </div>
                        <a href="product-details-basic.html" class="image-wrap">
                          <img
                            src="./src/assets/img/products/product-11-1-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                          <img
                            src="./src/assets/img/products/product-11-2-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                        </a>
                        <div class="product-hover-icon-wrapper">
                          <span class="single-icon single-icon--quick-view">
                            <a
                              class="cd-trigger"
                              href="#qv-1"
                              data-tippy="Quick View"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-search"></i>
                            </a>
                          </span>
                          <span class="single-icon single-icon--add-to-cart">
                            <a
                              href="#"
                              data-tippy="Add to cart"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-shopping-basket"></i>{" "}
                              <span>ADD TO CART</span>
                            </a>
                          </span>
                          <span class="single-icon single-icon--compare">
                            <a
                              href="#"
                              data-tippy="Compare"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-exchange"></i>
                            </a>
                          </span>
                        </div>
                      </div>
                      <div class="single-grid-product__content">
                        <h3 class="title">
                          <a href="product-details-basic.html">
                            Jane Lauren Gregory Chair
                          </a>
                        </h3>
                        <div class="price">
                          <span class="main-price discounted">$125</span>{" "}
                          <span class="discounted-price">$90</span>
                        </div>
                        <div class="rating">
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star"></i>
                        </div>

                        <a
                          href="#"
                          class="favorite-icon"
                          data-tippy="Add to Wishlist"
                          data-tippy-inertia="true"
                          data-tippy-animation="shift-away"
                          data-tippy-delay="50"
                          data-tippy-arrow="true"
                          data-tippy-theme="sharpborder"
                          data-tippy-placement="left"
                        >
                          <i class="fa fa-heart-o"></i>
                          <i class="fa fa-heart"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-4 col-sm-6 col-custom-sm-6">
                    <div class="single-grid-product">
                      <div class="single-grid-product__image">
                        <div class="product-badge-wrapper">
                          <span class="onsale">-10%</span>
                        </div>
                        <a href="product-details-basic.html" class="image-wrap">
                          <img
                            src="./src/assets/img/products/product-12-1-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                          <img
                            src="./src/assets/img/products/product-12-2-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                        </a>
                        <div class="product-hover-icon-wrapper">
                          <span class="single-icon single-icon--quick-view">
                            <a
                              class="cd-trigger"
                              href="#qv-1"
                              data-tippy="Quick View"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-search"></i>
                            </a>
                          </span>
                          <span class="single-icon single-icon--add-to-cart">
                            <a
                              href="#"
                              data-tippy="Add to cart"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-shopping-basket"></i>{" "}
                              <span>ADD TO CART</span>
                            </a>
                          </span>
                          <span class="single-icon single-icon--compare">
                            <a
                              href="#"
                              data-tippy="Compare"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-exchange"></i>
                            </a>
                          </span>
                        </div>
                      </div>
                      <div class="single-grid-product__content">
                        <h3 class="title">
                          <a href="product-details-basic.html">
                            Candice Desk Lamp
                          </a>
                        </h3>
                        <div class="price">
                          <span class="main-price">$100</span>
                        </div>
                        <div class="rating">
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star"></i>
                        </div>

                        <a
                          href="#"
                          class="favorite-icon"
                          data-tippy="Add to Wishlist"
                          data-tippy-inertia="true"
                          data-tippy-animation="shift-away"
                          data-tippy-delay="50"
                          data-tippy-arrow="true"
                          data-tippy-theme="sharpborder"
                          data-tippy-placement="left"
                        >
                          <i class="fa fa-heart-o"></i>
                          <i class="fa fa-heart"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-4 col-sm-6 col-custom-sm-6">
                    <div class="single-grid-product">
                      <div class="single-grid-product__image">
                        <a href="product-details-basic.html" class="image-wrap">
                          <img
                            src="./src/assets/img/products/product-13-1-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                          <img
                            src="./src/assets/img/products/product-13-2-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                        </a>
                        <div class="product-hover-icon-wrapper">
                          <span class="single-icon single-icon--quick-view">
                            <a
                              class="cd-trigger"
                              href="#qv-1"
                              data-tippy="Quick View"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-search"></i>
                            </a>
                          </span>
                          <span class="single-icon single-icon--add-to-cart">
                            <a
                              href="#"
                              data-tippy="Add to cart"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-shopping-basket"></i>{" "}
                              <span>ADD TO CART</span>
                            </a>
                          </span>
                          <span class="single-icon single-icon--compare">
                            <a
                              href="#"
                              data-tippy="Compare"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-exchange"></i>
                            </a>
                          </span>
                        </div>
                      </div>
                      <div class="single-grid-product__content">
                        <h3 class="title">
                          <a href="product-details-basic.html">
                            Ovora Step stool
                          </a>
                        </h3>
                        <div class="price">
                          <span class="main-price discounted">$185</span>{" "}
                          <span class="discounted-price">$140</span>
                        </div>
                        <div class="rating">
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star"></i>
                        </div>
                        <div class="color">
                          <ul>
                            <li>
                              <a
                                class="active"
                                href="#"
                                data-tippy="Black"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker black"></span>
                              </a>
                            </li>
                            <li>
                              <a
                                href="#"
                                data-tippy="Blue"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker blue"></span>
                              </a>
                            </li>
                            <li>
                              <a
                                href="#"
                                data-tippy="Brown"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker brown"></span>
                              </a>
                            </li>
                          </ul>
                        </div>
                        <a
                          href="#"
                          class="favorite-icon"
                          data-tippy="Add to Wishlist"
                          data-tippy-inertia="true"
                          data-tippy-animation="shift-away"
                          data-tippy-delay="50"
                          data-tippy-arrow="true"
                          data-tippy-theme="sharpborder"
                          data-tippy-placement="left"
                        >
                          <i class="fa fa-heart-o"></i>
                          <i class="fa fa-heart"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-4 col-sm-6 col-custom-sm-6">
                    <div class="single-grid-product">
                      <div class="single-grid-product__image">
                        <div class="product-badge-wrapper">
                          <span class="onsale">-17%</span>
                          <span class="hot">Hot</span>
                        </div>
                        <a href="product-details-basic.html" class="image-wrap">
                          <img
                            src="./src/assets/img/products/product-14-1-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                          <img
                            src="./src/assets/img/products/product-14-2-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                        </a>
                        <div class="product-hover-icon-wrapper">
                          <span class="single-icon single-icon--quick-view">
                            <a
                              class="cd-trigger"
                              href="#qv-1"
                              data-tippy="Quick View"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-search"></i>
                            </a>
                          </span>
                          <span class="single-icon single-icon--add-to-cart">
                            <a
                              href="#"
                              data-tippy="Add to cart"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-shopping-basket"></i>{" "}
                              <span>ADD TO CART</span>
                            </a>
                          </span>
                          <span class="single-icon single-icon--compare">
                            <a
                              href="#"
                              data-tippy="Compare"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-exchange"></i>
                            </a>
                          </span>
                        </div>
                      </div>
                      <div class="single-grid-product__content">
                        <h3 class="title">
                          <a href="product-details-basic.html">
                            Jane Lauren Carson Chair
                          </a>
                        </h3>
                        <div class="price">
                          <span class="main-price">$145</span>
                        </div>
                        <div class="rating">
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star"></i>
                        </div>

                        <a
                          href="#"
                          class="favorite-icon"
                          data-tippy="Add to Wishlist"
                          data-tippy-inertia="true"
                          data-tippy-animation="shift-away"
                          data-tippy-delay="50"
                          data-tippy-arrow="true"
                          data-tippy-theme="sharpborder"
                          data-tippy-placement="left"
                        >
                          <i class="fa fa-heart-o"></i>
                          <i class="fa fa-heart"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-4 col-sm-6 col-custom-sm-6">
                    <div class="single-grid-product">
                      <div class="single-grid-product__image">
                        <div class="product-badge-wrapper">
                          <span class="onsale">-17%</span>
                          <span class="hot">Hot</span>
                        </div>
                        <a href="product-details-basic.html" class="image-wrap">
                          <img
                            src="./src/assets/img/products/product-15-1-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                          <img
                            src="./src/assets/img/products/product-15-2-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                        </a>
                        <div class="product-hover-icon-wrapper">
                          <span class="single-icon single-icon--quick-view">
                            <a
                              class="cd-trigger"
                              href="#qv-1"
                              data-tippy="Quick View"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-search"></i>
                            </a>
                          </span>
                          <span class="single-icon single-icon--add-to-cart">
                            <a
                              href="#"
                              data-tippy="Add to cart"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-shopping-basket"></i>{" "}
                              <span>ADD TO CART</span>
                            </a>
                          </span>
                          <span class="single-icon single-icon--compare">
                            <a
                              href="#"
                              data-tippy="Compare"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-exchange"></i>
                            </a>
                          </span>
                        </div>
                      </div>
                      <div class="single-grid-product__content">
                        <h3 class="title">
                          <a href="product-details-basic.html">
                            Alexa Classic Towels
                          </a>
                        </h3>
                        <div class="price">
                          <span class="main-price discounted">$14</span>{" "}
                          <span class="discounted-price">$11</span>
                        </div>
                        <div class="rating">
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star"></i>
                        </div>
                        <div class="color">
                          <ul>
                            <li>
                              <a
                                class="active"
                                href="#"
                                data-tippy="Black"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker black"></span>
                              </a>
                            </li>
                            <li>
                              <a
                                href="#"
                                data-tippy="Blue"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker blue"></span>
                              </a>
                            </li>
                            <li>
                              <a
                                href="#"
                                data-tippy="Brown"
                                data-tippy-inertia="true"
                                data-tippy-animation="shift-away"
                                data-tippy-delay="50"
                                data-tippy-arrow="true"
                                data-tippy-theme="roundborder"
                              >
                                <span class="color-picker brown"></span>
                              </a>
                            </li>
                          </ul>
                        </div>
                        <a
                          href="#"
                          class="favorite-icon"
                          data-tippy="Add to Wishlist"
                          data-tippy-inertia="true"
                          data-tippy-animation="shift-away"
                          data-tippy-delay="50"
                          data-tippy-arrow="true"
                          data-tippy-theme="sharpborder"
                          data-tippy-placement="left"
                        >
                          <i class="fa fa-heart-o"></i>
                          <i class="fa fa-heart"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-4 col-sm-6 col-custom-sm-6">
                    <div class="single-grid-product">
                      <div class="single-grid-product__image">
                        <a href="product-details-basic.html" class="image-wrap">
                          <img
                            src="./src/assets/img/products/product-16-1-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                          <img
                            src="./src/assets/img/products/product-16-2-270x360.jpg"
                            class="img-fluid"
                            alt=""
                          />
                        </a>
                        <div class="product-hover-icon-wrapper">
                          <span class="single-icon single-icon--quick-view">
                            <a
                              class="cd-trigger"
                              href="#qv-1"
                              data-tippy="Quick View"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-search"></i>
                            </a>
                          </span>
                          <span class="single-icon single-icon--add-to-cart">
                            <a
                              href="#"
                              data-tippy="Add to cart"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-shopping-basket"></i>{" "}
                              <span>ADD TO CART</span>
                            </a>
                          </span>
                          <span class="single-icon single-icon--compare">
                            <a
                              href="#"
                              data-tippy="Compare"
                              data-tippy-inertia="true"
                              data-tippy-animation="shift-away"
                              data-tippy-delay="50"
                              data-tippy-arrow="true"
                              data-tippy-theme="sharpborder"
                            >
                              <i class="fa fa-exchange"></i>
                            </a>
                          </span>
                        </div>
                      </div>
                      <div class="single-grid-product__content">
                        <h3 class="title">
                          <a href="product-details-basic.html">
                            Olivia Shayn Cover Chair
                          </a>
                        </h3>
                        <div class="price">
                          <span class="main-price">$98</span>
                        </div>
                        <div class="rating">
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star active"></i>
                          <i class="fa fa-star"></i>
                        </div>

                        <a
                          href="#"
                          class="favorite-icon"
                          data-tippy="Add to Wishlist"
                          data-tippy-inertia="true"
                          data-tippy-animation="shift-away"
                          data-tippy-delay="50"
                          data-tippy-arrow="true"
                          data-tippy-theme="sharpborder"
                          data-tippy-placement="left"
                        >
                          <i class="fa fa-heart-o"></i>
                          <i class="fa fa-heart"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <BlogSlider />

      <div class="brand-logo-slider-area bg--light-grey">
        <div class="container wide">
          <div class="row">
            <div class="col-lg-12">
              <div
                class="brand-logo-slider-wrapper theme-slick-slider"
                data-slick-setting='{
                        "slidesToShow": 6,
                        "arrows": true,
                        "autoplay": false,
                        "autoplaySpeed": 5000,
                        "speed": 500,
                        "prevArrow": {"buttonClass": "slick-prev", "iconClass": "fa fa-angle-left" },
                        "nextArrow": {"buttonClass": "slick-next", "iconClass": "fa fa-angle-right" }
                    }'
                data-slick-responsive='[
                        {"breakpoint":1501, "settings": {"slidesToShow": 5} },
                        {"breakpoint":1199, "settings": {"slidesToShow": 4} },
                        {"breakpoint":991, "settings": {"slidesToShow": 3} },
                        {"breakpoint":767, "settings": {"slidesToShow": 2} },
                        {"breakpoint":575, "settings": {"slidesToShow": 2} },
                        {"breakpoint":479, "settings": {"slidesToShow": 1} }
                    ]'
              >
                <div class="single-brand-logo">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/brands/brand-2.png"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-brand-logo">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/brands/brand-3.png"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-brand-logo">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/brands/brand-4.png"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-brand-logo">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/brands/brand-6.png"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-brand-logo">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/brands/brand-7.png"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-brand-logo">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/brands/brand-11.png"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>

                <div class="single-brand-logo">
                  <a href="shop-left-sidebar.html">
                    <img
                      src="./src/assets/img/brands/brand-12.png"
                      class="img-fluid"
                      alt=""
                    />
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default HomePage;
