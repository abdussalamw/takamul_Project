import React from 'react';
import { PhaseHero, StepItem, FeatureCard } from '../components/ui';
import { Lightbulb, Users, FileText, Search } from 'lucide-react';

const Phase1: React.FC = () => {
  return (
    <div className="animate-fade-in">
      <div className="relative bg-blue-600 text-white py-16 px-6 overflow-hidden rounded-b-3xl mb-10">
        <div className="absolute inset-0 opacity-20">
          <img src={`https://picsum.photos/id/48/1200/400`} alt="Background" className="w-full h-full object-cover" />
        </div>
        <div className="container mx-auto relative z-10 text-center">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">المرحلة الأولى: مرحلة الإنضاج</h1>
          <p className="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto">الخطوة الأولى نحو تحويل الفكرة إلى واقع، من خلال بناء التصور، تكوين الفريق، ودراسة الاحتياج.</p>
        </div>
      </div>

      <div className="container mx-auto px-6 py-10">
        <div className="grid lg:grid-cols-3 gap-10">
          
          {/* Main Content / Steps */}
          <div className="lg:col-span-2">
            <h2 className="text-2xl font-bold text-blue-900 mb-6 border-b-2 border-blue-200 pb-2 inline-block">
              خطوات مرحلة الإنضاج
            </h2>
            
            <div className="mt-4">
              <StepItem number="01" title="تحديد الفكرة (بناء التصور)">
                <p className="mb-2">يتم في هذه الخطوة بناء تصور شامل عن المشروع من خلال:</p>
                <ul className="list-disc list-inside space-y-1 text-sm">
                  <li>دراسة الجهات المهتمة والمشاريع المشابهة.</li>
                  <li>استشارة الشخصيات ذات الخبرة.</li>
                  <li>البحث في المقالات والدراسات والمسح الميداني.</li>
                </ul>
              </StepItem>

              <StepItem number="02" title="تكوين فريق عمل الإنضاج">
                <p>يتكون الفريق من قسمين:</p>
                <div className="flex gap-4 mt-2">
                  <span className="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm font-bold">فريق تنفيذي</span>
                  <span className="bg-teal-50 text-teal-700 px-3 py-1 rounded-full text-sm font-bold">فريق استشاري</span>
                </div>
                <p className="text-sm mt-2 text-gray-500">يفضل أن يكون عددهم من 3-5 بحد أقصى لضمان المرونة.</p>
              </StepItem>

              <StepItem number="03" title="وضع أهداف الإنضاج">
                <p>وضع الرؤية المشتركة للمبادرة وأهداف المرحلة الحالية للوصول إلى الغاية النهائية.</p>
              </StepItem>

              <StepItem number="04" title="تنفيذ مجموعات التركيز (Focus Groups)">
                <p>دعوة المهتمين والمختصين (4-12 شخص) لمناقشة الأفكار وتبادل الخبرات حول خطة المشروع وآليات التنفيذ.</p>
              </StepItem>

              <StepItem number="05" title="الزيارات واللقاءات">
                <p>الاطلاع على التجارب والممارسات المماثلة، واللقاء مع الخبراء والشرائح المستفيدة.</p>
              </StepItem>

               <StepItem number="06" title="إصدار وثيقة المشروع">
                <p>المنتج النهائي لهذه المرحلة: وثيقة شاملة تتضمن التوصيات، الهوية، الأهداف، الموازنة التقديرية، وخارطة الطريق.</p>
              </StepItem>
            </div>
          </div>

          {/* Sidebar / Highlights */}
          <div className="space-y-6">
            <div className="bg-blue-50 p-6 rounded-xl border border-blue-100">
               <h3 className="text-lg font-bold text-blue-900 mb-4 flex items-center">
                 <Lightbulb className="ml-2" size={20} />
                 مخرجات المرحلة
               </h3>
               <ul className="space-y-3">
                 <li className="flex items-center text-gray-700 bg-white p-3 rounded shadow-sm">
                   <FileText size={16} className="ml-2 text-blue-500" />
                   التصور الأولي للفكرة
                 </li>
                 <li className="flex items-center text-gray-700 bg-white p-3 rounded shadow-sm">
                   <Users size={16} className="ml-2 text-blue-500" />
                   اللوائح الإدارية والمالية الأولية
                 </li>
                 <li className="flex items-center text-gray-700 bg-white p-3 rounded shadow-sm">
                   <Search size={16} className="ml-2 text-blue-500" />
                   المظلة القانونية المقترحة
                 </li>
               </ul>
            </div>

            <div className="bg-yellow-50 p-6 rounded-xl border border-yellow-100">
               <h3 className="text-lg font-bold text-yellow-800 mb-2">إشراقة</h3>
               <p className="text-gray-700 text-sm leading-relaxed">
                 التعاون بين الكيانات وعقد الشراكات في مرحلة الإنضاج (مثلاً: محاسب واحد، موقع واحد) يقلل التكاليف ويزيد من فرص النجاح.
               </p>
            </div>
          </div>

        </div>
      </div>
    </div>
  );
};

export default Phase1;
