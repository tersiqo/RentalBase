# 🎨 RentalBase Design System Specification (`design_system.md`)

Spesifikasi resmi sistem desain visual untuk platform SaaS **RentalBase**. Dokumen ini menjadi acuan utama bagi pengembang frontend (Blade/Tailwind/CSS) dan desainer dalam membangun halaman landing page, katalog client, serta dashboard operasional.

---

## 1. 🌟 Prinsip Desain (Design Philosophy)

* **Fresh, Modern, & Professional SaaS**: Mengusung estetika SaaS kelas dunia (seperti Linear, Stripe, dan Vercel) yang mengutamakan kebersihan visual, kejelasan hirarki, dan tipografi tajam.
* **Canvas Putih Bersih dengan Ambient Glow**: Latar belakang utama menggunakan warna putih murni (`#FFFFFF`) yang dihiasi gradien tipis oranye/peach yang sangat lembut di sudut atas hero section (*soft atmospheric wash*).
* **Solid Orange Primary Accent**: Menggunakan warna aksen tunggal **Oranye Tegas/Solid** (`#F97316`). Tombol utama **wajib solid oranye** (tidak boleh menggunakan gradien pada tombol).
* **Warna Terpadu (No Rainbow Colors)**: Menghindari penggunaan warna-warni acak (seperti hijau mint, ungu, atau biru pada badge). Seluruh elemen menggunakan palet yang seragam dan elegan.
* **Sudut Mulus (Extra-Rounded Cards)**: Kartu dan komponen menggunakan *border-radius* yang besar dan mulus (`20px` - `24px`), dipadukan dengan bayangan lembut (*soft ambient drop shadow*) tanpa garis tepi kaku yang tebal.

---

## 2. 🎨 Palet Warna (Color Palette)

### A. Primary Accent (Oranye Utama)
| Token Name | HEX | HSL | Tailwind Class | Penggunaan |
| :--- | :--- | :--- | :--- | :--- |
| `primary-orange` | `#F97316` | `hsl(24, 95%, 53%)` | `bg-orange-500`, `text-orange-500` | Tombol CTA Utama (Solid), ikon aktif, sorotan harga |
| `primary-orange-hover` | `#EA580C` | `hsl(21, 90%, 48%)` | `bg-orange-600` | State Hover tombol CTA |
| `primary-orange-light` | `#FFF7ED` | `hsl(33, 100%, 96%)` | `bg-orange-50` | Background badge ikon, tag pill lembut |
| `ambient-peach-glow` | `rgba(251,146,60,0.12)` | - | - | Gradien latar belakang sudut atas hero |

### B. Neutral Canvas & Typography (Netral)
| Token Name | HEX | Tailwind Class | Penggunaan |
| :--- | :--- | :--- | :--- |
| `bg-canvas` | `#FFFFFF` | `bg-white` | Latar belakang utama aplikasi & kartu |
| `bg-surface-off` | `#FAFAF9` | `bg-stone-50` / `bg-neutral-50` | Alternatif background section & input form |
| `text-heading` | `#111827` | `text-gray-900` | Judul utama (H1, H2, H3), teks serba tebal |
| `text-body` | `#4B5563` | `text-gray-600` | Subtitle, deskripsi paragraf, deskripsi fitur |
| `text-muted` | `#9CA3AF` | `text-gray-400` | Caption, timestamp, teks bantuan form |
| `border-hairline` | `#F3F4F6` | `border-gray-100` / `border-stone-100` | Garis tepi kartu yang sangat tipis & ramah |

### C. Dark Surface (Multi-Tenant & Footer)
| Token Name | HEX | Tailwind Class | Penggunaan |
| :--- | :--- | :--- | :--- |
| `dark-surface` | `#0F172A` | `bg-slate-900` | Section Banner SaaS Multi-Tenant & Footer |
| `dark-card` | `#1E293B` | `bg-slate-800` | Kartu fitur di dalam section dark banner |
| `dark-text-muted` | `#94A3B8` | `text-slate-400` | Teks sekunder pada section dark |

---

## 3. 🗂️ Tipografi (Typography)

* **Font Family Utama**: `Plus Jakarta Sans`, atau `Inter` (Sans-Serif).
* **Konfigurasi CSS**: `font-family: 'Plus Jakarta Sans', sans-serif;`

| Level | Size | Weight | Line Height | Tailwind Class | Penggunaan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Display H1** | 48px (`3rem`) | Bold (700) / Extrabold (800) | `1.15` | `text-4xl md:text-5xl font-extrabold tracking-tight` | Headline Utama Hero Landing Page |
| **Heading H2** | 36px (`2.25rem`)| Bold (700) | `1.2` | `text-3xl font-bold tracking-tight` | Judul Section Utama |
| **Heading H3** | 24px (`1.5rem`) | Semibold (600) | `1.3` | `text-2xl font-semibold` | Sub-section / Judul Kartu |
| **Heading H4** | 18px (`1.125rem`)| Semibold (600) | `1.4` | `text-lg font-semibold` | Judul Fitur / Nama Paket |
| **Body Large** | 18px (`1.125rem`)| Regular (400) | `1.6` | `text-lg font-normal text-gray-600` | Subtitle Hero |
| **Body Base** | 15px (`0.9375rem`)| Regular (400) / Medium (500) | `1.5` | `text-base font-normal text-gray-600` | Teks Deskripsi Paragraf |
| **Small / Badge**| 13px (`0.8125rem`)| Medium (500) / Semibold (600) | `1.4` | `text-sm font-medium` | Tag Subdomain, Status Badge |

