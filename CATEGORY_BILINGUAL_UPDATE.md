# تحديث جدول Categories لدعم اللغتين العربية والإنجليزية

## التغييرات المنفذة

### 1. Migration للإضافة
تم إنشاء migration جديد: `2025_10_02_173820_add_bilingual_names_to_categories_table.php`
- إضافة حقل `name_ar` للأسماء العربية
- إضافة حقل `name_en` للأسماء الإنجليزية
- نقل البيانات الحالية من `name` إلى `name_ar` تلقائياً

### 2. Migration للحذف ✨
تم إنشاء migration جديد: `2025_10_02_174234_remove_name_column_from_categories_table.php`
- حذف حقل `name` القديم من الجدول
- الآن الجدول يحتوي فقط على `name_ar` و `name_en`

### 3. تحديث Model
**ملف**: `app/Models/Category.php`
- إزالة `name` من `$fillable`
- الإبقاء على `name_ar` و `name_en` فقط

### 4. تحديث Resource
**ملف**: `app/Http/Resources/CategoryResource.php`
- إزالة حقل `name` من الـ Response
- عرض `name_ar` و `name_en` فقط

### 5. تحديث Controller
**ملف**: `app/Http/Controllers/Admin/CategoryController.php`
- إزالة `name` من validation في `store()` و `update()`
- `name_ar` و `name_en` مطلوبين فقط

### 6. تحديث Seeders
**ملفات**:
- `database/seeders/CategorySeeder.php`
- `database/seeders/UpdateDataSeeder.php`

التغييرات:
- إزالة حقل `name` من جميع البيانات
- استخدام `where('name_ar', ...)` بدلاً من `where('name', ...)`
- جميع الفئات الآن تحتوي على:
```php
[
    'name_ar' => 'الإعانة الشهرية',
    'name_en' => 'Monthly Allowance',
    'status' => 'active',
]
```

### 7. تحديث Factory
**ملف**: `database/factories/CategoryFactory.php`
- إزالة `name` من التعريف
- إنشاء بيانات تجريبية بـ `name_ar` و `name_en` فقط

## الفئات المضافة

### فئات برامج الدعم الطلابي (4 فئات)
1. **الإعانة الشهرية** - Monthly Allowance
2. **السكن والنقل** - Housing and Transportation
3. **فرص التعليم العالي** - Higher Education Opportunities
4. **رسوم الاختبارات** - Examination Fees

### فئات حملات التبرع الخيرية (5 فئات)
1. **حملات الطوارئ** - Emergency Campaigns
2. **حملات التعليم** - Education Campaigns
3. **حملات الصحة** - Health Campaigns
4. **حملات الإغاثة** - Relief Campaigns
5. **حملات البناء** - Construction Campaigns

## هيكل الـ API Response

عند استدعاء الـ API، ستحصل على:

```json
{
  "id": 1,
  "name_ar": "الإعانة الشهرية",
  "name_en": "Monthly Allowance",
  "status": "active",
  "programs_count": 0,
  "created_at": "2025-10-02T17:39:58.000000Z",
  "updated_at": "2025-10-02T17:39:58.000000Z"
}
```

**ملاحظة:** لا يوجد حقل `name` في الـ Response ✨

## كيفية الاستخدام

### إنشاء فئة جديدة
```php
POST /api/v1/admin/categories
{
    "name_ar": "فئة جديدة",
    "name_en": "New Category",
    "status": "active"
}
```

### تحديث فئة موجودة
```php
PUT /api/v1/admin/categories/{id}
{
    "name_ar": "فئة محدثة",
    "name_en": "Updated Category",
    "status": "active"
}
```

## الأوامر المستخدمة

```bash
# إنشاء الـ migration للإضافة
php artisan make:migration add_bilingual_names_to_categories_table --table=categories

# إنشاء الـ migration للحذف
php artisan make:migration remove_name_column_from_categories_table --table=categories

# تشغيل الـ migrations
php artisan migrate

# تحديث البيانات
php artisan db:seed --class=UpdateDataSeeder
```

## ملاحظات إضافية

### تم إصلاح مشكلة في جدول Programs
- تم إزالة حقول `goal_amount`, `raised_amount`, `start_date`, `end_date` من Model الـ Program
- تم تحديث الـ UpdateDataSeeder ليتوافق مع التغييرات

### التوافق مع الأنظمة القديمة
- ❌ تم حذف حقل `name` القديم نهائياً
- ✅ الآن يستخدم فقط `name_ar` و `name_en`
- ✅ جميع الملفات تم تحديثها للعمل بدون حقل `name`

## التحقق من النجاح

تم التحقق من نجاح التحديث:
- ✅ الـ migration للإضافة تم تنفيذه بنجاح
- ✅ الـ migration للحذف تم تنفيذه بنجاح
- ✅ البيانات تم تحديثها بنجاح (9 فئات)
- ✅ الأسماء بالعربي والإنجليزي موجودة في جميع الفئات
- ✅ الـ API يعرض `name_ar` و `name_en` فقط في الـ Response
- ✅ حقل `name` تم حذفه نهائياً من الجدول والكود

