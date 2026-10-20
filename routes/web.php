<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\StayBillingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\FrontController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome'); // Your hotel template
})->name('home');
Route::get('/about', [FrontController::class, 'about'])->name('about');
Route::get('/contact', [FrontController::class, 'contact'])->name('contact');
Route::post('/contact', [FrontController::class, 'submitContact'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| Every module is limited to the roles that work in it. Admins pass every
| role check (see EnsureUserHasRole and the Gate::before in AppServiceProvider).
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Any signed-in user
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/address', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/data', [ProfileController::class, 'getData'])->name('profile.data');

    /*
    |--------------------------------------------------------------------------
    | Front Office
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:manager,receptionist')->group(function () {
        Route::resource('guests', GuestController::class);

        // Bookings
        Route::post('stays/quote', [StayController::class, 'quote'])->name('stays.quote');
        Route::resource('stays', StayController::class)->except(['destroy']);
        Route::post('stays/{stay}/check-in', [StayController::class, 'checkIn'])->name('stays.check-in');
        Route::post('stays/{stay}/check-out', [StayController::class, 'checkOut'])->name('stays.check-out');
        Route::post('stays/{stay}/cancel', [StayController::class, 'cancel'])->name('stays.cancel');
    });

    // Stay folio: front desk takes payments; finance too
    Route::middleware('role:manager,receptionist,finance')->group(function () {
        Route::post('stays/{stay}/payments', [StayBillingController::class, 'storePayment'])->name('stays.payments.store');
        Route::get('stays/{stay}/invoice', [StayBillingController::class, 'invoice'])->name('stays.invoice');
        Route::get('stays/{stay}/payments/{payment}/receipt', [StayBillingController::class, 'receipt'])->name('stays.receipt');
    });
    Route::post('stays/{stay}/payments/{payment}/void', [StayBillingController::class, 'voidPayment'])
        ->middleware('role:manager,finance')
        ->name('stays.payments.void');

    Route::middleware('role:manager,receptionist')->group(function () {
        Route::resource('room-types', RoomTypeController::class);
        Route::resource('facilities', FacilityController::class);
    });

    // Rooms: front office + housekeeping can view and change status; managers manage
    Route::middleware('role:manager,receptionist,housekeeping')->group(function () {
        Route::get('rooms/availability', [RoomController::class, 'availability'])->name('rooms.availability');
        Route::resource('rooms', RoomController::class)->only(['index', 'show']);
        Route::patch('rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.status');
    });
    Route::resource('rooms', RoomController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:manager');
    Route::delete('stays/{stay}', [StayController::class, 'destroy'])
        ->middleware('role:manager')
        ->name('stays.destroy');

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */
    Route::resource('tasks', TaskController::class)
        ->middleware('role:manager,receptionist,housekeeping,kitchen,procurement,hr,finance');

    Route::resource('kitchen', KitchenController::class)
        ->middleware('role:manager,kitchen');

    Route::resource('inventory', InventoryController::class)
        ->middleware('role:manager,procurement,kitchen');

    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:manager,finance')->group(function () {
        Route::resource('finance', FinanceController::class);
        Route::post('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
        Route::resource('invoices', InvoiceController::class);
        Route::resource('payments', PaymentController::class);

        Route::resource('reports', ReportController::class)->only([
            'index', 'show', 'store'
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Human Resource
    |--------------------------------------------------------------------------
    */
    Route::resource('hr', HRController::class)
        ->parameters(['hr' => 'staff'])
        ->middleware('role:manager,hr');

    /*
    |--------------------------------------------------------------------------
    | Administration (admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);

        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SystemController::class, 'index'])->name('index');
            Route::put('/update', [SystemController::class, 'update'])->name('update');
            Route::post('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
            Route::post('/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Template pages still linked from the UI
    |--------------------------------------------------------------------------
    */
    Route::view('/calendar', 'calendar')->name('calendar');
});

/*
|--------------------------------------------------------------------------
| Laravel Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
