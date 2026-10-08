// Livewire v4 sudah membawa Alpine.js sendiri (otomatis di-start oleh @livewireScripts).
// Jangan import/start Alpine lagi di sini, kalau tidak Alpine ke-load dobel dan Livewire (wire:click, wire:loading, dll) mati.
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.Alpine = Alpine;

// Tambah plugin Alpine jika ada
// Alpine.plugin(...);

// Jalankan Livewire (ini otomatis meng-start Alpine yang sudah dilengkapi fitur Livewire)
Livewire.start();