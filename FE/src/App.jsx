import { useState } from "react";
import "./App.css";
import { Routes, Route } from "react-router-dom";
import HomePage from "./pages/(website)/home/HomePage";
import LayoutWebsite from "./pages/layout";

function App() {
  return (
    <>
      <Routes>
        <Route path="/" element={<LayoutWebsite />}>
          <Route index element={<HomePage />}></Route>
        </Route>
      </Routes>
    </>
  );
}

export default App;
