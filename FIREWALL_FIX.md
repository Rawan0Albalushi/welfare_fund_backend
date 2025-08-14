# حل مشكلة الوصول من الهاتف 🔥

## المشكلة
لا يمكن الوصول إلى `http://192.168.100.130:3000` من الهاتف

## الحل السريع

### الطريقة الأولى (الأسهل):
1. انقر بزر الماوس الأيمن على ملف `allow_port_3000.bat`
2. اختر "Run as administrator"
3. انتظر رسالة النجاح
4. جرب الوصول من الهاتف

### الطريقة الثانية (يدوياً):
1. افتح Windows Defender Firewall
2. انقر "Advanced settings"
3. اختر "Inbound Rules" من اليسار
4. انقر "New Rule" من اليمين
5. اختر "Port" ثم "Next"
6. اختر "TCP" واكتب "3000" ثم "Next"
7. اختر "Allow the connection" ثم "Next"
8. اختر جميع الـ profiles ثم "Next"
9. اكتب اسم "Laravel API 3000" ثم "Finish"

### الطريقة الثالثة (إيقاف Firewall مؤقتاً):
1. افتح Windows Defender Firewall
2. انقر "Turn Windows Defender Firewall on or off"
3. اختر "Turn off Windows Defender Firewall" لكلا الخيارين
4. انقر "OK"
5. جرب الوصول من الهاتف

## اختبار سريع

بعد تطبيق الحل، افتح هذا الرابط في هاتفك:
```
http://192.168.100.130:3000
```

يجب أن تظهر صفحة Laravel.

## اختبار الـ API

```
http://192.168.100.130:3000/api/v1/categories
```

يجب أن يظهر JSON response.

## ملاحظات مهمة

- تأكد من أن الهاتف والكمبيوتر على نفس الشبكة WiFi
- تأكد من أن عنوان IP صحيح: `192.168.100.130`
- إذا لم يعمل، جرب إيقاف Firewall مؤقتاً
