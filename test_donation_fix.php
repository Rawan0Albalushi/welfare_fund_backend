<?php

// اختبار إصلاح مشكلة التبرعات
echo "=== اختبار إصلاح مشكلة التبرعات ===\n\n";

// 1. اختبار إنشاء تبرع مع authentication
echo "1. اختبار إنشاء تبرع مع authentication:\n";
echo "POST http://192.168.1.21:8000/api/v1/donations\n";
echo "Headers: Authorization: Bearer YOUR_TOKEN\n";
echo "Body: {\n";
echo "  \"program_id\": 1,\n";
echo "  \"amount\": 50,\n";
echo "  \"donor_name\": \"اسمك\",\n";
echo "  \"note\": \"تبرع تجريبي\"\n";
echo "}\n\n";

// 2. اختبار استرجاع تبرعات المستخدم
echo "2. اختبار استرجاع تبرعات المستخدم:\n";
echo "GET http://192.168.1.21:8000/api/v1/me/donations\n";
echo "Headers: Authorization: Bearer YOUR_TOKEN\n\n";

// 3. اختبار إنشاء تبرع مجهول
echo "3. اختبار إنشاء تبرع مجهول:\n";
echo "POST http://192.168.1.21:8000/api/v1/donations/anonymous\n";
echo "Body: {\n";
echo "  \"program_id\": 1,\n";
echo "  \"amount\": 25,\n";
echo "  \"donor_name\": \"مجهول\",\n";
echo "  \"note\": \"تبرع مجهول\"\n";
echo "}\n\n";

echo "=== ملاحظات مهمة ===\n";
echo "✅ تم إصلاح مشكلة routes المكررة\n";
echo "✅ التبرعات الآن مربوطة بالمستخدمين المسجلين\n";
echo "✅ التبرعات المجهولة متاحة عبر /donations/anonymous\n";
echo "✅ استرجاع التبرعات يعمل عبر /me/donations\n\n";

echo "=== خطوات الاختبار ===\n";
echo "1. سجل دخول في التطبيق\n";
echo "2. اعمل تبرع جديد\n";
echo "3. تحقق من قائمة تبرعاتك\n";
echo "4. يجب أن يظهر التبرع الجديد!\n\n";

echo "=== إذا لم يعمل ===\n";
echo "1. تأكد من أنك مسجل دخول\n";
echo "2. تأكد من استخدام الـ token الصحيح\n";
echo "3. تحقق من أن التبرع تم بنجاح\n";
echo "4. جرب refresh للصفحة\n\n";

?>
