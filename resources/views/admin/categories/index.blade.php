@extends('layouts.admin')

@section('page-title', 'Kategori')

@section('content')
@php
    $catJson = $categories->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'description' => $c->description,
        'products_count' => $c->products_count,
    ])->values();

    $flashes = collect();
    if (session('success')) {
        $flashes->push(['type' => 'ok', 'title' => 'Berhasil', 'msg' => session('success')]);
    }
    if (session('error')) {
        $flashes->push(['type' => 'error', 'title' => 'Gagal', 'msg' => session('error')]);
    }

    $config = [
        'categories' => $catJson,
        'limit' => $limit,
        'planLabel' => $subscription->plan_name ?? 'Starter',
        'storeUrl' => route('admin.categories.store'),
        'updateUrl' => route('admin.categories.update', ['category' => '__ID__']),
        'deleteUrl' => route('admin.categories.destroy', ['category' => '__ID__']),
        'showForm' => $errors->has('name'),
        'editId' => old('_edit_id') ? (int) old('_edit_id') : null,
        'oldName' => old('name', ''),
        'oldDesc' => old('description', ''),
        'formError' => $errors->first('name'),
        'flashes' => $flashes->all(),
        'canManage' => (bool) (Auth::user()?->isPrimaryAdmin()),
    ];
@endphp

<style>
#catPage{
  --o50:#fff7ed;--o100:#ffedd5;--o200:#fed7aa;--o500:#f97316;--o600:#ea580c;--o700:#c2410c;
  --stone50:#fafaf9;--stone100:#f5f5f4;
  --g100:#f3f4f6;--g200:#e5e7eb;--g300:#d1d5db;--g400:#9ca3af;--g500:#6b7280;--g600:#4b5563;--g800:#1f2937;--g900:#111827;
  --slate900:#0f172a;
  --amber50:#fffbeb;--amber500:#f59e0b;--amber600:#d97706;
  --red50:#fef2f2;--red500:#ef4444;--red600:#dc2626;
  --em50:#ecfdf5;--em500:#10b981;--em600:#059669;
  --shadow-sm:0 1px 2px rgba(0,0,0,.05);--shadow-md:0 4px 10px -2px rgba(0,0,0,.08);
  color:var(--g800);font-size:14px;
}
#catPage *{box-sizing:border-box}
#catPage button,#catPage input,#catPage textarea,#catPage select{font:inherit;color:inherit}

#catPage .eyebrow{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--o500);display:flex;align-items:center;gap:6px;margin-bottom:4px}
#catPage .eyebrow::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--o500)}
#catPage .head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin:12px 0 28px}
#catPage h1{font-size:30px;font-weight:800;color:var(--g900);letter-spacing:-.025em;line-height:1.2}
#catPage .head p.sub{margin-top:4px;font-size:14px;color:var(--g500);font-weight:500}
#catPage .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:12px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid var(--g200);background:#fff;color:var(--g600);transition:.15s;box-shadow:var(--shadow-sm)}
#catPage .btn:hover{background:var(--stone50)}
#catPage .btn.primary{background:var(--o500);border-color:var(--o500);color:#fff;box-shadow:0 2px 6px rgba(249,115,22,.3)}
#catPage .btn.primary:hover{background:var(--o600)}
#catPage .btn.danger{background:var(--red500);border-color:var(--red500);color:#fff}
#catPage .btn.danger:hover{background:var(--red600)}
#catPage .btn:disabled{opacity:.5;cursor:not-allowed;box-shadow:none}
#catPage .btn svg{width:16px;height:16px}
#catPage .btn.lg{padding:10px 18px}

#catPage .card{background:#fff;border:1px solid var(--g100);border-radius:16px;box-shadow:var(--shadow-sm);transition:.2s}

