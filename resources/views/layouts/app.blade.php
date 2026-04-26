<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Clinic Management System</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    @auth
    <nav class="bg-medical-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold">🏥 ClinicMS</a>
                    <a href="{{ route('dashboard') }}" class="hover:text-medical-100">Dashboard</a>
                    <a href="{{ route('patients.index') }}" class="hover:text-medical-100">Patients</a>
                    <a href="{{ route('medicines.index') }}" class="hover:text-medical-100">Inventory</a>
                    <a href="{{ route('staff.index') }}" class="hover:text-medical-100">Staff</a>
                    <a href="{{ route('scan.index') }}" class="hover:text-medical-100">📷 Scan</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">{{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-medical-600 hover:bg-medical-500 px-4 py-2 rounded-lg text-sm">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth
    <main class="max-w-7xl mx-auto px-4 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>