@echo off
echo ========================================
echo إيقاف Windows Firewall مؤقتاً
echo ========================================
echo.

echo سيتم إيقاف Windows Firewall مؤقتاً...
echo هذا سيسمح بالوصول من الهاتف
echo.

netsh advfirewall set allprofiles state off

if %errorlevel% equ 0 (
    echo.
    echo ✅ تم إيقاف Windows Firewall بنجاح!
    echo.
    echo الآن جرب الوصول من الهاتف:
    echo http://192.168.100.130:3000
    echo.
    echo للـ API:
    echo http://192.168.100.130:3000/api/v1/categories
    echo.
    echo ⚠️ تذكر إعادة تشغيل الـ firewall لاحقاً
    echo.
) else (
    echo.
    echo ❌ فشل في إيقاف الـ firewall
    echo تأكد من تشغيل الملف كـ Administrator
    echo.
)

echo اضغط أي مفتاح للخروج...
pause > nul