#catPage .quota{padding:20px;margin-bottom:20px;display:flex;gap:16px;align-items:center;flex-wrap:wrap}
#catPage .quota:hover{box-shadow:var(--shadow-md)}
#catPage .ico{width:36px;height:36px;border-radius:12px;background:var(--o50);display:grid;place-items:center;flex-shrink:0;color:var(--o500)}
#catPage .ico svg{width:18px;height:18px}
#catPage .qi{flex:1;min-width:240px}
#catPage .qr{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:8px}
#catPage .qr b{font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--g400)}
#catPage .qr span{font-size:13px;font-weight:800;color:var(--g900)}
#catPage .bar{height:8px;border-radius:99px;background:var(--g100);overflow:hidden}
#catPage .bar i{display:block;height:100%;border-radius:99px;background:var(--o500);transition:width .4s}
#catPage .note{margin-top:8px;font-size:12px;font-weight:500;color:var(--o600)}
#catPage .quota a{font-size:12px;font-weight:700;color:var(--o600);text-decoration:none;white-space:nowrap}
#catPage .quota a:hover{text-decoration:underline}
#catPage .quota.warn{background:var(--amber50);border-color:#fde68a}
#catPage .quota.warn .ico{background:#fef3c7;color:var(--amber500)}
#catPage .quota.warn .bar i{background:var(--amber500)}
#catPage .quota.warn .note{color:var(--amber600)}
#catPage .quota.full{background:var(--red50);border-color:#fecaca}
#catPage .quota.full .ico{background:#fee2e2;color:var(--red500)}
#catPage .quota.full .bar i{background:var(--red500)}
#catPage .quota.full .note{color:var(--red600);font-weight:600}

#catPage .tools{padding:16px 20px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;border-bottom:1px solid var(--g100)}
#catPage .search{position:relative;flex:1;max-width:340px}
#catPage .search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--g400)}
#catPage .search input{width:100%;padding:8px 12px 8px 36px;background:#fff;border:1px solid var(--g200);border-radius:12px;font-size:12px;font-weight:500;outline:0;box-shadow:var(--shadow-sm);transition:.15s}
#catPage .search input::placeholder{color:var(--g400)}
#catPage .search input:focus{border-color:var(--o500);box-shadow:0 0 0 3px rgba(249,115,22,.15)}
#catPage .count{font-size:12px;color:var(--g500);font-weight:500}
#catPage table{width:100%;border-collapse:collapse}
#catPage th{text-align:left;padding:12px 20px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--g400);background:rgba(250,250,249,.7)}
#catPage td{padding:14px 20px;border-top:1px solid var(--g100);vertical-align:middle}
#catPage tbody tr{transition:.12s}
#catPage tbody tr:hover{background:rgba(255,247,237,.35)}
#catPage .name{display:flex;align-items:center;gap:12px;font-weight:700;color:var(--g900)}
#catPage .dot{width:36px;height:36px;border-radius:12px;background:var(--o50);color:var(--o500);display:grid;place-items:center;flex-shrink:0}
#catPage .dot svg{width:18px;height:18px}
#catPage .desc{color:var(--g500);font-weight:500;max-width:340px}
#catPage .pill{display:inline-flex;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:var(--o50);color:var(--o700)}
#catPage .pill.zero{background:var(--g100);color:var(--g500)}
#catPage th:last-child{text-align:right}
#catPage .act{display:flex;gap:6px;justify-content:flex-end}
#catPage .ib{width:34px;height:34px;display:grid;place-items:center;border-radius:12px;border:1px solid var(--g200);background:#fff;color:var(--g500);cursor:pointer;transition:.15s}
#catPage .ib svg{width:16px;height:16px}
#catPage .ib:hover{background:var(--o50);color:var(--o600);border-color:var(--o200)}
#catPage .ib.del:hover{background:var(--red50);color:var(--red600);border-color:#fecaca}
#catPage .ib.locked{opacity:.55}

#catPage .state{padding:60px 20px;text-align:center}
#catPage .state .eb{width:56px;height:56px;border-radius:16px;background:var(--o50);color:var(--o500);display:grid;place-items:center;margin:0 auto 12px}
#catPage .state .eb svg{width:26px;height:26px}
#catPage .state h3{font-size:16px;font-weight:800;color:var(--g900);margin-bottom:4px}
#catPage .state p{color:var(--g500);font-weight:500;margin-bottom:16px;font-size:13px}

