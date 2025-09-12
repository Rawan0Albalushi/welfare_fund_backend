# إعادة هيكلة تدفق الدفع مع ثواني

## ملخص التغييرات

تم إعادة هيكلة تدفق الدفع مع ثواني وفقاً للمتطلبات المحددة لتحسين الأمان والاعتمادية.

## 1. تحديث ThawaniService

### التغييرات في `createSession`:
- **المعاملات الجديدة**: `createSession($donation, $products, $successUrl, $cancelUrl)`
- **تمرير metadata**: `['donation_db_id' => $donation->id]`
- **تحديث فوري**: حفظ `payment_session_id`, `payment_url`, `status`, `expires_at` في قاعدة البيانات

### إضافة دالة `confirmPayment`:
- جلب حالة الدفع من ثواني
- تحديث حالة التبرع حسب النتيجة
- تحويل المبالغ من بيسة إلى ريال عماني
- معالجة idempotent (عدم تكرار التحديث)

## 2. PaymentsController الجديد

### Endpoints:
- `POST /api/v1/payments/create` - إنشاء جلسة دفع
- `POST /api/v1/payments/confirm` - تأكيد حالة الدفع

### الميزات:
- التحقق من صحة البيانات
- معالجة الأخطاء
- تسجيل مفصل للعمليات
- دعم `session_id` أو `donation_id` في confirm

## 3. PaymentsReconcile Command

### الوظائف:
- تسوية دورية للدفعات المعلقة
- معالجة التبرعات الحديثة (< 48 ساعة)
- تحديث الحالات تلقائياً
- خيار `--dry-run` للتشغيل التجريبي

### التشغيل:
```bash
php artisan payments:reconcile
php artisan payments:reconcile --dry-run
```

## 4. Scheduler

تم إضافة تسوية دورية كل 5 دقائق في `routes/console.php`:
```php
Schedule::command('payments:reconcile')->everyFiveMinutes();
```

## 5. تحسينات قاعدة البيانات

### Migration جديدة:
- فهرس على `payment_session_id`
- فهرس مركب على `['status', 'created_at']`
- فهرس مركب على `['status', 'payment_session_id']`
- التأكد من نوع `DECIMAL` للحقول المالية

### تحديث نموذج Donation:
- إضافة `payment_session_id`, `payment_url`, `paid_amount` للـ fillable
- إضافة cast لـ `paid_amount` كـ decimal

## 6. التوافق مع الكود الموجود

### ThawaniPaymentService:
- دعم الاستخدام القديم والجديد
- تحويل تلقائي للمعاملات
- الحفاظ على التوافق مع Controllers الموجودة

### تحديث Controllers:
- `PaymentController` - تحديث لاستخدام التوقيع الجديد
- `Public/DonationController` - تحديث لاستخدام التوقيع الجديد

## 7. الأمان والاعتمادية

### الميزات الأمنية:
- **Idempotent operations**: عدم تكرار التحديث للتبرعات المدفوعة
- **Transaction safety**: استخدام DB transactions
- **Error handling**: معالجة شاملة للأخطاء
- **Logging**: تسجيل مفصل لجميع العمليات

### التحسينات:
- فهارس قاعدة البيانات لتحسين الأداء
- تحويل دقيق للمبالغ المالية
- معالجة المناطق الزمنية
- التحقق من صحة البيانات

## 8. API Documentation

تم إضافة OpenAPI documentation كاملة للـ endpoints الجديدة مع:
- أمثلة على الطلبات والاستجابات
- رموز الحالة HTTP
- وصف مفصل للمعاملات

## 9. الاستخدام

### إنشاء جلسة دفع:
```bash
POST /api/v1/payments/create
{
    "donation_id": "DN_12345678-1234-1234-1234-123456789012",
    "products": [
        {
            "name": "تبرع خيري",
            "quantity": 1,
            "unit_amount": 10000
        }
    ],
    "success_url": "https://example.com/success",
    "cancel_url": "https://example.com/cancel"
}
```

### تأكيد الدفع:
```bash
POST /api/v1/payments/confirm
{
    "session_id": "sess_1234567890"
}
```

أو:
```bash
POST /api/v1/payments/confirm
{
    "donation_id": "DN_12345678-1234-1234-1234-123456789012"
}
```

## 10. Migration

لتطبيق التغييرات:
```bash
php artisan migrate
```

## 11. Testing

لاختبار التسوية الدورية:
```bash
php artisan payments:reconcile --dry-run
```

## 12. Monitoring

- مراقبة logs للتأكد من عمل التسوية الدورية
- مراقبة حالة التبرعات المعلقة
- تتبع الأخطاء في معالجة الدفعات

---

**ملاحظة**: جميع التغييرات متوافقة مع الكود الموجود ولا تؤثر على الوظائف الحالية.
