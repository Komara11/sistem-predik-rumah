@extends('layouts.app')

@section('title', 'Estimasi Harga Rumah - PrediksiRumah Majalengka')

@section('content')
<main class="min-h-screen py-16 px-4 md:px-0">
    <div class="max-w-4xl mx-auto">
        <!-- Hero Title -->
        <div class="text-center mb-12">
            <h1 class="font-headline-lg text-headline-lg text-text-heading mb-4">Estimasi Harga Rumah</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Lengkapi detail properti Anda untuk mendapatkan prediksi harga pasar yang akurat.</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
        <div class="mb-8 p-6 rounded-2xl neumorphic-inset bg-background border-l-4 border-error-red">
            <div class="flex items-center gap-2 mb-2 text-error-red">
                <span class="material-symbols-outlined">error</span>
                <span class="font-bold">Terjadi Kesalahan</span>
            </div>
            <ul class="list-disc list-inside text-label-md text-on-surface-variant space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        <!-- Form Card -->
        <div class="neumorphic-outset rounded-3xl p-8 md:p-12 bg-background">
            <form class="space-y-10" id="predictionForm" action="{{ url('/prediksi/hasil') }}" method="GET">
                <!-- Properti Section -->
                <section>
                    <div class="flex items-center gap-2 mb-6 text-primary">
                        <span class="material-symbols-outlined">home</span>
                        <h2 class="font-headline-md text-headline-md">Detail Properti</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                        <div class="space-y-2">
                            <label class="font-label-md text-label-md ml-1" for="luas_tanah">Luas Tanah (m²)</label>
                            <input
                                id="luas_tanah"
                                name="luas_tanah"
                                class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md"
                                placeholder="Contoh: 120" type="number" required min="1"
                                value="{{ old('luas_tanah') }}" />
                        </div>
                        <div class="space-y-2">
                            <label class="font-label-md text-label-md ml-1" for="luas_bangunan">Luas Bangunan (m²)</label>
                            <input
                                id="luas_bangunan"
                                name="luas_bangunan"
                                class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md"
                                placeholder="Contoh: 80" type="number" required min="1"
                                value="{{ old('luas_bangunan') }}" />
                        </div>
                        <div class="space-y-2">
                            <label class="font-label-md text-label-md ml-1" for="kmr_tidur">Kamar Tidur</label>
                            <input
                                id="kmr_tidur"
                                name="kmr_tidur"
                                class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md"
                                placeholder="Jumlah Kamar Tidur" type="number" required min="1" max="10"
                                value="{{ old('kmr_tidur') }}" />
                        </div>
                        <div class="space-y-2">
                            <label class="font-label-md text-label-md ml-1" for="kmr_mandi">Kamar Mandi</label>
                            <input
                                id="kmr_mandi"
                                name="kmr_mandi"
                                class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md"
                                placeholder="Jumlah Kamar Mandi" type="number" required min="1" max="5"
                                value="{{ old('kmr_mandi') }}" />
                        </div>
                    </div>
                </section>
                
                <hr class="border-none h-[2px] bg-gradient-to-r from-transparent via-surface-dark-shadow to-transparent opacity-50" />
                
                <!-- Lokasi Section -->
                <section>
                    <div class="flex items-center gap-2 mb-6 text-primary">
                        <span class="material-symbols-outlined">location_on</span>
                        <h2 class="font-headline-md text-headline-md">Lokasi &amp; Tipe</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                        <div class="space-y-2">
                            <label class="font-label-md text-label-md ml-1" for="lokasi">Kecamatan (Kab. Majalengka)</label>
                            <div class="relative">
                                <select
                                    id="lokasi"
                                    name="lokasi"
                                    class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md appearance-none" required>
                                    <option disabled {{ old('lokasi') ? '' : 'selected' }} value="">Pilih Kecamatan</option>
                                    <option value="Majalengka" {{ old('lokasi') == 'Majalengka' ? 'selected' : '' }}>Majalengka</option>
                                    <option value="Jatiwangi" {{ old('lokasi') == 'Jatiwangi' ? 'selected' : '' }}>Jatiwangi</option>
                                    <option value="Kertajati" {{ old('lokasi') == 'Kertajati' ? 'selected' : '' }}>Kertajati</option>
                                    <option value="Sumberjaya" {{ old('lokasi') == 'Sumberjaya' ? 'selected' : '' }}>Sumberjaya</option>
                                    <option value="Ligung" {{ old('lokasi') == 'Ligung' ? 'selected' : '' }}>Ligung</option>
                                    <option value="Argapura" {{ old('lokasi') == 'Argapura' ? 'selected' : '' }}>Argapura</option>
                                    <option value="Lainnya" {{ old('lokasi') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">expand_more</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="font-label-md text-label-md ml-1" for="jarak">Jarak ke Pusat Kota (km)</label>
                            <input
                                id="jarak"
                                name="jarak"
                                class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md"
                                placeholder="Opsional jika bukan 'Lainnya'" type="number" step="0.1" min="0"
                                value="{{ old('jarak') }}" />
                        </div>
                        <div class="space-y-2">
                            <label class="font-label-md text-label-md ml-1" for="tipe_properti">Tipe Properti</label>
                            <div class="relative">
                                <select
                                    id="tipe_properti"
                                    name="tipe_properti"
                                    class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md appearance-none" required>
                                    <option disabled {{ old('tipe_properti') ? '' : 'selected' }} value="">Pilih Tipe</option>
                                    <option value="Subsidi" {{ old('tipe_properti') == 'Subsidi' ? 'selected' : '' }}>Subsidi</option>
                                    <option value="Minimalis" {{ old('tipe_properti') == 'Minimalis' ? 'selected' : '' }}>Minimalis</option>
                                    <option value="Mewah" {{ old('tipe_properti') == 'Mewah' ? 'selected' : '' }}>Mewah</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">expand_more</span>
                            </div>
                        </div>
                    </div>
                </section>
                
                <hr class="border-none h-[2px] bg-gradient-to-r from-transparent via-surface-dark-shadow to-transparent opacity-50" />
                
                <!-- Informasi Tambahan Section -->
                <section>
                    <div class="flex items-center gap-2 mb-6 text-primary">
                        <span class="material-symbols-outlined">stars</span>
                        <h2 class="font-headline-md text-headline-md">Informasi Tambahan</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="font-label-md text-label-md ml-1 block" for="usia">Usia Bangunan (tahun)</label>
                                <input
                                    id="usia"
                                    name="usia"
                                    class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md"
                                    placeholder="Contoh: 5" type="number" required min="0" max="50"
                                    value="{{ old('usia') }}" />
                            </div>
                            <div class="space-y-2">
                                <label class="font-label-md text-label-md ml-1 block" for="kondisi">Kondisi Bangunan</label>
                                <div class="relative">
                                    <select
                                        id="kondisi"
                                        name="kondisi"
                                        class="w-full px-4 py-3 rounded-xl neumorphic-inset bg-background border-none focus:ring-1 focus:ring-primary outline-none text-body-md appearance-none" required>
                                        <option disabled {{ old('kondisi') ? '' : 'selected' }} value="">Pilih Kondisi</option>
                                        <option value="Baru" {{ old('kondisi') == 'Baru' ? 'selected' : '' }}>Baru</option>
                                        <option value="Bekas" {{ old('kondisi') == 'Bekas' ? 'selected' : '' }}>Bekas</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">expand_more</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col justify-center space-y-4 pl-0 md:pl-8">
                            <label class="flex items-center gap-4 cursor-pointer group">
                                <input class="neumorphic-checkbox" type="checkbox" name="ada_garasi" value="1" {{ old('ada_garasi') ? 'checked' : '' }} />
                                <span class="font-body-md text-body-md text-on-surface group-hover:text-primary transition-colors">Tersedia Garasi</span>
                            </label>
                        </div>
                    </div>
                </section>
                
                <!-- Submit Button -->
                <div class="pt-8">
                    <button
                        class="w-full py-5 rounded-2xl bg-background neumorphic-outset text-primary font-bold text-headline-md flex items-center justify-center gap-3 transition-all duration-300 hover:scale-[1.02] active:scale-95 active:shadow-inner"
                        id="submitBtn" type="submit">
                        <span class="material-symbols-outlined">calculate</span>
                        Hitung Estimasi Harga
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Descriptive Image Section -->
        <div class="mt-16 rounded-3xl overflow-hidden neumorphic-outset">
            <img alt="Real Estate Analysis" class="w-full h-80 object-cover" src="{{ asset('images/real-estate-analysis.png') }}" />
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('predictionForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', (e) => {
            // Add visual feedback for button press
            submitBtn.style.boxShadow = 'inset 4px 4px 8px #D1D9E6, inset -4px -4px 8px #FFFFFF';
            submitBtn.innerHTML = `
                <span class="material-symbols-outlined animate-spin">sync</span>
                Memproses Prediksi...
            `;
            
            // Allow form to submit and navigate
        });
    });
</script>
@endpush
