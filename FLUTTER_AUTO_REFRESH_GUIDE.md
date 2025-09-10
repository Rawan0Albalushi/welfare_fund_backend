# دليل التحديث التلقائي في Flutter

## 🔄 التحديث التلقائي بعد إنشاء تبرع جديد

### 1. استخدام StreamController للاتصال بين الصفحات

```dart
// في ملف منفصل: donation_stream.dart
import 'dart:async';

class DonationStream {
  static final DonationStream _instance = DonationStream._internal();
  factory DonationStream() => _instance;
  DonationStream._internal();

  final StreamController<Map<String, dynamic>> _donationController = 
      StreamController<Map<String, dynamic>>.broadcast();

  Stream<Map<String, dynamic>> get donationStream => _donationController.stream;

  void notifyDonationCreated(Map<String, dynamic> donation) {
    _donationController.add({
      'type': 'created',
      'donation': donation,
    });
  }

  void notifyDonationUpdated(Map<String, dynamic> donation) {
    _donationController.add({
      'type': 'updated',
      'donation': donation,
    });
  }

  void dispose() {
    _donationController.close();
  }
}
```

### 2. تحديث صفحة التبرعات للاستماع للتحديثات

```dart
class _DonationsPageState extends State<DonationsPage> {
  final DonationsService _donationsService = DonationsService();
  final DonationStream _donationStream = DonationStream();
  StreamSubscription? _donationSubscription;
  
  List<dynamic> donations = [];
  bool isLoading = true;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _loadDonations();
    _listenToDonationUpdates();
  }

  @override
  void dispose() {
    _donationSubscription?.cancel();
    super.dispose();
  }

  void _listenToDonationUpdates() {
    _donationSubscription = _donationStream.donationStream.listen((event) {
      if (event['type'] == 'created') {
        _onDonationCreated(event['donation']);
      } else if (event['type'] == 'updated') {
        _onDonationUpdated(event['donation']);
      }
    });
  }

  void _onDonationCreated(Map<String, dynamic> newDonation) {
    setState(() {
      donations.insert(0, newDonation); // إضافة في المقدمة
    });
    
    // عرض رسالة نجاح
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('تم إنشاء التبرع بنجاح!'),
        backgroundColor: Colors.green,
        duration: Duration(seconds: 3),
      ),
    );
  }

  void _onDonationUpdated(Map<String, dynamic> updatedDonation) {
    setState(() {
      final index = donations.indexWhere(
        (donation) => donation['id'] == updatedDonation['id']
      );
      if (index != -1) {
        donations[index] = updatedDonation;
      }
    });
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
        isLoading = false;
      });
    } else {
      setState(() {
        errorMessage = result['error'];
        isLoading = false;
      });
    }
  }
}
```

### 3. تحديث صفحة إنشاء التبرع

```dart
class CreateDonationPage extends StatefulWidget {
  @override
  _CreateDonationPageState createState() => _CreateDonationPageState();
}

class _CreateDonationPageState extends State<CreateDonationPage> {
  final DonationsService _donationsService = DonationsService();
  final DonationStream _donationStream = DonationStream();
  
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  final _nameController = TextEditingController();
  final _noteController = TextEditingController();
  
  int? _selectedProgramId;
  bool _isCreating = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('إنشاء تبرع جديد'),
      ),
      body: Form(
        key: _formKey,
        child: Padding(
          padding: EdgeInsets.all(16),
          child: Column(
            children: [
              // حقول النموذج
              TextFormField(
                controller: _amountController,
                decoration: InputDecoration(
                  labelText: 'المبلغ (ريال)',
                  border: OutlineInputBorder(),
                ),
                keyboardType: TextInputType.number,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'يرجى إدخال المبلغ';
                  }
                  if (double.tryParse(value) == null) {
                    return 'يرجى إدخال مبلغ صحيح';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              
              TextFormField(
                controller: _nameController,
                decoration: InputDecoration(
                  labelText: 'اسم المتبرع',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'يرجى إدخال اسم المتبرع';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              
              TextFormField(
                controller: _noteController,
                decoration: InputDecoration(
                  labelText: 'ملاحظة (اختياري)',
                  border: OutlineInputBorder(),
                ),
                maxLines: 3,
              ),
              SizedBox(height: 24),
              
              // زر إنشاء التبرع
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isCreating ? null : _createDonation,
                  child: _isCreating
                      ? CircularProgressIndicator(color: Colors.white)
                      : Text('إنشاء التبرع'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _createDonation() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isCreating = true;
    });

    try {
      final result = await _donationsService.createDonation(
        programId: _selectedProgramId ?? 1, // افتراضي
        amount: double.parse(_amountController.text),
        donorName: _nameController.text,
        note: _noteController.text.isNotEmpty ? _noteController.text : null,
      );

      if (result['success']) {
        // إشعار صفحة التبرعات بالتحديث
        _donationStream.notifyDonationCreated(result['data']);
        
        // العودة للصفحة السابقة
        Navigator.of(context).pop();
        
        // عرض رسالة نجاح
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('تم إنشاء التبرع بنجاح!'),
            backgroundColor: Colors.green,
          ),
        );
      } else {
        // عرض رسالة خطأ
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['error']),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('حدث خطأ: ${e.toString()}'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isCreating = false;
      });
    }
  }
}
```

