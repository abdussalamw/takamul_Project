import { Mail, MapPin, Phone } from 'lucide-react';

export default function Footer() {
  return (
    <footer className="bg-primary text-white mt-20">
      <div className="container py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
          {/* About */}
          <div>
            <div className="flex items-center gap-3 mb-4">
              <img
                src="/images/logo2-01.jpg"
                alt="شعار تكامل"
                className="h-12 w-auto object-contain"
              />
              <div>
                <h3 className="text-xl font-bold text-secondary">تكامل</h3>
                <p className="text-sm text-white/70">المجموعة التنسيقية للكيانات النسائية العاملة في الرياض</p>
              </div>
            </div>
            <p className="text-white/80 leading-relaxed text-sm">
              مجموعة تنسيقية تضم (30) جهة تعمل في المجال التنموي النسائي بمدينة الرياض.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-xl font-bold mb-4 text-secondary">روابط سريعة</h3>
            <ul className="space-y-2 text-sm">
              <li>
                <a href="https://ccsa.org.sa/" target="_blank" rel="noopener noreferrer" className="text-white/80 hover:text-secondary transition-colors">
                  مجلس الجمعيات الأهلية
                </a>
              </li>
              <li>
                <a href="https://majlis-ngos.org/" target="_blank" rel="noopener noreferrer" className="text-white/80 hover:text-secondary transition-colors">
                  اللجنة التنسيقية للجمعيات النسائية
                </a>
              </li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="text-xl font-bold mb-4 text-secondary">تواصل معنا</h3>
            <div className="space-y-3 text-sm">
              <div className="flex items-center gap-2">
                <Mail size={18} className="text-secondary" />
                <span className="text-white/80">takamul15@gmail.com</span>
              </div>
              <div className="flex items-center gap-2">
                <Phone size={18} className="text-secondary" />
                <span className="text-white/80">0560341046</span>
              </div>
              <div className="flex items-start gap-2">
                <MapPin size={18} className="text-secondary mt-1 flex-shrink-0" />
                <span className="text-white/80">الرياض، المملكة العربية السعودية</span>
              </div>
            </div>

            {/* Social Links */}
            <div className="mt-6">
              <h4 className="text-lg font-semibold mb-3 text-secondary">تابعنا</h4>
              <div className="flex gap-3">
                <a href="#" className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-secondary transition-colors">
                  <span className="text-white text-sm">📘</span>
                </a>
                <a href="#" className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-secondary transition-colors">
                  <span className="text-white text-sm">🐦</span>
                </a>
                <a href="#" className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-secondary transition-colors">
                  <span className="text-white text-sm">📷</span>
                </a>
              </div>
            </div>
          </div>
        </div>

        {/* Divider */}
        <div className="border-t border-white/20 pt-8">
          <div className="text-center text-sm text-white/70">
            <p>&copy; 2025 مجموعة تكامل - جميع الحقوق محفوظة</p>
          </div>
        </div>
      </div>
    </footer>
  );
}
