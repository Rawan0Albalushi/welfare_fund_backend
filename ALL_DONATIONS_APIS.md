# جميع APIs التبرعات في النظام

## 📋 نظرة عامة
هذا الملف يحتوي على جميع الـ APIs المتاحة لإرجاع التبرعات في نظام صندوق رعاية الطلاب.

---

## 🔐 APIs تبرعات المستخدم (تتطلب مصادقة)

### 1. الحصول على تبرعات المستخدم المسجل دخول
```
GET /api/v1/me/donations
```

**المصادقة:** مطلوبة (Bearer Token)

**المعاملات:**
- `page` (integer): رقم الصفحة (افتراضي: 1)
- `per_page` (integer): عدد العناصر في الصفحة (افتراضي: 10)
- `status` (string): تصفية حسب الحالة - `pending`, `paid`, `failed`, `expired`
- `type` (string): تصفية حسب النوع - `quick`, `gift`

**مثال:**
```bash
GET /api/v1/me/donations?status=paid&per_page=20
Authorization: Bearer YOUR_TOKEN
```

**الاستجابة:**
```json
{
  "message": "Donations retrieved successfully",
  "data": [
    {
      "id": 109,
      "donation_id": "DN_cc5db624-a4a0-4fa6-8a3f-a9144fcf503b",
      "amount": 25.00,
      "donor_name": "روان البلوشية",
      "type": "quick",
      "status": "paid",
      "note": "تبرع تجريبي لاختبار API",
      "paid_at": "2025-09-10T15:36:04.000000Z",
      "program": {
        "id": 26,
        "title": "برنامج فرص التعليم العالي"
      },
      "created_at": "2025-09-10T15:36:04.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  }
}
```

---

## 🌐 APIs التبرعات العامة (لا تتطلب مصادقة)

### 2. الحصول على التبرعات الحديثة
```
GET /api/v1/donations/recent
```

**المعاملات:**
- `limit` (integer): عدد التبرعات المطلوب (افتراضي: 10)

**مثال:**
```bash
GET /api/v1/donations/recent?limit=5
```

**الاستجابة:**
```json
{
  "message": "Recent donations retrieved successfully",
  "data": [
    {
      "donor_name": "أحمد محمد",
      "amount": 100.00,
      "program_title": "صندوق رعاية الطلاب",
      "paid_at": "2025-09-10T15:36:04.000000Z"
    }
  ]
}
```

### 3. الحصول على تبرعات برنامج محدد
```
GET /api/v1/programs/{id}/donations
```

**المعاملات:**
- `id` (integer): معرف البرنامج (مطلوب)
- `page` (integer): رقم الصفحة (افتراضي: 1)
- `per_page` (integer): عدد العناصر في الصفحة (افتراضي: 10)

**مثال:**
```bash
GET /api/v1/programs/26/donations?per_page=20
```

**الاستجابة:**
```json
{
  "message": "Donations retrieved successfully",
  "data": [
    {
      "id": 109,
      "donation_id": "DN_cc5db624-a4a0-4fa6-8a3f-a9144fcf503b",
      "amount": 25.00,
      "donor_name": "روان البلوشية",
      "type": "quick",
      "status": "paid",
      "program": {
        "id": 26,
        "title": "برنامج فرص التعليم العالي"
      },
      "paid_at": "2025-09-10T15:36:04.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  }
}
```

### 4. الحصول على مبالغ التبرع السريع
```
GET /api/v1/donations/quick-amounts
```

