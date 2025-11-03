# 📊 إعداد ميزة تصدير التقارير

تم إضافة ميزة تصدير التقارير إلى Excel وPDF بنجاح! 

## 📦 المكتبات المضافة

تمت إضافة المكتبات التالية إلى `composer.json`:
- `maatwebsite/excel` (^3.1) - لتصدير Excel
- `barryvdh/laravel-dompdf` (^3.0) - لتصدير PDF

## 🔧 خطوات التثبيت

### 1. تثبيت المكتبات
```bash
composer install
```

أو إذا كانت المكتبات موجودة بالفعل:
```bash
composer update
```

### 2. نشر ملفات التكوين (اختياري)
```bash
# نشر ملفات Excel
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

# نشر ملفات PDF
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

## 📋 Endpoints التصدير المتاحة

جميع endpoints تحتاج صلاحيات admin وتكون تحت:
`/api/v1/admin/reports/`

### 1. تصدير تقرير التبرعات
- **Excel**: `GET /api/v1/admin/reports/donations/export/excel`
- **PDF**: `GET /api/v1/admin/reports/donations/export/pdf`

**Query Parameters (نفس فلاتر تقرير التبرعات):**
- `from_date`: تاريخ البداية
- `to_date`: تاريخ النهاية
- `status`: حالة التبرع
- `type`: نوع التبرع
- `program_id`: فلترة حسب البرنامج
- `campaign_id`: فلترة حسب الحملة

### 2. تصدير التقرير المالي
- **Excel**: `GET /api/v1/admin/reports/financial/export/excel`
- **PDF**: `GET /api/v1/admin/reports/financial/export/pdf`

**Query Parameters:**
- `from_date`: تاريخ البداية
- `to_date`: تاريخ النهاية
- `period`: نوع الفترة (daily, weekly, monthly)

### 3. تصدير تقرير البرامج
- **Excel**: `GET /api/v1/admin/reports/programs/export/excel`

### 4. تصدير تقرير الطلبات
- **Excel**: `GET /api/v1/admin/reports/applications/export/excel`
- **PDF**: `GET /api/v1/admin/reports/applications/export/pdf`

**Query Parameters:**
- `status`: حالة الطلب
- `program_id`: فلترة حسب البرنامج
- `from_date`: تاريخ البداية
- `to_date`: تاريخ النهاية

## 📁 الملفات المنشأة

### Export Classes
- `app/Exports/DonationsExport.php` - تصدير التبرعات
- `app/Exports/FinancialExport.php` - تصدير التقرير المالي (متعدد الأوراق)
- `app/Exports/ProgramsExport.php` - تصدير البرامج
- `app/Exports/ApplicationsExport.php` - تصدير الطلبات

### PDF Views
- `resources/views/reports/donations.blade.php` - قالب تقرير التبرعات
- `resources/views/reports/financial.blade.php` - قالب التقرير المالي
- `resources/views/reports/applications.blade.php` - قالب تقرير الطلبات

### Controller Methods
تم إضافة methods التصدير في `app/Http/Controllers/Admin/ReportController.php`:
- `exportDonationsExcel()` - تصدير التبرعات Excel
- `exportDonationsPdf()` - تصدير التبرعات PDF
- `exportFinancialExcel()` - تصدير مالي Excel
- `exportFinancialPdf()` - تصدير مالي PDF
- `exportProgramsExcel()` - تصدير البرامج Excel
- `exportApplicationsExcel()` - تصدير الطلبات Excel
- `exportApplicationsPdf()` - تصدير الطلبات PDF

## 🎨 المميزات

### Excel Exports
- ✅ تنسيق احترافي مع رؤوس الأعمدة المميزة
- ✅ دعم الفلترة الكاملة (نفس فلاتر التقارير)
- ✅ أسماء الأعمدة بالعربية
- ✅ تقرير مالي متعدد الأوراق (ملخص، حسب الحالة، حسب النوع)

### PDF Exports
- ✅ تصميم احترافي مع دعم RTL
- ✅ إحصائيات ملخصة في أعلى التقرير
- ✅ جداول منظمة وقابلة للقراءة
- ✅ دعم الخطوط العربية

## 📝 ملاحظات مهمة

1. **الخطوط العربية**: تأكد من أن خادمك يدعم الخطوط العربية لتصدير PDF. قد تحتاج إلى تثبيت خطوط عربية إضافية.

2. **الذاكرة**: لملفات Excel/PDF كبيرة، قد تحتاج إلى زيادة `memory_limit` في `php.ini`:
   ```ini
   memory_limit = 256M
   ```

3. **الوقت**: للتقارير الكبيرة جداً، قد تحتاج إلى زيادة `max_execution_time`:
   ```ini
   max_execution_time = 300
   ```

## 🔍 اختبار التصدير

يمكنك اختبار التصدير باستخدام:
```bash
# مثال: تصدير تقرير التبرعات إلى Excel
curl -X GET "http://your-domain.com/api/v1/admin/reports/donations/export/excel?from_date=2024-01-01&to_date=2024-12-31" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -o donations_report.xlsx
```

## ✅ جاهز للاستخدام!

بعد تثبيت المكتبات، ستكون جميع endpoints التصدير جاهزة للاستخدام مباشرة.

