# API حملات التبرع الخيرية

## نظرة عامة

تم إنشاء نظام منفصل لحملات التبرع الخيرية منفصل عن برامج الدعم الطلابي. هذا النظام يتضمن:

### الميزات الرئيسية:
- **حملات التبرع**: حملات خيرية متنوعة مع أهداف محددة
- **فئات الحملات**: تصنيف واضح للحملات حسب نوعها
- **نظام التبرعات**: إمكانية التبرع للحملات مباشرة
- **الحملات العاجلة**: حملات تنتهي قريباً تحتاج دعم عاجل

## الفئات الجديدة

### 1. حملات الطوارئ
- حملات إغاثة عاجلة للكوارث الطبيعية
- حملات إغاثة ضحايا الزلازل والفيضانات

### 2. حملات التعليم
- بناء المدارس في المناطق النائية
- توفير الأجهزة التعليمية للطلاب المحتاجين

### 3. حملات الصحة
- علاج الأطفال المصابين بالسرطان
- فحص طبي مجاني للفقراء

### 4. حملات الإغاثة
- إغاثة اللاجئين في المخيمات
- توفير المساعدات الأساسية

### 5. حملات البناء
- بناء المساجد في الأحياء الفقيرة
- إنشاء المرافق المجتمعية

## الحملات المضافة

### 1. حملات الطوارئ
- **حملة إغاثة ضحايا الزلزال**: 200,000 ريال (150,000 ريال مجمع) - 25 يوم متبقي
- **حملة إغاثة ضحايا الفيضانات**: 150,000 ريال (95,000 ريال مجمع) - 17 يوم متبقي

### 2. حملات التعليم
- **حملة بناء مدرسة في القرية النائية**: 500,000 ريال (320,000 ريال مجمع) - 60 يوم متبقي
- **حملة توفير أجهزة حاسوب للطلاب المحتاجين**: 250,000 ريال (160,000 ريال مجمع) - 35 يوم متبقي

### 3. حملات الصحة
- **حملة علاج الأطفال المصابين بالسرطان**: 800,000 ريال (450,000 ريال مجمع) - 45 يوم متبقي
- **حملة فحص طبي مجاني للفقراء**: 120,000 ريال (75,000 ريال مجمع) - 28 يوم متبقي

### 4. حملات الإغاثة
- **حملة إغاثة اللاجئين**: 300,000 ريال (180,000 ريال مجمع) - 20 يوم متبقي

### 5. حملات البناء
- **حملة بناء مسجد في الحي الفقير**: 400,000 ريال (280,000 ريال مجمع) - 40 يوم متبقي

## API Endpoints

### 1. عرض الحملات

```http
GET /api/v1/campaigns
```

**المعاملات:**
- `category_id`: فلترة حسب الفئة
- `search`: البحث في العنوان والوصف
- `urgent`: عرض الحملات العاجلة فقط (تنتهي خلال 7 أيام)
- `featured`: عرض الحملات المميزة فقط
- `page`: رقم الصفحة
- `per_page`: عدد العناصر في الصفحة

**الاستجابة:**
```json
{
  "message": "Campaigns retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "حملة إغاثة ضحايا الزلزال",
      "description": "حملة عاجلة لإغاثة ضحايا الزلزال...",
      "image": "https://images.unsplash.com/...",
      "goal_amount": "200000.00",
      "raised_amount": "150000.00",
      "progress_percentage": 75.0,
      "status": "active",
      "status_in_arabic": "نشط",
      "days_remaining": 25,
      "target_donors": 1000,
      "impact_description": "ستساعد هذه الحملة في توفير المأوى والطعام والدواء لـ 500 عائلة متضررة من الزلزال.",
      "campaign_highlights": [
        "توفير مأوى مؤقت لـ 500 عائلة",
        "توزيع 2000 وجبة يومية",
        "توفير الأدوية والرعاية الطبية",
        "إعادة بناء 50 منزل"
      ],
      "is_urgent": false,
      "is_completed": false,
      "donors_count": 245,
      "category": {
        "id": 1,
        "name": "حملات الطوارئ"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 8,
    "last_page": 1
  }
}
```

### 2. الحملات العاجلة

```http
GET /api/v1/campaigns/urgent
```

**الاستجابة:**
```json
{
  "message": "Urgent campaigns retrieved successfully",
  "data": [
    {
      "id": 2,
      "title": "حملة إغاثة ضحايا الفيضانات",
      "days_remaining": 5,
      "is_urgent": true,
      // ... باقي البيانات
    }
  ]
}
```

### 3. الحملات المميزة

```http
GET /api/v1/campaigns/featured
```

**الاستجابة:**
```json
{
  "message": "Featured campaigns retrieved successfully",
  "data": [
    {
      "id": 3,
      "title": "حملة علاج الأطفال المصابين بالسرطان",
      "raised_amount": "450000.00",
      // ... باقي البيانات
    }
  ]
}
```

### 4. تفاصيل الحملة

```http
GET /api/v1/campaigns/{id}
```

**الاستجابة:**
```json
{
  "message": "Campaign retrieved successfully",
  "data": {
    "id": 1,
    "title": "حملة إغاثة ضحايا الزلزال",
    "description": "حملة عاجلة لإغاثة ضحايا الزلزال الذي ضرب المنطقة...",
    "image": "https://images.unsplash.com/...",
    "goal_amount": "200000.00",
    "raised_amount": "150000.00",
    "progress_percentage": 75.0,
    "days_remaining": 25,
    "target_donors": 1000,
    "impact_description": "ستساعد هذه الحملة في توفير المأوى والطعام والدواء لـ 500 عائلة متضررة من الزلزال.",
    "campaign_highlights": [
      "توفير مأوى مؤقت لـ 500 عائلة",
      "توزيع 2000 وجبة يومية",
      "توفير الأدوية والرعاية الطبية",
      "إعادة بناء 50 منزل"
    ],
    "is_urgent": false,
    "is_completed": false,
    "donors_count": 245,
    "category": {
      "id": 1,
      "name": "حملات الطوارئ"
    }
  }
}
```

### 5. التبرع للحملة

```http
POST /api/v1/donations
```

**البيانات المطلوبة:**
```json
{
  "campaign_id": 1,
  "amount": 100.00,
  "donor_name": "أحمد محمد",
  "note": "تبرع خيري",
  "type": "quick"
}
```

**الاستجابة:**
```json
{
  "message": "Donation created successfully",
  "data": {
    "id": 1,
    "donation_id": "DN_...",
    "campaign_id": 1,
    "amount": "100.00",
    "donor_name": "أحمد محمد",
    "note": "تبرع خيري",
    "type": "quick",
    "status": "pending",
    "expires_at": "2025-08-26T07:42:20.000000Z"
  }
}
```

## استخدام Flutter

### 1. عرض الحملات
```dart
// جلب جميع الحملات
final response = await http.get(Uri.parse('$baseUrl/api/v1/campaigns'));
final campaigns = jsonDecode(response.body)['data'];

// جلب الحملات العاجلة
final urgentResponse = await http.get(Uri.parse('$baseUrl/api/v1/campaigns/urgent'));
final urgentCampaigns = jsonDecode(urgentResponse.body)['data'];

// جلب الحملات المميزة
final featuredResponse = await http.get(Uri.parse('$baseUrl/api/v1/campaigns/featured'));
final featuredCampaigns = jsonDecode(featuredResponse.body)['data'];
```

### 2. تفاصيل الحملة
```dart
// جلب تفاصيل الحملة
final response = await http.get(Uri.parse('$baseUrl/api/v1/campaigns/$campaignId'));
final campaign = jsonDecode(response.body)['data'];
```

### 3. التبرع للحملة
```dart
// إنشاء تبرع للحملة
final response = await http.post(
  Uri.parse('$baseUrl/api/v1/donations'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'campaign_id': campaignId,
    'amount': amount,
    'donor_name': donorName,
    'note': note,
    'type': 'quick'
  })
);
```

## الميزات المضافة

### 1. الحملات العاجلة
- **is_urgent**: تحدد إذا كانت الحملة عاجلة (تنتهي خلال 7 أيام)
- **days_remaining**: الأيام المتبقية للحملة
- فلترة خاصة للحملات العاجلة

### 2. الحملات المميزة
- **featured**: عرض الحملات الأكثر نجاحاً
- ترتيب حسب المبلغ المجمع

### 3. معلومات إضافية
- **target_donors**: عدد المتبرعين المستهدف
- **impact_description**: وصف التأثير المتوقع
- **campaign_highlights**: نقاط بارزة للحملة
- **status_in_arabic**: حالة الحملة بالعربية

### 4. الفلترة المتقدمة
- فلترة حسب الفئة
- فلترة الحملات العاجلة
- فلترة الحملات المميزة
- البحث في العنوان والوصف

## الفرق بين البرامج والحملات

### برامج الدعم الطلابي (Programs)
- **الهدف**: دعم الطلاب في التعليم
- **المدة**: طويلة الأمد
- **النوع**: برامج مستمرة
- **المستفيدون**: الطلاب والمؤسسات التعليمية

### حملات التبرع الخيرية (Campaigns)
- **الهدف**: إغاثة وحل مشاكل اجتماعية
- **المدة**: محدودة بوقت معين
- **النوع**: حملات مؤقتة
- **المستفيدون**: المجتمع بأسره

## التشغيل

1. **تشغيل الهجرات:**
```bash
php artisan migrate
```

2. **إضافة البيانات:**
```bash
php artisan db:seed --class=DonationCampaignsSeeder
```

3. **تشغيل الخادم:**
```bash
php artisan serve
```

## ملاحظات

- جميع الحملات نشطة وجاهزة للعرض
- الصور من مصادر موثوقة (Unsplash)
- الأسماء واضحة ومناسبة للسياق العربي
- النظام يدعم التبرعات الفورية والمؤجلة
- يمكن التبرع للبرامج أو الحملات من نفس النظام
