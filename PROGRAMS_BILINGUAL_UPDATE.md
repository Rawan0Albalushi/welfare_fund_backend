# تحديث جدول Programs لدعم اللغتين العربية والإنجليزية

## التغييرات المنفذة

### 1. Migration لإضافة الحقول الجديدة
تم إنشاء migration: `2025_10_02_185451_add_bilingual_fields_to_programs_table.php`
- إضافة `title_ar` و `title_en` للعنوان بالعربي والإنجليزي
- إضافة `description_ar` و `description_en` للوصف بالعربي والإنجليزي
- نقل البيانات الحالية من الحقول القديمة إلى الحقول العربية تلقائياً

### 2. Migration لحذف الحقول القديمة ✨
تم إنشاء migration: `2025_10_02_185557_remove_old_fields_from_programs_table.php`
- حذف `title` و `description` القديمة
- الآن الجدول يحتوي فقط على الحقول الثنائية اللغة

### 3. تحديث Model
**ملف**: `app/Models/Program.php`
- تحديث `$fillable` لاستخدام الحقول الجديدة
- تحديث `scopeSearch` للبحث في الحقول العربية والإنجليزية معاً

```php
protected $fillable = [
    'category_id',
    'title_ar',
    'title_en',
    'description_ar',
    'description_en',
    'image',
    'status',
];
```

### 4. تحديث Resource
**ملف**: `app/Http/Resources/ProgramResource.php`
- تحديث Response لعرض الحقول الجديدة
- إزالة الحقول القديمة

### 5. تحديث Controller
**ملف**: `app/Http/Controllers/Admin/ProgramController.php`
- تحديث `store()` method: جعل `title_ar`, `title_en`, `description_ar`, `description_en` مطلوبة
- تحديث `update()` method: جعل جميع الحقول الثنائية اللغة اختيارية

### 6. إنشاء Seeder جديد ✨
**ملف**: `database/seeders/BilingualProgramsSeeder.php`
- تم إنشاء seeder جديد يحتوي على 4 برامج بالكامل باللغتين
- جميع البرامج لديها عناوين وأوصاف باللغتين العربية والإنجليزية

## هيكل الحقول الجديدة

### الحقول المضافة:
- `title_ar` - عنوان البرنامج بالعربي
- `title_en` - عنوان البرنامج بالإنجليزي
- `description_ar` - وصف البرنامج بالعربي
- `description_en` - وصف البرنامج بالإنجليزي

### الحقول المحذوفة:
- ❌ `title`
- ❌ `description`

## البرامج المضافة (4 برامج)

### 1. برنامج الإعانة الشهرية
- **العربي**: برنامج مخصص لتقديم دعم مالي شهري للطلاب المحتاجين
- **English**: A program dedicated to providing monthly financial support to needy students

### 2. برنامج السكن والنقل
- **العربي**: برنامج يساعد الطلاب في توفير سكن مناسب ووسائل النقل
- **English**: A program that helps students provide adequate housing and transportation

### 3. برنامج فرص التعليم العالي
- **العربي**: برنامج يهدف إلى توفير فرص تعليمية للطلاب المتفوقين
- **English**: A program aimed at providing educational opportunities for outstanding students

### 4. برنامج رسوم الاختبارات
- **العربي**: برنامج لتغطية رسوم الاختبارات والامتحانات للطلاب المحتاجين
- **English**: A program to cover examination and test fees for needy students

## هيكل الـ API Response

```json
{
  "id": 1,
  "title_ar": "برنامج الإعانة الشهرية",
  "title_en": "Monthly Allowance Program",
  "description_ar": "برنامج مخصص لتقديم دعم مالي شهري...",
  "description_en": "A program dedicated to providing monthly financial...",
  "image": "https://images.unsplash.com/...",
  "status": "active",
  "category": {
    "id": 1,
    "name_ar": "الإعانة الشهرية",
    "name_en": "Monthly Allowance",
    "status": "active"
  },
  "created_at": "2025-10-02T18:55:57.000000Z",
  "updated_at": "2025-10-02T18:55:57.000000Z"
}
```

## كيفية الاستخدام

### إنشاء برنامج جديد
```http
POST /api/v1/admin/programs
Content-Type: application/json

{
  "category_id": 1,
  "title_ar": "برنامج جديد",
  "title_en": "New Program",
  "description_ar": "وصف البرنامج بالعربي",
  "description_en": "Program description in English",
  "status": "active"
}
```

### تحديث برنامج
```http
PUT /api/v1/admin/programs/{id}
Content-Type: application/json

{
  "title_ar": "برنامج محدث",
  "title_en": "Updated Program",
  "description_ar": "وصف محدث",
  "description_en": "Updated description"
}
```

## الأوامر المستخدمة

```bash
# إنشاء الـ migration للإضافة
php artisan make:migration add_bilingual_fields_to_programs_table --table=programs

# إنشاء الـ migration للحذف
php artisan make:migration remove_old_fields_from_programs_table --table=programs

# تشغيل الـ migrations
php artisan migrate

# تشغيل الـ seeder الجديد
php artisan db:seed --class=BilingualProgramsSeeder
```

## البحث في البرامج

تم تحديث `scopeSearch` للبحث في جميع الحقول الثنائية اللغة:

```php
public function scopeSearch($query, $search)
{
    return $query->where('title_ar', 'like', "%{$search}%")
                ->orWhere('title_en', 'like', "%{$search}%")
                ->orWhere('description_ar', 'like', "%{$search}%")
                ->orWhere('description_en', 'like', "%{$search}%");
}
```

## التحقق من النجاح

تم التحقق من نجاح التحديث:
- ✅ الـ migration للإضافة تم تنفيذه بنجاح
- ✅ الـ migration للحذف تم تنفيذه بنجاح
- ✅ البيانات تم تحديثها بنجاح (4 برامج)
- ✅ جميع البرامج لديها عناوين وأوصاف باللغتين
- ✅ الـ API يعرض الحقول الثنائية اللغة فقط
- ✅ حقول `title` و `description` تم حذفها نهائياً
- ✅ البحث يعمل على الحقول العربية والإنجليزية معاً

## الملفات المحدثة

1. ✅ `database/migrations/2025_10_02_185451_add_bilingual_fields_to_programs_table.php`
2. ✅ `database/migrations/2025_10_02_185557_remove_old_fields_from_programs_table.php`
3. ✅ `app/Models/Program.php`
4. ✅ `app/Http/Resources/ProgramResource.php`
5. ✅ `app/Http/Controllers/Admin/ProgramController.php`
6. ✅ `database/seeders/BilingualProgramsSeeder.php` (جديد)

## ملاحظات مهمة

- جميع الحقول الثنائية اللغة للعنوان والوصف **مطلوبة** عند إنشاء برنامج جديد
- تم الاحتفاظ بجميع الحقول الأخرى كما هي (category_id, image, status)
- الـ seeder القديم في `UpdateDataSeeder.php` يمكن تحديثه للتوافق مع الحقول الجديدة
- الـ seeder الجديد `BilingualProgramsSeeder.php` يمكن استخدامه مستقلاً

## الارتباط مع الجداول الأخرى

الآن جميع الجداول الرئيسية تدعم اللغتين:
- ✅ **Categories**: `name_ar`, `name_en`
- ✅ **Programs**: `title_ar`, `title_en`, `description_ar`, `description_en`
- ✅ **Campaigns**: `title_ar`, `title_en`, `description_ar`, `description_en`, `impact_description_ar`, `impact_description_en`

هذا يوفر تجربة متكاملة ثنائية اللغة للمستخدمين! 🎉

