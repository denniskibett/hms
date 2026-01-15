@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Edit Transaction</h2>
                <p class="text-gray-600 dark:text-gray-400">Update transaction #{{ $transaction->id }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('finance.show', $transaction) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    View Details
                </a>
                <a href="{{ route('finance.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('finance.update', $transaction) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Transaction Type -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transaction Type *</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="type" value="income" {{ $transaction->type == 'income' ? 'checked' : '' }} class="text-primary" required>
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Income</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="type" value="expense" {{ $transaction->type == 'expense' ? 'checked' : '' }} class="text-primary">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Expense</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="type" value="investment" {{ $transaction->type == 'investment' ? 'checked' : '' }} class="text-primary">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Investment</span>
                                    </label>
                                </div>
                                @error('type')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">$</span>
                                    </div>
                                    <input type="number" name="amount" step="0.01" required 
                                           class="pl-8 w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('amount', $transaction->amount) }}">
                                </div>
                                @error('amount')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                                <input type="date" name="date" required 
                                       class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                       value="{{ old('date', $transaction->date->format('Y-m-d')) }}">
                                @error('date')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                                <select name="category" required 
                                        class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                    <option value="">Select Category</option>
                                    <option value="room_booking" {{ old('category', $transaction->category) == 'room_booking' ? 'selected' : '' }}>Room Booking</option>
                                    <option value="food_beverage" {{ old('category', $transaction->category) == 'food_beverage' ? 'selected' : '' }}>Food & Beverage</option>
                                    <option value="maintenance" {{ old('category', $transaction->category) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="salaries" {{ old('category', $transaction->category) == 'salaries' ? 'selected' : '' }}>Salaries</option>
                                    <option value="utilities" {{ old('category', $transaction->category) == 'utilities' ? 'selected' : '' }}>Utilities</option>
                                    <option value="supplies" {{ old('category', $transaction->category) == 'supplies' ? 'selected' : '' }}>Supplies</option>
                                    <option value="marketing" {{ old('category', $transaction->category) == 'marketing' ? 'selected' : '' }}>Marketing</option>
                                    <option value="other" {{ old('category', $transaction->category) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('category')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                                <select name="status" required 
                                        class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                    <option value="pending" {{ old('status', $transaction->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ old('status', $transaction->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $transaction->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                                <select name="payment_method" 
                                        class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ old('payment_method', $transaction->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="credit_card" {{ old('payment_method', $transaction->payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ old('payment_method', $transaction->payment_method) == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                    <option value="bank_transfer" {{ old('payment_method', $transaction->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="digital_wallet" {{ old('payment_method', $transaction->payment_method) == 'digital_wallet' ? 'selected' : '' }}>Digital Wallet</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description *</label>
                                <textarea name="description" rows="3" required 
                                          class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('description', $transaction->description) }}</textarea>
                                @error('description')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Reference -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reference Number</label>
                                <input type="text" name="reference" 
                                       class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                       value="{{ old('reference', $transaction->reference) }}">
                            </div>

                            <!-- Current Attachment -->
                            @if($transaction->attachment)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Attachment</label>
                                <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded">
                                    <i class="fas fa-file text-primary mr-2"></i>
                                    <span>{{ basename($transaction->attachment) }}</span>
                                    <a href="{{ asset('storage/' . $transaction->attachment) }}" target="_blank" class="ml-auto text-primary hover:text-primary/80">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                            @endif

                            <!-- New Attachment -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Update Attachment</label>
                                <input type="file" name="attachment" 
                                       class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to keep current attachment</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('finance.show', $transaction) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                Update Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Transaction Summary -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Transaction Summary</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Original Amount:</span>
                        <span class="font-medium text-gray-800 dark:text-white">
                            ${{ number_format($transaction->amount, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Type:</span>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($transaction->type === 'income') bg-success/10 text-success 
                            @elseif($transaction->type === 'expense') bg-danger/10 text-danger 
                            @else bg-warning/10 text-warning @endif">
                            {{ ucfirst($transaction->type) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Status:</span>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($transaction->status === 'completed') bg-success/10 text-success 
                            @elseif($transaction->status === 'pending') bg-warning/10 text-warning 
                            @else bg-danger/10 text-danger @endif">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Created:</span>
                        <span class="text-gray-800 dark:text-white">{{ $transaction->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Update Notes -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Update Notes</h4>
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start">
                        <i class="fas fa-history text-primary mt-1 mr-2"></i>
                        <span>All changes are logged in the system audit trail</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-warning mt-1 mr-2"></i>
                        <span>Changing transaction type may affect financial reports</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user-check text-success mt-1 mr-2"></i>
                        <span>Your name will be recorded as the editor</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection