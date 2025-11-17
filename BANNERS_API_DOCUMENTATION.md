# Banners API Documentation

## نظرة عامة
تم إضافة نظام إدارة البانرات (Banners) بالكامل مع APIs للواجهة العامة والإدارة.

---

## Public APIs (APIs العامة - لا تحتاج مصادقة)

### 1. الحصول على جميع البانرات النشطة
```
GET /api/v1/banners
```

**Query Parameters:**
- `featured` (boolean, optional): للحصول على البانرات المميزة فقط
- `limit` (integer, optional): عدد النتائج (افتراضي: 10)

**Response:**
```json
{
  "message": "Banners retrieved successfully",
  "data": [
    {
      "id": 1,
      "title_ar": "عنوان البانر بالعربي",
      "title_en": "Banner Title in English",
      "description_ar": "وصف البانر بالعربي",
      "description_en": "Banner description in English",
      "image": "banners/example.jpg",
      "image_url": "http://domain.com/image/banners/example.jpg",
      "link": "https://example.com",
      "status": "active",
      "order": 0,
      "is_featured": true,
      "start_date": "2025-01-01",
      "end_date": "2025-12-31",
      "is_currently_active": true,
      "created_at": "2025-11-17T07:00:00.000000Z",
      "updated_at": "2025-11-17T07:00:00.000000Z"
    }
  ]
}
```

---

### 2. الحصول على بانر محدد
```
GET /api/v1/banners/{id}
```

**Response:**
```json
{
  "message": "Banner retrieved successfully",
  "data": {
    "id": 1,
    "title_ar": "عنوان البانر بالعربي",
    "title_en": "Banner Title in English",
    ...
  }
}
```

---

### 3. الحصول على البانرات المميزة
```
GET /api/v1/banners/featured
```

**Query Parameters:**
- `limit` (integer, optional): عدد النتائج (افتراضي: 5)

**Response:**
```json
{
  "message": "Featured banners retrieved successfully",
  "data": [...]
}
```

---

## Admin APIs (APIs الإدارة - تحتاج مصادقة + صلاحية admin)

**ملاحظة:** جميع APIs الإدارة تحتاج:
- Header: `Authorization: Bearer {token}`
- صلاحية: `admin` role

---

### 1. الحصول على جميع البانرات (للإدارة)
```
GET /api/v1/admin/banners
```

**Query Parameters:**
- `status` (string, optional): `active` أو `inactive`
- `is_featured` (boolean, optional): للحصول على البانرات المميزة فقط
- `search` (string, optional): البحث في العنوان والوصف
- `per_page` (integer, optional): عدد النتائج في الصفحة (افتراضي: 10)
- `page` (integer, optional): رقم الصفحة

**Response:**
```json
{
  "message": "Banners retrieved successfully",
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 20,
    "last_page": 2
  }
}
```

---

### 2. إنشاء بانر جديد
```
POST /api/v1/admin/banners
```

**Content-Type:** 
- `multipart/form-data` (إذا كنت ترسل ملف صورة مباشرة)
- `application/json` (إذا كنت تستخدم `image_path`)

**Body Parameters:**
- `title_ar` (string, required): العنوان بالعربي
- `title_en` (string, required): العنوان بالإنجليزي
- `description_ar` (string, optional): الوصف بالعربي
- `description_en` (string, optional): الوصف بالإنجليزي
- `image` (file, optional): **الطريقة الأولى:** صورة البانر كملف مباشر (jpg, jpeg, png, webp - حد أقصى 5MB)
- `image_path` (string, optional): **الطريقة الثانية:** مسار الصورة من `/banners/upload/image` (مثل: `banners/example.jpg`)
  - **ملاحظة مهمة:** استخدم إما `image` (ملف) أو `image_path` (string)، وليس كلاهما
  - إذا لم ترسل أي منهما، سيتم إنشاء البانر بدون صورة
- `link` (string, optional): رابط البانر (URL)
- `status` (string, optional): `active` أو `inactive` (افتراضي: `active`)
- `order` (integer, optional): ترتيب البانر (افتراضي: 0)
- `is_featured` (boolean, optional): هل البانر مميز (افتراضي: false)
- `start_date` (date, optional): تاريخ البدء (YYYY-MM-DD)
- `end_date` (date, optional): تاريخ الانتهاء (YYYY-MM-DD) - يجب أن يكون بعد start_date

