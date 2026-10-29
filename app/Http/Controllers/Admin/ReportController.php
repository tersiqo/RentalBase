<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\DamageCase;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ReturnModel;
use App\Models\User;
use App\Exports\TransactionReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman laporan transaksi admin dengan filter periode dan ringkasan finansial.
     */
    public function index(Request $request)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        // Validasi dan resolusi rentang tanggal filter
        $filter = $this->resolveDateRange($request);
        if ($filter['validator']->fails()) {
            return redirect()->route('admin.reports.index')
                ->withErrors($filter['validator'])
                ->withInput();
        }

        $startDate = $filter['start_date'];
        $endDate = $filter['end_date'];
        $preset = $filter['preset'];
        $startCarbon = $filter['start_carbon'];
        $endCarbon = $filter['end_carbon'];

        // Agregasi finansial langsung pada database yang terisolasi multi-tenant
        $summary = $this->getFinancialSummary($client->id, $startCarbon, $endCarbon);

        // Query tabel rincian transaksi dengan eager loading untuk mencegah N+1
        $orders = $this->getBaseOrdersQuery($client->id, $startCarbon, $endCarbon)
            ->with([
                'user',
                'returnModel',
                'damageCases',
                'damageReports.damageCase',
                'payments.clientPaymentMethod',
                'latestPayment.clientPaymentMethod',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', array_merge([
            'client' => $client,
            'user' => $user,
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'preset' => $preset,
            'presetLabel' => $filter['preset_label'],
            'activePeriodText' => $filter['active_period_text'],
            'todayStr' => $filter['today_str'],
            'sevenDaysAgoStr' => $filter['seven_days_ago_str'],
            'startOfMonthStr' => $filter['start_of_month_str'],
            'endOfMonthStr' => $filter['end_of_month_str'],
            'formatPaymentMethod' => fn (?Payment $p) => $this->formatPaymentMethod($p),
        ], $summary));
    }

    /**
     * Handler utama ekspor laporan transaksi (CSV, Excel, PDF) tanpa preview dokumen.
     */
    public function export(Request $request)
    {
        $format = strtolower($request->query('format', 'csv'));

        if ($format === 'excel' || $format === 'xlsx') {
            return $this->exportExcel($request);
        }

        if ($format === 'pdf') {
            return $this->exportPdf($request);
        }

        return $this->exportCsv($request);
    }

    /**
     * Ekspor laporan transaksi ke format Excel (.xlsx) menggunakan maatwebsite/excel (langsung unduh).
     */
    public function exportExcel(Request $request)
    {
        $client = $this->resolveClient();

        $filter = $this->resolveDateRange($request);
        if ($filter['validator']->fails()) {
            return redirect()->route('admin.reports.index')
                ->withErrors($filter['validator'])
                ->withInput();
        }

        $startDate = $filter['start_date'];
        $endDate = $filter['end_date'];
        $startCarbon = $filter['start_carbon'];
        $endCarbon = $filter['end_carbon'];

        $orders = $this->getBaseOrdersQuery($client->id, $startCarbon, $endCarbon)
            ->with([
                'user',
                'returnModel',
                'damageCases',
                'damageReports.damageCase',
                'payments.clientPaymentMethod',
                'latestPayment.clientPaymentMethod',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = sprintf('laporan-transaksi-%s-%s.xlsx', $startDate, $endDate);

        return Excel::download(
            new TransactionReportExport($orders, fn (?Payment $p) => $this->formatPaymentMethod($p)),
            $filename
        );
    }

    /**
     * Ekspor laporan transaksi ke format PDF menggunakan barryvdh/laravel-dompdf (langsung unduh tanpa preview).
     */
    public function exportPdf(Request $request)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        $filter = $this->resolveDateRange($request);
        if ($filter['validator']->fails()) {
            return redirect()->route('admin.reports.index')
                ->withErrors($filter['validator'])
                ->withInput();
        }

        $startDate = $filter['start_date'];
        $endDate = $filter['end_date'];
        $preset = $filter['preset'];
        $startCarbon = $filter['start_carbon'];
        $endCarbon = $filter['end_carbon'];

        $summary = $this->getFinancialSummary($client->id, $startCarbon, $endCarbon);

        $orders = $this->getBaseOrdersQuery($client->id, $startCarbon, $endCarbon)
            ->with([
                'user',
                'returnModel',
                'damageCases',
                'damageReports.damageCase',
                'payments.clientPaymentMethod',
                'latestPayment.clientPaymentMethod',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('admin.reports.print', array_merge([
            'client' => $client,
            'user' => $user,
            'orders' => $orders,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'preset' => $preset,
            'presetLabel' => $filter['preset_label'],
            'activePeriodText' => $filter['active_period_text'],
            'formatPaymentMethod' => fn (?Payment $p) => $this->formatPaymentMethod($p),
        ], $summary));

        $pdf->setPaper('a4', 'landscape');

        $filename = sprintf('laporan-transaksi-%s-%s.pdf', $startDate, $endDate);

        // download() mengembalikan response attachment sehingga langsung terunduh tanpa preview
        return $pdf->download($filename);
    }

    /**
     * Ekspor laporan transaksi ke format CSV menggunakan response streaming.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $client = $this->resolveClient();

        // Validasi dan resolusi tanggal
        $filter = $this->resolveDateRange($request);
        if ($filter['validator']->fails()) {
            return redirect()->route('admin.reports.index')
                ->withErrors($filter['validator'])
                ->withInput();
        }

        $startDate = $filter['start_date'];
        $endDate = $filter['end_date'];
        $startCarbon = $filter['start_carbon'];
        $endCarbon = $filter['end_carbon'];

        $filename = sprintf('laporan-transaksi-%s-%s.csv', $startDate, $endDate);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $baseQuery = $this->getBaseOrdersQuery($client->id, $startCarbon, $endCarbon);

        return response()->streamDownload(function () use ($baseQuery) {
            $handle = fopen('php://output', 'w');

            // Tulis UTF-8 BOM untuk kompatibilitas Microsoft Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Baris Header CSV
            fputcsv($handle, [
                'No',
                'Tanggal Transaksi',
                'Kode Order',
                'Nama Customer',
                'Biaya Sewa',
                'Denda',
                'Kompensasi Kerusakan',
                'Metode Pembayaran',
                'Total Bersih',
                'Status Transaksi',
            ]);

            $rowNumber = 1;

            // Proses bertahap menggunakan lazy() dengan eager loading untuk efisiensi memori & anti N+1
            $baseQuery->with([
                'user',
                'returnModel',
                'damageCases',
                'damageReports.damageCase',
                'payments.clientPaymentMethod',
                'latestPayment.clientPaymentMethod',
            ])
            ->orderBy('created_at', 'desc')
            ->lazy(100)
            ->each(function ($order) use ($handle, &$rowNumber) {
                $sewa = ($order->status === 'selesai' && $this->isOrderPaymentVerified($order))
                    ? (float) $order->total_amount
                    : 0;

                $denda = ($order->returnModel && $order->returnModel->late_fee_status === 'lunas')
                    ? (float) $order->returnModel->late_fee_amount
                    : 0;

                $kompensasi = (float) $order->damageCases
                    ->where('payment_status', 'lunas')
                    ->sum(fn ($dc) => (float) $dc->repair_cost + (float) $dc->fine_amount);

                $totalBersih = $sewa + $denda + $kompensasi;

                $customerName = $this->sanitizeForCsv($order->user?->name ?? 'Customer');
                $orderNumber = $this->sanitizeForCsv('#' . $order->order_number);
                $paymentMethod = $this->sanitizeForCsv($this->formatPaymentMethod($order->latestPayment ?? $order->payments->first()));
                $statusTransaksi = $this->sanitizeForCsv($order->status_label);

                fputcsv($handle, [
                    $rowNumber++,
                    $order->created_at ? $order->created_at->format('Y-m-d H:i') : '-',
                    $orderNumber,
                    $customerName,
                    $sewa,
                    $denda,
                    $kompensasi,
                    $paymentMethod,
                    $totalBersih,
                    $statusTransaksi,
                ]);
            });

            fclose($handle);
        }, $filename, $headers);
    }

    /**
     * Dapatkan instance Client milik admin rental yang sedang terotentikasi.
     */
    private function resolveClient(): Client
    {
        $user = Auth::user();
        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;
        if (! $client) {
            abort(403, 'Akses ditolak: Akun Anda tidak terhubung ke toko rental manapun.');
        }

        return $client;
    }

    /**
     * Resolusi dan validasi rentang tanggal berdasarkan input atau preset shortcut.
     */
    private function resolveDateRange(Request $request): array
    {
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::today($timezone);

        $preset = $request->query('preset');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $todayStr = $today->toDateString();
        $sevenDaysAgoStr = $today->copy()->subDays(6)->toDateString();
        $startOfMonthStr = $today->copy()->startOfMonth()->toDateString();
        $endOfMonthStr = $today->copy()->endOfMonth()->toDateString();

        // 1. Jika preset diberikan secara eksplisit
        if ($preset === 'hari_ini' || $preset === 'today') {
            $startDate = $todayStr;
            $endDate = $todayStr;
            $preset = 'hari_ini';
        } elseif ($preset === '7_hari_terakhir' || $preset === '7_days') {
            $startDate = $sevenDaysAgoStr;
            $endDate = $todayStr;
            $preset = '7_hari_terakhir';
        } elseif ($preset === 'bulan_ini' || $preset === 'this_month') {
            $startDate = $startOfMonthStr;
            $endDate = $endOfMonthStr;
            $preset = 'bulan_ini';
        } elseif ($preset === 'kustom' || $preset === 'custom') {
            $preset = 'kustom';
        } elseif (! empty($startDate) && ! empty($endDate)) {
            // Evaluasi apakah tanggal cocok dengan salah satu preset
            if ($startDate === $todayStr && $endDate === $todayStr) {
                $preset = 'hari_ini';
            } elseif ($startDate === $sevenDaysAgoStr && $endDate === $todayStr) {
                $preset = '7_hari_terakhir';
            } elseif ($startDate === $startOfMonthStr && $endDate === $endOfMonthStr) {
                $preset = 'bulan_ini';
            } else {
                $preset = 'kustom';
            }
        } else {
            // Default awal: Periode Bulan Ini
            $preset = 'bulan_ini';
            $startDate = $startOfMonthStr;
            $endDate = $endOfMonthStr;
        }

        $validator = Validator::make([
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ], [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ], [
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date_format' => 'Format tanggal mulai harus Y-m-d.',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.date_format' => 'Format tanggal akhir harus Y-m-d.',
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $startCarbon = null;
        $endCarbon = null;
        $activePeriodText = '-';

        if (! $validator->fails()) {
            $startCarbon = Carbon::createFromFormat('Y-m-d', $startDate, $timezone)->startOfDay();
            $endCarbon = Carbon::createFromFormat('Y-m-d', $endDate, $timezone)->endOfDay();
            $activePeriodText = $this->formatActivePeriodText($startCarbon, $endCarbon);
        }

        $presetLabels = [
            'hari_ini' => 'Hari Ini',
            '7_hari_terakhir' => '7 Hari Terakhir',
            'bulan_ini' => 'Bulan Ini',
            'kustom' => 'Rentang Kustom',
        ];
        $presetLabel = $presetLabels[$preset] ?? 'Bulan Ini';

        return [
            'validator'          => $validator,
            'start_date'         => $startDate,
            'end_date'           => $endDate,
            'preset'             => $preset,
            'preset_label'       => $presetLabel,
            'active_period_text' => $activePeriodText,
            'today_str'          => $todayStr,
            'seven_days_ago_str' => $sevenDaysAgoStr,
            'start_of_month_str' => $startOfMonthStr,
            'end_of_month_str'   => $endOfMonthStr,
            'start_carbon'       => $startCarbon,
            'end_carbon'         => $endCarbon,
        ];
    }

    /**
     * Format teks ringkasan periode aktif laporan secara konsisten dan informatif.
     */
    private function formatActivePeriodText(?Carbon $start, ?Carbon $end): string
    {
        if (! $start || ! $end) {
            return '-';
        }

        // Contoh: "29 Oktober 2026"
        if ($start->isSameDay($end)) {
            return $start->translatedFormat('j F Y');
        }

        // Contoh: "23–29 Oktober 2026" atau "1–31 Oktober 2026"
        if ($start->isSameMonth($end) && $start->isSameYear($end)) {
            return $start->translatedFormat('j') . '–' . $end->translatedFormat('j F Y');
        }

        // Contoh: "25 Oktober – 5 November 2026"
        if ($start->isSameYear($end)) {
            return $start->translatedFormat('j F') . ' – ' . $end->translatedFormat('j F Y');
        }

        // Contoh: "25 Desember 2025 – 5 Januari 2026"
        return $start->translatedFormat('j F Y') . ' – ' . $end->translatedFormat('j F Y');
    }

    /**
     * Hitung ringkasan metrik finansial pada database dengan isolasi client_id mutlak.
     */
    private function getFinancialSummary(int $clientId, Carbon $start, Carbon $end): array
    {
        // 1. Total Transaksi Selesai: jumlah pesanan unik berstatus 'selesai' dalam rentang tanggal
        $totalTransaksiSelesai = Order::where('client_id', $clientId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'selesai')
            ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak'])
            ->count();

        // 2. Pendapatan Sewa Pokok: total biaya sewa dari order selesai dengan pembayaran terverifikasi
        $pendapatanSewaPokok = (float) Order::where('client_id', $clientId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'selesai')
            ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak'])
            ->where(function ($q) {
                $q->whereHas('payments', function ($pq) {
                    $pq->whereIn('status', ['terverifikasi', 'diterima']);
                })->orWhereDoesntHave('payments');
            })
            ->sum('total_amount');

        // 3. Pendapatan Denda: denda keterlambatan pengembalian yang telah dibayar lunas
        $pendapatanDenda = (float) ReturnModel::whereHas('order', function ($q) use ($clientId, $start, $end) {
                $q->where('client_id', $clientId)
                  ->whereBetween('created_at', [$start, $end])
                  ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak']);
            })
            ->where('late_fee_status', 'lunas')
            ->sum('late_fee_amount');

        // 4. Kompensasi Kerusakan: ganti rugi kerusakan peralatan yang berstatus lunas
        $kompensasiKerusakan = (float) DamageCase::whereHas('damageReport.order', function ($q) use ($clientId, $start, $end) {
                $q->where('client_id', $clientId)
                  ->whereBetween('created_at', [$start, $end])
                  ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak']);
            })
            ->where('payment_status', 'lunas')
            ->sum(DB::raw('COALESCE(repair_cost, 0) + COALESCE(fine_amount, 0)'));

        // 5. Total Pendapatan: total dana masuk lunas/terverifikasi tanpa duplikasi
        $totalPendapatan = $pendapatanSewaPokok + $pendapatanDenda + $kompensasiKerusakan;

        return compact(
            'totalPendapatan',
            'pendapatanSewaPokok',
            'pendapatanDenda',
            'kompensasiKerusakan',
            'totalTransaksiSelesai'
        );
    }

    /**
     * Query dasar transaksi pesanan yang terverifikasi dan sah untuk laporan & ekspor.
     */
    private function getBaseOrdersQuery(int $clientId, Carbon $start, Carbon $end)
    {
        return Order::where('client_id', $clientId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotIn('status', ['batal', 'dibatalkan', 'ditolak'])
            ->where(function ($q) {
                $q->where('status', 'selesai')
                  ->orWhereHas('payments', function ($pq) {
                      $pq->whereIn('status', ['terverifikasi', 'diterima']);
                  });
            });
    }

    /**
     * Periksa apakah pembayaran pesanan memenuhi syarat terverifikasi / lunas.
     */
    private function isOrderPaymentVerified(Order $order): bool
    {
        if ($order->payments->isEmpty()) {
            return $order->status === 'selesai';
        }

        return $order->payments->contains(fn ($p) => in_array($p->status, ['terverifikasi', 'diterima']));
    }

    /**
     * Format label metode pembayaran secara aman (Transfer Bank / QRIS).
     */
    public function formatPaymentMethod(?Payment $payment): string
    {
        if (! $payment) {
            return '-';
        }

        $cpm = $payment->clientPaymentMethod;
        if ($cpm) {
            $bankName = trim((string) $cpm->bank_name);
            if (! empty($cpm->qris_image) || stripos($bankName, 'qris') !== false) {
                return 'QRIS';
            }
            if (! empty($bankName)) {
                return 'Transfer Bank (' . $bankName . ')';
            }
            return 'Transfer Bank';
        }

        if ($payment->client_payment_method_id) {
            $pm = PaymentMethod::find($payment->client_payment_method_id);
            if ($pm) {
                if ($pm->type === 'qris') {
                    return 'QRIS';
                }
                if ($pm->bank_name) {
                    return 'Transfer Bank (' . $pm->bank_name . ')';
                }
                return 'Transfer Bank';
            }
        }

        if ($payment->notes && stripos($payment->notes, 'qris') !== false) {
            return 'QRIS';
        }

        return 'Transfer Bank';
    }

    /**
     * Sanitasi nilai string untuk mencegah CSV formula injection pada Microsoft Excel & spreadsheet.
     */
    private function sanitizeForCsv(?string $value): string
    {
        if ($value === null || $value === '' || $value === '-') {
            return $value ?? '';
        }

        $firstChar = substr($value, 0, 1);
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"])) {
            if ($value === '-' || is_numeric($value)) {
                return $value;
            }
            return "'" . $value;
        }

        return $value;
    }
}