**مثال:**
```bash
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

### 5. الحصول على حالة تبرع محدد
```
GET /api/v1/donations/{id}/status
```

**المعاملات:**
- `id` (string): معرف التبرع (مطلوب)

**مثال:**
```bash
GET /api/v1/donations/DN_cc5db624-a4a0-4fa6-8a3f-a9144fcf503b/status
```

**الاستجابة:**
```json
{
  "message": "Donation status retrieved successfully",
  "data": {
    "status": "paid",
    "amount": 25.00,
    "type": "quick",
    "expires_at": "2025-09-17T15:36:04.000000Z",
    "paid_at": "2025-09-10T15:36:04.000000Z"
  }
}
```

---

## 👨‍💼 APIs إدارة التبرعات (للمديرين)

### 6. الحصول على جميع التبرعات (للمديرين)
```
GET /admin/donations
```

**المصادقة:** مطلوبة (Admin Token)

**المعاملات:**
- `page` (integer): رقم الصفحة (افتراضي: 1)
- `per_page` (integer): عدد العناصر في الصفحة (افتراضي: 10)
- `status` (string): تصفية حسب الحالة - `pending`, `paid`, `failed`, `expired`
- `type` (string): تصفية حسب النوع - `quick`, `gift`

**مثال:**
```bash
GET /admin/donations?status=paid&per_page=50
Authorization: Bearer ADMIN_TOKEN
```

**الاستجابة:**
```json
{
  "message": "Donations retrieved successfully",
  "data": [
    {
      "id": 109,
      "donation_id": "DN_cc5db624-a4a0-4fa6-8a3f-a9144fcf503b",
      "amount": 25.00,
      "donor_name": "روان البلوشية",
      "type": "quick",
      "status": "paid",
      "user": {
        "id": 2,
        "name": "روان البلوشية",
        "phone": "96339555"
      },
      "program": {
        "id": 26,
        "title": "برنامج فرص التعليم العالي"
      },
      "paid_at": "2025-09-10T15:36:04.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  }
}
```

---

## 📊 ملخص APIs التبرعات

| API | الطريقة | المسار | المصادقة | الوصف |
|-----|---------|--------|----------|--------|
| تبرعات المستخدم | GET | `/api/v1/me/donations` | ✅ مطلوبة | تبرعات المستخدم المسجل دخول |
| التبرعات الحديثة | GET | `/api/v1/donations/recent` | ❌ غير مطلوبة | آخر التبرعات المدفوعة |
| تبرعات البرنامج | GET | `/api/v1/programs/{id}/donations` | ❌ غير مطلوبة | تبرعات برنامج محدد |
| مبالغ سريعة | GET | `/api/v1/donations/quick-amounts` | ❌ غير مطلوبة | مبالغ التبرع السريع |
| حالة التبرع | GET | `/api/v1/donations/{id}/status` | ❌ غير مطلوبة | حالة تبرع محدد |
| جميع التبرعات | GET | `/admin/donations` | ✅ مطلوبة | جميع التبرعات (للمديرين) |

---

## 🔧 أمثلة الاستخدام

### Flutter - تبرعات المستخدم
```dart
Future<Map<String, dynamic>> getUserDonations() async {
  final token = await getAuthToken();
  
  final response = await http.get(
    Uri.parse('http://localhost:8000/api/v1/me/donations'),
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  );
  
  return json.decode(response.body);
}
```

### Flutter - التبرعات الحديثة
```dart
Future<Map<String, dynamic>> getRecentDonations() async {
  final response = await http.get(
    Uri.parse('http://localhost:8000/api/v1/donations/recent?limit=10'),
    headers: {
      'Accept': 'application/json',
    },
  );
  
  return json.decode(response.body);
}
```

### Flutter - تبرعات البرنامج
```dart
Future<Map<String, dynamic>> getProgramDonations(int programId) async {
  final response = await http.get(
    Uri.parse('http://localhost:8000/api/v1/programs/$programId/donations'),
    headers: {
      'Accept': 'application/json',
    },
  );
  
  return json.decode(response.body);
}
```

---

## 📱 Postman Collection

تم إنشاء ملفات Postman للاختبار:
- `User_Donations_API.postman_collection.json` - مجموعة APIs تبرعات المستخدم
- `User_Donations_API.postman_environment.json` - بيئة الاختبار

---

## ⚠️ ملاحظات مهمة

1. **الأمان:** APIs المستخدم تتطلب Bearer Token صالح
2. **الصفحات:** جميع APIs تدعم التقسيم على صفحات
3. **التصفية:** معظم APIs تدعم التصفية حسب الحالة والنوع
4. **الأداء:** النتائج مقسمة على صفحات لتحسين الأداء
5. **الترتيب:** التبرعات مرتبة حسب التاريخ (الأحدث أولاً)

---

## 🎯 الاستخدام المقترح

- **لصفحة التبرعات الشخصية:** استخدم `/api/v1/me/donations`
- **لصفحة البرنامج:** استخدم `/api/v1/programs/{id}/donations`
- **للصفحة الرئيسية:** استخدم `/api/v1/donations/recent`
- **للوحة الإدارة:** استخدم `/admin/donations`
