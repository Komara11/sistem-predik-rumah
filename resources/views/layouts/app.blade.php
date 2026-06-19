<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PrediksiRumah Majalengka - Estimasi Harga Properti Akurat')</title>
    
    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-on-surface flex flex-col min-h-screen">

    <!-- TopNavBar -->
    <header class="w-full sticky top-0 z-50 glass-nav shadow-[16px_16px_32px_#D1D9E6,-16px_-16px_32px_#FFFFFF]">
        <nav class="flex justify-between items-center px-margin-desktop py-4 w-full max-w-container-max mx-auto">
            <a href="{{ url('/') }}" class="font-headline-md text-headline-md font-bold text-primary">PrediksiRumah</a>
            <div class="hidden md:flex gap-8 items-center">
                <a class="{{ request()->is('/') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant font-medium hover:text-primary' }} font-body-md text-body-md cursor-pointer transition-all duration-300"
                    href="{{ url('/') }}">Home</a>
                <a class="{{ request()->is('prediksi*') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant font-medium hover:text-primary' }} font-body-md text-body-md cursor-pointer transition-all duration-300"
                    href="{{ url('/prediksi') }}">Prediksi Harga</a>
                <a class="{{ request()->is('kalkulator-kpr*') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant font-medium hover:text-primary' }} font-body-md text-body-md cursor-pointer transition-all duration-300"
                    href="{{ url('/kalkulator-kpr') }}">Kalkulator KPR</a>
            </div>
            <!-- Mobile Toggle -->
            <button class="md:hidden p-2 neumorphic-outset rounded-lg active:scale-95 transition-all z-50 relative" id="mobile-menu-toggle">
                <span class="material-symbols-outlined" id="mobile-menu-icon">menu</span>
            </button>
        </nav>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="absolute top-full left-0 right-0 mx-margin-mobile mt-2 p-6 rounded-3xl neumorphic-outset bg-background/95 backdrop-blur-md z-40 flex flex-col gap-3 opacity-0 pointer-events-none transform -translate-y-2 transition-all duration-300 md:hidden">
            <a class="{{ request()->is('/') ? 'text-primary font-bold neumorphic-inset' : 'text-on-surface hover:text-primary' }} font-body-md text-body-md p-3 rounded-xl transition-all duration-200" href="{{ url('/') }}">Home</a>
            <a class="{{ request()->is('prediksi*') ? 'text-primary font-bold neumorphic-inset' : 'text-on-surface hover:text-primary' }} font-body-md text-body-md p-3 rounded-xl transition-all duration-200" href="{{ url('/prediksi') }}">Prediksi Harga</a>
            <a class="{{ request()->is('kalkulator-kpr*') ? 'text-primary font-bold neumorphic-inset' : 'text-on-surface hover:text-primary' }} font-body-md text-body-md p-3 rounded-xl transition-all duration-200" href="{{ url('/kalkulator-kpr') }}">Kalkulator KPR</a>
        </div>
    </header>

    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="w-full mt-auto bg-background shadow-[16px_16px_32px_#D1D9E6,-16px_-16px_32px_#FFFFFF]">
        <div class="flex flex-col md:flex-row justify-between items-center px-margin-desktop py-12 w-full max-w-container-max mx-auto">
            <div class="flex flex-col gap-4 mb-8 md:mb-0">
                <div class="font-headline-md text-headline-md text-primary font-bold">PrediksiRumah</div>
                <p class="font-label-sm text-label-sm text-secondary max-w-xs">
                    Platform prediksi harga properti cerdas untuk membantu masyarakat Majalengka dalam estimasi nilai aset.
                </p>
            </div>
            <div class="flex flex-col items-center md:items-end gap-6">
                <div class="flex gap-8">
                    <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-secondary transition-colors" href="#">Privacy Policy</a>
                    <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-secondary transition-colors" href="#">Terms of Service</a>
                    <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-secondary transition-colors" href="#">Contact</a>
                </div>
                <div class="font-label-sm text-label-sm text-secondary opacity-75">
                    © {{ date('Y') }} PrediksiRumah - Universitas Muhammadiyah Cirebon (UMC)
                </div>
            </div>
        </div>
    </footer>

    <!-- Global Modal (Hidden by default) -->
    <div id="method-modal" class="fixed inset-0 z-[100] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300 px-4">
        <div class="absolute inset-0 bg-surface-dark-shadow/60 backdrop-blur-sm" id="modal-backdrop"></div>
        <div class="relative w-full max-w-2xl bg-background rounded-3xl p-8 neumorphic-outset transform scale-95 transition-transform duration-300 shadow-2xl overflow-y-auto max-h-[90vh]" id="modal-content">
            <button id="close-modal" class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center neumorphic-outset rounded-full text-on-surface-variant hover:text-error-red transition-colors active:scale-95">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h2 class="font-headline-lg text-headline-lg text-primary mb-4">Metode Random Forest</h2>
            <div class="space-y-4 font-body-md text-on-surface-variant">
                <p>Sistem ini menggunakan algoritma <strong>Machine Learning Random Forest</strong> untuk memprediksi harga properti di Kabupaten Majalengka.</p>
                <div class="p-6 neumorphic-inset rounded-2xl bg-background my-6">
                    <h3 class="font-headline-md text-on-surface font-bold mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">psychology</span> Bagaimana cara kerjanya?
                    </h3>
                    <ul class="list-disc list-inside space-y-2 ml-2">
                        <li>Sistem mengumpulkan ratusan data historis rumah di Majalengka.</li>
                        <li>Algoritma membangun <strong>100 "Pohon Keputusan" (Decision Trees)</strong> secara paralel.</li>
                        <li>Setiap pohon membuat tebakan harganya sendiri berdasarkan fitur seperti luas tanah, usia bangunan, dan lokasi.</li>
                        <li>Hasil akhir adalah rata-rata dari ke-100 pohon tersebut, yang menghasilkan prediksi sangat akurat dan tahan terhadap outlier (data anomali).</li>
                    </ul>
                </div>
                <p>Dengan memadukan data nyata dari pasar lokal dan kecerdasan buatan, sistem ini memberikan interval harga yang wajar untuk membantu keputusan Anda.</p>
            </div>
            <div class="mt-8 text-center">
                <button id="btn-mengerti" class="px-8 py-3 neumorphic-button-primary rounded-xl font-bold text-white transition-transform active:scale-95">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Global Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Smooth hover interactions for Neumorphic buttons
            document.querySelectorAll('button').forEach(button => {
                button.addEventListener('mousedown', function () {
                    if (!this.classList.contains('neumorphic-button-primary')) {
                        this.style.boxShadow = 'inset 4px 4px 8px #D1D9E6, inset -4px -4px 8px #FFFFFF';
                    }
                });
                button.addEventListener('mouseup', function () {
                    if (!this.classList.contains('neumorphic-button-primary')) {
                        this.style.boxShadow = '';
                    }
                });
            });

            // Mobile menu logic
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuIcon = document.getElementById('mobile-menu-icon');
            
            if (mobileMenuToggle && mobileMenu) {
                mobileMenuToggle.addEventListener('click', () => {
                    const isOpen = mobileMenu.classList.contains('opacity-100');
                    if (isOpen) {
                        mobileMenu.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
                        mobileMenu.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
                        mobileMenuIcon.textContent = 'menu';
                    } else {
                        mobileMenu.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2');
                        mobileMenu.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
                        mobileMenuIcon.textContent = 'close';
                    }
                });
            }

            // Modal Logic
            const methodModal = document.getElementById('method-modal');
            const modalContent = document.getElementById('modal-content');
            const btnCloseModal = document.getElementById('close-modal');
            const btnMengerti = document.getElementById('btn-mengerti');
            const backdrop = document.getElementById('modal-backdrop');

            window.openMethodModal = function() {
                methodModal.classList.remove('opacity-0', 'pointer-events-none');
                methodModal.classList.add('opacity-100', 'pointer-events-auto');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
                document.body.style.overflow = 'hidden';
            }

            window.closeMethodModal = function() {
                methodModal.classList.remove('opacity-100', 'pointer-events-auto');
                methodModal.classList.add('opacity-0', 'pointer-events-none');
                modalContent.classList.remove('scale-100');
                modalContent.classList.add('scale-95');
                document.body.style.overflow = '';
            }

            if(btnCloseModal) btnCloseModal.addEventListener('click', closeMethodModal);
            if(btnMengerti) btnMengerti.addEventListener('click', closeMethodModal);
            if(backdrop) backdrop.addEventListener('click', closeMethodModal);

        });
    </script>
    @stack('scripts')
</body>
</html>
