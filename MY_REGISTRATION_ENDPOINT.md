# My Registration Endpoint

## Overview
Endpoint جديد لعرض حالة طلب تسجيل الطالب الحالي بعد تسجيل الدخول.

## Endpoint Details

### URL
```
GET /api/v1/students/registration/my-registration
```

### Authentication
مطلوب - Bearer Token

### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

## Response Format

### Success Response (200)
```json
{
  "message": "Registration status retrieved successfully",
  "data": {
    "id": 1,
    "registration_id": "REG_e7e01d2b-960c-43c1-b0c5-c2f8b5d0d8f8",
    "status": "under_review",
    "rejection_reason": null,
    "personal": {
      "full_name": "اسم الطالب",
      "student_id": "رقم الطالب",
      "email": "البريد الإلكتروني",
      "phone": "رقم الهاتف",
      "gender": "male"
    },
    "academic": {
      "university": "اسم الجامعة",
      "college": "اسم الكلية",
      "major": "التخصص",
      "program": "اسم البرنامج",
      "academic_year": 3,
      "gpa": 3.8
    },
    "financial": {
      "income_level": "low",
      "family_size": 6
    },
    "program": {
      "id": 1,
      "title": "Emergency Financial Aid"
    },
    "created_at": "2025-08-12T19:33:12.000000Z",
    "updated_at": "2025-08-12T19:33:12.000000Z"
  }
}
```

### Not Found Response (404)
```json
{
  "message": "No registration found",
  "data": null
}
```

### Unauthorized Response (401)
```json
{
  "message": "Unauthenticated."
}
```

## Status Values

| Status | Description | Arabic |
|--------|-------------|---------|
| `under_review` | الطلب تحت المراجعة | الطلب تحت المراجعة |
| `accepted` | الطلب مقبول | الطلب مقبول |
| `rejected` | الطلب مرفوض | الطلب مرفوض |

## Fields Description

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | معرف الطلب الفريد |
| `registration_id` | string | معرف الطلب المخصص (REG_xxx format) |
| `status` | string | حالة الطلب (under_review, accepted, rejected) |
| `rejection_reason` | string/null | سبب الرفض (إذا كان مرفوض) |
| `personal` | object | البيانات الشخصية للطالب |
| `personal.full_name` | string | الاسم الكامل |
| `personal.student_id` | string | رقم الطالب |
| `personal.email` | string | البريد الإلكتروني |
| `personal.phone` | string | رقم الهاتف |
| `personal.gender` | string | الجنس (male/female) |
| `academic` | object | البيانات الأكاديمية |
| `academic.university` | string | اسم الجامعة |
| `academic.college` | string | اسم الكلية |
| `academic.major` | string | التخصص |
| `academic.program` | string | اسم البرنامج |
| `academic.academic_year` | integer | السنة الدراسية |
| `academic.gpa` | number | المعدل التراكمي |
| `financial` | object | البيانات المالية |
| `financial.income_level` | string | مستوى الدخل (low/medium/high) |
| `financial.family_size` | integer | حجم العائلة |
| `program.id` | integer | معرف البرنامج |
| `program.title` | string | عنوان البرنامج |
| `created_at` | string | تاريخ إنشاء الطلب |
| `updated_at` | string | تاريخ آخر تحديث للطلب |

## Example Usage

### cURL
```bash
curl -X GET \
  http://localhost:8000/api/v1/students/registration/my-registration \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

### JavaScript (Fetch)
```javascript
const response = await fetch('/api/v1/students/registration/my-registration', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  }
});

const data = await response.json();
console.log(data);
```

### PHP
```php
$response = Http::withHeaders([
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json'
])->get('/api/v1/students/registration/my-registration');

$data = $response->json();
```

## Notes

1. هذا الـ endpoint يرجع آخر طلب تسجيل للطالب الحالي
2. إذا لم يكن لدى الطالب أي طلبات، سيتم إرجاع 404
3. الـ endpoint محمي بـ authentication
4. يتم إرجاع معلومات البرنامج مع الطلب
5. سبب الرفض متاح فقط إذا كان الطلب مرفوض
