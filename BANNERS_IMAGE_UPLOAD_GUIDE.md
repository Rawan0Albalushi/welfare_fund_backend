# دليل إرسال الصور في APIs البانرات

## الإجابة على الأسئلة

### سؤال 1: هل يجب إرسال image_url أم ملف صورة مباشرة؟

**الجواب:** يمكنك استخدام **أي من الطريقتين**:

#### الطريقة الأولى: إرسال ملف صورة مباشرة
```javascript
const formData = new FormData();
formData.append('title_ar', 'عنوان البانر');
formData.append('title_en', 'Banner Title');
formData.append('image', fileInput.files[0]); // ملف مباشر
// ... باقي الحقول
```

#### الطريقة الثانية: استخدام image_path (من /banners/upload/image)
```javascript
// 1. رفع الصورة أولاً
const uploadFormData = new FormData();
uploadFormData.append('image', fileInput.files[0]);

const uploadResponse = await fetch('/api/v1/admin/banners/upload/image', {
  method: 'POST',
  headers: { 'Authorization': 'Bearer ' + token },
  body: uploadFormData
});

const { data } = await uploadResponse.json();
// data.path = "banners/example.jpg"

// 2. إنشاء/تحديث البانر باستخدام image_path
fetch('/api/v1/admin/banners', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title_ar: 'عنوان البانر',
    title_en: 'Banner Title',
    image_path: data.path, // ✅ استخدم path هنا
    // ... باقي الحقول
  })
});
```

---

### سؤال 2: إذا استخدمت image_path، هل يجب عدم إرسال حقل image؟

**الجواب:** نعم، **لا ترسل حقل `image` على الإطلاق** إذا كنت تستخدم `image_path`.

**❌ خطأ:**
```javascript
{
  image: null,        // ❌ لا ترسل هذا
  image_path: "banners/example.jpg"
}
```

**✅ صحيح:**
```javascript
{
  // لا ترسل image على الإطلاق
  image_path: "banners/example.jpg"
}
```

---

### سؤال 3: ما هو الخطأ الثاني في "(and 1 more error)"؟

**الجواب:** الخطأ الثاني عادة يكون:
- `The image field must be an image` - لأن Laravel يحاول التحقق من `image` كملف، لكنك أرسلت string أو null

**الحل:** استخدم `image_path` بدلاً من `image` إذا كنت تستخدم الرفع المسبق.

---

## ملخص سريع

| السيناريو | ما يجب إرساله |
|-----------|---------------|
| رفع ملف مباشر | `image` (file) في FormData |
| استخدام صورة مرفوعة مسبقاً | `image_path` (string) في JSON |
| بدون صورة (إنشاء) | لا ترسل `image` ولا `image_path` |
| بدون صورة (تحديث) | لا ترسل `image` ولا `image_path` (ستبقى الصورة القديمة) |
| حذف الصورة (تحديث) | `image: null` في JSON |

---

## مثال كامل (الطريقة الموصى بها)

```javascript
// 1. رفع الصورة
const uploadFormData = new FormData();
uploadFormData.append('image', fileInput.files[0]);

const uploadRes = await fetch('/api/v1/admin/banners/upload/image', {
  method: 'POST',
  headers: { 'Authorization': 'Bearer ' + token },
  body: uploadFormData
});

const uploadData = await uploadRes.json();

// 2. إنشاء البانر
const bannerData = {
  title_ar: 'عنوان البانر',
  title_en: 'Banner Title',
  description_ar: 'وصف البانر',
  description_en: 'Banner description',
  image_path: uploadData.data.path, // ✅ استخدم path هنا
  link: 'https://example.com',
  status: 'active',
  order: 0,
  is_featured: true
};

const createRes = await fetch('/api/v1/admin/banners', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json' // ✅ مهم: JSON وليس FormData
  },
  body: JSON.stringify(bannerData)
});

const result = await createRes.json();
console.log(result);
```

---

## ملاحظات مهمة

1. ✅ استخدم `image_path` (string) وليس `image_url` (URL كامل)
2. ✅ `image_path` يأتي من `data.path` في response من `/banners/upload/image`
3. ✅ لا ترسل `image` و `image_path` معاً
4. ✅ استخدم `Content-Type: application/json` عند إرسال `image_path`
5. ✅ استخدم `multipart/form-data` عند إرسال ملف مباشر في `image`

