import { useState, useEffect } from 'react';

const THEME_KEY = 'takamel-theme';

export function useTheme() {
  const [theme, setTheme] = useState('blue'); // Default to blue theme

  useEffect(() => {
    // Load theme from localStorage
    const savedTheme = localStorage.getItem(THEME_KEY);
    if (savedTheme && (savedTheme === 'blue' || savedTheme === 'gold')) {
      setTheme(savedTheme);
      applyTheme(savedTheme);
    } else {
      // Default to blue theme
      applyTheme('blue');
    }
  }, []);

  const applyTheme = (newTheme) => {
    const root = document.documentElement;
    if (newTheme === 'gold') {
      root.setAttribute('data-theme', 'gold');
    } else {
      root.removeAttribute('data-theme');
    }
  };

  const toggleTheme = () => {
    const newTheme = theme === 'blue' ? 'gold' : 'blue';
    setTheme(newTheme);
    applyTheme(newTheme);
    localStorage.setItem(THEME_KEY, newTheme);
  };

  const getThemeLabel = () => {
    return theme === 'blue' ? 'الستايل الأزرق' : 'الستايل الذهبي';
  };

  const getNextThemeLabel = () => {
    return theme === 'blue' ? 'الستايل الذهبي' : 'الستايل الأزرق';
  };

  return {
    theme,
    toggleTheme,
    getThemeLabel,
    getNextThemeLabel,
  };
}
