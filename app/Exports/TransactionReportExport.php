<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Collection $orders;
    protected $paymentFormatter;
    protected int $rowNumber = 0;

    public function __construct(Collection $orders, ?callable $paymentFormatter = null)
    {
        $this->orders = $orders;
        $this->paymentFormatter = $paymentFormatter;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->orders;
    }

    /**
     * Header kolom di file Excel.
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal Transaksi',
            'Kode Order',
            'Nama Customer',
            'Biaya Sewa (Rp)',
            'Denda (Rp)',
            'Kompensasi Kerusakan (Rp)',
            'Metode Pembayaran',
            'Total Bersih (Rp)',
            'Status Transaksi',
        ];
    }

    /**
     * Map setiap record order ke format baris Excel.
     *
     * @param Order $order
     */
    public function map($order): array
    {
        $this->rowNumber++;

        $hasVerifiedPayment = $order->payments && $order->payments->where('status', 'diverifikasi')->isNotEmpty();
        $isSelesai = $order->status === 'selesai';

        $sewa = ($isSelesai && $hasVerifiedPayment) ? (float) $order->total_amount : 0;

        $denda = ($order->returnModel && $order->returnModel->late_fee_status === 'lunas')
            ? (float) $order->returnModel->late_fee_amount
            : 0;

        $kompensasi = (float) ($order->damageCases
            ? $order->damageCases->where('payment_status', 'lunas')->sum(fn ($dc) => (float) $dc->repair_cost + (float) $dc->fine_amount)
            : 0);

        $totalBersih = $sewa + $denda + $kompensasi;

        $paymentMethod = $this->paymentFormatter
            ? ($this->paymentFormatter)($order->latestPayment ?? $order->payments?->first())
            : '-';

        return [
            $this->rowNumber,
            $order->created_at ? $order->created_at->format('Y-m-d H:i') : '-',
            '#' . $order->order_number,
            $order->user?->name ?? 'Customer',
            $sewa,
            $denda,
            $kompensasi,
            $paymentMethod,
            $totalBersih,
            $order->status_label ?? $order->status,
        ];
    }

    /**
     * Styling untuk worksheet Excel.
     */
    public function styles(Worksheet $sheet): array
    {
        // Styling header baris pertama
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EA580C'], // Warna oranye RentalBase
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Format angka desimal / nominal rupiah untuk kolom E, F, G, I
        $highestRow = $sheet->getHighestRow();
        if ($highestRow > 1) {
            $sheet->getStyle('E2:E' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('F2:F' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G2:G' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('I2:I' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B2:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H2:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J2:J' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
