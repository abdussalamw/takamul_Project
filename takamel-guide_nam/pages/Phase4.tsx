import React from 'react';
import { PhaseHero, FeatureCard } from '../components/ui';
import { TrendingUp, Award, Cpu, Globe, Anchor, Lightbulb } from 'lucide-react';

const Phase4: React.FC = () => {
  return (
    <div className="animate-fade-in">
      <div className="relative bg-purple-600 text-white py-16 px-6 overflow-hidden rounded-b-3xl mb-10">
        <div className="absolute inset-0 opacity-20">
          <img src={`https://picsum.photos/id/180/1200/400`} alt="Background" className="w-full h-full object-cover" />
        </div>
        <div className="container mx-auto relative z-10 text-center">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">المرحلة الرابعة: مرحلة التطوير</h1>
          <p className="text-xl md:text-2xl text-purple-100 max-w-3xl mx-auto">الارتقاء بالأداء، تحقيق التميز المؤسسي، وتبني الابتكار والتقنية لضمان الاستدامة.</p>
        </div>
      </div>

      <div className="container mx-auto px-6 py-10">
        
        <div className="text-center mb-12">
          <h2 className="text-3xl font-bold text-purple-900 mb-4 border-b-2 border-purple-200 pb-2 inline-block">محاور التطوير الأساسية</h2>
          <p className="text-gray-600 max-w-2xl mx-auto">
            يعرف التطوير بأنه التغيير والتحول من طور إلى طور بهدف إحداث آثار إيجابية. يرتكز في "تكامل" على تسعة محاور:
          </p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <FeatureCard 
            icon={Award} 
            title="التميز المؤسسي" 
            description="خلق مزايا تنافسية فريدة وتحقيق أداء متميز عبر الجودة والتنفيذ والتحسين المستمر." 
          />
          <FeatureCard 
            icon={Anchor} 
            title="الجودة" 
            description="مقياس للتميز والكفاءة في المنتجات والخدمات والعمليات. (السعي لشهادة ISO)." 
            delay={100}
          />
          <FeatureCard 
            icon={TrendingUp} 
            title="الاستدامة المالية" 
            description="تحقيق توازن بين النفقات والإيرادات لضمان استمرار العمل دون ديون متراكمة." 
            delay={200}
          />
           <FeatureCard 
            icon={Globe} 
            title="التخطيط الاستراتيجي" 
            description="تحديد الأهداف طويلة المدى وتوجيه الموارد والقرارات نحو تحقيق الرؤية." 
            delay={300}
          />
           <FeatureCard 
            icon={Lightbulb} 
            title="الابتكار" 
            description="ابتكار خدمات أو أساليب عمل جديدة من خلال البحث المستمر لتحقيق التقدم." 
            delay={400}
          />
           <FeatureCard 
            icon={Cpu} 
            title="التحول الرقمي" 
            description="استخدام الذكاء الاصطناعي والتقنية لإنجاز الأعمال بإبداع وإنتاجية أعلى." 
            delay={500}
          />
        </div>

        <div className="mt-16 bg-gradient-to-l from-takamel-dark to-takamel-primary text-white p-8 rounded-2xl shadow-xl">
           <div className="md:flex items-center justify-between">
              <div className="mb-6 md:mb-0 md:w-2/3">
                 <h3 className="text-2xl font-bold mb-3">الخاتمة</h3>
                 <p className="leading-relaxed opacity-90">
                   نأمل أن يكون هذا الدليل قد قدم المعلومات الضرورية والمفيدة التي تساهم في تحقيق الأهداف المرجوة بأفضل صورة ممكنة، 
                   لتحقيق التنمية المستدامة والتعاون المثمر بين الجهات.
                 </p>
              </div>
              <div className="md:w-1/3 text-center">
                 <div className="inline-block border-2 border-white px-6 py-2 rounded-full font-bold">
                   نسأل الله التوفيق
                 </div>
              </div>
           </div>
        </div>

      </div>
    </div>
  );
};

export default Phase4;
