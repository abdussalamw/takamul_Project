import React from 'react';
import { PhaseHero, StepItem } from '../components/ui';
import { Settings, UserCheck, CreditCard, FileText } from 'lucide-react';

const Phase3: React.FC = () => {
  return (
    <div className="animate-fade-in">
      <div className="relative bg-teal-600 text-white py-16 px-6 overflow-hidden rounded-b-3xl mb-10">
        <div className="absolute inset-0 opacity-20">
          <img src={`https://picsum.photos/id/3/1200/400`} alt="Background" className="w-full h-full object-cover" />
        </div>
        <div className="container mx-auto relative z-10 text-center">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">المرحلة الثالثة: مرحلة التشغيل</h1>
          <p className="text-xl md:text-2xl text-teal-100 max-w-3xl mx-auto">تفعيل الأنظمة الإدارية والفنية والمالية، وبدء العمليات اليومية لتحقيق أهداف الكيان.</p>
        </div>
      </div>

      <div className="container mx-auto px-6 py-10">
        
        {/* Systems Cards */}
         <div className="flex flex-col md:flex-row gap-6 mb-12">
            <div className="flex-1 bg-white p-6 rounded-xl shadow-md border-r-4 border-teal-500">
               <div className="flex items-center gap-3 mb-3">
                 <Settings className="text-teal-500" />
                 <h3 className="text-xl font-bold">النظام الفني</h3>
               </div>
               <p className="text-gray-600 text-sm">يشمل الجانب المهني والتقني، تنظيم اللقاءات الدورية، التنسيق مع الجهات الخارجية، والمتابعة والتقييم.</p>
            </div>
             <div className="flex-1 bg-white p-6 rounded-xl shadow-md border-r-4 border-teal-500">
               <div className="flex items-center gap-3 mb-3">
                 <UserCheck className="text-teal-500" />
                 <h3 className="text-xl font-bold">النظام الإداري</h3>
               </div>
               <p className="text-gray-600 text-sm">التخطيط، التنظيم، التوجيه، والرقابة. يعتمد على العمل الجماعي بقيادة المدير التنفيذي.</p>
            </div>
        </div>

        <div className="grid lg:grid-cols-3 gap-10">
          <div className="lg:col-span-2">
            <h2 className="text-2xl font-bold text-teal-900 mb-6 border-b-2 border-teal-200 pb-2 inline-block">مكونات التشغيل الأساسية</h2>

            <StepItem number="01" title="الإعداد والتهيئة">
               <p className="text-gray-600">تهيئة نهائية للبيئة الفنية وتوفير خدمات ومواد قبيل البدء. تشمل العقود التشغيلية وتوفير الكفاءة المهنية.</p>
            </StepItem>

            <StepItem number="02" title="التكاليف والعوائد المالية">
               <p className="text-gray-600 mb-2">إدارة بيان الدخل والمؤشرات المالية.</p>
               <ul className="list-disc list-inside text-sm text-gray-500 bg-gray-50 p-3 rounded">
                 <li>تكاليف ثابتة (رواتب، إيجار).</li>
                 <li>تكاليف متغيرة (تسويق، صيانة).</li>
               </ul>
            </StepItem>

            <StepItem number="03" title="العمليات الإدارية اليومية">
               <div className="grid gap-3">
                 <div className="p-3 border border-gray-200 rounded">
                    <strong>عملية الخدمات:</strong> تقديم الخدمات بأعلى جودة وضمان رضا المستفيدين.
                 </div>
                 <div className="p-3 border border-gray-200 rounded">
                    <strong>إدارة يوم العمل:</strong> تنظيم الوقت، توزيع المهام، ومتابعة الأداء.
                 </div>
                 <div className="p-3 border border-gray-200 rounded">
                    <strong>الكادر الإداري:</strong> إدارة الموارد البشرية، التدريب، والتقييم.
                 </div>
               </div>
            </StepItem>

            <StepItem number="04" title="الخطة التنفيذية والتشغيلية">
               <p className="text-gray-600">وثيقة عمل تفصيلية تحدد الأعمال والمهام، المؤشرات، المسؤوليات، والجداول الزمنية.</p>
            </StepItem>
             
            <StepItem number="05" title="نماذج المتابعة">
               <p className="text-gray-600">رصد وتوثيق: مستوى الانحراف عن المخطط، الإنجازات، الصعوبات، والدروس المستفادة.</p>
            </StepItem>

          </div>
          
           {/* Sidebar */}
           <div className="space-y-6">
              <div className="bg-teal-900 text-white p-6 rounded-xl">
                 <h3 className="text-lg font-bold mb-4 flex items-center gap-2">
                   <CreditCard /> الاحتياج المالي
                 </h3>
                 <p className="text-teal-100 text-sm mb-4">
                   من أهم روافد إقامة "تكامل" توفر المورد المالي لتغطية المصاريف التأسيسية والتشغيلية.
                 </p>
                 <div className="text-xs bg-white/10 p-3 rounded space-y-2">
                    <p>• استثمار المرافق</p>
                    <p>• التسويق على المانحين</p>
                    <p>• ترشيد التكاليف</p>
                 </div>
              </div>

               <div className="bg-red-50 p-6 rounded-xl border border-red-100">
                 <h3 className="text-lg font-bold text-red-800 mb-2 flex items-center gap-2">
                   <FileText size={20} /> إدارة المخاطر
                 </h3>
                 <p className="text-sm text-gray-700 mb-2">
                   جزء أساسي من التخطيط. تشمل:
                 </p>
                 <ul className="text-xs text-gray-600 space-y-1">
                    <li>- مخاطر غير متوقعة (كوارث).</li>
                    <li>- مخاطر متوقعة (حرائق، إصابات، دعاوى).</li>
                 </ul>
              </div>
           </div>
        </div>
      </div>
    </div>
  );
};

export default Phase3;
