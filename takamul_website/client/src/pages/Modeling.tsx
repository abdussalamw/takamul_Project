import Navigation from '@/components/Navigation';
import Footer from '@/components/Footer';
import { CheckCircle2, FileText, Download, ArrowRight } from 'lucide-react';

export default function Modeling() {
  const stages = [
    {
      number: 1,
      title: 'وضع الأهداف والرؤية',
      description: 'وضع أهداف ورسالة ورؤية مشتركة للمجموعة',
      icon: '🎯',
    },
    {
      number: 2,
      title: 'تحديد الجهات',
      description: 'تحديد الجهات ذات العلاقة من بين الجمعيات',
      icon: '🏢',
    },
    {
      number: 3,
      title: 'دعوة الجهات',
      description: 'مخاطبة الجهات ودعوتهم للانضمام للمجموعة',
      icon: '📧',
    },
    {
      number: 4,
      title: 'الاجتماع التعريفي',
      description: 'عقد الاجتماع التعريفي الأول مع الجمعيات المنظمة',
      icon: '👥',
    },
    {
      number: 5,
      title: 'التكليف الرسمي',
      description: 'اصدار تكليف رسمي للمجموعة من قبل اللجنة التنسيقية',
      icon: '📜',
    },
    {
      number: 6,
      title: 'تعيين المدير التنفيذي',
      description: 'تعيين المدير التنفيذي للمجموعة',
      icon: '👔',
    },
    {
      number: 7,
      title: 'الخطة التنفيذية',
      description: 'رسم خطة تنفيذية سنوية مع موازنتها التشغيلية',
      icon: '📋',
    },
    {
      number: 8,
      title: 'بدء التنفيذ',
      description: 'البدء بتنفيذ الخطة وتحقيق الأهداف',
      icon: '🚀',
    },
  ];

  return (
    <div className="min-h-screen flex flex-col">
      <Navigation />

      <main className="flex-1">
        {/* Hero Section */}
        <section className="py-16 bg-gradient-to-r from-primary/10 to-secondary/10">
          <div className="container">
            <div className="max-w-3xl">
              <h1 className="text-4xl md:text-5xl font-bold text-primary mb-4">نمذجة المجموعة</h1>
              <p className="text-lg text-muted-foreground mb-6">
                نموذج احترافي في العمل التنسيقي المشترك بين الجهات التنموية النسائية
              </p>
              <p className="text-base text-muted-foreground">
                تمثل مجموعة تكامل نموذجاً احترافياً يقوم على أسس علمية وإدارية متقنة، حيث تم تطوير هذا النموذج ليكون قابلاً للتطبيق والتوسع ليشمل جهات أخرى في مناطق مختلفة.
              </p>
            </div>
          </div>
        </section>

        {/* Download Section */}
        <section className="py-12 bg-white border-b border-border">
          <div className="container">
            <div className="bg-gradient-to-r from-primary/5 to-secondary/5 rounded-xl p-8 border border-primary/10">
              <div className="flex items-center justify-between flex-col md:flex-row gap-6">
                <div>
                  <h3 className="text-2xl font-bold text-primary mb-2">الدليل الإجرائي لتكامل</h3>
                  <p className="text-muted-foreground">
                    وثيقة شاملة تحتوي على جميع الإجراءات والعمليات والنماذج المستخدمة
                  </p>
                </div>
                <a
                  href="https://takamulgroup.org/media/%D8%A7%D9%84%D8%AF%D9%84%D9%8A%D9%84%20%D8%A7%D9%84%D8%A7%D8%AC%D8%B1%D8%A7%D8%A6%D9%8A%20%D9%84%D8%AA%D9%83%D8%A7%D9%85%D9%84.pdf"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-lg hover:shadow-lg transition-all duration-300 font-medium whitespace-nowrap"
                >
                  <Download size={20} />
                  تحميل الدليل
                </a>
              </div>
            </div>
          </div>
        </section>

        {/* Stages Section */}
        <section className="py-20">
          <div className="container">
            <div className="max-w-3xl mx-auto text-center mb-12">
              <h2 className="text-3xl font-bold text-primary mb-4">مراحل التأسيس والانطلاق</h2>
              <p className="text-lg text-muted-foreground">
                ثمانية مراحل أساسية تم اتباعها في بناء وتطوير مجموعة تكامل
              </p>
            </div>

            {/* Timeline */}
            <div className="relative">
              {/* Vertical line for desktop */}
              <div className="hidden md:block absolute right-1/2 top-0 bottom-0 w-1 bg-gradient-to-b from-primary to-secondary"></div>

              <div className="space-y-8">
                {stages.map((stage, index) => (
                  <div key={stage.number} className="relative">
                    {/* Timeline dot */}
                    <div className="hidden md:flex absolute right-1/2 top-8 -translate-x-1/2 -translate-y-1/2 items-center justify-center">
                      <div className="w-16 h-16 bg-white border-4 border-primary rounded-full flex items-center justify-center font-bold text-primary text-lg shadow-lg">
                        {stage.number}
                      </div>
                    </div>

                    {/* Content */}
                    <div className={`md:w-1/2 ${index % 2 === 0 ? 'md:mr-auto md:pr-12' : 'md:ml-auto md:pl-12'}`}>
                      <div className="bg-white border border-border rounded-xl p-8 hover:shadow-lg transition-all duration-300">
                        <div className="flex items-start gap-4 md:hidden mb-4">
                          <div className="w-12 h-12 bg-gradient-to-br from-primary to-secondary rounded-lg flex items-center justify-center flex-shrink-0">
                            <span className="text-white font-bold">{stage.number}</span>
                          </div>
                        </div>

                        <div className="text-3xl mb-3">{stage.icon}</div>
                        <h3 className="text-xl font-bold text-primary mb-2">{stage.title}</h3>
                        <p className="text-muted-foreground">{stage.description}</p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </section>

        {/* Key Achievements Section */}
        <section className="py-20 bg-muted/30">
          <div className="container">
            <div className="max-w-3xl mx-auto text-center mb-12">
              <h2 className="text-3xl font-bold text-primary mb-4">نتائج النموذج</h2>
              <p className="text-lg text-muted-foreground">
                إنجازات ملموسة حققها النموذج منذ تطبيقه
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {[
                {
                  title: 'الشراكات',
                  value: '100+',
                  description: 'شراكة مبرمة بين جهات تكامل',
                  icon: '🤝',
                },
                {
                  title: 'التدريب',
                  value: '8+',
                  description: 'مدراء تنفيذيين تم تدريبهم',
                  icon: '🎓',
                },
                {
                  title: 'البرامج الصيفية',
                  value: '1447',
                  description: 'إصدار دليل البرامج الصيفية',
                  icon: '☀️',
                },
                {
                  title: 'التكريم',
                  value: '600',
                  description: 'مديرة ومشرفة ومتطوعة مكرمة',
                  icon: '🏆',
                },
                {
                  title: 'الاجتماعات',
                  value: '17',
                  description: 'اجتماع لأعضاء المجموعة',
                  icon: '👥',
                },
                {
                  title: 'المرافق المشتركة',
                  value: '15+',
                  description: 'جهة تستفيد من المقرات والموارد',
                  icon: '🏢',
                },
              ].map((achievement, index) => (
                <div
                  key={index}
                  className="bg-white rounded-xl p-6 border border-border hover:shadow-lg transition-all duration-300"
                >
                  <div className="text-4xl mb-3">{achievement.icon}</div>
                  <div className="text-3xl font-bold text-primary mb-1">{achievement.value}</div>
                  <h3 className="font-bold text-foreground mb-1">{achievement.title}</h3>
                  <p className="text-sm text-muted-foreground">{achievement.description}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Features Section */}
        <section className="py-20">
          <div className="container">
            <div className="max-w-3xl mx-auto text-center mb-12">
              <h2 className="text-3xl font-bold text-primary mb-4">مميزات النموذج</h2>
              <p className="text-lg text-muted-foreground">
                خصائص تميز نموذج تكامل عن غيره
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {[
                {
                  title: 'الاحترافية',
                  description: 'نموذج احترافي قائم على أسس علمية وإدارية متقنة',
                },
                {
                  title: 'التنسيق المشترك',
                  description: 'عمل تنسيقي فعال بين جهات متعددة بأهداف موحدة',
                },
                {
                  title: 'تبادل الخبرات',
                  description: 'نقل المعرفة والخبرات بين الجهات الأعضاء',
                },
                {
                  title: 'الاستدامة',
                  description: 'نموذج قابل للتطبيق والتوسع في مناطق أخرى',
                },
                {
                  title: 'الشفافية',
                  description: 'وضوح الأدوار والمسؤوليات والعمليات',
                },
                {
                  title: 'التطوير المستمر',
                  description: 'تحسين مستمر للعمليات والممارسات',
                },
              ].map((feature, index) => (
                <div key={index} className="flex gap-4">
                  <div className="flex-shrink-0">
                    <CheckCircle2 className="text-secondary" size={24} />
                  </div>
                  <div>
                    <h3 className="font-bold text-foreground mb-1">{feature.title}</h3>
                    <p className="text-muted-foreground">{feature.description}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* CTA Section */}
        <section className="py-20 bg-gradient-to-r from-primary to-secondary/20">
          <div className="container">
            <div className="max-w-2xl mx-auto text-center text-white">
              <h2 className="text-3xl font-bold mb-4">هل تريد معرفة المزيد؟</h2>
              <p className="text-lg mb-8 text-white/90">
                تحميل الدليل الإجرائي الشامل لفهم كامل النموذج والعمليات
              </p>
              <a
                href="https://takamulgroup.org/media/%D8%A7%D9%84%D8%AF%D9%84%D9%8A%D9%84%20%D8%A7%D9%84%D8%A7%D8%AC%D8%B1%D8%A7%D8%A6%D9%8A%20%D9%84%D8%AA%D9%83%D8%A7%D9%85%D9%84.pdf"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 px-8 py-3 bg-white text-primary rounded-lg hover:shadow-lg transition-all duration-300 font-bold"
              >
                <FileText size={20} />
                تحميل الدليل الإجرائي
              </a>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}
