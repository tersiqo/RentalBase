<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi - {{ $client->name ?? 'RentalBase' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #ea580c;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header .store-title {
            font-size: 18px;
            font-weight: bold;
            color: #ea580c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .header .meta-info {
            font-size: 9px;
            color: #64748b;
            text-align: right;
        }
        .meta-info strong {
            color: #334155;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 6px;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: left;
        }
        .summary-card.highlight {
            background-color: #fff7ed;
            border: 1px solid #fdba74;
        }
        .summary-card .label {
            font-size: 8px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-card.highlight .label {
            color: #c2410c;
        }
        .summary-card .value {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        .summary-card.highlight .value {
            color: #ea580c;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .table-data th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #cbd5e1;
            padding: 7px 6px;
        }
        .table-data td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            font-size: 9px;
            color: #334155;
        }
        .table-data tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-info { background-color: #e0f2fe; color: #075985; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .footer-note {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-size: 8px;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-note .left {
            display: table-cell;
            text-align: left;
        }
        .footer-note .right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- HEADER DOKUMEN --}}
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="store-title">{{ $client->name ?? 'RentalBase Store' }}</div>
                    <div class="doc-title">LAPORAN TRANSAKSI & PENDAPATAN OPERASIONAL</div>
                    <div style="font-size: 9px; color: #64748b; margin-top: 3px;">
                        Periode Laporan: <strong style="color: #ea580c;">{{ $activePeriodText }}</strong> (Preset: {{ $presetLabel }})
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top;" class="meta-info">
                    <div>Dicetak Pada: <strong>{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</strong></div>
                    <div>Oleh: <strong>{{ $user->name ?? 'Administrator Toko' }}</strong></div>
                    <div>Total Transaksi: <strong>{{ count($orders) }} Pesanan</strong></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- RINGKASAN METRIK FINANSIAL --}}
    <table class="summary-box">
        <tr>
            <td class="summary-card highlight" style="width: 26%;">
                <div class="label">Total Pendapatan Bersih</div>
                <div class="value">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 20%;">
                <div class="label">Sewa Pokok</div>
                <div class="value">Rp {{ number_format($pendapatanSewaPokok, 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 18%;">
                <div class="label">Pendapatan Denda</div>
                <div class="value">Rp {{ number_format($pendapatanDenda, 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 18%;">
                <div class="label">Kompensasi Kerusakan</div>
                <div class="value">Rp {{ number_format($kompensasiKerusakan, 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 18%;">
                <div class="label">Transaksi Selesai</div>
                <div class="value">{{ number_format($totalTransaksiSelesai) }} Order</div>
            </td>
        </tr>
    </table>

    {{-- TABEL DATA TRANSAKSI --}}
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 12%;" class="text-left">Tgl Transaksi</th>
                <th style="width: 12%;" class="text-left">Kode Order</th>
                <th style="width: 16%;" class="text-left">Nama Customer</th>
                <th style="width: 11%;" class="text-right">Biaya Sewa</th>
                <th style="width: 10%;" class="text-right">Denda</th>
                <th style="width: 11%;" class="text-right">Kompensasi</th>
                <th style="width: 10%;" class="text-center">Metode Bayar</th>
                <th style="width: 12%;" class="text-right">Total Bersih</th>
                <th style="width: 12%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
                @php
                    $isSelesai = $order->status === 'selesai';
                    $hasVerifiedPayment = $order->payments->where('status', 'diverifikasi')->isNotEmpty();
                    $sewa = ($isSelesai && $hasVerifiedPayment) ? (float) $order->total_amount : 0;

                    $denda = ($order->returnModel && $order->returnModel->late_fee_status === 'lunas')
                        ? (float) $order->returnModel->late_fee_amount
                        : 0;

                    $kompensasi = (float) $order->damageCases
                        ->where('payment_status', 'lunas')
                        ->sum(fn ($dc) => (float) $dc->repair_cost + (float) $dc->fine_amount);

                    $totalBersih = $sewa + $denda + $kompensasi;
                    $metodeBayar = $formatPaymentMethod($order->latestPayment ?? $order->payments->first());
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-' }}</td>
                    <td class="font-bold">#{{ $order->order_number }}</td>
                    <td>{{ $order->user?->name ?? 'Customer' }}</td>
                    <td class="text-right">
                        @if($sewa > 0)
                            Rp {{ number_format($sewa, 0, ',', '.') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($denda > 0)
                            <span style="color: #d97706; font-weight: bold;">+Rp {{ number_format($denda, 0, ',', '.') }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($kompensasi > 0)
                            <span style="color: #e11d48; font-weight: bold;">+Rp {{ number_format($kompensasi, 0, ',', '.') }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $metodeBayar }}</td>
                    <td class="text-right font-bold" style="color: #ea580c;">
                        Rp {{ number_format($totalBersih, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $order->status === 'selesai' ? 'badge-success' : ($order->status === 'disewa' ? 'badge-info' : 'badge-warning') }}">
                            {{ $order->status_label ?? $order->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada transaksi ditemukan pada periode {{ $activePeriodText }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($orders) > 0)
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #cbd5e1;">
                    <td colspan="4" class="text-right" style="padding: 8px;">TOTAL REKAPITULASI:</td>
                    <td class="text-right" style="padding: 8px;">Rp {{ number_format($pendapatanSewaPokok, 0, ',', '.') }}</td>
                    <td class="text-right" style="padding: 8px;">Rp {{ number_format($pendapatanDenda, 0, ',', '.') }}</td>
                    <td class="text-right" style="padding: 8px;">Rp {{ number_format($kompensasiKerusakan, 0, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right" style="padding: 8px; color: #ea580c;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                    <td class="text-center">{{ count($orders) }} Trx</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- FOOTER CATATAN & LEGAL --}}
    <div class="footer-note">
        <div class="left">
            Dokumen ini dihasilkan secara otomatis oleh Sistem RentalBase Toko (Multi-tenant terisolasi).
        </div>
        <div class="right">
            RentalBase &bull; Laporan Operasional &amp; Finansial
        </div>
    </div>

</body>
</html>
