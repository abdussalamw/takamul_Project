<footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3>عن الدليل</h3>
                <p>دليل البرامج الصيفية للفتيات بمدينة الرياض يهدف إلى تسهيل عملية البحث عن البرامج الصيفية المناسبة وتوفير معلومات شاملة عن كافة البرامج المتاحة في مختلف مناطق الرياض.</p>
            </div>
            
            <div class="footer-col">
                <h3>روابط سريعة</h3>
                <ul class="footer-links">
                    <li><a href="https://masaaksa.com" target="_blank">منصة "ماسة" للبرامج والفعاليات النسائية</a></li>
                    <li><a href="https://majlis-ngos.org" target="_blank">اللجنة التنسيقية للجمعيات النسائية في المملكة</a></li>
                    <li><a href="https://ccsa.org.sa" target="_blank">مجلس الجمعيات الأهلية</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h3>روابط هامة</h3>
                <ul class="footer-header-nav">
                    <?php if (!empty($site_settings['whatsapp_channel_footer_enabled']) && !empty($site_settings['whatsapp_channel_url'])): ?>
                        <li><a href="<?php echo htmlspecialchars($site_settings['whatsapp_channel_url']); ?>" target="_blank"><i class="fab fa-whatsapp"></i> قناة الواتساب</a></li>
                    <?php endif; ?>
                    <?php if (!empty($site_settings['telegram_channel_footer_enabled']) && !empty($site_settings['telegram_channel_url'])): ?>
                        <li><a href="<?php echo htmlspecialchars($site_settings['telegram_channel_url']); ?>" target="_blank"><i class="fab fa-telegram"></i> قناة التليجرام</a></li>
                    <?php endif; ?>
                    <?php if (!empty($site_settings['guide_pdf_footer_enabled']) && !empty($site_settings['guide_pdf_path'])): ?>
                        <li><a href="<?php echo htmlspecialchars($site_settings['guide_pdf_path']); ?>" target="_blank"><i class="fas fa-file-pdf"></i> تحميل الدليل</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h3>اتصل بنا</h3>
                <ul class="footer-links">
                    <?php if (!empty($site_settings['contact_email'])): ?>
                        <li><i class="fas fa-envelope"></i> <a href="mailto:<?php echo htmlspecialchars($site_settings['contact_email']); ?>" style="color: #ddd; text-decoration: none;"><?php echo htmlspecialchars($site_settings['contact_email']); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($site_settings['contact_number'])): ?>
                        <li><i class="fas fa-phone"></i> <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $site_settings['contact_number']); ?>" target="_blank" style="color: #ddd; text-decoration: none;"><?php echo htmlspecialchars($site_settings['contact_number']); ?></a></li>
                    <?php endif; ?>
                    <li><i class="fas fa-map-marker-alt"></i> الرياض، المملكة العربية السعودية</li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            © <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_settings['guide_name'] ?? 'دليل البرامج الصيفية'); ?>. جميع الحقوق محفوظة لتكامل.
        </div>
    </footer>

</body>
</html>
