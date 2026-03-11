<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketsPerTicketExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithStyles, WithEvents
{
    use Exportable;

    /**
     * @param  array<int>|null  $ids  ID tiket yang mau diexport; null = semua.
     */
    public function __construct(
        private readonly ?array $ids = null
    ) {
    }

    public function collection()
    {
        $query = Ticket::query()
            ->orderBy('waktu_catat', 'desc')
            ->orderBy('id', 'desc');

        if ($this->ids !== null && $this->ids !== []) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Ticket ID',
            'Judul Sinyal',
            'Ringkasan',
            'Level Sinyal',
            'Urgensi Sinyal',
            'Kategori',
            'Status Tiket',
            'Status Eskalasi',
            'Nama Pelapor',
            'Nama Lokasi',
            'Nama Program',
            'Waktu Catat',
            'Skor Urgensi Hukum',
            'Skor Urgensi Tertinggi',
            'Jenis Sumber',
            'Rekomendasi AI',
            'Risiko Teridentifikasi',
        ];
    }

    /**
     * @param  Ticket  $ticket
     */
    public function map($ticket): array
    {
        return [
            $ticket->ticket_id,
            $ticket->judul_sinyal,
            $ticket->ringkasan_sinyal,
            $ticket->level_sinyal,
            $ticket->urgensi_sinyal,
            $ticket->kategori_sinyal,
            $ticket->status_ticket,
            $ticket->status_eskalasi,
            $ticket->nama_pelapor,
            $ticket->nama_lokasi,
            $ticket->nama_program,
            $ticket->waktu_catat?->format('Y-m-d H:i'),
            $ticket->skor_urgensi_hukum,
            $ticket->skor_urgensi_tertinggi,
            $ticket->jenis_sumber,
            $ticket->rekomendasi_ai,
            $ticket->risiko_teridentifikasi,
        ];
    }

    public function title(): string
    {
        return 'Per Tiket';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 28,
            'C' => 36,
            'D' => 10,
            'E' => 12,
            'F' => 18,
            'G' => 12,
            'H' => 14,
            'I' => 18,
            'J' => 18,
            'K' => 18,
            'L' => 16,
            'M' => 10,
            'N' => 10,
            'O' => 12,
            'P' => 32,
            'Q' => 32,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $headerRange = 'A1:' . $highestColumn . '1';
        $dataRange = 'A2:' . $highestColumn . $highestRow;

        return [
            $headerRange => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '333333'],
                    ],
                ],
            ],
            $dataRange => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [$this, 'afterSheet'],
        ];
    }

    public function afterSheet(AfterSheet $event): void
    {
        $sheet = $event->getSheet()->getDelegate();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Baris berselang-selang untuk data (baris 2 sampai akhir)
        for ($row = 2; $row <= $highestRow; $row++) {
            $fillColor = ($row % 2 === 0) ? 'FFFFFF' : 'F9F9F9';
            $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB($fillColor);
        }
    }
}
