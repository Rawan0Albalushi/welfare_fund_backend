# دليل سريع - API تبرعات المستخدم

## 🚀 نظرة عامة
تم إنشاء API جديد يسمح للمستخدمين المسجلين دخول في التطبيق بالحصول على تاريخ تبرعاتهم.

## 📍 الـ Endpoint
```
GET /api/v1/me/donations
```

## 🔐 المصادقة
- **مطلوبة:** Bearer Token
- **الحصول على التوكن:** استخدم `/auth/login`

## 🎯 المعاملات المتاحة
| المعامل | النوع | الوصف | القيم المتاحة |
|---------|-------|--------|---------------|
| `page` | integer | رقم الصفحة | أي رقم (افتراضي: 1) |
| `per_page` | integer | عدد العناصر | أي رقم (افتراضي: 10) |
| `status` | string | حالة التبرع | `pending`, `paid`, `failed`, `expired` |
| `type` | string | نوع التبرع | `quick`, `gift` |

## 📝 أمثلة الاستخدام

### 1. الحصول على جميع التبرعات
```bash
GET /api/v1/me/donations
Authorization: Bearer YOUR_TOKEN
```

### 2. التبرعات المدفوعة فقط
```bash
GET /api/v1/me/donations?status=paid
Authorization: Bearer YOUR_TOKEN
```

### 3. تبرعات الهدايا فقط
```bash
GET /api/v1/me/donations?type=gift
Authorization: Bearer YOUR_TOKEN
```

### 4. مع التصفية والصفحات
```bash
GET /api/v1/me/donations?status=paid&type=quick&page=1&per_page=20
Authorization: Bearer YOUR_TOKEN
```

## 📊 مثال على الاستجابة
```json
{
  "message": "Donations retrieved successfully",
  "data": [
    {
      "id": 1,
      "donation_id": "DN_12345678-1234-1234-1234-123456789012",
      "amount": 100.00,
      "donor_name": "أحمد محمد",
      "type": "quick",
      "status": "paid",
      "note": "تبرع لصالح الطلاب المحتاجين",
      "paid_at": "2024-01-15T10:30:00.000000Z",
      "program": {
        "id": 1,
        "title": "صندوق رعاية الطلاب"
      },
      "created_at": "2024-01-15T10:25:00.000000Z"
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

## 🧪 الاختبار

### باستخدام Postman:
1. استورد `User_Donations_API.postman_collection.json`
2. استورد `User_Donations_API.postman_environment.json`
3. قم بتسجيل الدخول أولاً للحصول على التوكن
4. اختبر الـ endpoint

### باستخدام cURL:
```bash
# 1. تسجيل الدخول
curl -X POST "http://localhost:8000/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"phone": "+96812345678", "password": "password123"}'

# 2. استخدام التوكن
curl -X GET "http://localhost:8000/api/v1/me/donations" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## 🔧 التكامل مع Flutter

```dart
// مثال بسيط
Future<void> loadUserDonations() async {
  final token = await getStoredToken();
  
  final response = await http.get(
    Uri.parse('http://localhost:8000/api/v1/me/donations'),
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  );
  
  if (response.statusCode == 200) {
    final data = json.decode(response.body);
    // معالجة البيانات
  }
}
```

## ⚠️ ملاحظات مهمة
- **الأمان:** المستخدم يرى تبرعاته فقط
- **الأداء:** النتائج مقسمة على صفحات
- **الترتيب:** الأحدث أولاً
- **العلاقات:** يتم تحميل معلومات البرنامج تلقائياً

## 📁 الملفات المضافة
- `app/Http/Controllers/Me/DonationsController.php` - الكنترولر
- `routes/api.php` - تم إضافة الـ route
- `USER_DONATIONS_API.md` - التوثيق الكامل
- `User_Donations_API.postman_collection.json` - مجموعة Postman
- `User_Donations_API.postman_environment.json` - بيئة Postman

## ✅ الحالة
- ✅ الكنترولر تم إنشاؤه
- ✅ الـ route تم تسجيله
- ✅ التوثيق تم إنشاؤه
- ✅ ملفات Postman تم إنشاؤها
- ✅ جاهز للاختبار
