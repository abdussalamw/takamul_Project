import Navigation from '@/components/Navigation';
import Footer from '@/components/Footer';
import Carousel from '@/components/Carousel';
import { Users, Target, Heart, TrendingUp, Award, Handshake, CheckCircle2, Download, FileText } from 'lucide-react';
import { Link } from 'wouter';

export default function Home() {
  const integrationFields = [
    {
      id: 1,
      title: 'المساهمة في تطوير وتحسين أداء الكيانات',
      description: 'المساهمة في تطوير وتحسين أداء الكيانات',
      icon: '📈',
    },
    {
      id: 2,
      title: 'إقامة برامج مشتركة',
      description: 'إقامة برامج مشتركة',
      icon: '🎯',
    },
    {
      id: 3,
      title: 'مشاركة الكوادر البشرية',
      description: 'مشاركة الكوادر البشرية',
      icon: '👥',
    },
    {
      id: 4,
      title: 'التدريب والتطوير المشترك',
      description: 'التدريب والتطوير المشترك',
      icon: '🎓',
    },
    {
      id: 5,
      title: 'تبادل المعرفة',
      description: 'تبادل المعرفة',
      icon: '💡',
    },
    {
      id: 6,
      title: 'خدمات مساندة تشاركية',
      description: 'خدمات مساندة تشاركية',
      icon: '🤝',
    },
  ];

  return (
    <div className="min-h-screen flex flex-col" dir="rtl">
      <Navigation />

      <main className="flex-1">
        {/* Quick Links Section */}
        <section className="py-4 bg-secondary/10 border-b border-secondary/20">
          <div className="container">
            <div className="flex flex-col md:flex-row gap-3 justify-center md:justify-end">
              <a
                href="https://takamulgroup.org/uploads/settings/guide_pdf_path_1751410271.pdf"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-white rounded-lg hover:shadow-md transition-all duration-300 font-medium text-sm"
              >
                <Download size={18} />
                دليل برامج الصيف
              </a>
              <a
                href="https://takamulgroup.org/media/%D8%A7%D9%84%D8%AF%D9%84%D9%8A%D9%84%20%D8%A7%D9%84%D8%A7%D8%AC%D8%B1%D8%A7%D8%A6%D9%8A%20%D9%84%D8%AA%D9%83%D8%A7%D9%85%D9%84.pdf"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:shadow-md transition-all duration-300 font-medium text-sm"
              >
                <FileText size={18} />
                الدليل الإجرائي
              </a>
            </div>
          </div>
        </section>

        {/* Hero Section */}
        <section className="py-20 md:py-32 bg-gradient-to-br from-primary/10 via-secondary/5 to-background">
          <div className="container">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
              <div>
                <div className="inline-block px-4 py-2 bg-secondary/20 rounded-full mb-6">
                  <span className="text-primary font-semibold text-sm">مرحباً بك في تكامل</span>
                </div>
                <h1 className="text-4xl md:text-5xl font-bold text-primary mb-6 leading-tight">
                  التكامل والتميز في العمل المؤسسي المشترك
                </h1>
                <p className="text-lg text-muted-foreground mb-8 leading-relaxed">
                  مجموعة تنسيقية تضم كيانات مهتمة بالعمل في المجال التنموي النسائي بمدينة الرياض.
                </p>
                <div className="flex flex-col sm:flex-row gap-4">
                  <a
                    href="#fields"
                    className="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:shadow-lg transition-all duration-300 font-semibold"
                  >
                    استكشف مجالات التكامل
                  </a>
                  <a
                    href="#about"
                    className="inline-flex items-center justify-center px-6 py-3 border-2 border-primary text-primary rounded-lg hover:bg-primary/5 transition-all duration-300 font-semibold"
                  >
                    تعرف علينا أكثر
                  </a>
                </div>
              </div>
              <div className="hidden md:block">
                <div className="w-full h-96 bg-gradient-to-br from-primary to-secondary rounded-2xl shadow-2xl flex flex-col items-center justify-center p-8">
                  <img src="/images/logopen-03.png" alt="شعار تكامل" className="h-64 w-auto object-contain mb-6" />
                  <p className="text-white text-center text-lg font-medium leading-relaxed max-w-md">
                    مجموعة تنسيقية تضم كيانات مهتمة بالعمل في المجال التنموي النسائي بمدينة الرياض.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Statistics Section */}
        <section className="py-16 bg-muted/50">
          <div className="container">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
              <div className="text-center p-8 bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                <div className="text-5xl font-bold text-primary mb-3">30+</div>
                <p className="text-muted-foreground font-medium text-lg">جهة عضو</p>
              </div>
              <div className="text-center p-8 bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                <div className="text-5xl font-bold text-secondary mb-3">600</div>
                <p className="text-muted-foreground font-medium text-lg">مكرمة ومتطوعة</p>
              </div>
              <div className="text-center p-8 bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                <div className="text-5xl font-bold text-primary mb-3">8+</div>
                <p className="text-muted-foreground font-medium text-lg">دورة تدريبية وورشة عمل</p>
              </div>
            </div>
          </div>
        </section>

        {/* Integration Fields Section */}
        <section id="fields" className="py-20 bg-gradient-to-br from-primary/5 to-secondary/5">
          <div className="container">
            <div className="max-w-2xl mx-auto text-center mb-12">
              <h2 className="text-3xl md:text-4xl font-bold text-primary mb-4">مجالات التكامل</h2>
              <p className="text-lg text-muted-foreground">
                ستة مجالات أساسية نعمل من خلالها على تحقيق التكامل والتنسيق بين الجهات الأعضاء
              </p>
            </div>

            <Carousel items={integrationFields} />
          </div>
        </section>

        {/* About Section */}
        <section id="about" className="py-20">
          <div className="container">
            <div className="max-w-2xl mx-auto text-center mb-12">
              <h2 className="text-3xl md:text-4xl font-bold text-primary mb-4">من نحن</h2>
              <p className="text-lg text-muted-foreground">
                مجموعة تنسيقية احترافية تعمل على تحقيق التكامل والتميز المؤسسي
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
              <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                <div className="text-4xl mb-4">🎯</div>
                <h3 className="text-2xl font-bold text-primary mb-4">الرؤية</h3>
                <p className="text-muted-foreground leading-relaxed">
                  الريادة في العمل التنسيقي المشترك بين الجهات العاملة مع الفتيات، بما يحقق تكامل الجهود وترشيد الموارد.
                </p>
              </div>

              <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                <div className="text-4xl mb-4">❤️</div>
                <h3 className="text-2xl font-bold text-primary mb-4">الرسالة</h3>
                <p className="text-muted-foreground leading-relaxed">
                  تنسيق الجهود بين الجهات العاملة مع الفتيات، وتعزيز التكامل وتبادل الخبرات والموارد، عبر برامج ومبادرات مشتركة وبناء القدرات المؤسسية، بما يسهم في ترشيد الموارد وتحسين كفاءة العمل التنسيقي.
                </p>
              </div>
            </div>

            <div className="bg-gradient-to-r from-primary/5 to-secondary/5 rounded-xl p-8 border border-primary/10">
              <h3 className="text-2xl font-bold text-primary mb-6">معلومات عن المجموعة</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="flex gap-4">
                  <CheckCircle2 className="text-secondary flex-shrink-0 mt-1" size={24} />
                  <div>
                    <p className="font-bold text-foreground mb-1">تاريخ النشأة</p>
                    <p className="text-muted-foreground">10/03/1442هـ الموافق: 27/10/2020م</p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <CheckCircle2 className="text-secondary flex-shrink-0 mt-1" size={24} />
                  <div>
                    <p className="font-bold text-foreground mb-1">عدد الجهات الأعضاء</p>
                    <p className="text-muted-foreground">30 جهة تنموية نسائية</p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <CheckCircle2 className="text-secondary flex-shrink-0 mt-1" size={24} />
                  <div>
                    <p className="font-bold text-foreground mb-1">الموقع</p>
                    <p className="text-muted-foreground">مدينة الرياض، المملكة العربية السعودية</p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <CheckCircle2 className="text-secondary flex-shrink-0 mt-1" size={24} />
                  <div>
                    <p className="font-bold text-foreground mb-1">النموذج</p>
                    <p className="text-muted-foreground">نموذج احترافي في العمل التنسيقي المشترك</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Objectives Section */}
        <section id="objectives" className="py-20 bg-muted/30">
          <div className="container">
            <div className="max-w-2xl mx-auto text-center mb-12">
              <h2 className="text-3xl md:text-4xl font-bold text-primary mb-4">أهدافنا</h2>
              <p className="text-lg text-muted-foreground">
                مجموعة من الأهداف الاستراتيجية التي نسعى لتحقيقها
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {[
                { icon: '👥', title: 'التكامل والتنسيق', desc: 'تحقيق التكامل والتنسيق والشراكات وتبادل الخبرات بين الكيانات المهتمة بالفتيات' },
                { icon: '🎯', title: 'البرامج المشتركة', desc: 'تقديم برامج مشتركة للتطوير والتدريب' },
                { icon: '❤️', title: 'تعزيز الترابط', desc: 'تعزيز أواصر الترابط وبناء الثقة بين الكيانات' },
                { icon: '🏆', title: 'البرامج النوعية', desc: 'إقامة برامج نوعية مشتركة' },
                { icon: '🤝', title: 'التشارك في الخدمات', desc: 'التشارك في الخدمات' },
                { icon: '📈', title: 'التطوير المستمر', desc: 'تطوير العمليات والممارسات بشكل مستمر' },
              ].map((obj, idx) => (
                <div key={idx} className="bg-white border border-border rounded-xl p-6 hover:shadow-lg transition-all duration-300">
                  <div className="text-4xl mb-3">{obj.icon}</div>
                  <h3 className="text-lg font-bold text-primary mb-2">{obj.title}</h3>
                  <p className="text-muted-foreground text-sm">{obj.desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Member Logos Section */}
        <section id="logos" className="py-16 bg-gradient-to-br from-white to-primary/5">
          <div className="container">
            <div className="text-center mb-12">
              <h2 className="text-3xl md:text-4xl font-bold text-primary mb-4">الجهات الأعضاء</h2>
              <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                مجموعة من 30 جهة تنموية نسائية تعمل معاً لتحقيق التكامل والتميز المؤسسي
              </p>
            </div>

            {/* السطر الأول: من اليمين لليسار → */}
            <div className="logo-row row-1 mb-6">
              <div className="logo-slider-track">
                <div className="logo-slide"><img src="/images/logos/إثراء المعرفة.png" alt="إثراء المعرفة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية إيراق.png" alt="جمعية إيراق" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/دعوتها.png" alt="دعوتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/شعار رسالتها.png" alt="رسالتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/عالم غراس.png" alt="عالم غراس" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مجمع نورين.png" alt="مجمع نورين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وابل.jpg" alt="مركز وابل" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وارث.png" alt="مركز وارث" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكنون.jpg" alt="مكنون" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكين.jpeg" alt="مكين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/همة.png" alt="همة" className="logo-image" /></div>
                {/* نسخة مكررة للحلقة المستمرة */}
                <div className="logo-slide"><img src="/images/logos/إثراء المعرفة.png" alt="إثراء المعرفة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية إيراق.png" alt="جمعية إيراق" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/دعوتها.png" alt="دعوتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/شعار رسالتها.png" alt="رسالتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/عالم غراس.png" alt="عالم غراس" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مجمع نورين.png" alt="مجمع نورين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وابل.jpg" alt="مركز وابل" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وارث.png" alt="مركز وارث" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكنون.jpg" alt="مكنون" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكين.jpeg" alt="مكين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/همة.png" alt="همة" className="logo-image" /></div>
              </div>
            </div>

            {/* السطر الثاني: من اليسار لليمين ← (عكس) */}
            <div className="logo-row row-2 mb-6">
              <div className="logo-slider-track">
                <div className="logo-slide"><img src="/images/logos/إثراء المعرفة.png" alt="إثراء المعرفة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية إيراق.png" alt="جمعية إيراق" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/دعوتها.png" alt="دعوتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/شعار رسالتها.png" alt="رسالتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/عالم غراس.png" alt="عالم غراس" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مجمع نورين.png" alt="مجمع نورين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وابل.jpg" alt="مركز وابل" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وارث.png" alt="مركز وارث" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكنون.jpg" alt="مكنون" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكين.jpeg" alt="مكين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/همة.png" alt="همة" className="logo-image" /></div>
                {/* نسخة مكررة */}
                <div className="logo-slide"><img src="/images/logos/إثراء المعرفة.png" alt="إثراء المعرفة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية إيراق.png" alt="جمعية إيراق" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/دعوتها.png" alt="دعوتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/شعار رسالتها.png" alt="رسالتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/عالم غراس.png" alt="عالم غراس" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مجمع نورين.png" alt="مجمع نورين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وابل.jpg" alt="مركز وابل" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وارث.png" alt="مركز وارث" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكنون.jpg" alt="مكنون" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكين.jpeg" alt="مكين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/همة.png" alt="همة" className="logo-image" /></div>
              </div>
            </div>

            {/* السطر الثالث: من اليمين لليسار → (مثل الأول) */}
            <div className="logo-row row-3">
              <div className="logo-slider-track">
                <div className="logo-slide"><img src="/images/logos/إثراء المعرفة.png" alt="إثراء المعرفة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية إيراق.png" alt="جمعية إيراق" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/دعوتها.png" alt="دعوتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/شعار رسالتها.png" alt="رسالتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/عالم غراس.png" alt="عالم غراس" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مجمع نورين.png" alt="مجمع نورين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وابل.jpg" alt="مركز وابل" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وارث.png" alt="مركز وارث" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكنون.jpg" alt="مكنون" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكين.jpeg" alt="مكين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/همة.png" alt="همة" className="logo-image" /></div>
                {/* نسخة مكررة */}
                <div className="logo-slide"><img src="/images/logos/إثراء المعرفة.png" alt="إثراء المعرفة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/باحثات لدراسات المرأة.jpg" alt="باحثات لدراسات المرأة" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية أفكار الاجتماعية.png" alt="جمعية أفكار الاجتماعية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية إيراق.png" alt="جمعية إيراق" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية التنمية بالحمراء.png" alt="جمعية التنمية بالحمراء" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية الوقف الخيرية.png" alt="جمعية الوقف الخيرية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية فتاة العشرين.png" alt="جمعية فتاة العشرين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية كون النسائية.jpg" alt="جمعية كون النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/جمعية مكث النسائية.jpg" alt="جمعية مكث النسائية" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/دعوتها.png" alt="دعوتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/شعار رسالتها.png" alt="رسالتها" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/عالم غراس.png" alt="عالم غراس" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مجمع نورين.png" alt="مجمع نورين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز آسية الوقفي.png" alt="مركز آسية الوقفي" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز الرسالة للفتيات.png" alt="مركز الرسالة للفتيات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز صناعة الأفكار للاستشارات.png" alt="مركز صناعة الأفكار للاستشارات" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وابل.jpg" alt="مركز وابل" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مركز وارث.png" alt="مركز وارث" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكنون.jpg" alt="مكنون" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/مكين.jpeg" alt="مكين" className="logo-image" /></div>
                <div className="logo-slide"><img src="/images/logos/همة.png" alt="همة" className="logo-image" /></div>
              </div>
            </div>
          </div>
        </section>

        {/* Achievements Section */}
        <section id="achievements" className="py-20">
          <div className="container">
            <div className="max-w-2xl mx-auto text-center mb-12">
              <h2 className="text-3xl md:text-4xl font-bold text-primary mb-4">إنجازاتنا</h2>
              <p className="text-lg text-muted-foreground">
                إنجازات ملموسة حققتها المجموعة في مختلف المجالات
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                <h3 className="text-xl font-bold text-primary mb-6">الاجتماعات واللقاءات</h3>
                <ul className="space-y-3">
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">عقد 18 اجتماع لأعضاء المجموعة ـ والاجتماع التاسع عشر بعد اسبوع</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">إقامة 5 لقاءات اجتماعية للمجموعة للرجال وللنساء</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">عقد لقاءين لجهات تكامل مع المؤسسات المانحة لعرض المشاريع (بحضور 13 – 18 مؤسسة مانحة)</span>
                  </li>
                </ul>
              </div>

              <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                <h3 className="text-xl font-bold text-primary mb-6">التدريب والتطوير</h3>
                <ul className="space-y-3">
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">تقديم دورة تدريبية عن صناعة المشاريع المميزة قبل لقاء المؤسسات المانحة</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">إقامة سبعة دورات تدريبية مشتركة على مستوى القيادات والفريق التنفيذي</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">الانتهاء من تدريب 8 مدراء تنفيذيين – دورة إدارة المشاريع – دورة السكرتارية ..الخ</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">إقامة دورة المديرات التنفيذية والتي استفادة منها قائدة الكيانات المنظمة لتكامل</span>
                  </li>
                </ul>
              </div>

              <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                <h3 className="text-xl font-bold text-primary mb-6">الشراكات والتبادل</h3>
                <ul className="space-y-3">
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">عقد عدد من الشراكة بين جهات تكامل</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">إقامة عدد من الزيارات البينية بين جهات تكامل لتبادل الخبرات والمعلومات</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">زيارة جهات تكامل الى الجهات المشابهة في مكة المكرمة وجدة ـ الشرقية</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">زيارات بعض الجهات النسائية من خارج الرياض لمجموعة تكامل لتفعيل الشراكة والاستفادة من التجربة</span>
                  </li>
                </ul>
              </div>

              <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                <h3 className="text-xl font-bold text-primary mb-6">الخدمات والدعم</h3>
                <ul className="space-y-3">
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">تقديم العديد من الخدمات لبعض جهات تكامل (تصاميم الهويات، انشاء الخطط، الاستفادة من المقرات ..الخ)</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">اصدار دليل البرامج الصيفية لصيف عام 1444هـ - 1445هـ - 1446هـ - 1447 هـ لجميع جهات تكامل</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">تكريم المديرات والمشرفات والمتطوعات على البرامج الصيفية في كل عام من الأعوام السابقة</span>
                  </li>
                </ul>
              </div>

              <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                <h3 className="text-xl font-bold text-primary mb-6">التبادل والتعاون</h3>
                <ul className="space-y-3">
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">الاستفادة من المتطوعات من بعض الجهات التابعة لتكامل</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">توقيع عقد اتفاقية مع جهة متخصصة بالتصاميم والهويات</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">استفادة اكثر من 15 جهة من المقرات ووسائل النقل</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">الاستفادة من البرامج المحاسبية لدى بعض الجهات</span>
                  </li>
                </ul>
              </div>

              <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                <h3 className="text-xl font-bold text-primary mb-6">التطوير المؤسسي</h3>
                <ul className="space-y-3">
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">العمل على نمذجة عمل مجموعة تكامل</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">تعيين مساعد للمدير التنفيذي</span>
                  </li>
                  <li className="flex gap-3">
                    <CheckCircle2 className="text-secondary flex-shrink-0 mt-0.5" size={20} />
                    <span className="text-muted-foreground">التعاقد مع مكتب محاسب قانوني للأعضاء الراغبين بالاستفادة من خدماته</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        {/* Modeling Section CTA */}
        <section className="py-20 bg-gradient-to-r from-primary to-secondary/20">
          <div className="container">
            <div className="max-w-2xl mx-auto text-center text-white">
              <h2 className="text-3xl md:text-4xl font-bold mb-4">نمذجة المجموعة</h2>
              <p className="text-lg mb-8 text-white/90">
                تعرف على النموذج الاحترافي لمجموعة تكامل ومراحل تأسيسها وتطورها
              </p>
              <a
                href="https://nam.takamulgroup.org/"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-8 py-3 bg-white text-primary rounded-lg hover:shadow-lg transition-all duration-300 font-bold"
              >
                اكتشف المزيد
              </a>
            </div>
          </div>
        </section>

        {/* This Year Achievements Section */}
        <section className="py-20 bg-gradient-to-br from-primary/5 to-secondary/5">
          <div className="container">
            <div className="max-w-2xl mx-auto text-center mb-12">
              <h2 className="text-3xl md:text-4xl font-bold text-primary mb-4">أعمال تم إنجازها هذا العام</h2>
              <p className="text-lg text-muted-foreground">
                إنجازات هذا العام في مسيرة مجموعة تكامل
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {[
                'الانتهاء من نمذجة عمل مجموعة تكامل',
                'زيارة الكيانات الشبيهة في المنطقة الشرقية',
                'إصدار دليل البرامج الصيفية لصيف عام 1447هـ لجميع جهات تكامل',
                'إطلاق موقع إلكتروني خاص بدليل برامج الصيف',
                'التخطيط للقاء المؤسسات المانحة بطريقة مختلفة عن اللقاءات السابقة',
                'إقامة برنامج تدريب 20 مديرة تنفيذية من جهات تكامل',
                'اطلاق موقع خاص بالمجموعة',
              ].map((achievement, idx) => (
                <div key={idx} className="flex gap-4 p-6 bg-white border border-border rounded-lg hover:shadow-md transition-all duration-300">
                  <div className="text-2xl flex-shrink-0">✓</div>
                  <p className="text-muted-foreground">{achievement}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Future Aspirations Section */}
        <section className="py-20 bg-muted/30">
          <div className="container">
            <div className="max-w-2xl mx-auto text-center mb-12">
              <h2 className="text-3xl md:text-4xl font-bold text-primary mb-4">تطلعاتنا المستقبلية</h2>
              <p className="text-lg text-muted-foreground">
                نطمح إلى تحقيق قفزات نوعية في العمل التنموي النسائي
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {[
                'نطمح في اللجنة التنسيقية إلى تحقيق قفزات نوعية في العمل التنموي النسائي عبر رؤى عصرية وشراكات مبتكرة',
                'إنشاء منظومة متكاملة للتعاون بين الجمعيات والمؤسسات النسائية والمجتمع المحلي لتوحيد الجهود وتجنب التكرار',
                'تعزيز الشراكات بين مختلف الجهات',
                'تقديم مبادرات مبتكرة تدعم الاكتفاء الذاتي والاندماج المجتمعي',
                'إعداد لوائح تنظيمية واضحة لعمل اللجنة ومبادراتها',
                'استخدام التكنولوجيا في تحسين إدارة الموارد ومتابعة الأداء',
              ].map((aspiration, idx) => (
                <div key={idx} className="flex gap-4 p-6 bg-white border border-border rounded-lg hover:shadow-md transition-all duration-300">
                  <div className="text-2xl flex-shrink-0">✓</div>
                  <p className="text-muted-foreground">{aspiration}</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}