#catPage .ov{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);display:grid;place-items:center;padding:20px;z-index:60;animation:catFade .15s}
#catPage .modal{background:#fff;border-radius:16px;width:100%;max-width:460px;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);animation:catPop .2s}
#catPage .modal header{padding:22px 24px 0;display:flex;justify-content:space-between;align-items:flex-start}
#catPage .modal h2{font-size:18px;font-weight:800;color:var(--g900);letter-spacing:-.01em}
#catPage .x{border:0;background:none;font-size:18px;cursor:pointer;color:var(--g400);width:28px;height:28px;border-radius:8px}
#catPage .x:hover{background:var(--g100);color:var(--g800)}
#catPage .body{padding:16px 24px}
#catPage .modal footer{padding:8px 24px 22px;display:flex;justify-content:flex-end;gap:10px}
#catPage label{display:block;font-size:12px;font-weight:700;color:var(--g800);margin-bottom:6px}
#catPage label em{color:var(--red500);font-style:normal}
#catPage .hint{float:right;font-size:11px;font-weight:500;color:var(--g400)}
#catPage .f{margin-bottom:16px}
#catPage .f input,#catPage .f textarea{width:100%;padding:10px 12px;border:1px solid var(--g200);border-radius:12px;font-size:13px;font-weight:500;outline:0;box-shadow:var(--shadow-sm);transition:.15s}
#catPage .f textarea{resize:vertical;min-height:88px}
#catPage .f input:focus,#catPage .f textarea:focus{border-color:var(--o500);box-shadow:0 0 0 3px rgba(249,115,22,.15)}
#catPage .f.bad input{border-color:var(--red500);box-shadow:0 0 0 3px rgba(239,68,68,.12)}
#catPage .err{margin-top:6px;font-size:12px;font-weight:600;color:var(--red600)}
#catPage .box{padding:12px 14px;border-radius:12px;font-size:12px;font-weight:500;line-height:1.6}
#catPage .box.info{background:var(--o50);color:var(--o700);border:1px solid var(--o100)}
#catPage .box.err{background:var(--red50);color:#991b1b;border:1px solid #fecaca}
#catPage .spin{width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:catRot .6s linear infinite}
@keyframes catRot{to{transform:rotate(360deg)}}
@keyframes catFade{from{opacity:0}}
@keyframes catPop{from{opacity:0;transform:translateY(12px) scale(.97)}}

#catPage #toasts{position:fixed;right:20px;bottom:20px;display:flex;flex-direction:column;gap:10px;z-index:80}
#catPage .toast{background:#fff;border:1px solid var(--g100);border-left:4px solid var(--em500);border-radius:12px;padding:12px 16px;box-shadow:0 10px 25px rgba(0,0,0,.12);min-width:260px;max-width:360px;animation:catPop .25s}
#catPage .toast.error{border-left-color:var(--red500)}
#catPage .toast b{display:block;font-size:13px;color:var(--g900)}
#catPage .toast span{font-size:12px;color:var(--g500);font-weight:500}
</style>

