import { useState, useEffect } from "react";
import { motion } from "framer-motion";
// hiệu ứng bong bóng
const CursorGlow = () => {
  const [bubbles, setBubbles] = useState([]);

  useEffect(() => {
    const newBubbles = Array.from({ length: 30 }).map(() => ({
      id: Math.random(),
      size: Math.random() * 50 + 20, // Kích thước ngẫu nhiên từ 20px - 70px
      x: Math.random() * window.innerWidth, // Vị trí ngang ngẫu nhiên
      y: Math.random() * window.innerHeight, // Vị trí dọc ngẫu nhiên
      duration: Math.random() * 5 + 3, // Thời gian di chuyển ngẫu nhiên từ 3s - 8s
      type: Math.floor(Math.random() * 3), // 3 kiểu di chuyển
    }));
    setBubbles(newBubbles);
  }, []);

  return (
    <div className="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
      {bubbles.map((bubble) => (
        <motion.div
          key={bubble.id}
          initial={{
            x: bubble.x,
            y: bubble.y,
            opacity: 0.5,
            scale: 0.8,
          }}
          animate={
            bubble.type === 0
              ? { y: -100, opacity: 0 } // Trôi lên
              : bubble.type === 1
              ? { x: bubble.x + 100, opacity: 0 } // Trôi ngang
              : { y: -100, rotate: 360, opacity: 0 } // Xoay khi trôi lên
          }
          transition={{
            duration: bubble.duration,
            repeat: Infinity,
            ease: "linear",
          }}
          className="absolute bg-blue-700 rounded-full"
          style={{
            width: bubble.size,
            height: bubble.size,
            left: bubble.x,
            backgroundColor: `rgba(173, 216, 230, ${
              Math.random() * 0.5 + 0.3
            })`,
          }}
        />
      ))}
    </div>
  );
};

export default CursorGlow;

// import { useState, useEffect } from "react";

// const CursorGlow = () => {
//   const [position, setPosition] = useState({ x: 0, y: 0 });
//   const [color, setColor] = useState("rgba(0, 122, 255, 0.8)");

//   useEffect(() => {
//     const handleMouseMove = (e) => {
//       setPosition({ x: e.clientX, y: e.clientY });
//     };

//     const changeColor = () => {
//       const colors = ["rgba(0, 122, 255, 0.8)", "rgba(255, 0, 150, 0.8)", "rgba(255, 204, 0, 0.8)", "rgba(0, 255, 127, 0.8)"];
//       setColor(colors[Math.floor(Math.random() * colors.length)]);
//     };

//     window.addEventListener("mousemove", handleMouseMove);
//     const interval = setInterval(changeColor, 500); // đổi màu mỗi 500ms

//     return () => {
//       window.removeEventListener("mousemove", handleMouseMove);
//       clearInterval(interval);
//     };
//   }, []);

//   return (
//     <div
//       className="fixed top-0 left-0 w-screen h-screen pointer-events-none z-50"
//       style={{
//         background: `radial-gradient(circle at ${position.x}px ${position.y}px, ${color}, transparent 20px)`,
//       }}
//     />
//   );
// };

// export default CursorGlow;
