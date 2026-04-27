<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ClinicMS</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS (CDN for instant styling) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="hidden w-64 flex-shrink-0 flex-col bg-white border-r border-gray-200 md:flex transition-all duration-300">
            <div class="h-16 flex items-center justify-center border-b border-gray-100">
                <h1 class="text-xl font-bold text-brand-600 tracking-tight">
                    <i class="fa-solid fa-staff-snake mr-2"></i>ClinicMS
                </h1>
            </div>
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-3">
                    <li>
                        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->is('dashboard') ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <i class="fa-solid fa-chart-line w-5 text-center"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/medicines') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fa-solid fa-pills w-5 text-center"></i> Medicines
                        </a>
                    </li>
                   
                    <li>
                        <a href="{{ url('/patients') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fa-solid fa-user-injured w-5 text-center"></i> Patients
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/staff') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <i class="fa-solid fa-user-doctor w-5 text-center"></i> Staff
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="border-t border-gray-100 p-4">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=Dr+Smith&background=0D8ABC&color=fff" alt="" class="h-8 w-8 rounded-full">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Dr. Smith</p>
                        <p class="text-xs text-gray-500">Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6">
                <div class="flex items-center md:hidden">
                    <button class="text-gray-500 focus:outline-none">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
                <div class="flex items-center gap-4 ml-auto">
                    <button class="relative rounded-full bg-gray-50 p-1 text-gray-400 hover:text-gray-500">
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                        <i class="fa-solid fa-bell"></i>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>