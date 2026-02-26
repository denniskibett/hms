@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="container-fluid px-0">
    <!-- Overview -->
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-800 dark:text-white/90">Overview</h2>
            </div>
            <div>
                <button type="button" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition" data-toggle="modal" data-target="#createInvoiceModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M5 10.0002H15.0006M10.0002 5V15.0006" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Create an Invoice
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 rounded-xl border border-gray-200 sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-y-0 dark:divide-gray-800 dark:border-gray-800">
            <div class="border-b p-5 sm:border-r lg:border-b-0">
                <p class="mb-1.5 text-sm text-gray-400 dark:text-gray-500">Overdue</p>
                <h3 class="text-3xl text-gray-800 dark:text-white/90">KSH {{ number_format($overdueAmount, 2) }}</h3>
            </div>
            <div class="border-b p-5 lg:border-b-0">
                <p class="mb-1.5 text-sm text-gray-400 dark:text-gray-500">Due within next 30 days</p>
                <h3 class="text-3xl text-gray-800 dark:text-white/90">KSH {{ number_format($dueNext30Days, 2) }}</h3>
            </div>
            <div class="border-b p-5 sm:border-r sm:border-b-0">
                <p class="mb-1.5 text-sm text-gray-400 dark:text-gray-500">Average time to get paid</p>
                <h3 class="text-3xl text-gray-800 dark:text-white/90">{{ $avgDays }} days</h3>
            </div>
            <div class="p-5">
                <p class="mb-1.5 text-sm text-gray-400 dark:text-gray-500">Upcoming Payout</p>
                <h3 class="text-3xl text-gray-800 dark:text-white/90">KSH {{ number_format($upcomingPayout, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="invoiceTable()">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Invoices</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your most recent invoices list</p>
            </div>
            <div class="flex gap-3.5">
                <div class="hidden h-11 items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 lg:inline-flex dark:bg-gray-900">
                    <button @click="filterStatus = 'All'; currentPage = 1" :class="filterStatus === 'All' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium hover:text-gray-900 dark:hover:text-white">
                        All Invoices
                    </button>
                    <button @click="filterStatus = 'Unpaid'; currentPage = 1" :class="filterStatus === 'Unpaid' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium hover:text-gray-900 dark:hover:text-white">
                        Unpaid
                    </button>
                    <button @click="filterStatus = 'Draft'; currentPage = 1" :class="filterStatus === 'Draft' ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400'" class="text-theme-sm h-10 rounded-md px-3 py-2 font-medium hover:text-gray-900 dark:hover:text-white">
                        Draft
                    </button>
                </div>
                <div class="hidden flex-col gap-3 sm:flex sm:flex-row sm:items-center">
                    <div class="relative">
                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill=""/>
                            </svg>
                        </span>
                        <input type="text" placeholder="Search..." x-model="searchQuery" @input="filterInvoices()" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"/>
                    </div>

                    <div class="relative" x-data="{ showFilter: false }">
                        <button class="shadow-theme-xs flex h-11 w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 sm:w-auto sm:min-w-[100px] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" @click="showFilter = !showFilter" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M14.6537 5.90414C14.6537 4.48433 13.5027 3.33331 12.0829 3.33331C10.6631 3.33331 9.51206 4.48433 9.51204 5.90415M14.6537 5.90414C14.6537 7.32398 13.5027 8.47498 12.0829 8.47498C10.663 8.47498 9.51204 7.32398 9.51204 5.90415M14.6537 5.90414L17.7087 5.90411M9.51204 5.90415L2.29199 5.90411M5.34694 14.0958C5.34694 12.676 6.49794 11.525 7.91777 11.525C9.33761 11.525 10.4886 12.676 10.4886 14.0958M5.34694 14.0958C5.34694 15.5156 6.49794 16.6666 7.91778 16.6666C9.33761 16.6666 10.4886 15.5156 10.4886 14.0958M5.34694 14.0958L2.29199 14.0958M10.4886 14.0958L17.7087 14.0958" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Filter
                        </button>
                        <div x-show="showFilter" @click.away="showFilter = false" class="absolute right-0 z-10 mt-2 w-56 rounded-lg border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-5">
                                <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">Customer</label>
                                <input type="text" x-model="customerFilter" @input="filterInvoices()" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Search customer..."/>
                            </div>
                            <div class="mb-5">
                                <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">Date From</label>
                                <input type="date" x-model="dateFrom" @input="filterInvoices()" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"/>
                            </div>
                            <div class="mb-5">
                                <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">Date To</label>
                                <input type="date" x-model="dateTo" @input="filterInvoices()" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"/>
                            </div>
                            <button @click="resetFilters()" class="bg-brand-500 hover:bg-brand-600 h-10 w-full rounded-lg px-3 py-2 text-sm font-medium text-white">
                                Apply
                            </button>
                        </div>
                    </div>
                    <button @click="exportInvoices()" class="shadow-theme-xs flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-[11px] text-sm font-medium text-gray-700 sm:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M16.6671 13.3333V15.4166C16.6671 16.1069 16.1074 16.6666 15.4171 16.6666H4.58301C3.89265 16.6666 3.33301 16.1069 3.33301 15.4166V13.3333M10.0013 3.33325L10.0013 13.3333M6.14553 7.18708L9.99958 3.33549L13.8539 7.18708" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>
        <div class="custom-scrollbar overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="border-b border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                        <th class="p-4 whitespace-nowrap">
                            <div class="flex w-full cursor-pointer items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <label class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                                        <span class="relative">
                                            <input type="checkbox" class="sr-only" @change="toggleSelectAll" :checked="isAllSelected"/>
                                            <span :class="isAllSelected ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'" class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px]">
                                                <span :class="isAllSelected ? '' : 'opacity-0'">
                                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                        <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                    <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Invoice Number</p>
                                </div>
                            </div>
                        </th>
                        <th class="cursor-pointer p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('customer')">
                            <div class="flex items-center gap-3">
                                <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Customer</p>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sortBy === 'customer' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sortBy === 'customer' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th class="cursor-pointer p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('creationDate')">
                            <div class="flex items-center gap-3">
                                <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Creation Date</p>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sortBy === 'creationDate' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sortBy === 'creationDate' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th class="cursor-pointer p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400" @click="sort('dueDate')">
                            <div class="flex items-center gap-3">
                                <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Due Date</p>
                                <span class="flex flex-col gap-0.5">
                                    <svg :class="sortBy === 'dueDate' && sortDirection === 'asc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z" fill="currentColor"/>
                                    </svg>
                                    <svg :class="sortBy === 'dueDate' && sortDirection === 'desc' ? 'text-brand-500' : 'text-gray-300'" width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Total</th>
                        <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">Status</th>
                        <th class="p-4 text-left text-xs font-medium text-gray-700 dark:text-gray-400">
                            <div class="relative">
                                <span class="sr-only">Action</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-x divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($invoices as $invoice)
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="p-4 whitespace-nowrap">
                            <div class="group flex items-center gap-3">
                                <label class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                                    <span class="relative">
                                        <input type="checkbox" class="sr-only invoice-checkbox" data-id="{{ $invoice->id }}"/>
                                        <span class="flex h-4 w-4 items-center justify-center rounded-sm border-[1.25px] border-gray-300 dark:border-gray-700 bg-transparent">
                                            <span class="opacity-0">
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                    <path d="M10 3L4.5 8.5L2 6" stroke="white" stroke-width="1.6666" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-theme-xs font-medium text-gray-700 group-hover:underline dark:text-gray-400">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ $invoice->stay->guest->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <p class="text-sm text-gray-700 dark:text-gray-400">
                                {{ $invoice->issue_date->format('M d, Y') }}
                            </p>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <p class="text-sm text-gray-700 dark:text-gray-400">
                                {{ $invoice->due_date->format('M d, Y') }}
                            </p>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <p class="text-sm text-gray-700 dark:text-gray-400">
                                KSH {{ number_format($invoice->total, 2) }}
                            </p>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span :class="{
                                'bg-success-50 dark:bg-success-500/15 text-success-700 dark:text-success-500': '{{ $invoice->status }}' === 'paid',
                                'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': '{{ $invoice->status }}' === 'sent' || '{{ $invoice->status }}' === 'partial',
                                'bg-warning-50 dark:bg-warning-500/15 text-warning-700 dark:text-warning-500': '{{ $invoice->status }}' === 'draft',
                                'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400': '{{ $invoice->status }}' === 'overdue'
                            }" class="text-theme-xs rounded-full px-2 py-0.5 font-medium">
                                @if($invoice->is_overdue && $invoice->status !== 'paid')
                                    Overdue
                                @else
                                    {{ ucfirst($invoice->status) }}
                                @endif
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
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                        View More
                                    </a>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                        Download PDF
                                    </a>
                                    @if($invoice->status === 'draft')
                                    <form action="{{ route('invoices.show', $invoice) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-red-500 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this invoice?')">
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
        <div class="flex flex-col items-center justify-between border-t border-gray-200 px-5 py-4 sm:flex-row dark:border-gray-800">
            <div class="pb-3 sm:pb-0">
                <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                    Showing
                    <span class="text-gray-800 dark:text-white/90">{{ $invoices->firstItem() }}</span>
                    to
                    <span class="text-gray-800 dark:text-white/90">{{ $invoices->lastItem() }}</span>
                    of
                    <span class="text-gray-800 dark:text-white/90">{{ $invoices->total() }}</span>
                </span>
            </div>
            <div class="flex w-full items-center justify-between gap-2 rounded-lg bg-gray-50 p-4 sm:w-auto sm:justify-normal sm:bg-transparent sm:p-0 dark:bg-white/[0.03] dark:sm:bg-transparent">
                @if($invoices->previousPageUrl())
                <a href="{{ $invoices->previousPageUrl() }}" class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z" fill=""/>
                    </svg>
                </a>
                @endif

                <span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400">
                    Page {{ $invoices->currentPage() }} of {{ $invoices->lastPage() }}
                </span>

                <ul class="hidden items-center gap-0.5 sm:flex">
                    @foreach($invoices->getUrlRange(1, $invoices->lastPage()) as $page => $url)
                    @if($page == $invoices->currentPage())
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

                @if($invoices->nextPageUrl())
                <a href="{{ $invoices->nextPageUrl() }}" class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z" fill=""/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Invoice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Guest/Stay</label>
                                <select class="form-control select2" name="stay_id" required>
                                    <option value="">-- Select Guest/Stay --</option>
                                    @foreach($stays as $stay)
                                    <option value="{{ $stay->id }}">
                                        {{ $stay->guest->name }} - Stay #{{ $stay->id }} ({{ $stay->roomAllocations->first()->room->room_number ?? 'No Room' }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" class="form-control" name="due_date" min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Invoice Items -->
                    <div class="row">
                        <div class="col-12">
                            <h5>Invoice Items</h5>
                            <div id="invoice-items-container">
                                <div class="invoice-item row mb-2">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="items[0][description]" placeholder="Description" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" name="items[0][quantity]" placeholder="Qty" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control" name="items[0][unit_price]" placeholder="Unit Price" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-control" name="items[0][source_type]" required>
                                            <option value="room">Room</option>
                                            <option value="food">Food & Beverage</option>
                                            <option value="facility">Facility</option>
                                            <option value="service">Service</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger remove-item"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success mt-2" id="add-item-btn">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Notes (Optional)</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function invoiceTable() {
    return {
        searchQuery: '',
        customerFilter: '',
        dateFrom: '',
        dateTo: '',
        filterStatus: 'All',
        selected: [],
        sortBy: 'number',
        sortDirection: 'asc',
        currentPage: 1,
        itemsPerPage: 10,
        
        filterInvoices() {
            // This would be handled server-side in a real application
            // For now, we'll just submit the form
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
            
            if (this.customerFilter) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'customer';
                input.value = this.customerFilter;
                form.appendChild(input);
            }
            
            if (this.dateFrom) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'date_from';
                input.value = this.dateFrom;
                form.appendChild(input);
            }
            
            if (this.dateTo) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'date_to';
                input.value = this.dateTo;
                form.appendChild(input);
            }
            
            if (this.filterStatus !== 'All') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'status';
                input.value = this.filterStatus;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        },
        
        resetFilters() {
            this.searchQuery = '';
            this.customerFilter = '';
            this.dateFrom = '';
            this.dateTo = '';
            this.filterStatus = 'All';
            window.location.href = window.location.pathname;
        },
        
        exportInvoices() {
            const params = new URLSearchParams();
            if (this.searchQuery) params.append('search', this.searchQuery);
            if (this.customerFilter) params.append('customer', this.customerFilter);
            if (this.dateFrom) params.append('date_from', this.dateFrom);
            if (this.dateTo) params.append('date_to', this.dateTo);
            if (this.filterStatus !== 'All') params.append('status', this.filterStatus);
            
            window.location.href = `{{ route('invoices.create') }}?${params.toString()}`;
        },
        
        toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.invoice-checkbox');
            const isChecked = !this.isAllSelected;
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const id = checkbox.dataset.id;
                if (isChecked && !this.selected.includes(id)) {
                    this.selected.push(id);
                } else if (!isChecked) {
                    this.selected = this.selected.filter(i => i !== id);
                }
            });
        },
        
        get isAllSelected() {
            const checkboxes = document.querySelectorAll('.invoice-checkbox');
            return checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
        },
        
        sort(field) {
            if (this.sortBy === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = field;
                this.sortDirection = 'asc';
            }
        }
    };
}

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Select...'
    });
    
    // Add invoice item
    let itemCount = 1;
    $('#add-item-btn').click(function() {
        const itemHtml = `
        <div class="invoice-item row mb-2">
            <div class="col-md-5">
                <input type="text" class="form-control" name="items[${itemCount}][description]" placeholder="Description" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="items[${itemCount}][quantity]" placeholder="Qty" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="items[${itemCount}][unit_price]" placeholder="Unit Price" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <select class="form-control" name="items[${itemCount}][source_type]" required>
                    <option value="room">Room</option>
                    <option value="food">Food & Beverage</option>
                    <option value="facility">Facility</option>
                    <option value="service">Service</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-item"><i class="fas fa-trash"></i></button>
            </div>
        </div>`;
        $('#invoice-items-container').append(itemHtml);
        itemCount++;
    });
    
    // Remove invoice item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.invoice-item').remove();
    });
    
    // Initialize dropdowns
    $('.dropdown-toggle').dropdown();
});
</script>
@endpush