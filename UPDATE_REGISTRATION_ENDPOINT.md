# Update Registration Endpoint

## Overview
Endpoint جديد لتحديث طلب تسجيل مرفوض وإعادة تقديمه للمراجعة.

## Endpoint Details

### URL
```
PUT /api/v1/students/registration/{id}
```

### Authentication
مطلوب - Bearer Token

### Headers
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

## Request Format

### Form Data
```json
{
  "program_id": 1,
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
    "family_size": "4-6"
  },
  "id_card_image": "file" // اختياري
}
```

## Response Format

### Success Response (200)
```json
{
  "message": "Registration updated successfully",
  "data": {
    "id": 1,
    "registration_id": "REG_e7e01d2b-960c-43c1-b0c5-c2f8b5d0d8f8",
    "status": "under_review",
    "reject_reason": null,
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
      "family_size": "4-6"
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

### Forbidden Response (403)
```json
{
  "message": "Registration cannot be updated. Only rejected registrations can be updated."
}
```

### Not Found Response (404)
```json
{
  "message": "No query results for model [App\\Models\\StudentRegistration] {id}"
}
```

### Validation Error Response (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "program_id": ["The program id field is required."],
    "personal.full_name": ["The personal.full name field is required."]
  }
}
```

## Business Rules

1. **فقط الطلبات المرفوضة يمكن تحديثها**
   - إذا كان الطلب `accepted` أو `under_review`، سيتم رفض التحديث
   - فقط الطلبات بـ status `rejected` يمكن تحديثها

2. **إعادة تعيين الحالة**
   - بعد التحديث، يتم تعيين الحالة إلى `under_review`
   - يتم مسح `reject_reason`

3. **الحفاظ على الصورة**
   - إذا لم يتم رفع صورة جديدة، يتم الاحتفاظ بالصورة القديمة
   - إذا تم رفع صورة جديدة، يتم استبدال الصورة القديمة

## Example Usage

### cURL
```bash
curl -X PUT \
  http://localhost:8000/api/v1/students/registration/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "program_id=1" \
  -F "personal[full_name]=أحمد محمد علي" \
  -F "personal[student_id]=CS123456" \
  -F "personal[email]=ahmed@example.com" \
  -F "personal[phone]=+966501234567" \
  -F "personal[gender]=male" \
  -F "academic[university]=جامعة الملك سعود" \
  -F "academic[college]=كلية علوم الحاسب" \
  -F "academic[major]=هندسة البرمجيات" \
  -F "academic[program]=بكالوريوس علوم الحاسب" \
  -F "academic[academic_year]=3" \
  -F "academic[gpa]=3.8" \
  -F "financial[income_level]=low" \
  -F "financial[family_size]=4-6" \
  -F "id_card_image=@/path/to/image.jpg"
```

### JavaScript (Fetch)
```javascript
const formData = new FormData();
formData.append('program_id', 1);
formData.append('personal[full_name]', 'أحمد محمد علي');
formData.append('personal[student_id]', 'CS123456');
formData.append('personal[email]', 'ahmed@example.com');
formData.append('personal[phone]', '+966501234567');
formData.append('personal[gender]', 'male');
formData.append('academic[university]', 'جامعة الملك سعود');
formData.append('academic[college]', 'كلية علوم الحاسب');
formData.append('academic[major]', 'هندسة البرمجيات');
formData.append('academic[program]', 'بكالوريوس علوم الحاسب');
formData.append('academic[academic_year]', 3);
formData.append('academic[gpa]', 3.8);
formData.append('financial[income_level]', 'low');
formData.append('financial[family_size]', '4-6');

const response = await fetch('/api/v1/students/registration/1', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});

const data = await response.json();
console.log(data);
```

## Notes

1. هذا الـ endpoint مخصص فقط للطلبات المرفوضة
2. بعد التحديث، يتم إعادة تعيين الحالة إلى `under_review`
3. يتم مسح سبب الرفض السابق
4. يمكن رفع صورة جديدة أو الاحتفاظ بالصورة القديمة
5. جميع البيانات مطلوبة (نفس متطلبات إنشاء طلب جديد)
