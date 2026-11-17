<?php

namespace App\Console\Commands;

use App\Services\WebPushService;
use Illuminate\Console\Command;

class GenerateVapidKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:vapid {--show : Hanya tampilkan kunci yang saat ini aktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate atau tampilkan pasangan kunci VAPID untuk Web Push Notifications';

    /**
     * Execute the console command.
     */
    public function handle(WebPushService $service): int
    {
        if ($this->option('show')) {
            $keys = $service->getVapidKeys();
            $this->info('Kunci VAPID aktif saat ini:');
            $this->line('<comment>VAPID_PUBLIC_KEY=</comment>' . $keys['public_key']);
            $this->line('<comment>VAPID_PRIVATE_KEY=</comment>"' . trim(str_replace("\n", '\n', $keys['private_key'])) . '"');
            return Command::SUCCESS;
        }

        $keys = $service->generateVapidKeys();

        $this->info('Sepasang kunci VAPID baru berhasil digenerate:');
        $this->newLine();
        $this->line('<comment>VAPID_PUBLIC_KEY=</comment>' . $keys['public_key']);
        $this->newLine();
        $this->line('<comment>VAPID_PRIVATE_KEY=</comment>"' . trim(str_replace("\n", '\n', $keys['private_key'])) . '"');
        $this->newLine();
        $this->comment('Anda dapat menyalin variabel di atas ke file .env Anda jika ingin menetapkannya secara statis.');

        return Command::SUCCESS;
    }
}
