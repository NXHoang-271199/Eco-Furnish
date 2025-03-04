import React from "react";
import { TbTargetArrow } from "react-icons/tb";
import { PiRoadHorizonFill } from "react-icons/pi";
import { BiSolidBookHeart } from "react-icons/bi";
const About = () => {
  return (
    <div>
      <main className="mb-16 mt-12">
        {/* Banner */}
        <div>
          <img src="https://picsum.photos/id/1/1920/720" alt="Banner" />
        </div>
        {/* Content */}
        {/*  */}
        <div className="mt-16 max-w-6xl mx-auto">
          {/*  */}
          <div className="grid grid-cols-3 gap-8">
            <div className="items-center">
              <div className=" flex w-full justify-center ">
                <TbTargetArrow className="text-orange-400 w-[100px] h-[100px]" />
              </div>
              <div className=" flex w-full justify-center mt-5">
                <h3 className="font-bold text-[32px] uppercase">OUR mission</h3>
              </div>
              <div className=" flex w-full justify-center mt-3">
                <p className="text-center text-[18px]">
                  Lorem Ipsum is simply dummy text of the printing and
                  typesetting industry. Lorem Ipsum has been the industry's
                  standard dummy text ever since the 1500s
                </p>
              </div>
            </div>
            <div className="items-center">
              <div className=" flex w-full justify-center ">
                <PiRoadHorizonFill className="text-orange-400 w-[100px] h-[100px]" />
              </div>
              <div className=" flex w-full justify-center mt-5">
                <h3 className="font-bold text-[32px] uppercase">OUR vision</h3>
              </div>
              <div className=" flex w-full justify-center mt-3">
                <p className="text-center text-[18px]">
                  Lorem Ipsum is simply dummy text of the printing and
                  typesetting industry. Lorem Ipsum has been the industry's
                  standard dummy text ever since the 1500s
                </p>
              </div>
            </div>
            <div className="items-center">
              <div className=" flex w-full justify-center ">
                <BiSolidBookHeart className="text-orange-400 w-[100px] h-[100px]" />
              </div>
              <div className=" flex w-full justify-center mt-5">
                <h3 className="font-bold text-[32px] uppercase">OUR values</h3>
              </div>
              <div className=" flex w-full justify-center mt-3">
                <p className="text-center text-[18px]">
                  Lorem Ipsum is simply dummy text of the printing and
                  typesetting industry. Lorem Ipsum has been the industry's
                  standard dummy text ever since the 1500s
                </p>
              </div>
            </div>
          </div>
          {/*  */}
          <div className="grid grid-cols-2 mt-16 gap-8">
            <div>
              <img src="https://picsum.photos/id/1/624/408" alt="" />
            </div>
            <div>
              <h1 className="font-bold text-[32px] uppercase">
                about Furniro company
              </h1>
              <div className="text-[18px] mt-8 mb-3">
                Lorem Ipsum is simply dummy text of the printing and typesetting
                industry. Lorem Ipsum has been the industry's standard dummy
                text ever since the 1500s, when an unknown printer took a galley
                of type and scrambled it to make a type specimen book.
              </div>
              <div className="text-[18px] ">
                It has survived not only five centuries, but also the leap into
                electronic typesetting, remaining essentially unchanged. It was
                popularised in the 1960s with the release of Letraset sheets
                containing Lorem Ipsum passages, and more recently with desktop
                publishing software like Aldus PageMaker including versions of
                Lorem Ipsum.
              </div>
            </div>
          </div>
          {/*  */}
          <div className="mt-16">
            <h1 className="text-center text-[32px] font-bold ]">
              WE CREATE NATURAL WOOD FURNITURE
            </h1>
            <h1 className="text-center text-[32px] font-bold ]">
              AS WELL AS HIGH-QUALITY
            </h1>
            <div className="grid grid-cols-4 gap-8 mt-8 ">
              <div className="relative">
                <img src="https://picsum.photos/id/1/296/369" alt="" />
                <p className="text-[#FFFFFF] uppercase text-[16px] font-semibold absolute bottom-5 left-5">
                  flooring
                </p>
              </div>
              <div className="relative">
                <img src="https://picsum.photos/id/1/296/369" alt="" />
                <p className="text-[#FFFFFF] uppercase text-[16px] font-semibold absolute bottom-5 left-5">
                  refinishing
                </p>
              </div>
              <div className="relative">
                <img src="https://picsum.photos/id/1/296/369" alt="" />
                <p className="text-[#FFFFFF] uppercase text-[16px] font-semibold absolute bottom-5 left-5">
                  installation
                </p>
              </div>
              <div className="relative">
                <img src="https://picsum.photos/id/1/296/369" alt="" />
                <p className="text-[#FFFFFF] uppercase text-[16px] font-semibold absolute bottom-5 left-5">
                  warming the floor
                </p>
              </div>
            </div>
          </div>
        </div>
        {/*  */}
      </main>
    </div>
  );
};

export default About;
