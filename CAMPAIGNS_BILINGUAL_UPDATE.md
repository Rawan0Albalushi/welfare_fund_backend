# تحديث جدول Campaigns لدعم اللغتين العربية والإنجليزية

## التغييرات المنفذة

### 1. Migration لإضافة الحقول الجديدة
تم إنشاء migration: `2025_10_02_182150_add_bilingual_fields_to_campaigns_table.php`
- إضافة `title_ar` و `title_en` للعنوان بالعربي والإنجليزي
- إضافة `description_ar` و `description_en` للوصف بالعربي والإنجليزي
- إضافة `impact_description_ar` و `impact_description_en` لوصف التأثير بالعربي والإنجليزي
- نقل البيانات الحالية من الحقول القديمة إلى الحقول العربية تلقائياً

### 2. Migration لحذف الحقول القديمة ✨
تم إنشاء migration: `2025_10_02_182538_remove_old_fields_from_campaigns_table.php`
- حذف `title`, `description`, `impact_description` القديمة
- الآن الجدول يحتوي فقط على الحقول الثنائية اللغة

### 3. تحديث Model
**ملف**: `app/Models/Campaign.php`
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
    'goal_amount',
    'raised_amount',
    'status',
    'start_date',
    'end_date',
    'target_donors',
    'impact_description_ar',
    'impact_description_en',
    'campaign_highlights',
];
```

### 4. تحديث Resource
**ملف**: `app/Http/Resources/CampaignResource.php`
- تحديث Response لعرض الحقول الجديدة
- إزالة الحقول القديمة

### 5. تحديث Controller
**ملف**: `app/Http/Controllers/Admin/CampaignController.php`
- تحديث `store()` method: جعل `title_ar`, `title_en`, `description_ar`, `description_en` مطلوبة
- تحديث `update()` method: جعل جميع الحقول الثنائية اللغة اختيارية
- `impact_description_ar` و `impact_description_en` اختياريين في كلا الحالتين

### 6. إنشاء Seeder جديد ✨
**ملف**: `database/seeders/BilingualCampaignsSeeder.php`
- تم إنشاء seeder جديد يحتوي على 8 حملات بالكامل باللغتين
- جميع الحملات لديها عناوين وأوصاف باللغتين العربية والإنجليزية
- التصنيفات: حملات الطوارئ، التعليم، الصحة، الإغاثة، البناء

## هيكل الحقول الجديدة

### الحقول المضافة:
- `title_ar` - عنوان الحملة بالعربي
- `title_en` - عنوان الحملة بالإنجليزي
- `description_ar` - وصف الحملة بالعربي
- `description_en` - وصف الحملة بالإنجليزي
- `impact_description_ar` - وصف التأثير المتوقع بالعربي
- `impact_description_en` - وصف التأثير المتوقع بالإنجليزي

### الحقول المحذوفة:
- ❌ `title`
- ❌ `description`
- ❌ `impact_description`

## الحملات المضافة (8 حملات)

### 1. حملة إغاثة ضحايا الزلزال
- **العربي**: حملة عاجلة لإغاثة ضحايا الزلزال
- **English**: Earthquake Victims Relief Campaign

### 2. حملة إغاثة ضحايا الفيضانات
- **العربي**: حملة عاجلة لإغاثة ضحايا الفيضانات
- **English**: Flood Victims Relief Campaign

### 3. حملة بناء مدرسة في القرية النائية
- **العربي**: حملة لبناء مدرسة في قرية نائية
- **English**: Build a School in Remote Village Campaign

### 4. حملة علاج الأطفال المصابين بالسرطان
- **العربي**: حملة لعلاج 50 طفل مصاب بالسرطان
- **English**: Childhood Cancer Treatment Campaign

### 5. حملة إغاثة اللاجئين
- **العربي**: حملة لتوفير المساعدات للاجئين
- **English**: Refugee Relief Campaign

### 6. حملة بناء مسجد في الحي الفقير
- **العربي**: حملة لبناء مسجد في حي فقير
- **English**: Build a Mosque in Poor Neighborhood Campaign

### 7. حملة توفير أجهزة حاسوب للطلاب
- **العربي**: حملة لتوفير أجهزة حاسوب للطلاب المحتاجين
- **English**: Provide Computers for Needy Students Campaign

### 8. حملة فحص طبي مجاني للفقراء
- **العربي**: حملة لتوفير فحص طبي مجاني شامل
- **English**: Free Medical Checkup for the Poor Campaign

## هيكل الـ API Response

```json
{
  "id": 1,
  "title_ar": "حملة إغاثة ضحايا الزلزال",
  "title_en": "Earthquake Victims Relief Campaign",
  "description_ar": "حملة عاجلة لإغاثة ضحايا الزلزال...",
  "description_en": "Urgent campaign to relief earthquake victims...",
  "image": "https://images.unsplash.com/...",
  "goal_amount": "200000.00",
  "raised_amount": "150000.00",
  "progress_percentage": 75,
  "status": "active",
  "status_in_arabic": "نشطة",
  "start_date": "2025-09-27",
  "end_date": "2025-10-27",
  "days_remaining": 25,
  "target_donors": 1000,
  "impact_description_ar": "ستساعد هذه الحملة في توفير المأوى...",
  "impact_description_en": "This campaign will help provide shelter...",
  "campaign_highlights": [
    "توفير مأوى مؤقت لـ 500 عائلة",
    "توزيع 2000 وجبة يومية"
  ],
  "is_urgent": false,
  "is_completed": false,
  "category": {
    "id": 1,
    "name_ar": "حملات الطوارئ",
    "name_en": "Emergency Campaigns",
    "status": "active"
  },
  "created_at": "2025-10-02T18:25:18.000000Z",
  "updated_at": "2025-10-02T18:25:18.000000Z"
}
```

## كيفية الاستخدام

### إنشاء حملة جديدة
```http
POST /api/v1/admin/campaigns
Content-Type: application/json

