<?php

namespace App\Exports;

use App\Models\StudentRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ApplicationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function collection()
    {
        $query = StudentRegistration::with(['user', 'program']);

        if (isset($this->params['status'])) {
            $query->where('status', $this->params['status']);
        }
        if (isset($this->params['program_id'])) {
            $query->where('program_id', $this->params['program_id']);
        }
        if (isset($this->params['from_date'])) {
            $query->whereDate('created_at', '>=', $this->params['from_date']);
        }
        if (isset($this->params['to_date'])) {
            $query->whereDate('created_at', '<=', $this->params['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'رقم الطلب',
            'معرف الطلب',
            'اسم الطالب',
            'الهاتف',
            'البريد الإلكتروني',
            'البرنامج',
            'الحالة',
            'سبب الرفض',
            'تاريخ الإنشاء',
        ];
    }

    public function map($application): array
    {
        $personal = $application->personal_json ?? [];
        
        return [
            $application->id,
            $application->registration_id,
            $application->user ? $application->user->name : ($personal['name'] ?? '-'),
            $application->user ? $application->user->phone : ($personal['phone'] ?? '-'),
            $application->user ? $application->user->email : '-',
            $application->program ? $application->program->title_ar : '-',
            $this->getStatusInArabic($application->status),
            $application->reject_reason ?? '-',
            $application->created_at->format('Y-m-d H:i:s'),
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
        return 'تقرير الطلبات';
    }

    protected function getStatusInArabic($status)
    {
        $statuses = [
            'under_review' => 'قيد المراجعة',
            'accepted' => 'مقبول',
            'rejected' => 'مرفوض',
        ];

        return $statuses[$status] ?? $status;
    }
}