---

## 4. 📦 Komponen UI (Component Tokens)

### A. Tombol (Buttons)
1. **Primary Button (Solid Orange)**:
   * Class: `bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3.5 rounded-xl shadow-md shadow-orange-500/20 transition-all duration-200 active:scale-95`
   * Aturan: **Wajib warna oranye polos/solid**. Tidak boleh ada gradien warna pada tombol.
2. **Secondary / Outline Button**:
   * Class: `bg-white hover:bg-stone-50 text-gray-800 font-semibold px-6 py-3.5 rounded-xl border border-gray-200 transition-all duration-200`
3. **Ghost / Text Button**:
   * Class: `text-gray-700 hover:text-orange-500 font-medium px-4 py-2 transition-colors`

### B. Kartu Fitur (Feature Cards)
* **Spesifikasi**:
  * Background: `#FFFFFF`
  * Radius: `rounded-2xl` (20px - 24px)
  * Border: `border border-gray-100`
  * Shadow: `shadow-sm hover:shadow-xl hover:shadow-orange-500/5 transition-all duration-300`
  * Padding: `p-6` atau `p-8`
* **Wadahi Ikon (Icon Badge)**:
  * Dimensi: `w-12 h-12` (48x48px)
  * Shape: `rounded-xl`
  * Background: `bg-orange-50` (`#FFF7ED`)
  * Color Icon: `text-orange-500` (`#F97316`)

### C. Floating Hero Widgets (Showcase Kamera, Kalender, Subdomain)
1. **Main Showcase Card**:
   * Image kamera beresolusi tinggi di dalam frame `rounded-2xl border border-gray-100 shadow-2xl`.
2. **Floating Calendar Widget**:
   * Card melayang putih (`bg-white rounded-2xl shadow-xl border border-gray-100 p-4`).
   * Tanggal sewa terpilih menggunakan warna `bg-orange-500 text-white rounded-full`.
   * Rentang tanggal sewa menggunakan `bg-orange-100 text-orange-700`.
3. **Subdomain Tag Pill**:
   * Pill badge melayang: `bg-white/90 backdrop-blur-md rounded-full px-4 py-2 border border-gray-100 shadow-lg text-sm font-semibold text-gray-800 flex items-center gap-2`.
   * Text subdomain: `malang-camera.rentalbase.id`.

### D. Input Form & Cek Status
* **Input Box**: `w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all`

---

## 5. 🛠️ Kode Variabel CSS (`variables.css`)

Rekomendasi variabel CSS yang dapat ditempatkan pada `resources/css/app.css`:

```css
:root {
    /* Primary Accent */
    --color-primary-orange: #F97316;
    --color-primary-orange-hover: #EA580C;
    --color-primary-orange-light: #FFF7ED;
    --color-primary-orange-glow: rgba(251, 146, 60, 0.12);

    /* Neutrals */
    --color-bg-canvas: #FFFFFF;
    --color-bg-surface-off: #FAFAF9;
    --color-text-heading: #111827;
    --color-text-body: #4B5563;
    --color-text-muted: #9CA3AF;
    --color-border-hairline: #F3F4F6;

    /* Dark Surfaces */
    --color-dark-surface: #0F172A;
    --color-dark-card: #1E293B;

    /* Radii & Shadows */
    --radius-card: 1.5rem; /* 24px */
    --radius-button: 0.75rem; /* 12px */
    --shadow-soft-card: 0 10px 30px -5px rgba(0, 0, 0, 0.04);
    --shadow-orange-glow: 0 20px 40px -10px rgba(249, 115, 22, 0.15);
}

/* Background Ambient Glow Helper Class */
.hero-ambient-glow {
    background: 
        radial-gradient(circle at 10% 10%, rgba(253, 186, 116, 0.15) 0%, transparent 40%),
        radial-gradient(circle at 90% 15%, rgba(251, 146, 60, 0.12) 0%, transparent 45%),
        #FFFFFF;
}
```

---

## 6. 🚫 Checklist Anti-Slop UI (Pantangan Desain)

* ❌ **JANGAN** gunakan tombol bergradien warna (tombol harus solid oranye `#F97316`).
* ❌ **JANGAN** gunakan warna acak seperti hijau mint, biru, atau ungu pada badge ikon.
* ❌ **JANGAN** gunakan *border-line* hitam/abu-abu tebal yang kaku pada kartu.
* ❌ **JANGAN** gunakanklaim testimoni palsu atau statistik acak (misal: "1000+ rental", "99% puas").
* ❌ **JANGAN** gunakan efek *glassmorphism* tebal yang membuat teks tidak terbaca.
