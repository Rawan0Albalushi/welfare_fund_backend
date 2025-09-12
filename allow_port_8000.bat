@echo off
echo ========================================
echo إضافة Laravel API إلى Windows Firewall
echo ========================================
echo.

echo سيتم إضافة rule جديد للـ firewall...
echo هذا سيسمح بالوصول إلى port 8000 من الأجهزة الأخرى
echo.

netsh advfirewall firewall add rule name="Laravel API 8000" dir=in action=allow protocol=TCP localport=8000

if %errorlevel% equ 0 (
    echo.
    echo ✅ تم إضافة rule بنجاح!
    echo.
    echo الآن يمكن للهاتف الوصول إلى:
    echo http://192.168.1.21:8000
    echo.
    echo للـ API:
    echo http://192.168.1.21:8000/api/v1/categories
    echo.
) else (
    echo.
    echo ❌ فشل في إضافة rule
    echo تأكد من تشغيل الملف كـ Administrator
    echo.
)

echo اضغط أي مفتاح للخروج...
pause > nul
