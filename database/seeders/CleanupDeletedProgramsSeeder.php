<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class CleanupDeletedProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // حذف جميع البرامج المحذوفة نهائياً (soft delete)
        $deletedCount = Program::withTrashed()->whereNotNull('deleted_at')->count();
        
        if ($deletedCount > 0) {
            $this->command->info("Found {$deletedCount} deleted programs to permanently remove...");
            
            // حذف نهائي لجميع البرامج المحذوفة
            Program::withTrashed()->whereNotNull('deleted_at')->forceDelete();
            
            $this->command->info("Successfully permanently deleted {$deletedCount} programs!");
        } else {
            $this->command->info("No deleted programs found to clean up.");
        }
        
        // التحقق من البرامج المتبقية
        $activeCount = Program::count();
        $this->command->info("Active programs remaining: {$activeCount}");
        
        if ($activeCount == 4) {
            $this->command->info("✅ Perfect! Only 4 support programs remain.");
        } else {
            $this->command->warn("⚠️ Warning: Expected 4 programs, but found {$activeCount}");
        }
    }
}