<div id="catPage" x-data="categoryPage({{ Illuminate\Support\Js::from($config) }})" x-cloak>
    <div class="head">
        <div>
            <p class="eyebrow">Katalog Peralatan</p>
            <h1>Kategori</h1>
            <p class="sub">Kelompokkan produk rental agar mudah ditemukan pelanggan.</p>
        </div>
        <template x-if="canManage">
            <button class="btn primary lg" type="button" @click="openCreate()" :disabled="isFull"
                    :title="isFull ? ('Limit paket ' + planLabel + ' tercapai (' + limit + ' kategori)') : ''">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Kategori Baru
            </button>
        </template>
        <template x-if="!canManage">
            <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Akses Admin Staf (Hanya Lihat)</span>
            </div>
        </template>
    </div>

    <section class="card quota" :class="isFull ? 'full' : (isWarn ? 'warn' : '')" aria-live="polite">
        <div class="ico">
            <template x-if="isFull"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></template>
            <template x-if="!isFull && isWarn"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></template>
            <template x-if="!isFull && !isWarn"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></template>
        </div>
        <div class="qi">
            <div class="qr">
                <b>Penggunaan limit kategori</b>
                <span x-text="limit == null ? (used + ' kategori · Tanpa batas') : (used + ' / ' + limit + ' kategori')"></span>
            </div>
            <div class="bar"><i :style="'width:' + pct + '%'"></i></div>
            <div class="note" x-text="quotaNote"></div>
        </div>
        <a href="#" x-show="isFull || isWarn" @click.prevent>Upgrade paket &rarr;</a>
    </section>

    <section class="card" style="overflow:hidden">
        <div class="tools">
            <div class="search">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" placeholder="Cari nama atau deskripsi kategori" aria-label="Cari kategori" x-model="search">
            </div>
            <div class="count" x-text="filtered.length + ' dari ' + categories.length + ' kategori'"></div>
        </div>

        <div x-show="filtered.length > 0">
            <table>
                <thead>
                    <tr><th>Nama Kategori</th><th>Deskripsi</th><th>Jumlah Produk</th><th x-show="canManage">Aksi</th></tr>
                </thead>
                <tbody>
                    <template x-for="cat in filtered" :key="cat.id">
                        <tr>
                            <td>
                                <div class="name">
                                    <div class="dot"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg></div>
                                    <span x-text="cat.name"></span>
                                </div>
                            </td>
                            <td class="desc">
                                <template x-if="cat.description"><span x-text="cat.description"></span></template>
                                <template x-if="!cat.description"><span style="color:var(--g300)">&mdash;</span></template>
                            </td>
                            <td><span class="pill" :class="cat.products_count ? '' : 'zero'" x-text="cat.products_count + ' produk'"></span></td>
                            <td x-show="canManage">
                                <div class="act">
                                    <button type="button" class="ib" title="Edit" :aria-label="'Edit ' + cat.name" @click="openEdit(cat)">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" class="ib del" :class="cat.products_count ? 'locked' : ''"
                                            :title="cat.products_count ? ('Masih dipakai ' + cat.products_count + ' produk') : 'Hapus'"
                                            :aria-label="'Hapus ' + cat.name" @click="askDelete(cat)">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="state" x-show="filtered.length === 0">
            <template x-if="categories.length > 0">
                <div>
                    <div class="eb"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
                    <h3>Tidak ada hasil</h3>
                    <p>Tidak ditemukan kategori untuk pencarian Anda.</p>
                    <button class="btn" type="button" @click="search = ''">Reset pencarian</button>
                </div>
            </template>
            <template x-if="categories.length === 0">
                <div>
                    <div class="eb"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg></div>
                    <h3>Belum ada kategori</h3>
                    <p>Buat kategori pertama untuk mengelompokkan produk Anda.</p>
                    <template x-if="canManage">
                        <button class="btn primary" type="button" @click="openCreate()">Tambah Kategori Baru</button>
                    </template>
                </div>
            </template>
        </div>
    </section>

    {{-- Form modal --}}
    <div class="ov" x-show="showForm" x-cloak @click.self="showForm = false" @keydown.escape.window="showForm = false">
        <div class="modal" role="dialog" aria-modal="true">
            <header>
                <h2 x-text="editId ? 'Edit Kategori' : 'Tambah Kategori Baru'"></h2>
                <button class="x" type="button" @click="showForm = false" aria-label="Tutup">&times;</button>
            </header>
            <form :action="formAction" method="POST" @submit="guardSubmit($event)">
                @csrf
                <input type="hidden" name="_method" :value="editId ? 'PUT' : 'POST'">
                <input type="hidden" name="_edit_id" :value="editId ?? ''">
                <div class="body">
                    <div class="f" :class="formError ? 'bad' : ''">
                        <label for="iName">Nama kategori <em>*</em><span class="hint" x-text="form.name.length + '/60'"></span></label>
                        <input id="iName" name="name" maxlength="60" placeholder="mis. Stroller" autocomplete="off"
                               x-model="form.name" x-ref="nameInput">
                        <div class="err" x-show="formError" x-text="formError"></div>
                    </div>
                    <div class="f">
                        <label for="iDesc">Deskripsi <span class="hint" x-text="form.desc.length + '/200'"></span></label>
                        <textarea id="iDesc" name="description" maxlength="200" placeholder="Opsional — jelaskan isi kategori ini" x-model="form.desc"></textarea>
                    </div>
                    <div class="box info" x-show="!editId && limit != null"
                         x-text="'Setelah ini terpakai ' + (used + 1) + ' dari ' + limit + ' slot kategori paket ' + planLabel + '.'"></div>
                </div>
                <footer>
                    <button type="button" class="btn" @click="showForm = false">Batal</button>
                    <button type="submit" class="btn primary">Simpan</button>
                </footer>
            </form>
        </div>
    </div>

    {{-- Delete modal --}}
    <div class="ov" x-show="showDelete" x-cloak @click.self="showDelete = false" @keydown.escape.window="showDelete = false">
        <div class="modal" role="dialog" aria-modal="true">
            <template x-if="deleteBlocked">
                <div>
                    <header>
                        <h2>Kategori tidak dapat dihapus</h2>
                        <button class="x" type="button" @click="showDelete = false" aria-label="Tutup">&times;</button>
                    </header>
                    <div class="body">
                        <div class="box err">
                            Kategori <b x-text="deleteTarget?.name"></b> masih digunakan oleh
                            <b x-text="deleteTarget?.products_count + ' produk'"></b>. Pindahkan atau hapus produk tersebut terlebih dahulu, lalu coba lagi.
                        </div>
                    </div>
                    <footer>
                        <button class="btn" type="button" @click="showDelete = false">Mengerti</button>
                    </footer>
                </div>
            </template>
            <template x-if="!deleteBlocked">
                <div>
                    <header>
                        <h2>Hapus kategori?</h2>
                        <button class="x" type="button" @click="showDelete = false" aria-label="Tutup">&times;</button>
                    </header>
                    <div class="body">
                        <p style="font-size:13px;color:var(--g600);line-height:1.6">
                            Kategori <b style="color:var(--g900)" x-text="deleteTarget?.name"></b> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <footer>
                        <button class="btn" type="button" @click="showDelete = false">Batal</button>
                        <form :action="deleteTarget ? deleteUrl.replace('__ID__', deleteTarget.id) : '#'" method="POST">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn danger">Ya, Hapus</button>
                        </form>
                    </footer>
                </div>
            </template>
        </div>
    </div>

    <div id="toasts" aria-live="polite">
        <template x-for="t in toasts" :key="t.id">
            <div class="toast" :class="t.type === 'error' ? 'error' : ''">
                <b x-text="t.title"></b><span x-text="t.msg"></span>
            </div>
        </template>
    </div>
