import React, { useEffect } from 'react';
import { HashRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
import Layout from './components/Layout';
import Home from './pages/Home';
import Phase1 from './pages/Phase1';
import Phase2 from './pages/Phase2';
import Phase3 from './pages/Phase3';
import Phase4 from './pages/Phase4';

// Component to scroll to top on route change
const ScrollToTop = () => {
  const { pathname } = useLocation();
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);
  return null;
};

function App() {
  return (
    <Router>
      <ScrollToTop />
      <Layout>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/phase/1" element={<Phase1 />} />
          <Route path="/phase/2" element={<Phase2 />} />
          <Route path="/phase/3" element={<Phase3 />} />
          <Route path="/phase/4" element={<Phase4 />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;