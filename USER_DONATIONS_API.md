# User Donations API

## نظرة عامة
هذا الـ API يسمح للمستخدمين المسجلين دخول في التطبيق بالحصول على تاريخ تبرعاتهم.

## Endpoint

### الحصول على تبرعات المستخدم
```
GET /api/v1/me/donations
```

**المصادقة:** مطلوبة (Bearer Token)

**المعاملات الاختيارية:**
- `page` (integer): رقم الصفحة (مهمل - يعرض جميع التبرعات الآن)
- `per_page` (integer): عدد العناصر في الصفحة (مهمل - يعرض جميع التبرعات الآن)
- `status` (string): تصفية حسب الحالة - `pending`, `paid`, `failed`, `expired`
- `type` (string): تصفية حسب النوع - `quick`, `gift`

**مثال على الطلب:**
```bash
curl -X GET "http://localhost:8000/api/v1/me/donations?status=paid" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**مثال على الاستجابة:**
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
      "expires_at": null,
      "paid_at": "2024-01-15T10:30:00.000000Z",
      "program": {
        "id": 1,
        "title": "صندوق رعاية الطلاب"
      },
      "gift_meta": null,
      "created_at": "2024-01-15T10:25:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "donation_id": "DN_87654321-4321-4321-4321-210987654321",
      "amount": 250.00,
      "donor_name": "فاطمة علي",
      "type": "gift",
      "status": "paid",
      "note": "هدية لصديق",
      "expires_at": null,
      "paid_at": "2024-01-14T15:45:00.000000Z",
      "program": {
        "id": 2,
        "title": "برنامج الكتب الدراسية"
      },
      "gift_meta": {
        "recipient_name": "سارة أحمد",
        "recipient_phone": "+96812345678",
        "message": "تهنئة بالنجاح",
        "sender_name": "فاطمة علي",
        "sender_phone": "+96887654321",
        "hide_identity": false
      },
      "created_at": "2024-01-14T15:40:00.000000Z",
      "updated_at": "2024-01-14T15:45:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 2,
    "last_page": 1
  }
}
```

## حالات الاستجابة

### 200 - نجح الطلب
```json
{
  "message": "Donations retrieved successfully",
  "data": [...],
  "meta": {...}
}
```

### 401 - غير مصرح
```json
{
  "message": "Unauthenticated."
}
```

## ملاحظات مهمة

1. **المصادقة مطلوبة:** يجب إرسال Bearer Token صالح في header الطلب
2. **الترتيب:** التبرعات مرتبة حسب تاريخ الإنشاء (الأحدث أولاً)
3. **العلاقات:** يتم تحميل معلومات البرنامج وبيانات الهدية تلقائياً
4. **التصفية:** يمكن تصفية التبرعات حسب الحالة والنوع
5. **الصفحات:** النتائج مقسمة على صفحات لتحسين الأداء

## أمثلة على الاستخدام

### الحصول على جميع التبرعات
```bash
GET /api/v1/me/donations
```

### الحصول على التبرعات المدفوعة فقط
```bash
GET /api/v1/me/donations?status=paid
```

### الحصول على تبرعات الهدايا فقط
```bash
GET /api/v1/me/donations?type=gift
```

### الحصول على 20 تبرع في الصفحة
```bash
GET /api/v1/me/donations?per_page=20
```

### الحصول على الصفحة الثانية
```bash
GET /api/v1/me/donations?page=2
```

## التكامل مع Flutter

```dart
class DonationsService {
  static const String baseUrl = 'http://localhost:8000/api/v1';
  
  Future<Map<String, dynamic>> getUserDonations({
    String? status,
    String? type,
    int page = 1,
    int perPage = 10,
  }) async {
    final token = await getAuthToken(); // احصل على التوكن من التخزين المحلي
    
    final queryParams = <String, String>{
      'page': page.toString(),
      'per_page': perPage.toString(),
    };
    
    if (status != null) queryParams['status'] = status;
    if (type != null) queryParams['type'] = type;
    
    final uri = Uri.parse('$baseUrl/me/donations').replace(
      queryParameters: queryParams,
    );
    
    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    );
    
    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to load donations');
    }
  }
}
```

## اختبار الـ API

يمكنك اختبار الـ API باستخدام Postman أو أي أداة أخرى:

1. **احصل على التوكن:** استخدم `/auth/login` للحصول على Bearer Token
2. **أضف التوكن:** في Authorization header كـ "Bearer YOUR_TOKEN"
3. **اختبر الـ endpoint:** `GET /api/v1/me/donations`

## الأمان

- جميع الطلبات تتطلب مصادقة
- المستخدم يمكنه رؤية تبرعاته فقط
- لا يمكن الوصول لتبرعات مستخدمين آخرين
