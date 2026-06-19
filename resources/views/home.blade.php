@extends('layouts.app')

@section('title', 'PrediksiRumah Majalengka - Estimasi Harga Properti Akurat')

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden px-margin-mobile md:px-margin-desktop pt-16 pb-24 md:pt-32 md:pb-40 flex flex-col items-center text-center">
    <div class="max-w-4xl z-10">
        <div class="inline-block px-4 py-2 mb-6 rounded-full neumorphic-inset text-primary font-label-md text-label-md">
            Analisis Properti Berbasis Kecerdasan Buatan
        </div>
        <h1 class="font-display-lg text-display-lg mb-6 leading-tight">
            Prediksi Harga Rumah <span class="text-primary">Majalengka</span> Akurat
        </h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant mb-12 max-w-2xl mx-auto">
            Optimalkan keputusan investasi properti Anda dengan estimasi harga real-time menggunakan algoritma
            <span class="font-semibold text-on-surface">Random Forest Machine Learning</span> yang dilatih
            khusus untuk pasar lokal Majalengka.
        </p>
        <div class="flex flex-col sm:flex-row gap-6 justify-center">
            <a href="{{ url('/prediksi') }}"
                class="neumorphic-button-primary text-white px-10 py-4 rounded-xl font-headline-md text-headline-md flex items-center justify-center gap-2 group cursor-pointer">
                Mulai Prediksi
                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>
            <button onclick="openMethodModal()"
                class="neumorphic-outset text-primary px-10 py-4 rounded-xl font-headline-md text-headline-md hover:text-on-primary-fixed-variant transition-colors">
                Pelajari Metode
            </button>
        </div>
    </div>
    <!-- Decorative Neumorphic Elements -->
    <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full neumorphic-outset opacity-50 pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full neumorphic-inset opacity-30 pointer-events-none"></div>
</section>

<!-- Stats/Social Proof Section (Small Bento Fragment) -->
<section class="px-margin-mobile md:px-margin-desktop mb-32 max-w-container-max mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
        <div class="neumorphic-outset p-8 rounded-2xl flex flex-col items-center text-center">
            <span class="font-display-lg text-display-lg text-primary mb-2">{{ number_format($accuracy, 2) }}%</span>
            <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Akurasi Model</span>
        </div>
        <div class="neumorphic-outset p-8 rounded-2xl flex flex-col items-center text-center">
            <span class="font-display-lg text-display-lg text-primary mb-2">{{ $datasetRows }}</span>
            <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Data Properti</span>
        </div>
        <div class="neumorphic-outset p-8 rounded-2xl flex flex-col items-center text-center">
            <span class="font-display-lg text-display-lg text-primary mb-2">&lt; 1s</span>
            <span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Waktu Pemrosesan</span>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="bg-surface-container-low py-24 px-margin-mobile md:px-margin-desktop">
    <div class="max-w-container-max mx-auto">
        <div class="text-center mb-16">
            <h2 class="font-headline-lg text-headline-lg mb-4">Bagaimana Kami Bekerja</h2>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto">
                Tiga langkah sederhana untuk mendapatkan estimasi nilai properti yang presisi di wilayah Majalengka.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Step 1 -->
            <div class="neumorphic-outset p-10 rounded-3xl neumorphic-card-hover text-center relative">
                <div class="w-16 h-16 neumorphic-inset rounded-full flex items-center justify-center mx-auto mb-8 text-primary">
                    <span class="material-symbols-outlined text-3xl">edit_document</span>
                </div>
                <h3 class="font-headline-md text-headline-md mb-4">Input Data</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Masukkan detail properti seperti luas tanah, luas bangunan, jumlah kamar, dan lokasi spesifik di Majalengka.
                </p>
            </div>
            <!-- Step 2 -->
            <div class="neumorphic-outset p-10 rounded-3xl neumorphic-card-hover text-center relative">
                <div class="w-16 h-16 neumorphic-inset rounded-full flex items-center justify-center mx-auto mb-8 text-primary">
                    <span class="material-symbols-outlined text-3xl">memory</span>
                </div>
                <h3 class="font-headline-md text-headline-md mb-4">Proses AI</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Algoritma Random Forest kami melakukan komputasi regresi terhadap ribuan titik data pasar terkini.
                </p>
            </div>
            <!-- Step 3 -->
            <div class="neumorphic-outset p-10 rounded-3xl neumorphic-card-hover text-center relative">
                <div class="w-16 h-16 neumorphic-inset rounded-full flex items-center justify-center mx-auto mb-8 text-primary">
                    <span class="material-symbols-outlined text-3xl">payments</span>
                </div>
                <h3 class="font-headline-md text-headline-md mb-4">Hasil Estimasi</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Dapatkan kisaran harga pasar yang wajar dalam hitungan detik untuk keperluan jual beli atau investasi.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA/Methodology Teaser Section -->
<section class="py-24 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
    <div class="neumorphic-outset p-8 md:p-16 rounded-[40px] flex flex-col md:flex-row items-center gap-12">
        <div class="md:w-1/2">
            <img alt="Properti Modern" class="rounded-3xl shadow-xl w-full object-cover aspect-video"
                src="{{ asset('images/properti-modern.png') }}" />
        </div>
        <div class="md:w-1/2">
            <h2 class="font-headline-lg text-headline-lg mb-6">Analisis Berbasis Riset</h2>
            <p class="font-body-md text-body-md text-on-surface-variant mb-8">
                Dikembangkan oleh tim dari Universitas Muhammadiyah Cirebon, proyek ini menggabungkan keahlian ilmu komputer dengan data spasial wilayah Majalengka untuk menghasilkan transparansi harga properti bagi masyarakat.
            </p>
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined text-success-green">check_circle</span>
                    <span class="font-label-md text-label-md">Model tervalidasi dataset lokal</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined text-success-green">check_circle</span>
                    <span class="font-label-md text-label-md">Pembaruan data pasar berkala</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined text-success-green">check_circle</span>
                    <span class="font-label-md text-label-md">Open source untuk keperluan akademik</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
