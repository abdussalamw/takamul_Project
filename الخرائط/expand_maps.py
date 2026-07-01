import requests
import csv
import time
from urllib.parse import urlparse

# قاموس يحتوي على جميع البرامج وروابطها القصيرة
programs_data = {
    "فُلك فَلك": "https://maps.app.goo.gl/98Rrb3NTn63jogAq7",
    "ثروة 3": "https://maps.app.goo.gl/dZLmYuQiebZwBsEE9",
    "صيفُنا أثر": "https://maps.app.goo.gl/pNQE9s8SYcibgonA7",
    "نبض": "https://goo.gl/maps/KAgdGEK1M1cMPNjC6",
    "صيف الرسالة": "https://maps.app.goo.gl/otHMWR2oVw5mXNZDA",
    "فجر": "https://maps.app.goo.gl/r4Gr9h1LZoRKXHxh9",
    "صيف باسقة": "https://goo.gl/maps/KAgdGEK1M1cMPNjC6",
    "صحبة ٢": "https://maps.app.goo.gl/VtLAaZfMDjdpHiCD8",
    "مرتقى المعاهدة": "https://maps.app.goo.gl/nwPgqbbEZWJ1B5wB9",
    "تمكين": "https://maps.app.goo.gl/Kc8A4hkCntGF7bNc6",
    "سرج": "https://maps.app.goo.gl/DDPr5h9hTDb6uunC6",
    "ألق تِك": "https://maps.app.goo.gl/qmDxPKNeJpAyYZqX7",
    "حصن": "https://maps.app.goo.gl/rRuDswNCwaErrp5cA",
    "بواكير": "https://maps.app.goo.gl/rRuDswNCwaErrp5cA",
    "اللبنة الرابعة": "https://share.google/i6Tuit7y8oLNJAtk4",
    "صيف نبع 2026": "https://maps.app.goo.gl/xs1PkAdR8rDE9H8h7",
    "ارجوحة": "https://maps.app.goo.gl/d26Kwk4DNfQ5vGwt8",
    "فيض الرواد": "https://maps.app.goo.gl/uZngbrihmLerEEj29",
    "صيف صاد": "https://maps.app.goo.gl/vR4xL7usBG2FA8DU9",
    "معين": "https://maps.app.goo.gl/gmof9BFV5tEee22m6",
    "محبرة": "https://maps.app.goo.gl/E7vXES9z3kxQnkao6",
    "تحبير": "https://maps.app.goo.gl/CDLCjqoSqCpkdJtPA",
    "مرسى سمَق - أكاديمية التبيان": "https://maps.app.goo.gl/oQ5Bku4VEjsKnuxs8",
    "مرسى سمَق - مجمع الموسى": "https://maps.app.goo.gl/9ooSFjr9x37pD7A96",
    "إجلال": "https://maps.app.goo.gl/nBihwnoUY3o5E1Z69",
    "حِلية - غرناطة": "https://maps.app.goo.gl/quKp6YcGRxrgdfHs5",
    "حِلية - السويدي": "https://maps.app.goo.gl/fFwgqHCKuusDm2zM6",
    "همم للفتيات": "https://maps.app.goo.gl/KeJFhG3toBRhg7eZA",
    "أُبّهـة": "https://maps.app.goo.gl/kLpbgyFziCf4SxX76",
    "صيف الارتواء": "https://maps.app.goo.gl/RjuLbHFgk7wHnBaY7",
    "رحلة غرس": "https://maps.app.goo.gl/RjuLbHFgk7wHnBaY7",
    "صيف سمو": "https://maps.app.goo.gl/YKwzefKk1D4DNSjJ8",
    "رحلة بين الهوايات": "https://maps.app.goo.gl/nLs7A4UajHjMaX1V9",
    "جناح ٤٨": "https://maps.app.goo.gl/KUuAC1UpdiFd5CgEA",
    "جناح 48 الصيفي": "https://maps.app.goo.gl/7ApGjQbvTeijyDaB7",
    "مدار - المركز المتقدم": "https://maps.app.goo.gl/aV4pFT1rthX7opSm7",
    "مدار - رواد الخليج": "https://maps.app.goo.gl/n3fFsFN4FQQ6wVV99",
    "قاطرة عجائب 2": "https://maps.app.goo.gl/gzC8WR2LehHcbRZ78",
    "رَغِيـد": "https://maps.app.goo.gl/Czq4SWdiAu9RkpMv5",
    "مآب": "https://maps.app.goo.gl/9oZXoBRRUyaohMqR6",
    "وِصال": "https://maps.app.goo.gl/pAwnqsNA13hpPfYJ9",
    "أُفق": "https://maps.app.goo.gl/pAwnqsNA13hpPfYJ9",
    "كَون الحِرف": "https://maps.app.goo.gl/DhKAkSfa9Dmf6ciN6",
    "روافد أكمام": "https://maps.app.goo.gl/DxM1neWmEDW98niq6",
    "وتين": "https://maps.app.goo.gl/pz3YCj2CJBgxA5WX7",
}


