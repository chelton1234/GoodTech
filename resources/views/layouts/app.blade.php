<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GoodTech - Tecnologia de Ponta')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#2563eb', 600: '#1d4ed8', 700: '#1e40af', 800: '#1e3a8a', 900: '#1e3a5f' },
                        accent: { 500: '#ef4444', 600: '#dc2626' },
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .gradient-hero { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #1d4ed8 100%); }
        .gradient-card { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); }
        .glass { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <nav class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">GT</span>
                        </div>
                        <span class="text-xl font-bold text-brand-800">GoodTech</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium hover:text-brand-600 transition">Início</a>
                    <a href="{{ route('products.index') }}" class="text-sm font-medium hover:text-brand-600 transition">Produtos</a>
                    <a href="#" class="text-sm font-medium hover:text-brand-600 transition">Sobre Nós</a>
                    <a href="#" class="text-sm font-medium hover:text-brand-600 transition">Contacto</a>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-600 hover:text-brand-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <span class="absolute -top-1 -right-1 bg-accent-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center"
                              x-data x-init="$store.cartCount = {{ \Illuminate\Support\Facades\Session::get('cart', []) ? collect(\Illuminate\Support\Facades\Session::get('cart', []))->sum('quantity') : 0 }}"
                              x-text="$store.cartCount" x-cloak>0</span>
                    </a>

                    <a href="{{ route('admin.dashboard') }}" class="hidden md:inline-flex text-sm font-medium text-gray-600 hover:text-brand-600 transition">Admin</a>

                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t">
            <div class="px-4 py-3 space-y-2">
                <a href="{{ route('home') }}" class="block py-2 text-sm font-medium">Início</a>
                <a href="{{ route('products.index') }}" class="block py-2 text-sm font-medium">Produtos</a>
                <a href="{{ route('cart.index') }}" class="block py-2 text-sm font-medium">Carrinho</a>
                <a href="{{ route('admin.dashboard') }}" class="block py-2 text-sm font-medium">Admin</a>
            </div>
        </div>
    </nav>

    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    </div>
    @endif

    @yield('content')

    <footer class="bg-gray-900 text-gray-300 mt-20">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">GT</span>
                        </div>
                        <span class="text-lg font-bold text-white">GoodTech</span>
                    </div>
                    <p class="text-sm text-gray-400">Tecnologia de ponta ao seu alcance. Os melhores produtos com os melhores preços em Moçambique.</p>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Loja</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('products.index') }}" class="hover:text-white transition">Todos os Produtos</a></li>
                        <li><a href="#" class="hover:text-white transition">Ofertas</a></li>
                        <li><a href="#" class="hover:text-white transition">Novos Produtos</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Suporte</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition">Política de Devolução</a></li>
                        <li><a href="#" class="hover:text-white transition">Envio e Entrega</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Contacto</h3>
                    <ul class="space-y-2 text-sm">
                        <li>+258 84 000 0000</li>
                        <li>info@goodtech.co.mz</li>
                        <li>Maputo, Moçambique</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">&copy; {{ date('Y') }} GoodTech. Todos os direitos reservados.</p>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span>Pagamentos seguros via</span>
                    <span class="font-semibold text-white">Débito Pay</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
