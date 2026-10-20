<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:sync-sequences', function () {
    if (DB::getDriverName() !== 'pgsql') {
        $this->info('Sequence syncing is only needed for PostgreSQL.');
        return;
    }

    $tables = ['clients', 'subscriptions', 'client_registrations', 'users', 'orders', 'products', 'categories', 'payments', 'shipments', 'order_items'];

    foreach ($tables as $table) {
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            continue;
        }
        $maxId = DB::table($table)->max('id') ?? 0;
        $seq = DB::selectOne("SELECT pg_get_serial_sequence(?, 'id') as seq", [$table]);

        if ($seq && $seq->seq && $maxId > 0) {
            DB::statement("SELECT setval(?, ?, true)", [$seq->seq, $maxId]);
            $this->line("Synced sequence for <info>{$table}</info> to <comment>{$maxId}</comment>.");
        }
    }

    $this->info('All PostgreSQL sequences synchronized successfully.');
})->purpose('Synchronize PostgreSQL sequence primary keys with table MAX(id)');

