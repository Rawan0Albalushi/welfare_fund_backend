# Flutter Donations Service - معالجة محسنة للأخطاء

## 📱 خدمة التبرعات المحسنة

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class DonationsService {
  static const String baseUrl = 'http://192.168.1.21:8000/api/v1';
  
  // الحصول على التوكن من التخزين المحلي
  Future<String?> _getAuthToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  // معالجة الأخطاء المحسنة
  Map<String, dynamic> _handleError(http.Response response) {
    try {
      final errorData = json.decode(response.body);
      return {
        'success': false,
        'error': errorData['message'] ?? 'حدث خطأ غير متوقع',
        'status_code': response.statusCode,
        'details': errorData
      };
    } catch (e) {
      return {
        'success': false,
        'error': 'خطأ في تحليل الاستجابة',
        'status_code': response.statusCode,
        'details': response.body
      };
    }
  }

  // الحصول على تبرعات المستخدم مع معالجة الأخطاء
  Future<Map<String, dynamic>> getUserDonations({
    String? status,
    String? type,
    int page = 1,
    int perPage = 10,
  }) async {
    try {
      final token = await _getAuthToken();
      
      if (token == null) {
        return {
          'success': false,
          'error': 'لم يتم العثور على رمز المصادقة',
          'status_code': 401
        };
      }

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
        final data = json.decode(response.body);
        return {
          'success': true,
          'data': data['data'],
          'stats': data['stats'],
          'meta': data['meta'],
          'message': data['message']
        };
      } else {
        return _handleError(response);
      }
    } catch (e) {
      return {
        'success': false,
        'error': 'خطأ في الاتصال: ${e.toString()}',
        'status_code': 0
      };
    }
  }

  // الحصول على تبرع محدد
  Future<Map<String, dynamic>> getDonationById(int id) async {
    try {
      final token = await _getAuthToken();
      
      if (token == null) {
        return {
          'success': false,
          'error': 'لم يتم العثور على رمز المصادقة',
          'status_code': 401
        };
      }

      final response = await http.get(
        Uri.parse('$baseUrl/me/donations/$id'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'data': data['data'],
          'message': data['message']
        };
      } else {
        return _handleError(response);
      }
    } catch (e) {
      return {
        'success': false,
        'error': 'خطأ في الاتصال: ${e.toString()}',
        'status_code': 0
      };
    }
  }

  // الحصول على التبرعات الحديثة (بدون مصادقة)
  Future<Map<String, dynamic>> getRecentDonations({int limit = 10}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/donations/recent?limit=$limit'),
        headers: {
          'Accept': 'application/json',
        },
      );
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'data': data['data'],
          'message': data['message']
        };
      } else {
        return _handleError(response);
      }
    } catch (e) {
      return {
        'success': false,
        'error': 'خطأ في الاتصال: ${e.toString()}',
        'status_code': 0
      };
    }
  }

  // إنشاء تبرع جديد
  Future<Map<String, dynamic>> createDonation({
    required int programId,
    required double amount,
    required String donorName,
    String? note,
    String type = 'quick',
  }) async {
    try {
      final token = await _getAuthToken();
      
      if (token == null) {
        return {
          'success': false,
          'error': 'لم يتم العثور على رمز المصادقة',
          'status_code': 401
        };
      }

      final response = await http.post(
        Uri.parse('$baseUrl/donations'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: json.encode({
          'program_id': programId,
          'amount': amount,
          'donor_name': donorName,
          'note': note,
          'type': type,
        }),
      );
      
      if (response.statusCode == 201) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'data': data['data'],
          'message': data['message']
        };
      } else {
        return _handleError(response);
      }
    } catch (e) {
      return {
        'success': false,
        'error': 'خطأ في الاتصال: ${e.toString()}',
        'status_code': 0
      };
    }
  }
}
```

## 🎯 استخدام الخدمة في الواجهة

```dart
class DonationsPage extends StatefulWidget {
  @override
  _DonationsPageState createState() => _DonationsPageState();
}

