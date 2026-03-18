<?php

namespace App\Exports;

use App\Models\Pemesanan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PemesananExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        private readonly ?string $startDate,
        private readonly ?string $endDate,
    ) {
    }

    public function collection(): Collection
    {
        $query = Pemesanan::query()->with(['admin', 'kendaraan', 'driver', 'atasan1', 'atasan2']);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('tanggal_mulai', [$this->startDate, $this->endDate]);
        }

        return $query->latest()->get()->map(function (Pemesanan $pemesanan) {
            return [
                'ID' => $pemesanan->id,
                'Admin' => $pemesanan->admin->nama,
                'Kendaraan' => $pemesanan->kendaraan->nama,
                'Driver' => $pemesanan->driver->nama,
                'Atasan Level 1' => $pemesanan->atasan1->nama,
                'Atasan Level 2' => $pemesanan->atasan2->nama,
                'Tanggal Mulai' => Carbon::parse($pemesanan->tanggal_mulai)->format('d-m-Y H:i'),
                'Tanggal Selesai' => Carbon::parse($pemesanan->tanggal_selesai)->format('d-m-Y H:i'),
                'Status' => str_replace('_', ' ', ucfirst($pemesanan->status_pemesanan)),
                'Catatan' => $pemesanan->catatan ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Admin',
            'Kendaraan',
            'Driver',
            'Atasan Level 1',
            'Atasan Level 2',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
            'Catatan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E78'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:'.$highestColumn.'1');

                $sheet->getStyle('A1:'.$highestColumn.$highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color('FFD1D5DB'));

                $sheet->getStyle('A2:A'.$highestRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
