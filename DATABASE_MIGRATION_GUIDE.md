# 🔄 دليل التحويل من SQLite إلى MySQL

## 🎯 **الهدف:**
تحويل قاعدة البيانات من SQLite إلى MySQL لعرض البيانات في phpMyAdmin

## 📋 **الخطوات:**

### **1. إنشاء قاعدة بيانات MySQL:**

```sql
CREATE DATABASE student_welfare_fund;
```

### **2. تحديث ملف .env:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_welfare_fund
DB_USERNAME=root
DB_PASSWORD=your_password
```

### **3. تشغيل المايجريشن:**

```bash
php artisan migrate:fresh --seed
```

### **4. مسح الكاش:**

```bash
php artisan config:clear
php artisan cache:clear
```

## 🧪 **اختبار الاتصال:**

```bash
php artisan tinker --execute="echo 'Database: ' . DB::connection()->getDatabaseName();"
```

## 📊 **البيانات المتوقعة في MySQL:**

### **جدول التبرعات:**
- 6 تبرعات (من الاختبارات السابقة)
- جميع البيانات محفوظة

### **جدول الحملات:**
- 8 حملات (من الـ seeders)

### **جدول البرامج:**
- برامج الدعم الطلابي

## ⚠️ **ملاحظات مهمة:**

1. **البيانات الحالية في SQLite** ستُفقد عند التحويل
2. **المايجريشن الجديد** سينشئ الجداول في MySQL
3. **الـ seeders** سيعيد إنشاء البيانات

## 🚀 **البدء:**
