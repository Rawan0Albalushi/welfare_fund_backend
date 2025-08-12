# Student Registration API Documentation

## 📋 نظرة عامة

تم تحديث API تسجيل الطالب ليتطابق مع النموذج الحالي المطلوب. يحتوي النموذج على الأقسام التالية:

### 🔗 **نقطة النهاية (Endpoint)**
```
POST /api/v1/students/registration
```

### 🔐 **المصادقة**
- مطلوب Bearer Token
- يجب أن يكون المستخدم مسجل دخول

## 📝 **هيكل البيانات المطلوبة**

### **1. اختيار البرنامج**
```json
{
  "program_id": 1
}
```

### **2. البيانات الشخصية**
```json
{
  "personal": {
    "full_name": "أحمد محمد علي",
    "student_id": "CS123456",
    "email": "ahmed@example.com",
    "phone": "+966501234567",
    "gender": "male"
  }
}
```

### **3. البيانات الأكاديمية**
```json
{
  "academic": {
    "university": "جامعة الملك سعود",
    "college": "كلية علوم الحاسب والمعلومات",
    "major": "هندسة البرمجيات",
    "program": "بكالوريوس علوم الحاسب",
    "academic_year": 3,
    "gpa": 3.8
  }
}
```

### **4. البيانات المالية**
```json
{
  "financial": {
    "income_level": "medium",
    "family_size": "4-6"
  }
}
```

### **5. المستندات**
```json
{
  "id_card_image": "[file upload]"
}
```

## 📊 **تفاصيل الحقول وقواعد التحقق**

### **البيانات الشخصية**
| الحقل | النوع | مطلوب | القيود | الوصف |
|-------|-------|-------|--------|-------|
| `full_name` | نص | ✅ | أقصى 255 حرف | الاسم الكامل |
| `student_id` | نص | ✅ | أقصى 50 حرف | رقم الطالب |
| `email` | بريد إلكتروني | ❌ | صيغة صحيحة | البريد الإلكتروني |
| `phone` | نص | ✅ | أرقام وعلامات | رقم الهاتف |
| `gender` | قائمة | ✅ | ذكر/أنثى | الجنس |

### **البيانات الأكاديمية**
| الحقل | النوع | مطلوب | القيود | الوصف |
|-------|-------|-------|--------|-------|
| `university` | نص | ✅ | أقصى 255 حرف | اسم الجامعة |
| `college` | نص | ✅ | أقصى 255 حرف | اسم الكلية |
| `major` | نص | ✅ | أقصى 255 حرف | التخصص |
| `program` | نص | ✅ | أقصى 255 حرف | البرنامج الدراسي |
| `academic_year` | رقم | ✅ | 1-6 | السنة الدراسية |
| `gpa` | رقم | ✅ | 0.0-4.0 | المعدل التراكمي |

### **البيانات المالية**
| الحقل | النوع | مطلوب | القيم المتاحة | الوصف |
|-------|-------|-------|---------------|-------|
| `income_level` | قائمة | ✅ | منخفض/متوسط/مرتفع | مستوى الدخل |
| `family_size` | قائمة | ✅ | 1-3/4-6/7-9/10+ | حجم الأسرة |

### **المستندات**
| الحقل | النوع | مطلوب | القيود | الوصف |
|-------|-------|-------|--------|-------|
| `id_card_image` | ملف | ❌ | JPG/PNG/PDF، أقصى 10MB | صورة البطاقة الشخصية |

## 🔍 **الحصول على البرامج المتاحة**

**Endpoint:** `GET /api/v1/programs`

**الاستجابة:**
```json
{
  "message": "تم جلب البرامج بنجاح",
  "data": [
    {
      "id": 1,
      "title": "اسم البرنامج",
      "description": "وصف البرنامج",
      "goal_amount": 100000.00,
      "raised_amount": 50000.00,
      "progress_percentage": 50.0,
      "status": "active",
      "start_date": "2024-01-01",
      "end_date": "2024-12-31",
      "category": {
        "id": 1,
        "name": "اسم الفئة",
        "description": "وصف الفئة"
      }
    }
  ]
}
```

## ✅ **استجابة النجاح**

