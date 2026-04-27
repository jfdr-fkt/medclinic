@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
            <p class="text-sm text-gray-500">Welcome back, here's what's happening today.</p>
        </div>
        <div class="text-right hidden sm:block">
            <p class="text-sm font-medium text-gray-900">{{ date('l, F j, Y') }}</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Patients Card -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-blue-500 p-3">
                        <i class="fa-solid fa-user-group text-white text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Patients Today</dt>
                            <dd class="text-2xl font-bold text-gray-900">12</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium"><i class="fa-solid fa-arrow-up"></i> 4.5%</span>
                    <span class="text-gray-500 ml-1">from yesterday</span>
                </div>
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-red-500 p-3">
                        <i class="fa-solid fa-triangle-exclamation text-white text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Low Stock Items</dt>
                            <dd class="text-2xl font-bold text-red-600">3</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-red-600 font-medium">Action Required</span>
                </div>
            </div>
        </div>

        <!-- Expiring Soon Card -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-orange-500 p-3">
                        <i class="fa-solid fa-calendar-xmark text-white text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Expiring Soon</dt>
                            <dd class="text-2xl font-bold text-gray-900">5</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-500">Within 30 days</span>
                </div>
            </div>
        </div>

        <!-- Staff Online Card -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-green-500 p-3">
                        <i class="fa-solid fa-user-nurse text-white text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Staff Online</dt>
                            <dd class="text-2xl font-bold text-gray-900">4</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium">Active Now</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lower Section Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Low Stock Table -->
        <div class="lg:col-span-2 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center bg-red-50/50">
                <h3 class="text-base font-semibold leading-6 text-red-700">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Low Stock Medicines
                </h3>
                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">3 Items</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Amoxicillin 500mg</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">B-2024-001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12 Units</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Critical</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Paracetamol Syrup</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">B-2024-042</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5 Bottles</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800">Low</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Ibuprofen 400mg</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">B-2024-112</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">20 Units</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">Restock</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Online Staff List -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center bg-green-50/50">
                <h3 class="text-base font-semibold leading-6 text-green-700">
                    <i class="fa-solid fa-circle-check mr-1"></i> Online Staff
                </h3>
                <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
            </div>
            <ul role="list" class="divide-y divide-gray-200">
                <li class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Sarah+J&background=random" alt="">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Sarah Johnson</p>
                            <p class="text-xs text-gray-500">Front Desk</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Active</span>
                </li>
                <li class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Mike+R&background=random" alt="">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Mike Ross</p>
                            <p class="text-xs text-gray-500">Pharmacist</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Active</span>
                </li>
                <li class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Emily+C&background=random" alt="">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Emily Chen</p>
                            <p class="text-xs text-gray-500">Nurse</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Active</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection