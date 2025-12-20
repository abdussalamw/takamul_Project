import React from 'react';
import { PhaseHero, StepItem } from '../components/ui';
import { Briefcase, Gavel, PieChart, Layout } from 'lucide-react';

const Phase2: React.FC = () => {
  return (
    <div className="animate-fade-in">
      <div className="relative bg-cyan-600 text-white py-16 px-6 overflow-hidden rounded-b-3xl mb-10">
        <div className="absolute inset-0 opacity-20">
          <img src={`https://picsum.photos/id/1060/1200/400`} alt="Background" className="w-full h-full object-cover" />
        </div>
        <div className="container mx-auto relative z-10 text-center">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">المرحلة الثانية: مرحلة التأسيس</h1>
          <p className="text-xl md:text-2xl text-cyan-100 max-w-3xl mx-auto">بناء البنية التحتية الصلبة للمشروع، من الجوانب القانونية، المالية، الاستراتيجية، وتجهيز بيئة العمل.</p>
        </div>
      </div>

      <div className="container mx-auto px-6 py-10">
        
        {/* Key Areas Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
           <div className="bg-white p-5 rounded-lg shadow border-t-4 border-cyan-500 text-center">
             <Gavel className="mx-auto text-cyan-500 mb-2" size={32} />
             <h3 className="font-bold text-gray-800">المظلة القانونية</h3>
           </div>
           <div className="bg-white p-5 rounded-lg shadow border-t-4 border-cyan-500 text-center">
             <Layout className="mx-auto text-cyan-500 mb-2" size={32} />
             <h3 className="font-bold text-gray-800">بيئة العمل</h3>
           </div>
           <div className="bg-white p-5 rounded-lg shadow border-t-4 border-cyan-500 text-center">
             <PieChart className="mx-auto text-cyan-500 mb-2" size={32} />
             <h3 className="font-bold text-gray-800">دراسة الجدوى</h3>
           </div>
           <div className="bg-white p-5 rounded-lg shadow border-t-4 border-cyan-500 text-center">
             <Briefcase className="mx-auto text-cyan-500 mb-2" size={32} />
             <h3 className="font-bold text-gray-800">فريق التأسيس</h3>
           </div>
        </div>

        <div className="grid lg:grid-cols-3 gap-10">
          <div className="lg:col-span-2 space-y-8">
            
            <section>
              <h2 className="text-2xl font-bold text-cyan-900 mb-6 border-b-2 border-cyan-200 pb-2 inline-block">خطوات التأسيس الرئيسية</h2>
              <StepItem number="01" title="فريق التأسيس">
                <p className="text-gray-600 mb-2">جهة أو مجموعة أفراد يأخذون على عاتقهم التنسيق.</p>
                <div className="grid grid-cols-2 gap-2 text-sm">
                   <div className="bg-gray-50 p-2 rounded">شخصية قيادية</div>
                   <div className="bg-gray-50 p-2 rounded">خبرة في العمل الخيري</div>
                   <div className="bg-gray-50 p-2 rounded">الحس المالي</div>
                   <div className="bg-gray-50 p-2 rounded">القدرة القانونية</div>
                </div>
              </StepItem>

              <StepItem number="02" title="دراسة الجدوى">
                <p className="mb-2">حقائق ودلائل تدعم اتخاذ القرار. تتضمن:</p>
                <ul className="list-disc list-inside text-sm text-gray-600">
                  <li>مبررات وجود "تكامل".</li>
                  <li>المتطلبات القانونية (التراخيص).</li>
                  <li>الاحتياجات التشغيلية (الفني، التسويقي، المالي).</li>
                </ul>
              </StepItem>

              <StepItem number="03" title="الخطة الاستراتيجية (بوصلة تكامل)">
                 <div className="bg-cyan-50 p-4 rounded-lg border border-cyan-100">
                    <div className="grid grid-cols-2 md:grid-cols-3 gap-4 text-center">
                       <div>
                         <span className="block font-bold text-cyan-800">الرؤية</span>
                         <span className="text-xs text-gray-600">نموذج احترافي للتنسيق</span>
                       </div>
                       <div>
                         <span className="block font-bold text-cyan-800">الرسالة</span>
                         <span className="text-xs text-gray-600">تبادل خبرات وتأهيل</span>
                       </div>
                       <div>
                         <span className="block font-bold text-cyan-800">القيم</span>
                         <span className="text-xs text-gray-600">منظومة القيم الحاكمة</span>
                       </div>
                    </div>
                 </div>
              </StepItem>
              
               <StepItem number="04" title="التجهيز والموازنة">
                <p>تجهيز المقر، الأنظمة، الهوية البصرية، وإعداد الموازنة المالية (التأسيسية والتشغيلية).</p>
              </StepItem>
            </section>

          </div>

          <div className="bg-white p-6 rounded-xl shadow-lg h-fit sticky top-24">
            <h3 className="text-xl font-bold text-takamel-dark mb-4">الاحتياج الفني</h3>
            <p className="text-gray-600 mb-4 text-sm">
              المتطلبات المهنية والمادية والأنظمة اللازمة لبيئة العمل:
            </p>
            <ul className="space-y-2 text-sm">
               <li className="flex items-center gap-2">
                 <span className="w-2 h-2 bg-cyan-500 rounded-full"></span>
                 المعايير والصلاحيات
               </li>
               <li className="flex items-center gap-2">
                 <span className="w-2 h-2 bg-cyan-500 rounded-full"></span>
                 اللوائح والأنظمة
               </li>
               <li className="flex items-center gap-2">
                 <span className="w-2 h-2 bg-cyan-500 rounded-full"></span>
                 الأدلة الإجرائية
               </li>
                <li className="flex items-center gap-2">
                 <span className="w-2 h-2 bg-cyan-500 rounded-full"></span>
                 الأنظمة الإلكترونية
               </li>
            </ul>
          </div>

        </div>
      </div>
    </div>
  );
};

export default Phase2;
