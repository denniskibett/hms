@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="container-fluid px-0">
    <!-- Overview -->
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-800 dark:text-white/90">Payment Overview</h2>
            </div>
            <div>
                <button type="button" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition" data-toggle="modal" data-target="#recordPaymentModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M5 10.0002H15.0006M10.0002 5V15.0006" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Record Payment
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 rounded-xl border border-gray-200 sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-y-0 dark:divide-gray-800 dark:border-gray-800">
            <div class="border-b p-5 sm:border-r lg:border-b-0">
                <p class="mb-1.5 text-sm text-gray-400 dark:text-gray-500">Today's Payments</p>
                <h3 class="text-3xl text-gray-800 dark:text-white/90">KSH {{ number_format($todayPayments, 2) }}</h3>
            </div>
            <div class="border-b p-5 lg:border-b-0">
                <p class="mb-1.5 text-sm text-gray-400 dark:text-gray-500">This Week</p>
                <h3 class="text-3xl text-gray-800 dark:text-white/90">KSH {{ number_format($weekPayments, 2) }}</h3>
            </div>
            <div class="border-b p-5 sm:border-r sm:border-b-0">
                <p class="mb-1.5 text-sm text-gray-400 dark:text-gray-500">This Month</p>
                <h3 class="text-3xl text-gray-800 dark:text-white/90">KSH {{ number_format($monthPayments, 2) }}</h3>
            </div>
            <div class="p-5">
                <p class="mb-1.5 text-sm text-gray-400 dark:text-gray-500">Total Payments</p>
                <h3 class="text-3xl text-gray-800 dark:text-white/90">KSH {{ number_format($totalPayments, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="paymentTable()">
        <div class="flex flex-col justify-between gap-5 border-b border-gray-200 px-5 py-4 sm:flex-row lg:items-center dark:border-gray-800">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Transactions</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your most recent transactions list</p>
            </div>

            <div class="hidden flex-col gap-3 sm:flex sm:flex-row sm:items-center">
                <div class="relative">
                    <span class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""/>
                        </svg>
                    </span>
                    <input type="text" placeholder="Search..." x-model="searchQuery" @input="filterPayments()" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"/>
                </div>
                <div class="hidden lg:block">
                    <select x-model="methodFilter" @change="filterPayments()" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        <option value="">All Methods</option>
                        @foreach(\App\Models\Payment::getMethodOptions() as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button @click="exportPayments()" class="hover:text-dark-900 shadow-theme-xs relative flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 whitespace-nowrap text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M16.6661 13.3333V15.4166C16.6661 16.1069 16.1064 16.6666 15.4161 16.6666H4.58203C3.89168 16.6666 3.33203 16.1069 3.33203 15.4166V13.3333M10.0004 3.33325L10.0004 13.3333M6.14456 7.18708L9.9986 3.33549L13.8529 7.18708" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>
        </div>
        <div class="custom-scrollbar overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="border-b border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                        <th class="p-4 whitespace-nowrap">
                            <div class="flex w-full items-center gap-3">
                                <label class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                                    <span class="relative">
                                        <input type="checkbox" class="sr-only" x-model="selectAll" @change="toggleSelectAll()"/>
                                        <span :class="selectAll ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px]">
                                            <span :class="selectAll ? '' : 'opacity-0'">
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Reference</p>
                            </div>
                        </th>
                        <th class="p-4 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                            <div class="flex cursor-pointer items-center gap-3" @click="sortBy('date')">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Date & Time</p>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sort.key === 'date' && sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sort.key === 'date' && !sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                            <div class="flex cursor-pointer items-center gap-3" @click="sortBy('customer')">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Customer</p>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sort.key === 'customer' && sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sort.key === 'customer' && !sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                            <div class="flex cursor-pointer items-center gap-3" @click="sortBy('invoice')">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Invoice</p>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sort.key === 'invoice' && sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sort.key === 'invoice' && !sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                            <div class="flex cursor-pointer items-center gap-3" @click="sortBy('method')">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Method</p>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sort.key === 'method' && sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sort.key === 'method' && !sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                            <div class="flex cursor-pointer items-center gap-3" @click="sortBy('amount')">
                                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Amount</p>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sort.key === 'amount' && sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sort.key === 'amount' && !sort.asc ? 'text-gray-800 dark:text-gray-400' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Status</th>
                        <th class="p-4 text-left text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                            <div class="relative">
                                <span class="sr-only">Action</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-x divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($payments as $payment)
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="p-4 whitespace-nowrap">
                            <div class="group flex items-center gap-3">
                                <label class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                                    <span class="relative">
                                        <input type="checkbox" class="sr-only payment-checkbox" data-id="{{ $payment->id }}"/>
                                        <span class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px] border-gray-300 dark:border-gray-700 bg-transparent">
                                            <span class="opacity-0">
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                                <a href="{{ route('payments.show', $payment) }}" class="text-theme-xs font-medium text-gray-700 group-hover:underline dark:text-gray-400">
                                    {{ $payment->payment_reference }}
                                </a>
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $payment->received_at->format('M d, Y') }}<br>
                                <small class="text-gray-400">{{ $payment->received_at->format('h:i A') }}</small>
                            </p>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ $payment->invoice->stay->guest->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            @if($payment->invoice)
                            <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                            @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $payment->method)) }}
                            </span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <p class="text-sm font-medium {{ $payment->amount >= 0 ? 'text-success-600 dark:text-success-500' : 'text-error-600 dark:text-error-500' }}">
                                {{ $payment->amount >= 0 ? '+' : '-' }} KSH {{ number_format(abs($payment->amount), 2) }}
                            </p>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-theme-xs rounded-full px-2 py-0.5 font-medium 
                                {{ $payment->status === 'completed' ? 'bg-success-50 dark:bg-success-500/15 text-success-700 dark:text-success-500' : 
                                   ($payment->status === 'pending' ? 'bg-warning-50 dark:bg-warning-500/15 text-warning-700 dark:text-warning-500' : 
                                   'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="relative flex justify-center">
                                <button class="text-gray-500 dark:text-gray-400 dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""/>
                                    </svg>
                                </button>
                                <div class="dropdown-menu shadow-theme-lg dark:bg-gray-dark fixed w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">
                                    <a href="{{ route('payments.show', $payment) }}" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                        View More
                                    </a>
                                    <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                        Download Receipt
                                    </a>
                                    @if($payment->amount > 0 && $payment->created_at->diffInHours(now()) <= 24)
                                    <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-red-500 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this payment?')">
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            <div class="flex justify-center pb-4 sm:hidden">
                <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                    Showing
                    <span class="text-gray-800 dark:text-white/90">{{ $payments->firstItem() }}</span>
                    to
                    <span class="text-gray-800 dark:text-white/90">{{ $payments->lastItem() }}</span>
                    of
                    <span class="text-gray-800 dark:text-white/90">{{ $payments->total() }}</span>
                </span>
            </div>

            <div class="flex items-center justify-between">
                <div class="hidden sm:block">
                    <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                        Showing
                        <span class="text-gray-800 dark:text-white/90">{{ $payments->firstItem() }}</span>
                        to
                        <span class="text-gray-800 dark:text-white/90">{{ $payments->lastItem() }}</span>
                        of
                        <span class="text-gray-800 dark:text-white/90">{{ $payments->total() }}</span>
                    </span>
                </div>
                <div class="flex w-full items-center justify-between gap-2 rounded-lg bg-gray-50 p-4 sm:w-auto sm:justify-normal sm:rounded-none sm:bg-transparent sm:p-0 dark:bg-gray-900 dark:sm:bg-transparent">
                    @if($payments->previousPageUrl())
                    <a href="{{ $payments->previousPageUrl() }}" class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z" fill=""/>
                        </svg>
                    </a>
                    @endif

                    <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400">
                        Page {{ $payments->currentPage() }} of {{ $payments->lastPage() }}
                    </span>

                    <ul class="hidden items-center gap-0.5 sm:flex">
                        @foreach($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
                        @if($page == $payments->currentPage())
                        <li>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500 text-sm font-medium text-white">
                                {{ $page }}
                            </span>
                        </li>
                        @else
                        <li>
                            <a href="{{ $url }}" class="hover:bg-brand-500 flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium text-gray-700 hover:text-white dark:text-gray-400 dark:hover:text-white">
                                {{ $page }}
                            </a>
                        </li>
                        @endif
                        @endforeach
                    </ul>

                    @if($payments->nextPageUrl())
                    <a href="{{ $payments->nextPageUrl() }}" class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z" fill=""/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Payment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Invoice</label>
                                <select class="form-control select2" name="invoice_id" id="invoiceSelect" required>
                                    <option value="">-- Search Invoice --</option>
                                    @foreach($invoices as $invoice)
                                    <option value="{{ $invoice->id }}" 
                                            data-due="{{ $invoice->due_amount }}"
                                            data-guest="{{ $invoice->stay->guest->name ?? 'N/A' }}">
                                        {{ $invoice->invoice_number }} - {{ $invoice->stay->guest->name ?? 'N/A' }} (Due: KSH {{ number_format($invoice->due_amount, 2) }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select class="form-control" name="method" id="paymentMethod" required>
                                    @foreach(\App\Models\Payment::getMethodOptions() as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Amount (KSH)</label>
                                <input type="number" class="form-control" name="amount" id="paymentAmount" 
                                       step="0.01" min="0.01" required>
                                <small class="text-muted">Due amount: <span id="dueAmount">KSH 0.00</span></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="datetime-local" class="form-control" name="received_at" 
                                       value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details (Dynamic based on method) -->
                    <div id="paymentDetailsSection">
                        <div class="form-group">
                            <label>Payment Details (Optional)</label>
                            <textarea class="form-control" name="payment_details" rows="2" 
                                      placeholder="Reference number, mobile number, transaction ID, etc."></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>

                    <!-- Payment Summary -->
                    <div class="alert alert-info" id="paymentSummary">
                        <i class="fas fa-info-circle"></i>
                        Select an invoice to see payment summary
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function paymentTable() {
    return {
        searchQuery: '',
        methodFilter: '',
        dateFilter: '',
        selected: [],
        selectAll: false,
        sort: { key: 'date', asc: false },
        
        filterPayments() {
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = window.location.pathname;
            
            if (this.searchQuery) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'search';
                input.value = this.searchQuery;
                form.appendChild(input);
            }
            
            if (this.methodFilter) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'method';
                input.value = this.methodFilter;
                form.appendChild(input);
            }
            
            if (this.dateFilter) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'date_filter';
                input.value = this.dateFilter;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        },
        
        exportPayments() {
            const params = new URLSearchParams();
            if (this.searchQuery) params.append('search', this.searchQuery);
            if (this.methodFilter) params.append('method', this.methodFilter);
            if (this.dateFilter) params.append('date_filter', this.dateFilter);
            
            window.location.href = `{{ route('payments.create') }}?${params.toString()}`;
        },
        
        sortBy(key) {
            if (this.sort.key === key) {
                this.sort.asc = !this.sort.asc;
            } else {
                this.sort.key = key;
                this.sort.asc = true;
            }
        },
        
        toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            const isChecked = !this.selectAll;
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const id = checkbox.dataset.id;
                if (isChecked && !this.selected.includes(id)) {
                    this.selected.push(id);
                } else if (!isChecked) {
                    this.selected = this.selected.filter(i => i !== id);
                }
            });
            
            this.selectAll = isChecked;
        }
    };
}

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Search invoice...'
    });

    // Update payment amount when invoice is selected
    $('#invoiceSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const dueAmount = selectedOption.data('due') || 0;
        const guestName = selectedOption.data('guest') || 'N/A';
        
        $('#dueAmount').text('KSH ' + parseFloat(dueAmount).toFixed(2));
        $('#paymentAmount').val(dueAmount).attr('max', dueAmount);
        
        // Update summary
        $('#paymentSummary').html(`
            <strong>Payment Summary:</strong><br>
            Invoice: ${selectedOption.text()}<br>
            Guest: ${guestName}<br>
            Amount Due: KSH ${parseFloat(dueAmount).toFixed(2)}
        `);
    });

    // Show/hide payment details based on method
    $('#paymentMethod').on('change', function() {
        const method = $(this).val();
        let detailsHtml = '';
        
        switch(method) {
            case 'mobile_money':
                detailsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mobile Money Provider</label>
                                <select class="form-control" name="payment_details[provider]">
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="airtelmoney">Airtel Money</option>
                                    <option value="t-kash">T-Kash</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" class="form-control" name="payment_details[phone]" placeholder="0712345678">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Transaction Code</label>
                        <input type="text" class="form-control" name="payment_details[transaction_code]" placeholder="ABC123XYZ">
                    </div>
                `;
                break;
                
            case 'credit_card':
                detailsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Card Type</label>
                                <select class="form-control" name="payment_details[card_type]">
                                    <option value="visa">Visa</option>
                                    <option value="mastercard">Mastercard</option>
                                    <option value="amex">American Express</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last 4 Digits</label>
                                <input type="text" class="form-control" name="payment_details[last_four]" placeholder="1234" maxlength="4">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Authorization Code</label>
                        <input type="text" class="form-control" name="payment_details[auth_code]" placeholder="AUTH123">
                    </div>
                `;
                break;
                
            case 'bank_transfer':
                detailsHtml = `
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" class="form-control" name="payment_details[bank]" placeholder="Equity Bank">
                    </div>
                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" class="form-control" name="payment_details[reference]" placeholder="TRX123456">
                    </div>
                    <div class="form-group">
                        <label>Account Number (Optional)</label>
                        <input type="text" class="form-control" name="payment_details[account]" placeholder="1234567890">
                    </div>
                `;
                break;
                
            case 'cheque':
                detailsHtml = `
                    <div class="form-group">
                        <label>Cheque Number</label>
                        <input type="text" class="form-control" name="payment_details[cheque_number]" placeholder="CHQ123456">
                    </div>
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" class="form-control" name="payment_details[bank]" placeholder="Bank Name">
                    </div>
                    <div class="form-group">
                        <label>Cheque Date</label>
                        <input type="date" class="form-control" name="payment_details[cheque_date]">
                    </div>
                `;
                break;
                
            default:
                detailsHtml = `
                    <div class="form-group">
                        <label>Payment Details (Optional)</label>
                        <textarea class="form-control" name="payment_details" rows="2" 
                                  placeholder="Any additional details..."></textarea>
                    </div>
                `;
        }
        
        $('#paymentDetailsSection').html(detailsHtml);
    });

    // Trigger change to set initial state
    $('#paymentMethod').trigger('change');
    
    // Initialize dropdowns
    $('.dropdown-toggle').dropdown();
});
</script>
@endpush