# API برامج التبرعات الخيرية

## نظرة عامة

تم إنشاء نظام متكامل لبرامج التبرعات الخيرية يتضمن:

### الميزات الرئيسية:
- **الصفحة الرئيسية**: عرض البرامج مع الفلاتر حسب الفئة
- **صفحة التفاصيل**: معلومات مفصلة عن كل برنامج مع الإحصائيات
- **نظام التبرعات**: إمكانية إدخال مبلغ التبرع مع خيارات سريعة

## البرامج المضافة

### 1. الإعانة الشهرية
- **مساعدة كبار السن**: 50,000 ريال (35,000 ريال مجمع)
- **دعم الأرامل والأيتام**: 60,000 ريال (38,000 ريال مجمع)

### 2. السكن والنقل
- **مساعدة الأسر المحتاجة**: 25,000 ريال (18,000 ريال مجمع)
- **توفير سكن للطلاب الجامعيين**: 80,000 ريال (52,000 ريال مجمع)

### 3. فرص التعليم
- **منح دراسية للطلاب المتفوقين**: 75,000 ريال (42,000 ريال مجمع)
- **تطوير المهارات المهنية**: 40,000 ريال (28,000 ريال مجمع)

### 4. الرعاية الصحية
- **علاج الأطفال المرضى**: 100,000 ريال (65,000 ريال مجمع)

### 5. مساعدة الأسر المحتاجة
- **توفير الطعام للأسر الفقيرة**: 30,000 ريال (22,000 ريال مجمع)

## API Endpoints

### 1. عرض البرامج (الصفحة الرئيسية)

```http
GET /api/v1/programs
```

**المعاملات:**
- `category_id`: فلترة حسب الفئة
- `search`: البحث في العنوان والوصف
- `page`: رقم الصفحة
- `per_page`: عدد العناصر في الصفحة

**الاستجابة:**
```json
{
  "message": "Programs retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "مساعدة كبار السن",
      "description": "برنامج لدعم كبار السن...",
      "image": "https://images.unsplash.com/...",
      "goal_amount": "50000.00",
      "raised_amount": "35000.00",
      "progress_percentage": 70.0,
      "category": {
        "id": 1,
        "name": "الإعانة الشهرية"
      },
      "days_remaining": 59,
      "donors_count": 245
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

### 2. عرض الفئات

```http
GET /api/v1/categories
```

**الاستجابة:**
```json
{
  "message": "Categories retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "الإعانة الشهرية",
      "programs_count": 2
    }
  ]
}
```

### 3. تفاصيل البرنامج

```http
GET /api/v1/programs/{id}
```

**الاستجابة:**
```json
{
  "message": "Program retrieved successfully",
  "data": {
    "id": 1,
    "title": "مساعدة كبار السن",
    "description": "برنامج لدعم كبار السن في الحصول على الرعاية الصحية والاحتياجات الأساسية...",
    "image": "https://images.unsplash.com/...",
    "goal_amount": "50000.00",
    "raised_amount": "35000.00",
    "progress_percentage": 70.0,
    "days_remaining": 59,
    "donors_count": 245,
    "category": {
      "id": 1,
      "name": "الإعانة الشهرية"
    }
  }
}
```

### 4. إنشاء تبرع

```http
POST /api/v1/donations
```

**البيانات المطلوبة:**
```json
{
  "program_id": 1,
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
    "program_id": 1,
    "amount": "100.00",
    "donor_name": "أحمد محمد",
    "note": "تبرع خيري",
    "type": "quick",
    "status": "pending",
    "expires_at": "2025-08-21T08:06:20.000000Z"
  }
}
```

### 5. المبالغ السريعة

```http
GET /api/v1/donations/quick-amounts
```

**الاستجابة:**
```json
{
  "message": "Quick amounts retrieved successfully",
  "data": [
    {"amount": 50, "label": "50 ريال"},
    {"amount": 100, "label": "100 ريال"},
    {"amount": 200, "label": "200 ريال"},
    {"amount": 500, "label": "500 ريال"},
    {"amount": 1000, "label": "1000 ريال"}
  ]
}
```

### 6. تبرعات البرنامج

```http
GET /api/v1/programs/{id}/donations
```

**الاستجابة:**
```json
{
  "message": "Donations retrieved successfully",
  "data": [
    {
      "id": 1,
      "donation_id": "DN_...",
      "amount": "100.00",
      "donor_name": "أحمد محمد",
      "note": "تبرع خيري",
      "status": "paid",
      "paid_at": "2025-08-14T08:06:20.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 245,
    "last_page": 25
  }
}
```

## استخدام Flutter

### 1. الصفحة الرئيسية
```dart
// جلب البرامج
final response = await http.get(Uri.parse('$baseUrl/api/v1/programs'));
final programs = jsonDecode(response.body)['data'];

// جلب الفئات
final categoriesResponse = await http.get(Uri.parse('$baseUrl/api/v1/categories'));
final categories = jsonDecode(categoriesResponse.body)['data'];
```

### 2. صفحة التفاصيل
```dart
// جلب تفاصيل البرنامج
final response = await http.get(Uri.parse('$baseUrl/api/v1/programs/$programId'));
final program = jsonDecode(response.body)['data'];

// جلب المبالغ السريعة
final amountsResponse = await http.get(Uri.parse('$baseUrl/api/v1/donations/quick-amounts'));
final quickAmounts = jsonDecode(amountsResponse.body)['data'];
```

### 3. إنشاء تبرع
```dart
// إنشاء تبرع جديد
final response = await http.post(
  Uri.parse('$baseUrl/api/v1/donations'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'program_id': programId,
    'amount': amount,
    'donor_name': donorName,
    'note': note,
    'type': 'quick'
  })
);
```

## الميزات المضافة

### 1. الصور
- كل برنامج يحتوي على صورة مناسبة
- الصور من Unsplash عالية الجودة

### 2. الإحصائيات
- **الأيام المتبقية**: حساب تلقائي للأيام المتبقية
- **عدد المتبرعين**: عدد التبرعات المدفوعة
- **نسبة التقدم**: حساب تلقائي لنسبة الإنجاز

### 3. الفلاتر
- فلترة حسب الفئة
- البحث في العنوان والوصف
- ترتيب حسب التاريخ

### 4. نظام التبرعات
- مبالغ سريعة (50، 100، 200، 500، 1000 ريال)
- إدخال مبلغ مخصص
- تحديث تلقائي للمبلغ المجمع

## التشغيل

1. **تشغيل الهجرات:**
```bash
php artisan migrate
```

2. **إضافة البيانات:**
```bash
php artisan db:seed --class=CharityProgramsSeeder
```

3. **تشغيل الخادم:**
```bash
php artisan serve
```

## ملاحظات

- جميع البرامج نشطة وجاهزة للعرض
- الصور من مصادر موثوقة (Unsplash)
- الأسماء واضحة ومناسبة للسياق العربي
- النظام يدعم التبرعات الفورية والمؤجلة
