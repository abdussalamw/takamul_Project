import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Menu, X, Home, Palette } from 'lucide-react';
import { useTheme } from '../src/hooks/useTheme';

const Layout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const location = useLocation();
  const { theme, toggleTheme, getNextThemeLabel } = useTheme();

  const navLinks = [
    { name: 'الرئيسية', path: '/' },
    { name: 'مرحلة الإنضاج', path: '/phase/1' },
    { name: 'مرحلة التأسيس', path: '/phase/2' },
    { name: 'مرحلة التشغيل', path: '/phase/3' },
    { name: 'مرحلة التطوير', path: '/phase/4' },
  ];

  const isActive = (path: string) => location.pathname === path;

  return (
    <div className="min-h-screen flex flex-col font-sans bg-slate-50">
      {/* Navbar */}
      <nav className="bg-white shadow-md sticky top-0 z-50">
        <div className="container mx-auto px-6 py-4">
          <div className="flex justify-between items-center">
            {/* Logo */}
            <Link to="/" className="flex items-center gap-2 group">
               <img
                 src="/logo2-01.jpg"
                 alt="شعار تكامل"
                 className="w-10 h-10 object-contain group-hover:scale-110 transition-transform duration-300"
               />
               <span className="text-2xl font-extrabold theme-primary-text">تكامل</span>
            </Link>

            {/* Desktop Menu & Theme Toggle */}
            <div className="hidden md:flex items-center space-x-reverse space-x-4">
              <button
                onClick={toggleTheme}
                className="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-full hover:bg-gray-50 transition-colors font-medium text-sm"
                title={`التبديل إلى ${getNextThemeLabel()}`}
              >
                <Palette size={16} />
                <span>{getNextThemeLabel()}</span>
              </button>
              {navLinks.map((link) => (
                <Link
                  key={link.path}
                  to={link.path}
                  className={`px-4 py-2 rounded-full transition-colors font-medium text-sm ${
                    isActive(link.path)
                      ? 'theme-primary-bg text-white shadow-lg'
                      : 'text-gray-600 hover:theme-primary-text hover:bg-gray-50'
                  }`}
                >
                  {link.name}
                </Link>
              ))}
            </div>

            {/* Mobile Menu Button */}
            <button className="md:hidden text-gray-600" onClick={() => setIsMenuOpen(!isMenuOpen)}>
              {isMenuOpen ? <X size={28} /> : <Menu size={28} />}
            </button>
          </div>
        </div>

        {/* Mobile Menu Dropdown */}
        {isMenuOpen && (
          <div className="md:hidden bg-white border-t">
            <div className="flex flex-col p-4 space-y-2">
              <button
                onClick={() => {
                  toggleTheme();
                  setIsMenuOpen(false);
                }}
                className="flex items-center justify-center gap-2 w-full px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
              >
                <Palette size={16} />
                <span>{getNextThemeLabel()}</span>
              </button>
              {navLinks.map((link) => (
                <Link
                  key={link.path}
                  to={link.path}
                  onClick={() => setIsMenuOpen(false)}
                  className={`px-4 py-3 rounded-lg ${
                    isActive(link.path)
                      ? 'theme-light-bg theme-primary-text font-bold'
                      : 'text-gray-600 hover:bg-gray-50'
                  }`}
                >
                  {link.name}
                </Link>
              ))}
            </div>
          </div>
        )}
      </nav>

      {/* Main Content */}
      <main className="flex-grow">
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-gray-900 text-white py-12">
        <div className="container mx-auto px-6 grid md:grid-cols-3 gap-8">
          <div>
            <h3 className="text-2xl font-bold mb-4 flex items-center gap-2">
              <Home size={20} /> تكامل
            </h3>
            <p className="text-gray-400 text-sm leading-relaxed">
              الدليل الإجرائي لتأسيس تكامل. مرجع شامل للعمل الخيري التنموي النسائي.
            </p>
          </div>
          <div>
            <h4 className="font-bold mb-4 text-gray-200">روابط سريعة</h4>
            <ul className="space-y-2 text-sm text-gray-400">
               <li><Link to="/phase/1" className="hover:text-white">الإنضاج</Link></li>
               <li><Link to="/phase/2" className="hover:text-white">التأسيس</Link></li>
               <li><Link to="/phase/3" className="hover:text-white">التشغيل</Link></li>
               <li><Link to="/phase/4" className="hover:text-white">التطوير</Link></li>
            </ul>
          </div>
          <div>
            <h4 className="font-bold mb-4 text-gray-200">تواصل معنا</h4>
            <p className="text-gray-400 text-sm">الرياض، المملكة العربية السعودية</p>
            <p className="text-gray-400 text-sm mt-2">© 2025 جميع الحقوق محفوظة</p>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Layout;