**Response:**
```json
{
  "message": "Banner created successfully",
  "data": {
    "id": 1,
    "title_ar": "عنوان البانر بالعربي",
    ...
  }
}
```

---

### 3. الحصول على بانر محدد (للإدارة)
```
GET /api/v1/admin/banners/{id}
```

**Response:**
```json
{
  "message": "Banner retrieved successfully",
  "data": {...}
}
```

---

### 4. تحديث بانر
```
PUT /api/v1/admin/banners/{id}
```

**Content-Type:** 
- `multipart/form-data` (إذا كنت ترسل ملف صورة مباشرة)
- `application/json` (إذا كنت تستخدم `image_path` أو لا تريد تحديث الصورة)

**Body Parameters:** (جميعها optional - فقط أرسل الحقول التي تريد تحديثها)
- `title_ar` (string)
- `title_en` (string)
- `description_ar` (string)
- `description_en` (string)
- `image` (file, optional): **الطريقة الأولى:** صورة جديدة كملف مباشر (سيتم حذف الصورة القديمة تلقائياً)
- `image_path` (string, optional): **الطريقة الثانية:** مسار الصورة من `/banners/upload/image` (مثل: `banners/example.jpg`)
  - **ملاحظة مهمة:** استخدم إما `image` (ملف) أو `image_path` (string)، وليس كلاهما
  - إذا لم ترسل أي منهما، لن يتم تحديث الصورة (ستبقى الصورة القديمة)
  - لإزالة الصورة: أرسل `image: null` في JSON
- `link` (string)
- `status` (string): `active` أو `inactive`
- `order` (integer)
- `is_featured` (boolean)
- `start_date` (date)
- `end_date` (date)

**Response:**
```json
{
  "message": "Banner updated successfully",
  "data": {...}
}
```

---

### 5. حذف بانر
```
DELETE /api/v1/admin/banners/{id}
```

**Response:**
```json
{
  "message": "Banner deleted successfully"
}
```

**ملاحظة:** سيتم حذف الصورة تلقائياً عند حذف البانر.

---

### 6. رفع صورة فقط (بدون إنشاء بانر)
```
POST /api/v1/admin/banners/upload/image
```

**Content-Type:** `multipart/form-data`

**Body Parameters:**
- `image` (file, required): صورة البانر (jpg, jpeg, png, webp - حد أقصى 5MB)

**Response:**
```json
{
  "message": "Image uploaded successfully",
  "data": {
    "path": "banners/example.jpg",
    "url": "http://domain.com/storage/banners/example.jpg"
  }
}
```

---

## ملاحظات مهمة

1. **عرض الصور:** الصور تُعرض عبر:
   ```
   http://domain.com/image/banners/{filename}
   ```
   أو
   ```
   http://domain.com/storage/banners/{filename}
   ```

2. **الترتيب:** البانرات تُرتب حسب `order` (تصاعدي) ثم حسب تاريخ الإنشاء (تنازلي).

3. **البانرات النشطة:** البانرات التي تظهر في Public APIs يجب أن تكون:
   - `status = 'active'`
   - `start_date` <= اليوم الحالي (أو null)
   - `end_date` >= اليوم الحالي (أو null)

4. **الصلاحيات:** جميع Admin APIs تحتاج:
   - مصادقة: `Authorization: Bearer {token}`
   - صلاحية: `role:admin`

5. **اللغات:** النظام يدعم العربية والإنجليزية في جميع الحقول.

6. **إرسال الصورة - طريقتان:**
   - **الطريقة الأولى:** إرسال ملف صورة مباشرة في حقل `image` (multipart/form-data)
   - **الطريقة الثانية:** رفع الصورة أولاً عبر `/banners/upload/image` ثم إرسال `image_path` (string) في JSON
   - **مهم:** لا ترسل `image` و `image_path` معاً، استخدم واحدة فقط
   - إذا لم ترسل أي منهما عند الإنشاء، سيتم إنشاء البانر بدون صورة
   - إذا لم ترسل أي منهما عند التحديث، لن يتم تغيير الصورة (ستبقى الصورة القديمة)

