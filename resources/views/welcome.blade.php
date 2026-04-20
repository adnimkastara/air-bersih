<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tirta Sejahtera</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F3F8FC] text-[#1F2937] antialiased" style="font-family: 'Inter', sans-serif;">
    <div class="relative overflow-hidden">
        <div class="pointer-events-none absolute -top-40 right-0 h-[420px] w-[420px] rounded-full bg-[#1FA3C8]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute top-[520px] -left-24 h-80 w-80 rounded-full bg-[#0F3D5E]/10 blur-3xl"></div>

        <header id="mainNavbar" class="fixed inset-x-0 top-0 z-50 transition duration-300">
            <nav class="mx-auto mt-4 flex w-[min(1200px,95%)] items-center justify-between rounded-2xl border border-white/70 bg-white/85 px-4 py-3 shadow-lg shadow-[#0F3D5E]/10 backdrop-blur-md sm:px-6">
                <a href="#beranda" class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-[#0F3D5E] to-[#1FA3C8] text-white shadow-md shadow-[#145374]/35">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.5 3.3 6 6.9 6 10.5A6 6 0 1 1 6 13.5C6 9.9 9.5 6.3 12 3Z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold leading-tight text-[#0F3D5E]">Tirta Sejahtera</p>
                        <p class="text-xs text-slate-500">Sistem Layanan Air Bersih</p>
                    </div>
                </a>

                <button id="menuToggle" type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="hidden items-center gap-8 md:flex">
                    <a href="#beranda" class="text-sm font-semibold text-slate-600 transition hover:text-[#0F3D5E]">Beranda</a>
                    <a href="#fitur" class="text-sm font-semibold text-slate-600 transition hover:text-[#0F3D5E]">Fitur</a>
                    <a href="#tentang" class="text-sm font-semibold text-slate-600 transition hover:text-[#0F3D5E]">Tentang</a>
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl bg-gradient-to-r from-[#0F3D5E] to-[#145374] px-5 py-2.5 text-sm font-bold text-white shadow-xl shadow-[#0F3D5E]/30 ring-1 ring-white/40 transition hover:translate-y-[-1px] hover:shadow-2xl">
                            Masuk ke Sistem
                        </a>
                    @endif
                </div>
            </nav>

            <div id="menuMobile" class="mx-auto mt-2 hidden w-[min(1200px,95%)] rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-xl md:hidden">
                <div class="flex flex-col gap-2 text-sm font-semibold text-slate-700">
                    <a href="#beranda" class="rounded-lg px-3 py-2 hover:bg-slate-100">Beranda</a>
                    <a href="#fitur" class="rounded-lg px-3 py-2 hover:bg-slate-100">Fitur</a>
                    <a href="#tentang" class="rounded-lg px-3 py-2 hover:bg-slate-100">Tentang</a>
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="rounded-lg bg-[#0F3D5E] px-3 py-2 text-center font-bold text-white">Masuk ke Sistem</a>
                    @endif
                </div>
            </div>
        </header>

        <main class="pt-28">
            <section id="beranda" class="mx-auto grid w-[min(1200px,95%)] gap-12 pb-16 pt-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-4 py-1 text-xs font-bold uppercase tracking-wider text-[#145374]">
                        <span class="h-2 w-2 rounded-full bg-[#1FA3C8]"></span>
                        Tertib Data, Lancar Layanan
                    </span>

                    <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-[#0F3D5E] sm:text-5xl lg:text-6xl">Tirta Sejahtera</h1>
                    <p class="mt-4 text-xl font-semibold text-slate-700">Sistem Pengelolaan Air Bersih Desa dan Kecamatan</p>
                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg">
                        Platform terpadu untuk mengelola pelanggan, pencatatan meter, tagihan, pembayaran, monitoring layanan,
                        dan keluhan secara tertib dan terintegrasi bagi operasional BUM Desa Bersama.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-3">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl bg-gradient-to-r from-[#0F3D5E] to-[#145374] px-6 py-3.5 text-sm font-bold text-white shadow-2xl shadow-[#0F3D5E]/35 ring-1 ring-white/50 transition hover:translate-y-[-1px] hover:shadow-[#0F3D5E]/45">
                                Masuk ke Sistem
                            </a>
                        @endif
                        <a href="#fitur" class="inline-flex items-center rounded-xl border border-[#145374]/20 bg-white px-6 py-3.5 text-sm font-semibold text-[#145374] transition hover:border-[#145374]/40 hover:bg-[#F7FBFE]">
                            Lihat Fitur
                        </a>
                    </div>

                    <div class="mt-9 grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ([['2','Kecamatan Terhubung'],['24','Desa Aktif'],['1.280','Pelanggan Tercatat'],['98%','Layanan Tersalurkan']] as [$num, $label])
                            <article class="rounded-xl border border-slate-200/80 bg-white/95 p-4 shadow-sm">
                                <p class="text-xl font-extrabold text-[#0F3D5E]">{{ $num }}</p>
                                <p class="mt-1 text-xs font-medium text-slate-500">{{ $label }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-3 rounded-[28px] bg-gradient-to-br from-[#1FA3C8]/25 via-white to-[#0F3D5E]/10 blur-xl"></div>
                    <article class="relative rounded-[28px] border border-slate-200/90 bg-white p-6 shadow-2xl shadow-[#0F3D5E]/15">
                        <div class="mb-6 flex items-start justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#145374]">Dashboard Ringkas</p>
                                <h2 class="mt-1 text-lg font-bold text-[#0F3D5E]">Kontrol Operasional Harian</h2>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Normal</span>
                        </div>

                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-[#F5F9FC] p-4">
                                    <p class="text-xs text-slate-500">Pelanggan Aktif</p>
                                    <p class="mt-1 text-2xl font-extrabold text-[#0F3D5E]">1.280</p>
                                </div>
                                <div class="rounded-2xl bg-[#F5F9FC] p-4">
                                    <p class="text-xs text-slate-500">Tagihan Berjalan</p>
                                    <p class="mt-1 text-2xl font-extrabold text-[#0F3D5E]">1.146</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-2 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-600">Keluhan Aktif</span>
                                    <span class="font-bold text-amber-600">12 Tiket</span>
                                </div>
                                <div class="h-2 rounded-full bg-amber-100">
                                    <div class="h-2 w-[22%] rounded-full bg-amber-500"></div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-cyan-100 bg-cyan-50 p-4">
                                <div class="mb-2 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-[#145374]">Monitoring Petugas</span>
                                    <span class="font-bold text-[#0F3D5E]">24 Titik</span>
                                </div>
                                <div class="h-2 rounded-full bg-cyan-100">
                                    <div class="h-2 w-[98%] rounded-full bg-gradient-to-r from-[#145374] to-[#1FA3C8]"></div>
                                </div>
                                <p class="mt-2 text-xs text-slate-600">Distribusi air stabil dan termonitor secara terpusat.</p>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section id="fitur" class="mx-auto w-[min(1200px,95%)] py-10">
                <div class="mb-7 flex flex-col gap-2">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#145374]">Fitur Utama Sistem</p>
                    <h3 class="text-2xl font-bold text-[#0F3D5E] sm:text-3xl">Modul inti untuk operasional resmi layanan air bersih</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @php
                        $features = [
                            ['Manajemen Pelanggan', 'Pendataan pelanggan berbasis desa/kecamatan secara tertib dan terpusat.'],
                            ['Meter Record', 'Input meter berkala dengan histori pemakaian untuk akurasi tagihan.'],
                            ['Tagihan & Pembayaran', 'Siklus tagihan, pembayaran, dan validasi transaksi yang transparan.'],
                            ['Monitoring Layanan', 'Pemantauan distribusi, jadwal petugas, dan status jaringan.'],
                            ['Keluhan & Gangguan', 'Pencatatan aduan warga dan tindak lanjut gangguan secara terstruktur.'],
                            ['Laporan & Rekapitulasi', 'Laporan operasional dan rekap manajerial untuk evaluasi kebijakan.'],
                        ];
                    @endphp

                    @foreach ($features as [$title, $desc])
                        <article class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#0F3D5E]/10">
                            <span class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-[#F1F7FC] text-[#145374] transition group-hover:bg-[#E3F3F9]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10M4 17h7" />
                                </svg>
                            </span>
                            <h4 class="text-base font-bold text-[#0F3D5E]">{{ $title }}</h4>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $desc }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="tentang" class="mx-auto w-[min(1200px,95%)] py-10">
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#145374]">Peran Pengguna</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h4 class="text-base font-bold text-[#0F3D5E]">Admin Kecamatan</h4>
                        <p class="mt-2 text-sm text-slate-600">Mengelola layanan tingkat kecamatan, monitoring desa, dan rekapitulasi operasional.</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h4 class="text-base font-bold text-[#0F3D5E]">Admin Desa</h4>
                        <p class="mt-2 text-sm text-slate-600">Mengelola data pelanggan, tagihan, pembayaran, dan layanan air bersih di wilayah desa.</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h4 class="text-base font-bold text-[#0F3D5E]">Petugas Lapangan</h4>
                        <p class="mt-2 text-sm text-slate-600">Menjalankan pencatatan meter, penanganan keluhan, dan monitoring lapangan harian.</p>
                    </article>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-4">
                    @foreach (['Tertib Data', 'Transparan', 'Monitoring Terpadu', 'Siap Digunakan di Lapangan'] as $value)
                        <div class="rounded-xl border border-cyan-100 bg-cyan-50 p-4 text-center text-sm font-bold text-[#145374]">{{ $value }}</div>
                    @endforeach
                </div>
            </section>
        </main>

        <footer class="mt-12 border-t border-slate-200 bg-white/90">
            <div class="mx-auto flex w-[min(1200px,95%)] flex-col gap-1 py-8">
                <p class="text-lg font-extrabold text-[#0F3D5E]">Tirta Sejahtera</p>
                <p class="text-sm text-slate-600">Sistem Pengelolaan Air Bersih Desa dan Kecamatan</p>
                <p class="text-sm text-slate-600">BUM Desa Bersama Tirta Sejahtera Kecamatan Karanganyar</p>
                <p class="mt-3 text-xs text-slate-500">&copy; {{ date('Y') }} Tirta Sejahtera. Versi 1.0</p>
            </div>
        </footer>
    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const menuMobile = document.getElementById('menuMobile');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                menuMobile.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>