def expand_short_url(short_url, timeout=10):
    """
    تحويل الرابط القصير إلى رابط طويل
    يستخدم HEAD request لتجنب تحميل الصفحة كاملة
    """
    try:
        # إزالة أي معاملات إضافية مثل ?g_st=
        clean_url = short_url.split('?')[0]
        
        response = requests.head(
            clean_url,
            allow_redirects=True,
            timeout=timeout,
            headers={
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
        )
        
        long_url = response.url
        
        # التحقق من أن الرابط يحتوي على إحداثيات
        if '/maps?q=' in long_url or '/maps/place/' in long_url or '@' in long_url:
            return long_url
        else:
            return f"⚠️ رابط غير واضح: {long_url}"
            
    except requests.exceptions.Timeout:
        return "❌ Timeout - الرابط استغرق وقتاً طويلاً"
    except requests.exceptions.RequestException as e:
        return f"❌ خطأ: {str(e)}"
    except Exception as e:
        return f"❌ خطأ غير متوقع: {str(e)}"


def extract_coordinates(long_url):
    """
    استخراج الإحداثيات من الرابط الطويل إن وجدت
    """
    try:
        # البحث عن نمط @latitude,longitude
        if '@' in long_url:
            coords_part = long_url.split('@')[1].split(',')[0:2]
            if len(coords_part) == 2:
                return f"{coords_part[0]}, {coords_part[1]}"
        
        # البحث عن نمط !3d!4d
        if '!3d' in long_url and '!4d' in long_url:
            lat = long_url.split('!3d')[1].split('!')[0]
            lng = long_url.split('!4d')[1].split('!')[0]
            return f"{lat}, {lng}"
            
        return "لا توجد إحداثيات"
    except:
        return "لا توجد إحداثيات"


def main():
    print("=" * 80)
    print("🗺️  محول روابط خرائط جوجل - من قصير إلى طويل")
    print("=" * 80)
    print(f"\n📊 عدد البرامج: {len(programs_data)}")
    print("⏳ جاري التحويل...\n")
    
    results = []
    total = len(programs_data)
    
    for idx, (program_name, short_url) in enumerate(programs_data.items(), 1):
        print(f"[{idx}/{total}] معالجة: {program_name}")
        
        long_url = expand_short_url(short_url)
        coordinates = extract_coordinates(long_url) if not long_url.startswith('❌') else "N/A"
        
        results.append({
            'اسم البرنامج': program_name,
            'الرابط القصير': short_url,
            'الرابط الطويل': long_url,
            'الإحداثيات': coordinates
        })
        
        # تأخير بسيط لتجنب الحظر
        time.sleep(1.5)
    
    # حفظ النتائج في ملف CSV
    output_file = 'google_maps_expanded.csv'
    with open(output_file, 'w', newline='', encoding='utf-8-sig') as f:
        writer = csv.DictWriter(f, fieldnames=['اسم البرنامج', 'الرابط القصير', 'الرابط الطويل', 'الإحداثيات'])
        writer.writeheader()
        writer.writerows(results)
    
    print("\n" + "=" * 80)
    print(f"✅ تم الحفظ بنجاح في ملف: {output_file}")
    print("=" * 80)
    
    # طباعة ملخص
    success_count = sum(1 for r in results if not r['الرابط الطويل'].startswith('❌'))
    print(f"\n📈 الملخص:")
    print(f"   ✅ نجح: {success_count}/{total}")
    print(f"   ❌ فشل: {total - success_count}/{total}")


if __name__ == "__main__":
    main()