import React from 'react';
import { Link } from 'react-router-dom';
import { Layers, Rocket, Settings, TrendingUp, ArrowLeft, Users, FileText, Target } from 'lucide-react';
import { SectionTitle } from '../components/ui';

const Home: React.FC = () => {
  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative theme-primary-bg text-white pt-24 pb-32 px-6 overflow-hidden">
        <div className="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10 pointer-events-none">
           {/* Abstract shapes */}
           <div className="absolute top-10 right-10 w-64 h-64 bg-white rounded-full blur-3xl"></div>
           <div className="absolute bottom-10 left-10 w-96 h-96 bg-blue-300 rounded-full blur-3xl"></div>
        </div>

        <div className="container mx-auto grid md:grid-cols-2 gap-12 items-center relative z-10">
          <div className="space-y-6">
            <div className="inline-block bg-white/10 px-4 py-1 rounded-full text-sm font-medium backdrop-blur-sm">
              الدليل الإجرائي (2025)
            </div>
            <h1 className="text-5xl md:text-6xl font-bold leading-tight">
              تأسيس <span className="text-yellow-300">تكامل</span>
              <br />
              نحو عمل خيري مستدام
            </h1>
            <p className="text-lg text-blue-100 leading-relaxed max-w-lg">
              دليلك الشامل لبناء وتأسيس وتشغيل وتطوير كيانات التنسيق للعمل التنموي النسائي، بخطوات عملية ومدروسة.
            </p>
            <div className="flex flex-wrap gap-4 pt-4">
              <a href="/dalil/documantes/dalil1447.pdf" target="_blank" rel="noopener noreferrer" className="bg-white theme-primary-text px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition shadow-lg">
                ابدأ التصفح
              </a>
              <button className="bg-transparent border-2 border-white text-white px-8 py-3 rounded-full font-bold hover:bg-white/10 transition">
                عن الدليل
              </button>
            </div>
          </div>
          <div className="hidden md:block relative">
            <img 
              src="https://picsum.photos/id/20/600/400" 
              alt="Team Collaboration" 
              className="rounded-2xl shadow-2xl border-4 border-white/20 transform rotate-2 hover:rotate-0 transition duration-500"
            />
            <div className="absolute -bottom-6 -right-6 bg-white p-4 rounded-xl shadow-xl theme-primary-text max-w-xs">
              <p className="font-bold text-sm">"وتعاونوا على البر والتقوى"</p>
              <p className="text-xs text-gray-500 mt-1">المائدة - آية رقم 2</p>
            </div>
          </div>
        </div>
        
        {/* Wave Divider */}
        <div className="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
          <svg className="relative block w-full h-16 md:h-24" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="#f8fafc"></path>
          </svg>
        </div>
      </section>

      {/* Introduction */}
      <section className="py-20 bg-slate-50">
        <div className="container mx-auto px-6">
          <SectionTitle 
            title="مقدمة الدليل" 
            subtitle="لا يعيش الإنسان بمعزل عن الآخرين، بل هو بحاجة دائمة للتواصل والاندماج. ومن هنا تأتي أهمية أن يقوم هذا الاندماج على أسس من التكامل والتعاون."
          />
          <div className="grid md:grid-cols-3 gap-8 mt-12">
             <div className="bg-white p-8 rounded-2xl shadow-sm text-center hover:shadow-md transition">
                <Users className="w-12 h-12 theme-primary-text mx-auto mb-4" />
                <h3 className="text-xl font-bold mb-3">التنسيق والتعاون</h3>
                <p className="text-gray-600">مسار نحو التكامل في العمل الخيري، يسلط الضوء على مواطن القوة في كل كيان.</p>
             </div>
             <div className="bg-white p-8 rounded-2xl shadow-sm text-center hover:shadow-md transition">
                <Target className="w-12 h-12 theme-primary-text mx-auto mb-4" />
                <h3 className="text-xl font-bold mb-3">الأهداف المشتركة</h3>
                <p className="text-gray-600">تحفيز كل جهة لإبراز نقاط قوتها والتعاون لتحقيق الأهداف المشتركة بفاعلية أكبر.</p>
             </div>
             <div className="bg-white p-8 rounded-2xl shadow-sm text-center hover:shadow-md transition">
                <FileText className="w-12 h-12 theme-primary-text mx-auto mb-4" />
                <h3 className="text-xl font-bold mb-3">توثيق التجربة</h3>
                <p className="text-gray-600">تقديم تجربة "تكامل" كمرجع يمكن الاعتماد عليه في إعداد أدلة إجرائية جديدة.</p>
             </div>
          </div>
        </div>
      </section>

      {/* Phases Grid (Main Navigation) */}
      <section id="phases" className="py-20 px-6 container mx-auto">
        <SectionTitle 
          title="مراحل الدليل الإجرائي" 
          subtitle="تم تقسيم الدليل إلى أربعة مراحل رئيسية ليتم مراعاتها والاستفادة منها"
        />
        
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {/* Phase 1 */}
          <Link to="/phase/1" className="group relative bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-blue-400 hover:scale-105 transition-all duration-300">
            <div className="p-6">
              <div className="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-4 text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                <Rocket size={28} />
              </div>
              <h3 className="text-2xl font-bold text-gray-800 mb-2">مرحلة الإنضاج</h3>
              <p className="text-gray-500 text-sm mb-4">بناء التصور الأولي، تشكيل الفريق، ودراسة الاحتياج.</p>
              <div className="flex items-center text-blue-500 font-bold text-sm">
                استكشف المرحلة <ArrowLeft size={16} className="mr-2" />
              </div>
            </div>
            <div className="bg-blue-50 p-3 text-center text-xs text-blue-800 font-medium">
              المرحلة الأولى
            </div>
          </Link>

          {/* Phase 2 */}
          <Link to="/phase/2" className="group relative bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-cyan-500 hover:scale-105 transition-all duration-300">
            <div className="p-6">
              <div className="w-14 h-14 bg-cyan-50 rounded-2xl flex items-center justify-center mb-4 text-cyan-500 group-hover:bg-cyan-500 group-hover:text-white transition-colors">
                <Layers size={28} />
              </div>
              <h3 className="text-2xl font-bold text-gray-800 mb-2">مرحلة التأسيس</h3>
              <p className="text-gray-500 text-sm mb-4">البناء القانوني، الاستراتيجي، المالي، وتجهيز المقر.</p>
              <div className="flex items-center text-cyan-600 font-bold text-sm">
                استكشف المرحلة <ArrowLeft size={16} className="mr-2" />
              </div>
            </div>
            <div className="bg-cyan-50 p-3 text-center text-xs text-cyan-800 font-medium">
              المرحلة الثانية
            </div>
          </Link>

          {/* Phase 3 */}
          <Link to="/phase/3" className="group relative bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-teal-600 hover:scale-105 transition-all duration-300">
            <div className="p-6">
              <div className="w-14 h-14 bg-teal-50 rounded-2xl flex items-center justify-center mb-4 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                <Settings size={28} />
              </div>
              <h3 className="text-2xl font-bold text-gray-800 mb-2">مرحلة التشغيل</h3>
              <p className="text-gray-500 text-sm mb-4">الإجراءات اليومية، الأنظمة الإدارية، وإدارة العمليات.</p>
              <div className="flex items-center text-teal-700 font-bold text-sm">
                استكشف المرحلة <ArrowLeft size={16} className="mr-2" />
              </div>
            </div>
            <div className="bg-teal-50 p-3 text-center text-xs text-teal-800 font-medium">
              المرحلة الثالثة
            </div>
          </Link>

          {/* Phase 4 */}
          <Link to="/phase/4" className="group relative bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-purple-500 hover:scale-105 transition-all duration-300">
            <div className="p-6">
              <div className="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center mb-4 text-purple-500 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                <TrendingUp size={28} />
              </div>
              <h3 className="text-2xl font-bold text-gray-800 mb-2">مرحلة التطوير</h3>
              <p className="text-gray-500 text-sm mb-4">التميز المؤسسي، الابتكار، والتحول الرقمي.</p>
              <div className="flex items-center text-purple-600 font-bold text-sm">
                استكشف المرحلة <ArrowLeft size={16} className="mr-2" />
              </div>
            </div>
            <div className="bg-purple-50 p-3 text-center text-xs text-purple-800 font-medium">
              المرحلة الرابعة
            </div>
          </Link>
        </div>
      </section>
    </div>
  );
};

export default Home;
