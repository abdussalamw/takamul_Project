import { useState, useEffect } from 'react';

type Theme = 'gold' | 'blue';

export function useTheme() {
  const [theme, setTheme] = useState<Theme>('gold');

  useEffect(() => {
    // Load theme from localStorage
    const savedTheme = localStorage.getItem('takamul-theme') as Theme;
    if (savedTheme && (savedTheme === 'gold' || savedTheme === 'blue')) {
      setTheme(savedTheme);
      applyTheme(savedTheme);
    } else {
      // Default to gold theme
      applyTheme('gold');
    }
  }, []);

  const applyTheme = (newTheme: Theme) => {
    const root = document.documentElement;
    if (newTheme === 'blue') {
      root.setAttribute('data-theme', 'blue');
    } else {
      root.removeAttribute('data-theme');
    }
  };

  const toggleTheme = () => {
    const newTheme: Theme = theme === 'gold' ? 'blue' : 'gold';
    setTheme(newTheme);
    applyTheme(newTheme);
    localStorage.setItem('takamul-theme', newTheme);
  };

  const getThemeLabel = () => {
    return theme === 'gold' ? 'الستايل الذهبي' : 'الستايل الأزرق';
  };

  const getNextThemeLabel = () => {
    return theme === 'gold' ? 'الستايل الأزرق' : 'الستايل الذهبي';
  };

  return {
    theme,
    toggleTheme,
    getThemeLabel,
    getNextThemeLabel,
  };
}
