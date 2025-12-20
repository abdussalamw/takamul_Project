import React from 'react';
import { LucideIcon } from 'lucide-react';

export const SectionTitle: React.FC<{ title: string; subtitle?: string }> = ({ title, subtitle }) => (
  <div className="mb-8 text-center">
    <h2 className="text-3xl font-bold theme-primary-text mb-3 relative inline-block">
      {title}
      <span className="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-16 h-1 theme-primary-bg rounded-full"></span>
    </h2>
    {subtitle && <p className="text-gray-600 mt-4 max-w-2xl mx-auto">{subtitle}</p>}
  </div>
);

interface FeatureCardProps {
  icon: LucideIcon;
  title: string;
  description: string;
  delay?: number;
}

export const FeatureCard: React.FC<FeatureCardProps> = ({ icon: Icon, title, description, delay = 0 }) => (
  <div
    className="bg-white p-6 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border-t-4 theme-primary-bg transform hover:-translate-y-1"
    style={{ animationDelay: `${delay}ms` }}
  >
    <div className="w-12 h-12 theme-light-bg rounded-full flex items-center justify-center mb-4 theme-primary-text">
      <Icon size={24} />
    </div>
    <h3 className="text-xl font-bold text-gray-800 mb-2">{title}</h3>
    <p className="text-gray-600 text-sm leading-relaxed">{description}</p>
  </div>
);

export const PhaseHero: React.FC<{ title: string; description: string; imageId: number }> = ({ title, description, imageId }) => (
  <div className="relative theme-primary-bg text-white py-16 px-6 overflow-hidden rounded-b-3xl mb-10">
    <div className="absolute inset-0 opacity-20">
      <img src={`https://picsum.photos/id/${imageId}/1200/400`} alt="Background" className="w-full h-full object-cover" />
    </div>
    <div className="container mx-auto relative z-10 text-center">
      <h1 className="text-4xl md:text-5xl font-bold mb-4">{title}</h1>
      <p className="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto">{description}</p>
    </div>
  </div>
);

export const StepItem: React.FC<{ number: string; title: string; children: React.ReactNode }> = ({ number, title, children }) => (
  <div className="flex gap-4 mb-8 relative">
    <div className="flex-shrink-0">
      <div className="w-12 h-12 theme-primary-bg text-white rounded-full flex items-center justify-center font-bold text-xl shadow-lg z-10 relative">
        {number}
      </div>
      <div className="absolute top-12 bottom-[-2rem] right-6 w-0.5 bg-gray-200 -z-0 last:hidden"></div>
    </div>
    <div className="flex-grow pt-1">
      <h3 className="text-xl font-bold theme-primary-text mb-2">{title}</h3>
      <div className="text-gray-600 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
        {children}
      </div>
    </div>
  </div>
);