class _DonationsPageState extends State<DonationsPage> {
  final DonationsService _donationsService = DonationsService();
  List<dynamic> donations = [];
  Map<String, dynamic>? stats;
  bool isLoading = true;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _loadDonations();
  }

  Future<void> _loadDonations() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    final result = await _donationsService.getUserDonations();
    
    if (result['success']) {
      setState(() {
        donations = result['data'] ?? [];
        stats = result['stats'];
        isLoading = false;
      });
    } else {
      setState(() {
        errorMessage = result['error'];
        isLoading = false;
      });
      
      // عرض رسالة خطأ للمستخدم
      _showErrorDialog(result['error']);
    }
  }

  void _showErrorDialog(String error) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('خطأ'),
        content: Text(error),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              _loadDonations(); // إعادة المحاولة
            },
            child: Text('إعادة المحاولة'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('إغلاق'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Scaffold(
        appBar: AppBar(title: Text('تبرعاتي')),
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (errorMessage != null) {
      return Scaffold(
        appBar: AppBar(title: Text('تبرعاتي')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error, size: 64, color: Colors.red),
              SizedBox(height: 16),
              Text(
                errorMessage!,
                style: TextStyle(fontSize: 16),
                textAlign: TextAlign.center,
              ),
              SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loadDonations,
                child: Text('إعادة المحاولة'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('تبرعاتي'),
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: _loadDonations,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadDonations,
        child: Column(
          children: [
            // عرض الإحصائيات
            if (stats != null) _buildStatsCard(),
            
            // عرض قائمة التبرعات
            Expanded(
              child: donations.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.favorite_border, size: 64, color: Colors.grey),
                          SizedBox(height: 16),
                          Text(
                            'لا توجد تبرعات بعد',
                            style: TextStyle(fontSize: 18, color: Colors.grey),
                          ),
                          SizedBox(height: 8),
                          Text(
                            'ابدأ بالتبرع لرؤية تبرعاتك هنا',
                            style: TextStyle(color: Colors.grey),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      itemCount: donations.length,
                      itemBuilder: (context, index) {
                        final donation = donations[index];
                        return _buildDonationCard(donation);
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatsCard() {
    return Card(
      margin: EdgeInsets.all(16),
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'إحصائيات التبرعات',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatItem(
                  'إجمالي التبرعات',
                  '${stats!['total_donations']}',
                  Icons.favorite,
                ),
                _buildStatItem(
                  'المبلغ الإجمالي',
                  '${stats!['total_amount']} ريال',
                  Icons.attach_money,
                ),
                _buildStatItem(
                  'تبرعات مدفوعة',
                  '${stats!['paid_donations']}',
                  Icons.check_circle,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatItem(String label, String value, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: Colors.blue),
        SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
        Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.grey),
        ),
      ],
    );
  }

  Widget _buildDonationCard(Map<String, dynamic> donation) {
    return Card(
      margin: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: _getStatusColor(donation['status']),
          child: Icon(
            _getStatusIcon(donation['status']),
            color: Colors.white,
          ),
        ),
        title: Text(
          donation['donor_name'] ?? 'غير محدد',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('المبلغ: ${donation['amount']} ريال'),
            if (donation['program'] != null)
              Text('البرنامج: ${donation['program']['title']}'),
            Text(
              'الحالة: ${_getStatusText(donation['status'])}',
              style: TextStyle(
                color: _getStatusColor(donation['status']),
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
        trailing: Text(
          _formatDate(donation['created_at']),
          style: TextStyle(fontSize: 12, color: Colors.grey),
        ),
        onTap: () => _showDonationDetails(donation),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'paid':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'failed':
        return Colors.red;
      case 'expired':
        return Colors.grey;
      default:
        return Colors.blue;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'paid':
        return Icons.check_circle;
      case 'pending':
        return Icons.access_time;
      case 'failed':
        return Icons.error;
      case 'expired':
        return Icons.schedule;
      default:
        return Icons.help;
    }
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'paid':
        return 'مدفوع';
      case 'pending':
        return 'في الانتظار';
      case 'failed':
        return 'فشل';
      case 'expired':
        return 'منتهي الصلاحية';
      default:
        return 'غير محدد';
    }
  }

  String _formatDate(String? dateString) {
    if (dateString == null) return '';
    try {
      final date = DateTime.parse(dateString);
      return '${date.day}/${date.month}/${date.year}';
    } catch (e) {
      return '';
    }
  }

  void _showDonationDetails(Map<String, dynamic> donation) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('تفاصيل التبرع'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('المبلغ: ${donation['amount']} ريال'),
            Text('اسم المتبرع: ${donation['donor_name']}'),
            Text('النوع: ${donation['type']}'),
            Text('الحالة: ${_getStatusText(donation['status'])}'),
            if (donation['program'] != null)
              Text('البرنامج: ${donation['program']['title']}'),
            if (donation['note'] != null)
              Text('الملاحظة: ${donation['note']}'),
            Text('تاريخ الإنشاء: ${_formatDate(donation['created_at'])}'),
            if (donation['paid_at'] != null)
              Text('تاريخ الدفع: ${_formatDate(donation['paid_at'])}'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('إغلاق'),
          ),
        ],
      ),
    );
  }
}
```

## 🔄 التحديث التلقائي

```dart
class _DonationsPageState extends State<DonationsPage> {
  Timer? _refreshTimer;
  
  @override
  void initState() {
    super.initState();
    _loadDonations();
    _startAutoRefresh();
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  void _startAutoRefresh() {
    // تحديث كل 30 ثانية
    _refreshTimer = Timer.periodic(Duration(seconds: 30), (timer) {
      if (mounted) {
        _loadDonations();
      }
    });
  }

  // إيقاف التحديث التلقائي عند إنشاء تبرع جديد
  void _onDonationCreated() {
    _refreshTimer?.cancel();
    _loadDonations();
    _startAutoRefresh();
  }
}
```

## ⚠️ معالجة الأخطاء الشائعة

```dart
class ErrorHandler {
  static String getErrorMessage(Map<String, dynamic> error) {
    final statusCode = error['status_code'];
    final errorMessage = error['error'];
    
    switch (statusCode) {
      case 401:
        return 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى';
      case 403:
        return 'ليس لديك صلاحية للوصول إلى هذه البيانات';
      case 404:
        return 'لم يتم العثور على البيانات المطلوبة';
      case 422:
        return 'البيانات المرسلة غير صحيحة';
      case 500:
        return 'خطأ في الخادم، يرجى المحاولة لاحقاً';
      default:
        return errorMessage ?? 'حدث خطأ غير متوقع';
    }
  }
}
```

## 🎯 المميزات المضافة

1. **معالجة شاملة للأخطاء** - مع رسائل واضحة للمستخدم
2. **إعادة المحاولة التلقائية** - عند فشل الطلبات
3. **عرض الإحصائيات** - إجمالي التبرعات والمبالغ
4. **التحديث التلقائي** - كل 30 ثانية
5. **واجهة مستخدم محسنة** - مع أيقونات وألوان للحالات
6. **تفاصيل التبرع** - عرض كامل لمعلومات التبرع
7. **التحديث اليدوي** - مع RefreshIndicator
8. **معالجة الحالات الفارغة** - رسائل واضحة عند عدم وجود تبرعات
