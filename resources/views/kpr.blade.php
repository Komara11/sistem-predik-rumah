@extends('layouts.app')

@section('title', 'Kalkulator KPR - PrediksiRumah Majalengka')

@section('content')
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <!-- Header -->
        <div class="text-center mb-16">
            <div class="inline-block px-4 py-2 mb-4 rounded-full neumorphic-inset text-primary font-label-md text-label-md">
                Simulasi Keuangan Properti
            </div>
            <h1 class="font-display-lg text-display-lg mb-6 leading-tight">
                Kalkulator Angsuran <span class="text-primary">KPR</span>
            </h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">
                Hitung perkiraan angsuran bulanan rumah idaman Anda berdasarkan harga prediksi atau harga target properti di
                Majalengka.
            </p>
        </div>

        <!-- Main Calculator Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter items-stretch">
            <!-- Input Panel -->
            <div
                class="lg:col-span-2 neumorphic-outset rounded-[32px] p-8 md:p-12 bg-background flex flex-col justify-between">
                <form id="kprForm" class="space-y-8">
                    <!-- Property Price -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center ml-1">
                            <label for="harga" class="font-headline-md text-body-lg text-on-surface font-semibold">Harga
                                Properti (Rp)</label>
                            <span class="text-primary font-bold text-headline-md" id="hargaLabel">Rp 450.000.000</span>
                        </div>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-4 text-on-surface-variant">payments</span>
                            <input id="harga" name="harga" type="number" value="{{ request('harga', 450000000) }}"
                                step="10000000"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none font-semibold text-body-lg" />
                        </div>
                        <input id="hargaSlider" type="range" min="50000000" max="2000000000"
                            value="{{ request('harga', 450000000) }}" step="10000000"
                            class="w-full h-2 rounded-full neumorphic-inset bg-background appearance-none cursor-pointer accent-primary" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                        <!-- Down Payment -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center ml-1">
                                <label for="dpPercent"
                                    class="font-label-md text-label-md text-on-surface font-semibold">Uang Muka (%)</label>
                                <span class="text-primary font-bold" id="dpLabel">Rp 90.000.000 (20%)</span>
                            </div>
                            <div class="relative flex items-center">
                                <span
                                    class="material-symbols-outlined absolute left-4 text-on-surface-variant">percent</span>
                                <input id="dpPercent" name="dpPercent" type="number" value="20" min="10" max="90"
                                    class="w-full pl-12 pr-4 py-3.5 rounded-2xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md" />
                            </div>
                        </div>

                        <!-- Interest Rate -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center ml-1">
                                <label for="interest" class="font-label-md text-label-md text-on-surface font-semibold">Suku
                                    Bunga Per Tahun (%)</label>
                                <span class="text-primary font-bold" id="interestLabel">7.5%</span>
                            </div>
                            <div class="relative flex items-center">
                                <span
                                    class="material-symbols-outlined absolute left-4 text-on-surface-variant">trending_up</span>
                                <input id="interest" name="interest" type="number" step="0.1" value="7.5" min="1" max="30"
                                    class="w-full pl-12 pr-4 py-3.5 rounded-2xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md" />
                            </div>
                        </div>
                    </div>

                    <!-- Tenure -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center ml-1">
                            <label for="tenure" class="font-label-md text-label-md text-on-surface font-semibold">Jangka
                                Waktu Pinjaman (Tenor)</label>
                            <span class="text-primary font-bold text-headline-md" id="tenureLabel">15 Tahun</span>
                        </div>
                        <input id="tenure" name="tenure" type="range" min="5" max="30" value="15" step="1"
                            class="w-full h-3 rounded-full neumorphic-inset bg-background appearance-none cursor-pointer accent-primary" />
                        <div class="flex justify-between text-[11px] text-on-surface-variant px-1">
                            <span>5 Tahun</span>
                            <span>15 Tahun</span>
                            <span>30 Tahun</span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Output Panel -->
            <div
                class="lg:col-span-1 neumorphic-outset rounded-[32px] p-8 md:p-10 bg-background flex flex-col justify-between text-center relative overflow-hidden">
                <!-- Decorative circle -->
                <div
                    class="absolute -top-16 -right-16 w-40 h-40 rounded-full neumorphic-inset opacity-30 pointer-events-none">
                </div>

                <div class="z-10 space-y-8 flex-grow flex flex-col justify-center">
                    <div
                        class="w-16 h-16 neumorphic-outset rounded-2xl flex items-center justify-center mx-auto text-primary">
                        <span class="material-symbols-outlined text-3xl">calculate</span>
                    </div>

                    <!-- Main Installment Display in Inset Card -->
                    <div class="p-6 rounded-[24px] neumorphic-inset bg-background">
                        <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-2">
                            Estimasi Angsuran Bulanan</h3>
                        <div class="text-headline-lg md:text-headline-lg lg:text-headline-lg font-bold text-primary leading-tight my-2"
                            id="resultInstallment">Rp 3.332.000</div>
                        <p class="text-[11px] text-on-surface-variant/80 font-medium">Flat / Bunga Anuitas Efektif</p>
                    </div>

                    <!-- Detail list with subtle divider lines -->
                    <div class="space-y-4 text-left px-2 sm:px-4">
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-2 border-b border-surface-dark-shadow/30 gap-1 sm:gap-0">
                            <span class="text-label-md text-on-surface-variant">Pokok Pinjaman (KPR)</span>
                            <span class="font-bold text-on-surface text-right w-full sm:w-auto" id="resultPrincipal">Rp
                                360.000.000</span>
                        </div>
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-2 border-b border-surface-dark-shadow/30 gap-1 sm:gap-0">
                            <span class="text-label-md text-on-surface-variant">Total Uang Muka (DP)</span>
                            <span class="font-bold text-on-surface text-right w-full sm:w-auto" id="resultDp">Rp
                                90.000.000</span>
                        </div>
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-2 border-b border-surface-dark-shadow/30 gap-1 sm:gap-0">
                            <span class="text-label-md text-on-surface-variant">Total Bunga Dibayar</span>
                            <span class="font-bold text-tertiary-container text-right w-full sm:w-auto"
                                id="resultInterest">Rp 239.760.000</span>
                        </div>
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center py-2 gap-1 sm:gap-0 mt-2">
                            <span class="text-label-md text-on-surface-variant font-semibold">Total Pembayaran KPR</span>
                            <span class="font-bold text-primary text-right w-full sm:w-auto" id="resultTotalPay">Rp
                                599.760.000</span>
                        </div>
                    </div>
                </div>

                <div class="pt-8 z-10 w-full mt-auto">
                    <a href="{{ url('/prediksi') }}"
                        class="w-full py-3.5 bg-background text-primary neumorphic-outset font-bold text-body-md rounded-2xl flex items-center justify-center gap-3 transition-all duration-300 hover:scale-[1.02] active:scale-95 active:shadow-inner">
                        <span class="material-symbols-outlined">refresh</span>
                        <span>Prediksi Rumah Lain</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hargaInput = document.getElementById('harga');
            const hargaSlider = document.getElementById('hargaSlider');
            const dpPercentInput = document.getElementById('dpPercent');
            const interestInput = document.getElementById('interest');
            const tenureSlider = document.getElementById('tenure');

            const hargaLabel = document.getElementById('hargaLabel');
            const dpLabel = document.getElementById('dpLabel');
            const interestLabel = document.getElementById('interestLabel');
            const tenureLabel = document.getElementById('tenureLabel');

            const resultInstallment = document.getElementById('resultInstallment');
            const resultPrincipal = document.getElementById('resultPrincipal');
            const resultDp = document.getElementById('resultDp');
            const resultInterest = document.getElementById('resultInterest');
            const resultTotalPay = document.getElementById('resultTotalPay');

            function formatRupiah(value) {
                return 'Rp ' + Math.round(value).toLocaleString('id-ID');
            }

            function calculateKPR() {
                let harga = parseFloat(hargaInput.value) || 0;
                let dpPercent = parseFloat(dpPercentInput.value) || 0;
                let interestRate = parseFloat(interestInput.value) || 0;
                let tenureYears = parseInt(tenureSlider.value) || 1;

                // Constrain Down Payment % between 10% and 90%
                if (dpPercent < 10) dpPercent = 10;
                if (dpPercent > 90) dpPercent = 90;

                let dpValue = harga * (dpPercent / 100);
                let principal = harga - dpValue;

                // Monthly interest rate
                let monthlyRate = (interestRate / 100) / 12;
                let totalMonths = tenureYears * 12;
                let monthlyInstallment = 0;

                if (monthlyRate === 0) {
                    monthlyInstallment = principal / totalMonths;
                } else {
                    monthlyInstallment = principal * (monthlyRate * Math.pow(1 + monthlyRate, totalMonths)) / (Math.pow(1 + monthlyRate, totalMonths) - 1);
                }

                let totalPayment = monthlyInstallment * totalMonths;
                let totalInterest = totalPayment - principal;

                // Update Labels
                hargaLabel.innerText = formatRupiah(harga);
                dpLabel.innerText = formatRupiah(dpValue) + ' (' + dpPercent + '%)';
                interestLabel.innerText = interestRate.toFixed(1) + '%';
                tenureLabel.innerText = tenureYears + ' Tahun';

                // Update Results
                resultInstallment.innerText = formatRupiah(monthlyInstallment);
                resultPrincipal.innerText = formatRupiah(principal);
                resultDp.innerText = formatRupiah(dpValue);
                resultInterest.innerText = formatRupiah(totalInterest);
                resultTotalPay.innerText = formatRupiah(totalPayment);
            }

            // Connect input elements to events
            hargaInput.addEventListener('input', (e) => {
                hargaSlider.value = e.target.value;
                calculateKPR();
            });

            hargaSlider.addEventListener('input', (e) => {
                hargaInput.value = e.target.value;
                calculateKPR();
            });

            dpPercentInput.addEventListener('input', calculateKPR);
            interestInput.addEventListener('input', calculateKPR);
            tenureSlider.addEventListener('input', calculateKPR);

            // Run calculation on page load
            calculateKPR();
        });
    </script>
@endpush