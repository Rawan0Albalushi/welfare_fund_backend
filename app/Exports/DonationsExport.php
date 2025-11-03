<?php

namespace App\Exports;

use App\Models\Donation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DonationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function collection()
    {
        $query = Donation::with(['user', 'program', 'campaign']);

        if (isset($this->params['from_date'])) {
            $query->whereDate('created_at', '>=', $this->params['from_date']);
        }
        if (isset($this->params['to_date'])) {
            $query->whereDate('created_at', '<=', $this->params['to_date']);
        }
        if (isset($this->params['status'])) {
            $query->where('status', $this->params['status']);
        }
        if (isset($this->params['type'])) {
            $query->where('type', $this->params['type']);
        }
        if (isset($this->params['program_id'])) {
            $query->where('program_id', $this->params['program_id']);
        }
        if (isset($this->params['campaign_id'])) {
            $query->where('campaign_id', $this->params['campaign_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'رقم التبرع',
            'معرف التبرع',
            'اسم المتبرع',
            'المبلغ (ريال)',
            'النوع',
            'الحالة',
            'البرنامج',
            'الحملة',
            'المستخدم',
            'الهاتف',
            'ملاحظات',
            'تاريخ الإنشاء',
            'تاريخ الدفع',
        ];
    }

    public function map($donation): array
    {
        return [
            $donation->id,
            $donation->donation_id,
            $donation->donor_name,
            number_format($donation->amount, 2),
            $donation->type === 'quick' ? 'سريع' : 'هدية',
            $this->getStatusInArabic($donation->status),
            $donation->program ? $donation->program->title_ar : '-',
            $donation->campaign ? $donation->campaign->title_ar : '-',
            $donation->user ? $donation->user->name : 'مجهول',
            $donation->user ? $donation->user->phone : '-',
            $donation->note ?? '-',
            $donation->created_at->format('Y-m-d H:i:s'),
            $donation->paid_at ? $donation->paid_at->format('Y-m-d H:i:s') : '-',
        ];
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
        return 'تقرير التبرعات';
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

