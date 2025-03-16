import React from "react";
import { CiEdit } from "react-icons/ci";
import { MdAddLocation, MdDeleteOutline } from "react-icons/md";
const Address = () => {
  return (
    <>
      <main class="w-full md:w-3/4 p-6  ">
        <div className="flex justify-between items-center">
          <div className="text-xl font-semibold">Địa chỉ</div>
          <button className="bg-black text-white px-4 py-2 rounded-md flex items-center">
            <MdAddLocation className="mr-2" />
            Thêm địa chỉ nhận hàng
          </button>
        </div>
        <div className="grid grid-cols-1 lg:grid-cols-2 mt-5 gap-6 mb-14">
          <div class="rounded-lg border border-slate-200 bg-white text-slate-950  w-full max-w-md mx-auto shadow-lg hover:shadow-xl transition-shadow">
            <div class="p-6 flex flex-row items-center space-y-0 pb-2">
              <div class="flex items-center gap-x-2">
                <h2 class="text-2xl font-bold tracking-tight">
                  4 Ng. 75 P. Tư Đình
                </h2>
              </div>
              <div class="ml-auto">
                <button
                  class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-50 h-8 w-8"
                  type="button"
                  aria-haspopup="dialog"
                  aria-expanded="false"
                  aria-controls="radix-:r25p:"
                  data-state="closed"
                >
                  <CiEdit className="text-lg" />
                  <span class="sr-only">Cập nhật</span>{" "}
                </button>
                <button
                  class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-50 h-8 w-8"
                  type="button"
                  aria-haspopup="dialog"
                  aria-expanded="false"
                  aria-controls="radix-:r25s:"
                  data-state="closed"
                >
                  <MdDeleteOutline className="text-lg text-red-600" />
                  <span class="sr-only">Xóa</span>
                </button>
              </div>
            </div>
            <div class="p-6 pt-0 space-y-4">
              <p class="text-sm">dat dt03 - 0989219769</p>
              <p class="text-muted-foreground text-sm">
                4 Ng. 75 P. Tư Đình, Xã Mỹ Gia, Huyện Yên Bình, Tỉnh Yên Bái
              </p>
            </div>
          </div>
        </div>
      </main>
    </>
  );
};

export default Address;
