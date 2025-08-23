# 🔧 إعداد قاعدة البيانات MySQL

## 📋 **المطلوب منك:**

### **1. تحديث ملف .env:**

أضف هذه الأسطر في ملف `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=welfare_fund
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

**استبدل `your_mysql_password` بكلمة مرور MySQL الخاصة بك**

### **2. مسح الكاش:**

```bash
php artisan config:clear
```

### **3. تشغيل المايجريشن:**

```bash
php artisan migrate:fresh --seed
```

### **4. اختبار الاتصال:**

```bash
php artisan tinker --execute="echo 'Database: ' . DB::connection()->getDatabaseName();"
```

## 🎯 **النتيجة المتوقعة:**

بعد تنفيذ هذه الخطوات، ستجد في phpMyAdmin:

### **جدول التبرعات:**
- 6 تبرعات (من الاختبارات السابقة)
- جميع البيانات محفوظة

### **جدول الحملات:**
- 8 حملات (من الـ seeders)

### **جدول البرامج:**
- برامج الدعم الطلابي

## ⚠️ **ملاحظة مهمة:**
البيانات الحالية في SQLite ستُفقد، لكن الـ seeders سيعيد إنشاء جميع البيانات في MySQL.

## 🚀 **البدء:**