### 4. استخدام Timer للتحديث الدوري

```dart
class _DonationsPageState extends State<DonationsPage> {
  Timer? _refreshTimer;
  DateTime? _lastRefresh;
  
  @override
  void initState() {
    super.initState();
    _loadDonations();
    _startPeriodicRefresh();
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  void _startPeriodicRefresh() {
    // تحديث كل دقيقة
    _refreshTimer = Timer.periodic(Duration(minutes: 1), (timer) {
      if (mounted) {
        _refreshDonations();
      }
    });
  }

  Future<void> _refreshDonations() async {
    final result = await _donationsService.getUserDonations();
    
    if (result['success']) {
      final newDonations = result['data'] ?? [];
      
      // التحقق من وجود تبرعات جديدة
      if (_hasNewDonations(newDonations)) {
        setState(() {
          donations = newDonations;
        });
        
        // عرض إشعار بوجود تبرعات جديدة
        _showNewDonationsNotification();
      }
    }
  }

  bool _hasNewDonations(List<dynamic> newDonations) {
    if (donations.isEmpty) return false;
    
    final lastDonationId = donations.first['id'];
    final newLastDonationId = newDonations.first['id'];
    
    return newLastDonationId != lastDonationId;
  }

  void _showNewDonationsNotification() {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('تم تحديث التبرعات'),
        backgroundColor: Colors.blue,
        duration: Duration(seconds: 2),
        action: SnackBarAction(
          label: 'تحديث الآن',
          onPressed: _loadDonations,
        ),
      ),
    );
  }
}
```

### 5. استخدام WebSocket للتحديث الفوري (اختياري)

```dart
// في ملف منفصل: websocket_service.dart
import 'package:web_socket_channel/web_socket_channel.dart';

class WebSocketService {
  static final WebSocketService _instance = WebSocketService._internal();
  factory WebSocketService() => _instance;
  WebSocketService._internal();

  WebSocketChannel? _channel;
  final StreamController<Map<String, dynamic>> _messageController = 
      StreamController<Map<String, dynamic>>.broadcast();

  Stream<Map<String, dynamic>> get messageStream => _messageController.stream;

  void connect(String token) {
    try {
      _channel = WebSocketChannel.connect(
        Uri.parse('ws://localhost:8000/ws?token=$token'),
      );

      _channel!.stream.listen(
        (data) {
          final message = json.decode(data);
          _messageController.add(message);
        },
        onError: (error) {
          print('WebSocket error: $error');
        },
        onDone: () {
          print('WebSocket connection closed');
        },
      );
    } catch (e) {
      print('Failed to connect to WebSocket: $e');
    }
  }

  void disconnect() {
    _channel?.sink.close();
    _channel = null;
  }

  void dispose() {
    disconnect();
    _messageController.close();
  }
}
```

### 6. استخدام WebSocket في صفحة التبرعات

```dart
class _DonationsPageState extends State<DonationsPage> {
  final WebSocketService _webSocketService = WebSocketService();
  StreamSubscription? _webSocketSubscription;
  
  @override
  void initState() {
    super.initState();
    _loadDonations();
    _connectWebSocket();
  }

  @override
  void dispose() {
    _webSocketSubscription?.cancel();
    super.dispose();
  }

  void _connectWebSocket() async {
    final token = await _getAuthToken();
    if (token != null) {
      _webSocketService.connect(token);
      
      _webSocketSubscription = _webSocketService.messageStream.listen((message) {
        if (message['type'] == 'donation_created') {
          _onDonationCreated(message['data']);
        } else if (message['type'] == 'donation_updated') {
          _onDonationUpdated(message['data']);
        }
      });
    }
  }

  void _onDonationCreated(Map<String, dynamic> newDonation) {
    setState(() {
      donations.insert(0, newDonation);
    });
    
    // عرض إشعار
    _showNotification('تبرع جديد', 'تم إنشاء تبرع جديد');
  }

  void _showNotification(String title, String body) {
    // يمكن استخدام flutter_local_notifications هنا
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$title: $body'),
        backgroundColor: Colors.green,
      ),
    );
  }
}
```

## 🎯 المميزات المضافة

1. **التحديث الفوري** - عند إنشاء تبرع جديد
2. **التحديث الدوري** - كل دقيقة للتحقق من التحديثات
3. **WebSocket** - للتحديث الفوري (اختياري)
4. **إشعارات المستخدم** - عند وجود تحديثات جديدة
5. **معالجة الأخطاء** - في الاتصال والتحديث
6. **تحسين الأداء** - تحديث ذكي بدون إعادة تحميل كامل

## ⚠️ ملاحظات مهمة

1. **استهلاك البطارية** - التحديث الدوري يستهلك البطارية
2. **استهلاك البيانات** - WebSocket يستهلك بيانات أقل من HTTP
3. **الأمان** - تأكد من تشفير WebSocket
4. **الاستقرار** - أضف إعادة الاتصال التلقائي
5. **الاختبار** - اختبر في ظروف شبكة ضعيفة