{
  "category_id": 1,
  "title_ar": "حملة جديدة",
  "title_en": "New Campaign",
  "description_ar": "وصف الحملة بالعربي",
  "description_en": "Campaign description in English",
  "goal_amount": 100000,
  "status": "active",
  "start_date": "2025-10-01",
  "end_date": "2025-12-31",
  "target_donors": 500,
  "impact_description_ar": "وصف التأثير بالعربي",
  "impact_description_en": "Impact description in English",
  "campaign_highlights": ["نقطة 1", "نقطة 2"]
}
```

### تحديث حملة
```http
PUT /api/v1/admin/campaigns/{id}
Content-Type: application/json

{
  "title_ar": "حملة محدثة",
  "title_en": "Updated Campaign",
  "description_ar": "وصف محدث",
  "description_en": "Updated description"
}
```

## الأوامر المستخدمة

```bash
# إنشاء الـ migration للإضافة
php artisan make:migration add_bilingual_fields_to_campaigns_table --table=campaigns

# إنشاء الـ migration للحذف
php artisan make:migration remove_old_fields_from_campaigns_table --table=campaigns

# تشغيل الـ migrations
php artisan migrate

# تشغيل الـ seeder الجديد
php artisan db:seed --class=BilingualCampaignsSeeder
```

## البحث في الحملات

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
- ✅ البيانات تم تحديثها بنجاح (8 حملات)
- ✅ جميع الحملات لديها عناوين وأوصاف باللغتين
- ✅ الـ API يعرض الحقول الثنائية اللغة فقط
- ✅ حقول `title`, `description`, `impact_description` تم حذفها نهائياً
- ✅ البحث يعمل على الحقول العربية والإنجليزية معاً

## الملفات المحدثة

1. ✅ `database/migrations/2025_10_02_182150_add_bilingual_fields_to_campaigns_table.php`
2. ✅ `database/migrations/2025_10_02_182538_remove_old_fields_from_campaigns_table.php`
3. ✅ `app/Models/Campaign.php`
4. ✅ `app/Http/Resources/CampaignResource.php`
5. ✅ `app/Http/Controllers/Admin/CampaignController.php`
6. ✅ `database/seeders/BilingualCampaignsSeeder.php` (جديد)

## ملاحظات مهمة

- جميع الحقول الثنائية اللغة للعنوان والوصف **مطلوبة** عند إنشاء حملة جديدة
- `impact_description_ar` و `impact_description_en` **اختياريين**
- تم الاحتفاظ بجميع الحقول الأخرى كما هي (goal_amount, raised_amount, status, etc.)
- الـ seeder القديم في `UpdateDataSeeder.php` تم تحديثه للتوافق مع الحقول الجديدة
- الـ seeder الجديد `BilingualCampaignsSeeder.php` يمكن استخدامه مستقلاً

