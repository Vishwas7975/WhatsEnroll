<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsEnroll Admin — @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">

    <div class="flex h-screen">

        {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
        <div class="w-64 bg-blue-900 text-white flex flex-col">
            <div class="p-6 border-b border-blue-700">
                <h1 class="text-xl font-bold">WhatsEnroll</h1>
                <p class="text-blue-300 text-sm">Admin Panel</p>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-tachometer-alt w-4"></i> Dashboard
                </a>
                <a href="{{ route('admin.enrollments.index') }}"
                   class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.enrollments.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-user-graduate w-4"></i> Enrollments
                </a>
                <a href="{{ route('admin.payments.pending') }}"
                   class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.payments.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-money-bill w-4"></i> Payments
                </a>
                <a href="{{ route('admin.courses.index') }}"
                   class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.courses.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-book w-4"></i> Courses
                </a>
                <a href="{{ route('admin.broadcast.index') }}"
                   class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.broadcast.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-bullhorn w-4"></i> Broadcast
                </a>
            </nav>
            <div class="p-4 border-t border-blue-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 w-full text-left">
                        <i class="fas fa-sign-out-alt w-4"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Main Content ─────────────────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-700">@yield('title')</h2>
                <span class="text-gray-500 text-sm">{{ auth()->user()->name }}</span>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>

    </div>

    @yield('scripts')

</body>
</html>