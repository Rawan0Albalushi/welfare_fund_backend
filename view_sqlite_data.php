<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Donation;

echo "=== بيانات SQLite ===\n";
echo "قاعدة البيانات: database.sqlite\n\n";

// عرض التبرعات
echo "=== جدول التبرعات ===\n";
$donations = Donation::orderBy('id', 'desc')->get();

if ($donations->count() > 0) {
    echo "إجمالي التبرعات: " . $donations->count() . "\n\n";
    
    foreach ($donations as $donation) {
        echo "التبرع #{$donation->id}\n";
        echo "Donation ID: {$donation->donation_id}\n";
        echo "المبلغ: {$donation->amount} OMR\n";
        echo "المتبرع: {$donation->donor_name}\n";
        echo "الحالة: {$donation->status}\n";
        echo "النوع: {$donation->type}\n";
        echo "الحملة: {$donation->campaign_id}\n";
        echo "البرنامج: {$donation->program_id}\n";
        echo "تاريخ الإنشاء: {$donation->created_at}\n";
        echo "تاريخ الانتهاء: {$donation->expires_at}\n";
        echo "---\n";
    }
} else {
    echo "❌ لا توجد تبرعات!\n";
}

// عرض الحملات
echo "\n=== جدول الحملات ===\n";
try {
    $campaigns = DB::table('campaigns')->get();
    echo "إجمالي الحملات: " . $campaigns->count() . "\n\n";
    
    foreach ($campaigns as $campaign) {
        echo "الحملة #{$campaign->id}\n";
        echo "العنوان: {$campaign->title}\n";
        echo "المبلغ المطلوب: {$campaign->goal_amount} OMR\n";
        echo "المبلغ المجمع: {$campaign->raised_amount} OMR\n";
        echo "الحالة: {$campaign->status}\n";
        echo "---\n";
    }
} catch(Exception $e) {
    echo "❌ خطأ في قراءة الحملات: " . $e->getMessage() . "\n";
}

// عرض البرامج
echo "\n=== جدول البرامج ===\n";
try {
    $programs = DB::table('programs')->get();
    echo "إجمالي البرامج: " . $programs->count() . "\n\n";
    
    foreach ($programs as $program) {
        echo "البرنامج #{$program->id}\n";
        echo "العنوان: {$program->title}\n";
        echo "الحالة: {$program->status}\n";
        echo "---\n";
    }
} catch(Exception $e) {
    echo "❌ خطأ في قراءة البرامج: " . $e->getMessage() . "\n";
}