</div>

<script>
    function categoryPage(config) {
        return {
            categories: config.categories,
            limit: config.limit,
            planLabel: config.planLabel,
            storeUrl: config.storeUrl,
            updateUrl: config.updateUrl,
            deleteUrl: config.deleteUrl,
            canManage: Boolean(config.canManage),
            search: '',
            showForm: config.showForm,
            editId: config.editId,
            form: { name: config.oldName || '', desc: config.oldDesc || '' },
            formError: config.formError || '',
            showDelete: false,
            deleteBlocked: false,
            deleteTarget: null,
            toasts: [],

            get used() { return this.categories.length; },
            get pct() { return this.limit == null ? 12 : Math.min(100, this.used / this.limit * 100); },
            get isFull() { return this.limit != null && this.used >= this.limit; },
            get isWarn() { return this.limit != null && !this.isFull && this.pct >= 80; },
            get quotaNote() {
                if (this.limit == null) return 'Paket ' + this.planLabel + ': jumlah kategori tidak dibatasi.';
                if (this.isFull) return 'Paket ' + this.planLabel + ': batas ' + this.limit + ' kategori sudah tercapai. Hapus kategori yang tidak dipakai atau upgrade paket untuk menambah kategori baru.';
                return 'Paket ' + this.planLabel + ': tersisa ' + (this.limit - this.used) + ' slot kategori.';
            },
            get filtered() {
                const q = this.search.trim().toLowerCase();
                if (!q) return this.categories;
                return this.categories.filter(c =>
                    c.name.toLowerCase().includes(q) ||
                    (c.description || '').toLowerCase().includes(q)
                );
            },
            get formAction() {
                return this.editId ? this.updateUrl.replace('__ID__', this.editId) : this.storeUrl;
            },

            init() {
                (config.flashes || []).forEach(f => this.pushToast(f.type, f.title, f.msg));
                if (this.showForm) {
                    setTimeout(() => this.$refs.nameInput && this.$refs.nameInput.focus(), 0);
                }
            },

            openCreate() {
                if (!this.canManage) {
                    this.pushToast('error', 'Akses Ditolak', 'Hanya Admin Utama yang berwenang menambah kategori.');
                    return;
                }
                if (this.isFull) {
                    this.pushToast('error', 'Limit tercapai', 'Paket ' + this.planLabel + ' maksimal ' + this.limit + ' kategori.');
                    return;
                }
                this.editId = null;
                this.form = { name: '', desc: '' };
                this.formError = '';
                this.showForm = true;
                setTimeout(() => this.$refs.nameInput && this.$refs.nameInput.focus(), 0);
            },

            openEdit(cat) {
                if (!this.canManage) {
                    this.pushToast('error', 'Akses Ditolak', 'Hanya Admin Utama yang berwenang mengubah kategori.');
                    return;
                }
                this.editId = cat.id;
                this.form = { name: cat.name, desc: cat.description || '' };
                this.formError = '';
                this.showForm = true;
                setTimeout(() => this.$refs.nameInput && this.$refs.nameInput.focus(), 0);
            },

            guardSubmit(e) {
                if (!this.canManage) {
                    e.preventDefault();
                    this.pushToast('error', 'Akses Ditolak', 'Hanya Admin Utama yang berwenang menyimpan kategori.');
                    return;
                }
                this.formError = '';
                if (!this.form.name.trim()) {
                    e.preventDefault();
                    this.formError = 'Nama kategori wajib diisi.';
                }
            },

            askDelete(cat) {
                if (!this.canManage) {
                    this.pushToast('error', 'Akses Ditolak', 'Hanya Admin Utama yang berwenang menghapus kategori.');
                    return;
                }
                this.deleteTarget = cat;
                this.deleteBlocked = cat.products_count > 0;
                this.showDelete = true;
            },

            pushToast(type, title, msg) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type, title, msg });
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 4200);
            },
        };
    }
</script>
@endsection