---

## أمثلة على الاستخدام

### مثال 1: الحصول على البانرات المميزة
```javascript
fetch('http://domain.com/api/v1/banners/featured?limit=3')
  .then(res => res.json())
  .then(data => console.log(data));
```

### مثال 2: إنشاء بانر جديد - الطريقة الأولى (رفع ملف مباشر)
```javascript
const formData = new FormData();
formData.append('title_ar', 'عنوان البانر');
formData.append('title_en', 'Banner Title');
formData.append('image', fileInput.files[0]); // ملف صورة مباشر
formData.append('link', 'https://example.com');
formData.append('is_featured', true);
formData.append('order', 1);

fetch('http://domain.com/api/v1/admin/banners', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: formData
})
  .then(res => res.json())
  .then(data => console.log(data));
```

### مثال 3: إنشاء بانر جديد - الطريقة الثانية (استخدام image_path)
```javascript
// أولاً: رفع الصورة
const uploadFormData = new FormData();
uploadFormData.append('image', fileInput.files[0]);

const uploadResponse = await fetch('http://domain.com/api/v1/admin/banners/upload/image', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: uploadFormData
});

const uploadData = await uploadResponse.json();
// uploadData.data.path = "banners/example.jpg"

// ثانياً: إنشاء البانر باستخدام image_path
fetch('http://domain.com/api/v1/admin/banners', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title_ar: 'عنوان البانر',
    title_en: 'Banner Title',
    image_path: uploadData.data.path, // استخدام path من الرفع المسبق
    link: 'https://example.com',
    is_featured: true,
    order: 1
  })
})
  .then(res => res.json())
  .then(data => console.log(data));
```

### مثال 4: تحديث بانر (بدون تغيير الصورة)
```javascript
fetch('http://domain.com/api/v1/admin/banners/1', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    status: 'inactive',
    order: 5
  })
})
  .then(res => res.json())
  .then(data => console.log(data));
```

### مثال 5: تحديث بانر مع صورة جديدة (باستخدام image_path)
```javascript
// أولاً: رفع الصورة الجديدة
const uploadFormData = new FormData();
uploadFormData.append('image', fileInput.files[0]);

const uploadResponse = await fetch('http://domain.com/api/v1/admin/banners/upload/image', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: uploadFormData
});

const uploadData = await uploadResponse.json();

// ثانياً: تحديث البانر
fetch('http://domain.com/api/v1/admin/banners/1', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    image_path: uploadData.data.path,
    status: 'active'
  })
})
  .then(res => res.json())
  .then(data => console.log(data));
```

### مثال 6: حذف صورة البانر
```javascript
fetch('http://domain.com/api/v1/admin/banners/1', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    image: null // حذف الصورة
  })
})
  .then(res => res.json())
  .then(data => console.log(data));
```

---

## كود الحالة (Status Codes)

- `200` - نجاح
- `201` - تم الإنشاء بنجاح
- `404` - غير موجود
- `401` - غير مصرح (يحتاج مصادقة)
- `403` - ممنوع (يحتاج صلاحية admin)
- `422` - خطأ في التحقق من البيانات
- `500` - خطأ في الخادم

---

## بنية البيانات

### Banner Model Fields:
- `id` (integer)
- `title_ar` (string) - العنوان بالعربي
- `title_en` (string) - العنوان بالإنجليزي
- `description_ar` (text, nullable) - الوصف بالعربي
- `description_en` (text, nullable) - الوصف بالإنجليزي
- `image` (string, nullable) - مسار الصورة
- `link` (string, nullable) - رابط البانر
- `status` (enum: 'active', 'inactive') - حالة البانر
- `order` (integer) - ترتيب البانر
- `is_featured` (boolean) - هل البانر مميز
- `start_date` (date, nullable) - تاريخ البدء
- `end_date` (date, nullable) - تاريخ الانتهاء
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft delete

### Computed Attributes:
- `image_url` - رابط الصورة الكامل
- `is_currently_active` - هل البانر نشط حالياً (يأخذ في الاعتبار التواريخ)

