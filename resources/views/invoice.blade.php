@extends('layouts.app')

@section('content')<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - TailAdmin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        'primary-dark': '#4338ca',
                        secondary: '#64748b',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        dark: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #1f2937;
        }
        
        .dark body {
            background-color: #111827;
            color: #f3f4f6;
        }
        
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .invoice-header {
            background: linear-gradient(90deg, #4f46e5, #818cf8);
        }
        
        .invoice-table th {
            background-color: #f3f4f6;
        }
        
        .dark .invoice-table th {
            background-color: #1f2937;
        }
        
        .invoice-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .dark .invoice-table tr:nth-child(even) {
            background-color: #111827;
        }
        
        .total-section {
            background-color: #f9fafb;
        }
        
        .dark .total-section {
            background-color: #1f2937;
        }
        
        .btn-primary {
            background-color: #4f46e5;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .print-only {
                display: block;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Breadcrumb -->
        <div class="mx-auto max-w-screen-2xl px-4 py-4 md:px-6 md:py-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary dark:text-gray-400 dark:hover:text-white">
                            <i class="fas fa-home mr-2"></i>
                            Home
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Invoice</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Main Content -->
        <main>
            <div class="invoice-container rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-gray-800 xl:px-10 xl:py-12">
                <!-- Invoice Header -->
                <div class="invoice-header flex flex-col justify-between rounded-lg p-6 text-white md:flex-row">
                    <div class="mb-6 md:mb-0">
                        <div class="mb-2 flex items-center">
                            <div class="mr-3 h-10 w-10 rounded-lg bg-white p-2">
                                <div class="h-6 w-6 rounded bg-primary"></div>
                            </div>
                            <h1 class="text-xl font-bold">TailAdmin</h1>
                        </div>
                        <p class="text-white/80">Office: 149, 450 South Brand Brooklyn</p>
                        <p class="text-white/80">New York, United States of America</p>
                    </div>
                    
                    <div class="text-right">
                        <h2 class="mb-2 text-2xl font-bold">INVOICE</h2>
                        <p class="mb-1 text-white/80">#INV-0032</p>
                        <p class="text-white/80">Date: Jan 27, 2025</p>
                    </div>
                </div>
                
                <!-- Bill To / Ship To -->
                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div>
                        <h3 class="mb-3 text-lg font-semibold text-gray-800 dark:text-white">Bill To:</h3>
                        <p class="font-medium">David Peterson</p>
                        <p class="text-gray-600 dark:text-gray-300">299, The Mountain</p>
                        <p class="text-gray-600 dark:text-gray-300">Los Angeles, CA, 94111</p>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">Email: david@company.com</p>
                        <p class="text-gray-600 dark:text-gray-300">Phone: +1 (123) 456 789</p>
                    </div>
                    
                    <div>
                        <h3 class="mb-3 text-lg font-semibold text-gray-800 dark:text-white">Ship To:</h3>
                        <p class="font-medium">David Peterson</p>
                        <p class="text-gray-600 dark:text-gray-300">365, The Mountain</p>
                        <p class="text-gray-600 dark:text-gray-300">Los Angeles, CA, 94111</p>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">Email: david@company.com</p>
                        <p class="text-gray-600 dark:text-gray-300">Phone: +1 (123) 456 789</p>
                    </div>
                </div>
                
                <!-- Invoice Table -->
                <div class="mt-8 overflow-x-auto">
                    <table class="invoice-table w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="border-b px-4 py-3 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Item</th>
                                <th class="border-b px-4 py-3 text-right font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Price</th>
                                <th class="border-b px-4 py-3 text-right font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Qty</th>
                                <th class="border-b px-4 py-3 text-right font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border-b px-4 py-3 dark:border-gray-700">
                                    <p class="font-medium">Premium Support</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">24/7 technical support</p>
                                </td>
                                <td class="border-b px-4 py-3 text-right dark:border-gray-700">$9.00</td>
                                <td class="border-b px-4 py-3 text-right dark:border-gray-700">2</td>
                                <td class="border-b px-4 py-3 text-right font-medium dark:border-gray-700">$18.00</td>
                            </tr>
                            <tr>
                                <td class="border-b px-4 py-3 dark:border-gray-700">
                                    <p class="font-medium">Design Customization</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">UI/UX design services</p>
                                </td>
                                <td class="border-b px-4 py-3 text-right dark:border-gray-700">$150.00</td>
                                <td class="border-b px-4 py-3 text-right dark:border-gray-700">1</td>
                                <td class="border-b px-4 py-3 text-right font-medium dark:border-gray-700">$150.00</td>
                            </tr>
                            <tr>
                                <td class="border-b px-4 py-3 dark:border-gray-700">
                                    <p class="font-medium">Web Development</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Custom web application</p>
                                </td>
                                <td class="border-b px-4 py-3 text-right dark:border-gray-700">$500.00</td>
                                <td class="border-b px-4 py-3 text-right dark:border-gray-700">1</td>
                                <td class="border-b px-4 py-3 text-right font-medium dark:border-gray-700">$500.00</td>
                            </tr>
                            <tr>
                                <td class="border-b px-4 py-3 dark:border-gray-700">
                                    <p class="font-medium">SEO Optimization</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Search engine optimization</p>
                                </td>
                                <td class="border-b px-4 py-3 text-right dark:border-gray-700">$200.00</td>
                                <td class="border-b px-4 py-3 text-right dark:border-gray-700">1</td>
                                <td class="border-b px-4 py-3 text-right font-medium dark:border-gray-700">$200.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Notes and Total -->
                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div>
                        <h3 class="mb-3 text-lg font-semibold text-gray-800 dark:text-white">Notes:</h3>
                        <p class="text-gray-600 dark:text-gray-300">Thank you for your business. Please make sure to check our updates and new features.</p>
                        
                        <div class="mt-6">
                            <h3 class="mb-3 text-lg font-semibold text-gray-800 dark:text-white">Payment Terms:</h3>
                            <p class="text-gray-600 dark:text-gray-300">Please pay within 15 days. Thank you!</p>
                        </div>
                    </div>
                    
                    <div>
                        <div class="total-section rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600 dark:text-gray-300">Subtotal:</span>
                                <span class="font-medium">$868.00</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600 dark:text-gray-300">Discount (10%):</span>
                                <span class="font-medium">-$86.80</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600 dark:text-gray-300">Tax (8%):</span>
                                <span class="font-medium">$62.50</span>
                            </div>
                            <div class="mt-3 flex justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                                <span class="text-lg font-semibold">Total:</span>
                                <span class="text-lg font-semibold text-primary">$843.70</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Info -->
                <div class="mt-8 rounded-lg border border-gray-200 p-6 dark:border-gray-700">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Payment Information:</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-gray-600 dark:text-gray-300">Account No:</p>
                            <p class="font-medium">1234567890</p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-300">Account Name:</p>
                            <p class="font-medium">TailAdmin LLC</p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-300">Bank Name:</p>
                            <p class="font-medium">Bank of America</p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-300">SWIFT Code:</p>
                            <p class="font-medium">BOFAUS3N</p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="mt-10 flex flex-col justify-between border-t border-gray-200 pt-6 dark:border-gray-700 sm:flex-row">
                    <div class="mb-4 sm:mb-0">
                        <h4 class="font-semibold text-gray-800 dark:text-white">Terms & Conditions</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Payment is due within 15 days</p>
                    </div>
                    <div class="flex space-x-4">
                        <button class="btn-primary flex items-center rounded-lg px-4 py-2.5 font-medium text-white no-print">
                            <i class="fas fa-print mr-2"></i> Print Invoice
                        </button>
                        <button class="flex items-center rounded-lg border border-gray-300 px-4 py-2.5 font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300 no-print">
                            <i class="fas fa-download mr-2"></i> Download
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Print Message (Only visible when printing) -->
    <div class="print-only fixed inset-0 flex items-center justify-center bg-white p-10 text-center">
        <div>
            <div class="mb-4 flex justify-center">
                <div class="flex items-center">
                    <div class="mr-3 h-10 w-10 rounded-lg bg-primary p-2">
                        <div class="h-6 w-6 rounded bg-white"></div>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">TailAdmin</h1>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Thank You For Your Business!</h1>
            <p class="mt-4 text-lg text-gray-700">Invoice #INV-0032 has been processed successfully.</p>
            <p class="mt-2 text-gray-700">Amount Paid: <span class="font-semibold">$843.70</span></p>
        </div>
    </div>
@endsection