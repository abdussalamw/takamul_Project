import { Menu, X, Palette } from 'lucide-react';
import { useState } from 'react';
import { useTheme } from '@/hooks/useTheme';

export default function Navigation() {
  const [isOpen, setIsOpen] = useState(false);
  const { theme, toggleTheme, getNextThemeLabel } = useTheme();

  const navItems = [
    { label: 'الرئيسية', href: '#home' },
    { label: 'من نحن', href: '#about' },
    { label: 'نموذج تكامل', href: '#model' },
    { label: 'البرامج', href: '#programs' },
    { label: 'الشراكات', href: '#partnerships' },
    { label: 'التواصل', href: '#contact' },
  ];

  return (
    <nav className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-border shadow-sm">
      <div className="container flex items-center justify-between h-20">
        {/* Logo */}
        <div className="flex items-center gap-2">
          <img
            src="/images/logo2-01.jpg"
            alt="شعار تكامل"
            className="h-12 w-auto object-contain"
          />
          <div className="hidden sm:flex flex-col">
            <span className="font-bold text-primary text-lg">تكامل</span>
            <span className="text-xs text-muted-foreground">Takamul</span>
          </div>
        </div>

        {/* Desktop Navigation */}
        <div className="hidden md:flex items-center gap-8">
          {navItems.map((item) => (
            <a
              key={item.href}
              href={item.href}
              className="text-foreground hover:text-primary transition-colors font-medium text-sm"
            >
              {item.label}
            </a>
          ))}
        </div>

        {/* Theme Toggle & CTA Button */}
        <div className="hidden md:flex items-center gap-3">
          <button
            onClick={toggleTheme}
            className="flex items-center gap-2 px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors font-medium text-sm"
            title={`التبديل إلى ${getNextThemeLabel()}`}
          >
            <Palette size={16} />
            <span>{getNextThemeLabel()}</span>
          </button>
          <a
            href="#contact"
            className="px-6 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:shadow-lg transition-all duration-300 font-medium text-sm"
          >
            تواصل معنا
          </a>
        </div>

        {/* Mobile Menu Button */}
        <button
          onClick={() => setIsOpen(!isOpen)}
          className="md:hidden p-2 hover:bg-muted rounded-lg transition-colors"
        >
          {isOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      {/* Mobile Navigation */}
      {isOpen && (
        <div className="md:hidden border-t border-border bg-white">
          <div className="container py-4 space-y-3">
            {navItems.map((item) => (
              <a
                key={item.href}
                href={item.href}
                className="block px-4 py-2 text-foreground hover:bg-muted rounded-lg transition-colors"
                onClick={() => setIsOpen(false)}
              >
                {item.label}
              </a>
            ))}
            <button
              onClick={() => {
                toggleTheme();
                setIsOpen(false);
              }}
              className="flex items-center justify-center gap-2 w-full px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors font-medium"
            >
              <Palette size={16} />
              <span>{getNextThemeLabel()}</span>
            </button>
            <a
              href="#contact"
              className="block px-4 py-2 bg-gradient-to-r from-primary to-secondary text-white rounded-lg text-center font-medium"
              onClick={() => setIsOpen(false)}
            >
              تواصل معنا
            </a>
          </div>
        </div>
      )}
    </nav>
  );
}
