import React from "react";

const Payment = () => {
  return (
    <div className="my-20">
      <div class="max-w-6xl mx-auto py-10 px-6 grid grid-cols-1 lg:grid-cols-3 gap-6 ">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border">
          <h2 class="text-xl font-bold">Eco-Furnish</h2>
          {/* <p class="text-gray-600">Giỏ hàng - Thông tin giao hàng</p> */}

          <div class="mt-4 border-b pb-4">
            <h3 class="font-semibold">Thông tin giao hàng</h3>
            <p class="text-sm text-gray-600">
              Đinh Tấn Đạt
              {/* (dinhtandat11112003@gmail.com) */}
            </p>
            <div class="mt-2">
              <input
                type="text"
                class="w-full border rounded-lg p-2"
                placeholder="Thêm địa chỉ mới..."
              />
            </div>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
              <input
                type="text"
                class="w-full border rounded-lg p-2"
                placeholder="Số điện thoại"
              />
              <input
                type="text"
                class="w-full border rounded-lg p-2"
                placeholder="Địa chỉ"
              />
            </div>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
              <select
                class="field-input w-full border rounded-lg p-2"
                id="customer_shipping_province"
                name="customer_shipping_province"
                fdprocessedid="bvzjxy"
              >
                <option data-code="null" value="null" selected="">
                  Chọn tỉnh/ thành{" "}
                </option>

                <option data-code="HC" value="50">
                  Hồ Chí Minh
                </option>

                <option data-code="HI" value="1">
                  Hà Nội
                </option>

                <option data-code="DA" value="32">
                  Đà Nẵng
                </option>

                <option data-code="AG" value="57">
                  An Giang
                </option>

                <option data-code="BV" value="49">
                  Bà Rịa - Vũng Tàu
                </option>

                <option data-code="BI" value="47">
                  Bình Dương
                </option>

                <option data-code="BP" value="45">
                  Bình Phước
                </option>

                <option data-code="BU" value="39">
                  Bình Thuận
                </option>

                <option data-code="BD" value="35">
                  Bình Định
                </option>

                <option data-code="BL" value="62">
                  Bạc Liêu
                </option>

                <option data-code="BG" value="15">
                  Bắc Giang
                </option>

                <option data-code="BK" value="4">
                  Bắc Kạn
                </option>

                <option data-code="BN" value="18">
                  Bắc Ninh
                </option>

                <option data-code="BT" value="53">
                  Bến Tre
                </option>

                <option data-code="CB" value="3">
                  Cao Bằng
                </option>

                <option data-code="CM" value="63">
                  Cà Mau
                </option>

                <option data-code="CN" value="59">
                  Cần Thơ
                </option>

                <option data-code="GL" value="41">
                  Gia Lai
                </option>

                <option data-code="HG" value="2">
                  Hà Giang
                </option>

                <option data-code="HM" value="23">
                  Hà Nam
                </option>

                <option data-code="HT" value="28">
                  Hà Tĩnh
                </option>

                <option data-code="HO" value="11">
                  Hòa Bình
                </option>

                <option data-code="HY" value="21">
                  Hưng Yên
                </option>

                <option data-code="HD" value="19">
                  Hải Dương
                </option>

                <option data-code="HP" value="20">
                  Hải Phòng
                </option>

                <option data-code="HU" value="60">
                  Hậu Giang
                </option>

                <option data-code="KH" value="37">
                  Khánh Hòa
                </option>

                <option data-code="KG" value="58">
                  Kiên Giang
                </option>

                <option data-code="KT" value="40">
                  Kon Tum
                </option>

                <option data-code="LI" value="8">
                  Lai Châu
                </option>

                <option data-code="LA" value="51">
                  Long An
                </option>

                <option data-code="LO" value="6">
                  Lào Cai
                </option>

                <option data-code="LD" value="44">
                  Lâm Đồng
                </option>

                <option data-code="LS" value="13">
                  Lạng Sơn
                </option>

                <option data-code="ND" value="24">
                  Nam Định
                </option>

                <option data-code="NA" value="27">
                  Nghệ An
                </option>

                <option data-code="NB" value="25">
                  Ninh Bình
                </option>

                <option data-code="NT" value="38">
                  Ninh Thuận
                </option>

                <option data-code="PT" value="16">
                  Phú Thọ
                </option>

                <option data-code="PY" value="36">
                  Phú Yên
                </option>

                <option data-code="QB" value="29">
                  Quảng Bình
                </option>

                <option data-code="QM" value="33">
                  Quảng Nam
                </option>

                <option data-code="QG" value="34">
                  Quảng Ngãi
                </option>

                <option data-code="QN" value="14">
                  Quảng Ninh
                </option>

                <option data-code="QT" value="30">
                  Quảng Trị
                </option>

                <option data-code="ST" value="61">
                  Sóc Trăng
                </option>

                <option data-code="SL" value="9">
                  Sơn La
                </option>

                <option data-code="TH" value="26">
                  Thanh Hóa
                </option>

                <option data-code="TB" value="22">
                  Thái Bình
                </option>

                <option data-code="TY" value="12">
                  Thái Nguyên
                </option>

                <option data-code="TT" value="31">
                  Thừa Thiên Huế
                </option>

                <option data-code="TG" value="52">
                  Tiền Giang
                </option>

                <option data-code="TV" value="54">
                  Trà Vinh
                </option>

                <option data-code="TQ" value="5">
                  Tuyên Quang
                </option>

                <option data-code="TN" value="46">
                  Tây Ninh
                </option>

                <option data-code="VL" value="55">
                  Vĩnh Long
                </option>

                <option data-code="VT" value="17">
                  Vĩnh Phúc
                </option>

                <option data-code="YB" value="10">
                  Yên Bái
                </option>

                <option data-code="DB" value="7">
                  Điện Biên
                </option>

                <option data-code="DC" value="42">
                  Đắk Lắk
                </option>

                <option data-code="DO" value="43">
                  Đắk Nông
                </option>

                <option data-code="DN" value="48">
                  Đồng Nai
                </option>

                <option data-code="DT" value="56">
                  Đồng Tháp
                </option>
              </select>
              <select class="w-full border rounded-lg p-2">
                <option>Chọn quận/huyện</option>
              </select>
              <select class="w-full border rounded-lg p-2">
                <option>Chọn phường/xã</option>
              </select>
            </div>
          </div>

          <div class="mt-4 border-b pb-4">
            <h3 class="font-semibold">Phương thức vận chuyển</h3>
            <p class="text-gray-600 text-sm">
              Vui lòng chọn quận / huyện để có danh sách phương thức vận chuyển.
            </p>
          </div>

          <div class="mt-4">
            <h3 class="font-semibold">Phương thức thanh toán</h3>
            <div class="mt-2 space-y-2">
              <label class="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input type="radio" name="payment" checked />
                <span>Thanh toán chuyển khoản qua ngân hàng</span>
              </label>
              <label class="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input type="radio" name="payment" />
                <span>Thanh toán quẹt thẻ khi giao hàng (POS)</span>
              </label>
              <label class="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input type="radio" name="payment" />
                <span>Thanh toán online qua VNPAY</span>
              </label>
              <label class="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input type="radio" name="payment" />
                <span>Ví MoMo</span>
              </label>
            </div>
          </div>

          <div class="mt-4 flex justify-between">
            <button class="text-gray-600">
              <a href="cart">Giỏ hàng</a>
            </button>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">
              Hoàn tất đơn hàng
            </button>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border">
          <h3 class="font-semibold">Giỏ hàng</h3>
          <div class="mt-2 space-y-4">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 bg-gray-200"></div>
              <div>
                <p>Ghế Ăn Gỗ Cao Su Tự Nhiên MOHO SORO 661</p>
                <p class="text-gray-600">1,490,000đ</p>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 bg-gray-200"></div>
              <div>
                <p>Bàn Ăn Gỗ Cao Su Tự Nhiên MOHO VLINE 601 90cm</p>
                <p class="text-gray-600">2,199,000đ</p>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 bg-gray-200"></div>
              <div>
                <p>Bàn Làm Việc Gỗ Cao Su MOHO VLINE 602 Màu Nâu</p>
                <p class="text-gray-600">1,499,000đ</p>
              </div>
            </div>
          </div>

          {/* <div class="mt-4">
            <label class="block text-sm font-semibold">Mã giảm giá</label>
            <div class="flex mt-1">
              <input
                type="text"
                class="w-full border p-2 rounded-l-lg"
                placeholder="Nhập mã"
              />
              <button class="bg-gray-300 px-4 py-2 rounded-r-lg">
                Sử dụng
              </button>
            </div>
          </div> */}

          <div class="mt-4 border-t pt-4">
            <p class="flex justify-between">
              <span>Tạm tính</span> <span>5,188,000đ</span>
            </p>
            <p class="flex justify-between font-bold text-lg">
              <span>Tổng cộng</span> <span>5,188,000đ</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Payment;
