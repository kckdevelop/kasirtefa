<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - TEFa & Inventaris Alat</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Outfit', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            50: '#f0f4f8',
                            100: '#d9e2ec',
                            500: '#334e68',
                            800: '#102a43',
                            900: '#0b1b2b',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js & FontAwesome icons -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery & Toastr CSS/JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    @yield('styles')
</head>
<body class="h-full text-slate-800 antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed inset-y-0 left-0 z-50 w-64 bg-navy-900 text-white transition-transform duration-300 ease-in-out md:static md:translate-x-0 flex flex-col shadow-xl">
            <!-- Brand Logo -->
            <div class="h-16 flex items-center px-6 bg-navy-800 border-b border-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center font-bold text-lg justify-center shadow-lg shadow-blue-500/30 text-white">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-base leading-tight tracking-wide">TEFa & ALAT</h1>
                        <p class="text-xs text-blue-300 font-medium">Teaching Factory System</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-6">
                <!-- Group: Main -->
                <div>
                    <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Utama</p>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Group: TEFa & POS Kasir -->
                <div>
                    <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Modul TEFa</p>
                    <a href="{{ route('tefa.kasir') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all mb-1 {{ request()->routeIs('tefa.kasir') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' : 'text-emerald-400 hover:bg-slate-800' }}">
                        <i class="fa-solid fa-cash-register w-5 text-center"></i>
                        <span class="font-semibold">POS Kasir</span>
                    </a>
                    <a href="{{ route('tefa.kategori.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tefa.kategori.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-tags w-5 text-center"></i>
                        <span>Kategori Produk</span>
                    </a>
                    <a href="{{ route('tefa.produk.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tefa.produk.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-box-open w-5 text-center"></i>
                        <span>Daftar Produk</span>
                    </a>
                    <a href="{{ route('tefa.stok-masuk') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tefa.stok-masuk') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-file-import w-5 text-center"></i>
                        <span>Stok Masuk</span>
                    </a>
                    <a href="{{ route('tefa.stok-keluar') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tefa.stok-keluar') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center"></i>
                        <span>Stok Keluar</span>
                    </a>
                    <a href="{{ route('tefa.transaksi.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tefa.transaksi.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-receipt w-5 text-center"></i>
                        <span>Riwayat Penjualan</span>
                    </a>
                    <a href="{{ route('tefa.pelanggan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tefa.pelanggan.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span>Data Pelanggan</span>
                    </a>
                    <a href="{{ route('tefa.lisensi.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tefa.lisensi.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-key w-5 text-center"></i>
                        <span>Lisensi Aplikasi</span>
                    </a>
                    <a href="{{ route('tefa.reset-transaksi.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tefa.reset-transaksi.*') ? 'bg-rose-600 text-white' : 'text-rose-400 hover:bg-rose-950/40 hover:text-rose-300' }}">
                        <i class="fa-solid fa-rotate-left w-5 text-center"></i>
                        <span>Reset Transaksi</span>
                    </a>
                </div>

                <!-- Group: Alat & Barang & Peminjaman -->
                <div>
                    <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Modul Alat & Barang</p>
                    <a href="{{ route('alat.daftar.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('alat.daftar.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-wrench w-5 text-center"></i>
                        <span>Daftar Alat & Barang</span>
                    </a>
                    <a href="{{ route('alat.kategori.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('alat.kategori.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-layer-group w-5 text-center"></i>
                        <span>Kategori Alat & Barang</span>
                    </a>
                    <a href="{{ route('alat.peminjaman.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('alat.peminjaman.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-hand-holding-hand w-5 text-center"></i>
                        <span>Peminjaman Alat & Barang</span>
                    </a>
                    <a href="{{ route('alat.denda.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('alat.denda.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i>
                        <span>Denda Peminjaman</span>
                    </a>
                </div>

                <!-- Group: Laporan & Sistem -->
                <div>
                    <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Laporan & Pengaturan</p>
                    <a href="{{ route('laporan.penjualan') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('laporan.penjualan') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-column w-5 text-center"></i>
                        <span>Laporan Penjualan</span>
                    </a>
                    <a href="{{ route('laporan.peminjaman') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('laporan.peminjaman') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
                        <span>Laporan Peminjaman</span>
                    </a>
                    <a href="{{ route('laporan.inventaris') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('laporan.inventaris') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                        <span>Laporan Inventaris</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span>Manajemen User</span>
                    </a>
                    <a href="{{ route('pengaturan.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('pengaturan.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-sliders w-5 text-center"></i>
                        <span>Pengaturan</span>
                    </a>
                </div>
            </nav>

            <!-- User Footer Profile -->
            <div class="p-4 border-t border-slate-700/50 bg-navy-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()?->foto_url ?? 'https://ui-avatars.com/api/?name=Admin' }}" class="w-9 h-9 rounded-full border border-blue-400 object-cover" alt="User Avatar">
                    <div class="truncate">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()?->nama ?? auth()->user()?->name }}</p>
                        <p class="text-xs text-blue-300 capitalize truncate">{{ auth()->user()?->getRoleNames()->first() ?? 'Admin' }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-rose-400 p-2 transition-colors" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-slate-50">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between sticky top-0 z-40 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-600 hover:text-slate-900 md:hidden">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-lg font-bold text-slate-800">@yield('title', 'Dashboard')</h2>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('tefa.kasir') }}" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs shadow-md shadow-emerald-600/20 transition-all">
                        <i class="fa-solid fa-cash-register"></i> Buka Kasir
                    </a>
                    
                    <!-- User Dropdown Info -->
                    <div class="flex items-center gap-3 border-l border-slate-200 pl-4">
                        <span class="text-xs font-semibold text-slate-600 hidden sm:inline">{{ auth()->user()?->nama ?? auth()->user()?->name }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "4000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        $(document).ready(function() {
            @if(session('success'))
                toastr.success("{!! addslashes(session('success')) !!}", "Berhasil");
            @endif

            @if(session('error'))
                toastr.error("{!! addslashes(session('error')) !!}", "Gagal");
            @endif

            @if(session('warning'))
                toastr.warning("{!! addslashes(session('warning')) !!}", "Peringatan");
            @endif

            @if(session('info'))
                toastr.info("{!! addslashes(session('info')) !!}", "Informasi");
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    toastr.error("{!! addslashes($error) !!}", "Kesalahan Input");
                @endforeach
            @endif
        });
    </script>

    @yield('scripts')
</body>
</html>