```json
{
  "message": "تم إنشاء التسجيل بنجاح",
  "data": {
    "id": 1,
    "registration_id": "REG_uuid-here",
    "personal": {
      "full_name": "أحمد محمد علي",
      "student_id": "CS123456",
      "email": "ahmed@example.com",
      "phone": "+966501234567",
      "gender": "male"
    },
    "academic": {
      "university": "جامعة الملك سعود",
      "college": "كلية علوم الحاسب والمعلومات",
      "major": "هندسة البرمجيات",
      "program": "بكالوريوس علوم الحاسب",
      "academic_year": 3,
      "gpa": 3.8
    },
    "financial": {
      "income_level": "medium",
      "family_size": "4-6"
    },
    "status": "under_review",
    "reject_reason": null,
    "id_card_image": "students/id_cards/filename.jpg",
    "program": {
      "id": 1,
      "title": "اسم البرنامج"
    },
    "user": {
      "id": 1,
      "name": "اسم المستخدم"
    },
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

## ❌ **أخطاء التحقق**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "personal.full_name": ["الاسم الكامل مطلوب."],
    "personal.phone": ["يرجى إدخال رقم هاتف صحيح."],
    "academic.gpa": ["المعدل التراكمي يجب أن يكون رقم."],
    "financial.income_level": ["مستوى الدخل مطلوب."]
  }
}
```

## 📁 **رفع الملفات**

- **نوع المحتوى:** `multipart/form-data`
- **الملفات المدعومة:** JPG, JPEG, PNG, PDF
- **الحد الأقصى للحجم:** 10 ميجابايت
- **مسار التخزين:** `storage/app/public/students/id_cards/`

## 🔄 **حالات التسجيل**

| الحالة | الوصف |
|--------|-------|
| `under_review` | قيد المراجعة |
| `accepted` | مقبول |
| `rejected` | مرفوض |

## 📋 **ملاحظات مهمة**

1. **المصادقة مطلوبة:** يجب أن يكون المستخدم مسجل دخول
2. **معرف التسجيل:** يتم إنشاؤه تلقائياً بصيغة `REG_` + UUID
3. **الحالة:** تُضبط تلقائياً على `"under_review"`
4. **المستندات:** يمكن رفعها لاحقاً عبر endpoint منفصل
5. **التحقق:** جميع أخطاء التحقق تُرجع HTTP 422 مع رسائل مفصلة

## 🛠️ **أمثلة الاستخدام**

### **JavaScript (Fetch)**
```javascript
const formData = new FormData();
formData.append('program_id', '1');
formData.append('personal[full_name]', 'أحمد محمد علي');
formData.append('personal[student_id]', 'CS123456');
formData.append('personal[email]', 'ahmed@example.com');
formData.append('personal[phone]', '+966501234567');
formData.append('personal[gender]', 'male');
formData.append('academic[university]', 'جامعة الملك سعود');
formData.append('academic[college]', 'كلية علوم الحاسب والمعلومات');
formData.append('academic[major]', 'هندسة البرمجيات');
formData.append('academic[program]', 'بكالوريوس علوم الحاسب');
formData.append('academic[academic_year]', '3');
formData.append('academic[gpa]', '3.8');
formData.append('financial[income_level]', 'medium');
formData.append('financial[family_size]', '4-6');

if (idCardFile) {
  formData.append('id_card_image', idCardFile);
}

fetch('/api/v1/students/registration', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
  },
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

### **cURL**
```bash
curl -X POST \
  http://localhost:8000/api/v1/students/registration \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -F 'program_id=1' \
  -F 'personal[full_name]=أحمد محمد علي' \
  -F 'personal[student_id]=CS123456' \
  -F 'personal[email]=ahmed@example.com' \
  -F 'personal[phone]=+966501234567' \
  -F 'personal[gender]=male' \
  -F 'academic[university]=جامعة الملك سعود' \
  -F 'academic[college]=كلية علوم الحاسب والمعلومات' \
  -F 'academic[major]=هندسة البرمجيات' \
  -F 'academic[program]=بكالوريوس علوم الحاسب' \
  -F 'academic[academic_year]=3' \
  -F 'academic[gpa]=3.8' \
  -F 'financial[income_level]=medium' \
  -F 'financial[family_size]=4-6' \
  -F 'id_card_image=@/path/to/id_card.jpg'
```
