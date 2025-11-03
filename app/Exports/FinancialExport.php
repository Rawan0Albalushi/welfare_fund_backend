<?php

namespace App\Exports;

use App\Models\Donation;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class FinancialExport implements WithMultipleSheets
{
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new FinancialSummarySheet($this->params);
        $sheets[] = new FinancialByStatusSheet($this->params);
        $sheets[] = new FinancialByTypeSheet($this->params);
        
        return $sheets;
    }
}

class FinancialSummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function collection()
    {
        $fromDate = $this->params['from_date'] ?? Carbon::now()->subMonth()->toDateString();
        $toDate = $this->params['to_date'] ?? Carbon::now()->toDateString();

        $totalDonations = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->sum('amount');
        $paidDonations = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->where('status', 'paid')->sum('amount');
        $pendingDonations = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->where('status', 'pending')->sum('amount');
        $donorsCount = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->where('status', 'paid')->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $averageDonation = Donation::whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate)->where('status', 'paid')->avg('amount');

        return collect([
            [
                'المؤشر' => 'إجمالي التبرعات',
                'القيمة' => number_format($totalDonations, 2) . ' ريال',
            ],
            [
                'المؤشر' => 'التبرعات المدفوعة',
                'القيمة' => number_format($paidDonations, 2) . ' ريال',
            ],
            [
                'المؤشر' => 'التبرعات المعلقة',
                'القيمة' => number_format($pendingDonations, 2) . ' ريال',
            ],
            [
                'المؤشر' => 'عدد المتبرعين',
                'القيمة' => $donorsCount,
            ],
            [
                'المؤشر' => 'متوسط التبرع',
                'القيمة' => number_format($averageDonation ?? 0, 2) . ' ريال',
            ],
        ]);
    }

    public function headings(): array
    {
        return ['المؤشر', 'القيمة'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'الملخص المالي';
    }
}

class FinancialByStatusSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function collection()
    {
        $fromDate = $this->params['from_date'] ?? Carbon::now()->subMonth()->toDateString();
        $toDate = $this->params['to_date'] ?? Carbon::now()->toDateString();

        return Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('status')
            ->get();
    }

    public function headings(): array
    {
        return ['الحالة', 'العدد', 'الإجمالي (ريال)'];
    }

    public function map($item): array
    {
        return [
            $this->getStatusInArabic($item->status),
            $item->count,
            number_format($item->total, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'حسب الحالة';
    }

    protected function getStatusInArabic($status)
    {
        $statuses = [
            'pending' => 'معلق',
            'paid' => 'مدفوع',
            'failed' => 'فاشل',
            'expired' => 'منتهي',
        ];

        return $statuses[$status] ?? $status;
    }
}

class FinancialByTypeSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function collection()
    {
        $fromDate = $this->params['from_date'] ?? Carbon::now()->subMonth()->toDateString();
        $toDate = $this->params['to_date'] ?? Carbon::now()->toDateString();

        return Donation::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('type')
            ->get();
    }

    public function headings(): array
    {
        return ['النوع', 'العدد', 'الإجمالي (ريال)'];
    }

    public function map($item): array
    {
        return [
            $item->type === 'quick' ? 'سريع' : 'هدية',
            $item->count,
            number_format($item->total, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'حسب النوع';
    }
}

