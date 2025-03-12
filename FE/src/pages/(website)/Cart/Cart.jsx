import React from "react";
import { MdDelete } from "react-icons/md";

const Cart = () => {
  return (
    <div>
      <section className="mt-28 text-center text-5xl font-semibold mx-auto">
        <h1>Cart</h1>
      </section>
      <section className="max-w-6xl mx-auto mt-20 my-6 mb-20 ">
        <div className="flex flex-row">
          <div className="basis-3/4 mr-4 border-r">
            <table className="w-full ">
              <thead>
                <tr className=" text-center">
                  <th className="py-6 underline">Product</th>
                  <th className="py-6 "></th>
                  <th className="py-6 underline ">Price</th>
                  <th className="py-6 underline ">Quantity</th>
                  <th className="py-6 underline ">Subtotal</th>
                  <th className="py-6 "></th>
                </tr>
              </thead>
              <tbody>
                <tr className="text-center hover:bg-slate-50">
                  <td className="text-start">
                    <img
                      src="https://picsum.photos/77/77"
                      alt=""
                      className="mx-auto rounded-md"
                    />
                  </td>
                  <td>Asgaard sofa</td>
                  <td>25.000.000đ</td>
                  <td>
                    <div className="border flex justify-around rounded-md">
                      <button className="">-</button>
                      <span>1</span>
                      <button className="">+</button>
                    </div>
                  </td>
                  <td>25.000.000đ</td>
                  <td>
                    <a href="#" className="text-red-600">
                      <MdDelete className="w-[21px] h-[22.88px]" />
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div className="basis-1/4 py-5 ">
            <h2 className="text-xl font-semibold">Cart Total</h2>
            <hr className="my-3 font-semibold" />

            <div className="my-4">
              <p className="text-gray-400 mb-3">
                Thêm mã giảm giá của bạn ở đây
              </p>
              <div className="border rounded-md p-2">
                <input
                  type="text"
                  placeholder="Mã giảm giá"
                  className="outline-none"
                />
                <button className="border-l pl-2">Áp dụng</button>
              </div>
            </div>
            <div>
              <div className="flex justify-between mb-4">
                <p className="font-medium">Subtotal</p>
                <p className="text-gray-400 font-light">25.000.000đ</p>
              </div>

              <div className="flex justify-between mb-4">
                <p className="font-medium">Total</p>
                <p className="font-bold text-red-600 ">25.000.000đ</p>
              </div>
            </div>

            <div className="text-center border border-orange-400 py-2 mt-8">
              <a href="payment" className="text-orange-400 font-semibold">
                Checkout
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Cart;
