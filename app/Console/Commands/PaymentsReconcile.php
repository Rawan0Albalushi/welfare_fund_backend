<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Services\ThawaniService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PaymentsReconcile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:reconcile {--dry-run : تشغيل تجريبي بدون تحديث قاعدة البيانات}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تسوية دورية لحالات الدفع المعلقة مع ثواني';

    protected ThawaniService $thawaniService;

    public function __construct(ThawaniService $thawaniService)
    {
        parent::__construct();
        $this->thawaniService = $thawaniService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 تشغيل تجريبي - لن يتم تحديث قاعدة البيانات');
        }

        $this->info('🔄 بدء التسوية الدورية للدفعات...');

        // البحث عن التبرعات المعلقة التي لها session_id وحديثة (أقل من 48 ساعة)
        $pendingDonations = Donation::where('status', 'pending')
            ->whereNotNull('payment_session_id')
            ->where('created_at', '>=', now()->subHours(48))
            ->get();

        $this->info("📊 تم العثور على {$pendingDonations->count()} تبرع معلق للمراجعة");

        $processed = 0;
        $updated = 0;
        $errors = 0;

        foreach ($pendingDonations as $donation) {
            $processed++;
            
            try {
                $this->line("🔍 معالجة التبرع: {$donation->donation_id} (Session: {$donation->payment_session_id})");
                
                // جلب تفاصيل الجلسة من ثواني
                $sessionDetails = $this->thawaniService->getSessionDetails($donation->payment_session_id);
                $paymentStatus = $sessionDetails['payment_status'] ?? null;
                
                if (!$paymentStatus) {
                    $this->warn("⚠️  لم يتم العثور على حالة الدفع للتبرع: {$donation->donation_id}");
                    continue;
                }

                // إذا كانت الحالة لا تزال pending، لا نحتاج لتحديث
                if ($paymentStatus === 'pending') {
                    $this->line("⏳ التبرع لا يزال في حالة انتظار: {$donation->donation_id}");
                    continue;
                }

                // إذا كان التبرع مدفوعاً بالفعل، لا نكرر التحديث (idempotent)
                if ($donation->status === 'paid') {
                    $this->line("✅ التبرع مدفوع بالفعل: {$donation->donation_id}");
                    continue;
                }

                $updateData = [
                    'payload' => $sessionDetails,
                ];

                if ($paymentStatus === 'paid') {
                    // تحويل المبلغ من بيسة إلى ريال عماني
                    $capturedAmount = $sessionDetails['captured_amount'] ?? $sessionDetails['total_amount'] ?? 0;
                    $paidAmount = $capturedAmount / 1000; // بيسة -> ريال
                    
                    $updateData = array_merge($updateData, [
                        'status' => 'paid',
                        'paid_amount' => $paidAmount,
                        'paid_at' => isset($sessionDetails['paid_at']) 
                            ? \Carbon\Carbon::parse($sessionDetails['paid_at'])->setTimezone(config('app.timezone'))
                            : now(),
                    ]);

                    $this->info("💰 تم دفع التبرع: {$donation->donation_id} - المبلغ: {$paidAmount} ريال");
                } else {
                    // تحديث الحالة حسب حالة الدفع
                    $statusMap = [
                        'canceled' => 'canceled',
                        'expired' => 'expired',
                        'failed' => 'failed',
                    ];
                    
                    $updateData['status'] = $statusMap[$paymentStatus] ?? 'failed';
                    $this->warn("❌ تغيير حالة التبرع: {$donation->donation_id} إلى {$updateData['status']}");
                }

                if (!$isDryRun) {
                    $donation->update($updateData);
                }

                $updated++;

            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ خطأ في معالجة التبرع {$donation->donation_id}: " . $e->getMessage());
                
                Log::error('PaymentsReconcile error', [
                    'donation_id' => $donation->donation_id,
                    'session_id' => $donation->payment_session_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("✅ انتهت التسوية الدورية:");
        $this->info("   📊 تم معالجة: {$processed} تبرع");
        $this->info("   🔄 تم تحديث: {$updated} تبرع");
        $this->info("   ❌ أخطاء: {$errors} تبرع");

        if ($isDryRun) {
            $this->info("🔍 كان هذا تشغيل تجريبي - لم يتم تحديث قاعدة البيانات");
        }

        return 0;
    }
}
