<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketsRekapExport implements FromArray, WithTitle, WithColumnWidths, WithStyles
{
    use Exportable;

    /** Baris untuk styling (diisi di array()). */
    private int $titleRow = 1;
    private int $summaryHeaderRow = 3;
    private int $summaryDataEndRow = 9;
    private int $levelHeaderRow = 11;
    private int $levelSubRow = 12;
    private int $levelDataEndRow = 12;
    private int $eskalasiHeaderRow = 0;
    private int $eskalasiSubRow = 0;
    private int $eskalasiDataEndRow = 0;
    private int $kategoriHeaderRow = 0;
    private int $kategoriSubRow = 0;
    private int $kategoriDataEndRow = 0;

    public function array(): array
    {
        $total = Ticket::count();
        $open = Ticket::where('status_ticket', 'Open')->count();
        $inProgress = Ticket::where('status_ticket', 'In Progress')->count();
        $resolved = Ticket::where('status_ticket', 'Resolved')->count();
        $closed = Ticket::where('status_ticket', 'Closed')->count();
        $avgRaw = $total > 0 ? Ticket::avg('skor_urgensi_hukum') : null;
        $avgScore = $avgRaw !== null ? round((float) $avgRaw, 1) : '—';

        $byLevel = Ticket::select('level_sinyal', DB::raw('count(*) as total'))
            ->whereNotNull('level_sinyal')
            ->where('level_sinyal', '!=', '')
            ->groupBy('level_sinyal')
            ->orderBy('level_sinyal')
            ->get();

        $byUrgency = Ticket::select('status_eskalasi', DB::raw('count(*) as total'))
            ->groupBy('status_eskalasi')
            ->get();

        $byCategory = Ticket::select('kategori_sinyal', DB::raw('count(*) as total'))
            ->whereNotNull('kategori_sinyal')
            ->where('kategori_sinyal', '!=', '')
            ->groupBy('kategori_sinyal')
            ->orderByDesc('total')
            ->get();

        $rows = [
            ['Rekap Tiket OZJ'],
            [],
            ['Ringkasan'],
            ['Total Tiket', $total],
            ['Open', $open],
            ['In Progress', $inProgress],
            ['Resolved', $resolved],
            ['Closed', $closed],
            ['Rata-rata Skor Urgensi Hukum', $avgScore],
            [],
            ['Level Sinyal (Mikro / Meso / Makro)'],
            ['Level', 'Jumlah'],
        ];

        foreach ($byLevel as $row) {
            $rows[] = [$row->level_sinyal, $row->total];
        }
        $this->levelDataEndRow = 12 + $byLevel->count();

        $rows[] = [];
        $rows[] = ['Eskalasi'];
        $this->eskalasiHeaderRow = $this->levelDataEndRow + 2;
        $rows[] = ['Status Eskalasi', 'Jumlah'];
        $this->eskalasiSubRow = $this->eskalasiHeaderRow + 1;
        foreach ($byUrgency as $row) {
            $rows[] = [$row->status_eskalasi ?? '—', $row->total];
        }
        $this->eskalasiDataEndRow = $this->eskalasiSubRow + $byUrgency->count();

        $rows[] = [];
        $rows[] = ['Kategori Sinyal'];
        $this->kategoriHeaderRow = $this->eskalasiDataEndRow + 2;
        $rows[] = ['Kategori', 'Jumlah'];
        $this->kategoriSubRow = $this->kategoriHeaderRow + 1;
        foreach ($byCategory as $row) {
            $rows[] = [$row->kategori_sinyal ?? '—', $row->total];
        }
        $this->kategoriDataEndRow = $this->kategoriSubRow + $byCategory->count();

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 38,
            'B' => 14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $borderThin = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];
        $headerFill = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0'],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sectionFill = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        $styles = [
            // Judul utama: teks putih di atas biru
            'A1' => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Ringkasan
            "A{$this->summaryHeaderRow}:B{$this->summaryHeaderRow}" => $sectionFill,
            "A4:B{$this->summaryDataEndRow}" => array_merge($borderThin, [
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => false],
            ]),
        ];

        // Level Sinyal
        $styles["A{$this->levelHeaderRow}:B{$this->levelHeaderRow}"] = $sectionFill;
        $styles["A{$this->levelSubRow}:B{$this->levelSubRow}"] = array_merge($headerFill, $borderThin);
        if ($this->levelDataEndRow >= 13) {
            $styles["A13:B{$this->levelDataEndRow}"] = $borderThin;
        }

        // Eskalasi
        if ($this->eskalasiHeaderRow > 0) {
            $styles["A{$this->eskalasiHeaderRow}:B{$this->eskalasiHeaderRow}"] = $sectionFill;
            $styles["A{$this->eskalasiSubRow}:B{$this->eskalasiSubRow}"] = array_merge($headerFill, $borderThin);
            if ($this->eskalasiDataEndRow > $this->eskalasiSubRow) {
                $styles["A" . ($this->eskalasiSubRow + 1) . ":B{$this->eskalasiDataEndRow}"] = $borderThin;
            }
        }

        // Kategori
        if ($this->kategoriHeaderRow > 0) {
            $styles["A{$this->kategoriHeaderRow}:B{$this->kategoriHeaderRow}"] = $sectionFill;
            $styles["A{$this->kategoriSubRow}:B{$this->kategoriSubRow}"] = array_merge($headerFill, $borderThin);
            if ($this->kategoriDataEndRow > $this->kategoriSubRow) {
                $styles["A" . ($this->kategoriSubRow + 1) . ":B{$this->kategoriDataEndRow}"] = $borderThin;
            }
        }

        return $styles;
    }

    public function title(): string
    {
        return 'Rekap';
    }
}
