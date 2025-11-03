<?php

namespace App\Exports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProgramsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function collection()
    {
        return Program::withCount([
            'donations',
            'donations as paid_donations_count' => function ($query) {
                $query->where('status', 'paid');
            },
            'studentRegistrations',
        ])
        ->withSum([
            'donations as total_donations_amount' => function ($query) {
                $query->where('status', 'paid');
            }
        ], 'amount')
        ->get();
    }

    public function headings(): array
    {
        return [
            'رقم البرنامج',
            'العنوان (عربي)',
            'العنوان (إنجليزي)',
            'الحالة',
            'عدد التبرعات',
            'عدد التبرعات المدفوعة',
            'إجمالي المبلغ (ريال)',
            'عدد الطلبات',
        ];
    }

    public function map($program): array
    {
        return [
            $program->id,
            $program->title_ar,
            $program->title_en,
            $program->status === 'active' ? 'نشط' : 'غير نشط',
            $program->donations_count,
            $program->paid_donations_count,
            number_format($program->total_donations_amount ?? 0, 2),
            $program->student_registrations_count,
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
        return 'تقرير البرامج';
    }
}

