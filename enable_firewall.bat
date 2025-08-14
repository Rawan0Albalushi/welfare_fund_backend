@echo off
echo ========================================
echo إعادة تشغيل Windows Firewall
echo ========================================
echo.

echo سيتم إعادة تشغيل Windows Firewall...
echo.

netsh advfirewall set allprofiles state on

if %errorlevel% equ 0 (
    echo.
    echo ✅ تم إعادة تشغيل Windows Firewall بنجاح!
    echo.
) else (
    echo.
    echo ❌ فشل في إعادة تشغيل الـ firewall
    echo تأكد من تشغيل الملف كـ Administrator
    echo.
)

echo اضغط أي مفتاح للخروج...
pause > nul
