@extends('layouts.app')

@section('title', 'Analisis Nilai Properti - PrediksiRumah Majalengka')

@section('content')
    <style>
        @media print {
            /* Hide unnecessary UI elements like Navbar, Footer, Action Buttons, and Decorative Images */
            header, footer, .action-buttons, .decorative-section {
                display: none !important;
            }
            
            /* Reset body background for clean paper print */
            body, main {
                background: white !important;
                color: black !important;
            }
            
            /* Remove heavy neumorphic shadows that look messy in PDF */
            .neumorphic-outset, .neumorphic-inset {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important; /* light gray border */
                border-radius: 12px !important;
                background-color: transparent !important;
            }

            /* Ensure background colors for graphs/bars are printed */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Adjust spacing to fit on a single page if possible */
            main {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                margin-top: 0 !important;
            }
            
            @page {
                margin: 1.5cm;
            }
        }
    </style>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <!-- Header Text -->
        <div class="mb-12 text-center">
            <h1 class="font-headline-lg text-headline-lg text-primary mb-2">Analisis Nilai Properti</h1>
            <h2 class="font-headline-sm text-headline-sm text-on-surface mb-2">Laporan untuk: <span class="font-bold">{{ $namaPemohon }}</span></h2>
            <p class="text-on-surface-variant font-body-md">Berdasarkan model Random Forest yang dilatih dengan data pasar
                Kabupaten Majalengka.</p>
        </div>

        <!-- Main Results Card -->
        <section class="mb-12">
            <div class="neumorphic-outset rounded-3xl p-8 md:p-12 text-center bg-background">
                <span
                    class="inline-block px-4 py-2 rounded-full neumorphic-inset text-label-md font-label-md text-secondary mb-6">
                    Kategori: {{ $category }}
                </span>
                <h2 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-widest mb-2">Estimasi
                    Harga</h2>
                <div class="font-display-lg text-display-lg text-primary mb-6">
                    Rp {{ number_format($price, 0, ',', '.') }}
                </div>
                <div class="max-w-md mx-auto">
                    <div class="flex justify-between text-label-sm font-label-sm text-on-surface-variant mb-2">
                        <span>Interval Kepercayaan</span>
                        <span
                            class="{{ $confidence >= 80 ? 'text-success-green' : ($confidence >= 60 ? 'text-tertiary-container' : 'text-error-red') }}">
                            {{ $confidence >= 80 ? 'Tinggi' : ($confidence >= 60 ? 'Sedang' : 'Rendah') }}
                            ({{ $confidence }}%)
                        </span>
                    </div>
                    <div class="h-4 w-full bg-surface-container-low rounded-full neumorphic-inset p-1 overflow-hidden">
                        <div class="h-full {{ $confidence >= 80 ? 'bg-primary' : ($confidence >= 60 ? 'bg-tertiary-container' : 'bg-error') }} rounded-full"
                            style="width: {{ $confidence }}%"></div>
                    </div>
                    <p class="mt-4 text-label-sm font-label-sm text-on-surface-variant italic">
                        *Rentang estimasi: Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp
                        {{ number_format($maxPrice, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Model Accuracy Badge -->
        <section class="mb-12">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-gutter">
                <div class="neumorphic-outset rounded-3xl p-6 bg-background text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-success-green">verified</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Akurasi Model (MAPE)</span>
                    </div>
                    <div class="font-headline-lg text-headline-lg font-bold text-success-green">{{ $accuracyPct }}%</div>
                    <p class="text-[11px] text-on-surface-variant mt-1">Mean Absolute Percentage Error</p>
                </div>
                <div class="neumorphic-outset rounded-3xl p-6 bg-background text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-primary">leaderboard</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">R² Score (Test)</span>
                    </div>
                    <div class="font-headline-lg text-headline-lg font-bold text-primary">{{ $r2Test }}</div>
                    <p class="text-[11px] text-on-surface-variant mt-1">Koefisien Determinasi</p>
                </div>
                <div class="neumorphic-outset rounded-3xl p-6 bg-background text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-secondary">forest</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant">Algoritma</span>
                    </div>
                    <div class="font-headline-lg text-headline-lg font-bold text-secondary">Random Forest</div>
                    <p class="text-[11px] text-on-surface-variant mt-1">100 Decision Trees</p>
                </div>
            </div>
        </section>

        <!-- Analytics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mb-12">
            <!-- Feature Importance (Bento Style) -->
            <div class="neumorphic-outset rounded-3xl p-8 bg-background">
                <div class="flex items-center gap-3 mb-8">
                    <span class="material-symbols-outlined text-primary">analytics</span>
                    <h3 class="font-headline-md text-headline-md">Faktor Penentu Harga (Random Forest)</h3>
                </div>
                <div class="space-y-6">
                    @foreach ($importanceDisplay as $factor)
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-label-md font-label-md">{{ $factor['label'] }}</span>
                                <span class="text-label-md font-label-md">{{ $factor['value'] }}%</span>
                            </div>
                            <div class="h-2 w-full bg-surface-container-low rounded-full neumorphic-inset overflow-hidden">
                                <div class="h-full {{ $factor['color'] }} animate-bar" style="width: {{ $factor['value'] }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Property Comparison Table -->
            <div class="neumorphic-outset rounded-3xl p-8 bg-background">
                <div class="flex items-center gap-3 mb-8">
                    <span class="material-symbols-outlined text-primary">compare_arrows</span>
                    <h3 class="font-headline-md text-headline-md">Parameter Properti</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="neumorphic-outset rounded-xl">
                                <th class="p-4 font-label-md text-label-md text-on-surface-variant">Fitur</th>
                                <th class="p-4 font-label-md text-label-md text-on-surface-variant">Properti Anda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-0">
                            <tr class="border-b border-surface-dark-shadow/30">
                                <td class="p-4 text-on-surface-variant">Luas Tanah / Bangunan</td>
                                <td class="p-4 font-bold">{{ $luasTanah }} m² / {{ $luasBangunan }} m²</td>
                            </tr>
                            <tr class="border-b border-surface-dark-shadow/30">
                                <td class="p-4 text-on-surface-variant">Kamar Tidur / Kamar Mandi</td>
                                <td class="p-4 font-bold">{{ $kamarTidur }} / {{ $kamarMandi }}</td>
                            </tr>
                            <tr class="border-b border-surface-dark-shadow/30">
                                <td class="p-4 text-on-surface-variant">Lokasi</td>
                                <td class="p-4 font-bold">{{ $lokasi }}</td>
                            </tr>
                            <tr class="border-b border-surface-dark-shadow/30">
                                <td class="p-4 text-on-surface-variant">Tipe Properti</td>
                                <td class="p-4 font-bold">{{ $tipeProperti }}</td>
                            </tr>
                            <tr class="border-b border-surface-dark-shadow/30">
                                <td class="p-4 text-on-surface-variant">Kondisi / Usia</td>
                                <td class="p-4 font-bold">{{ $kondisi }} / {{ $usia }} tahun</td>
                            </tr>
                            <tr>
                                <td class="p-4 text-on-surface-variant">Garasi</td>
                                <td class="p-4 font-bold {{ $adaGarasi ? 'text-success-green' : 'text-error-red' }}">
                                    {{ $adaGarasi ? 'Tersedia' : 'Tidak Ada' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons flex flex-col md:flex-row justify-center items-center gap-8 py-12">
            <button
                class="w-full md:w-auto px-10 py-4 neumorphic-outset rounded-full text-primary font-bold flex items-center justify-center gap-3 transition-all hover:scale-105"
                id="downloadPdf">
                <span class="material-symbols-outlined">download</span>
                Laporan PDF
            </button>
            <a class="w-full md:w-auto px-10 py-4 neumorphic-outset rounded-full text-secondary font-bold flex items-center justify-center gap-3 transition-all hover:scale-105"
                href="{{ url('/kalkulator-kpr?harga=' . $price) }}">
                <span class="material-symbols-outlined">calculate</span>
                Simulasi KPR
            </a>
            <a class="w-full md:w-auto px-10 py-4 bg-background neumorphic-outset text-primary rounded-full font-bold flex items-center justify-center gap-3 transition-all hover:scale-105"
                href="{{ url('/prediksi') }}">
                <span class="material-symbols-outlined">refresh</span>
                Mulai Lagi
            </a>
        </div>

        <!-- Decorative Illustration Section -->
        <section class="decorative-section mt-8 mb-16 opacity-80 grayscale hover:grayscale-0 transition-all duration-700">
            <div class="neumorphic-inset rounded-3xl h-64 w-full relative overflow-hidden">
                <img alt="Modern House Architecture" class="w-full h-full object-cover"
                    src="{{ asset('images/modern-house.png') }}" />
                <div class="absolute inset-0 bg-gradient-to-t from-background/80 to-transparent"></div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Trigger factor progress bar animation on load
            const bars = document.querySelectorAll('.animate-bar');
            bars.forEach(bar => {
                const targetWidth = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = targetWidth;
                }, 300);
            });

            // PDF download placeholder
            const pdfBtn = document.getElementById('downloadPdf');
            if (pdfBtn) {
                pdfBtn.addEventListener('click', () => {
                    window.print();
                });
            }
        });
    </script>
@endpush