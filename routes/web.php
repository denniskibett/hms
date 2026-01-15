<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\StayController;
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
use App\Http\Controllers\Auth\PasswordResetLinkController;
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
Route::get('/contact-submit', [FrontController::class, 'contact-submit'])->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Authentication (Google OAuth)
|--------------------------------------------------------------------------
*/

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('login.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Password reset routes (public)
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'verified'])->group(function () {

    
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    // Route::prefix('profile')->name('profile.')->group(function () {
    //     Route::get('/', [ProfileController::class, 'show'])->name('show');
    //     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    //     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    //     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // });

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/address', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/data', [ProfileController::class, 'getUserData'])->name('profile.data');




    /*
    |--------------------------------------------------------------------------
    | Core Management Modules (CRUD ONLY)
    |--------------------------------------------------------------------------
    */
    Route::resource('users', UserController::class);
    Route::resource('guests', GuestController::class);
    Route::prefix('guests/{guest}')->group(function () {
        Route::post('/stays', [GuestController::class, 'createStay'])->name('guest.stays.store');
        Route::post('/stays/{stay}/checkin', [GuestController::class, 'checkinStay'])->name('guest.stays.checkin');
        Route::post('/stays/{stay}/checkout', [GuestController::class, 'checkoutStay'])->name('guest.stays.checkout');
    });
    // Route::prefix('guest')->name('guest.')->group(function () {
    //     Route::get('/stats', [GuestController::class, 'stats'])->name('stats');
    // });

    Route::resource('rooms', RoomController::class);
    Route::resource('room-types', RoomTypeController::class);
    Route::resource('stays', StayController::class);
    Route::resource('tasks', TaskController::class);
    Route::resource('inventory', InventoryController::class);
    Route::resource('facilities', FacilityController::class);
    Route::resource('kitchen', KitchenController::class);

    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */
    Route::resource('finance', FinanceController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::resource('payments', PaymentController::class);

    /*
    |--------------------------------------------------------------------------
    | Human Resource
    |--------------------------------------------------------------------------
    */
    Route::resource('hr', HRController::class)->parameters([
        'hr' => 'staff'
    ]);
  

    /*
    |--------------------------------------------------------------------------
    | Reports (Read / Generate Only)
    |--------------------------------------------------------------------------
    */
    Route::resource('reports', ReportController::class)->only([
        'index', 'show', 'store'
    ]);

    /*
    |--------------------------------------------------------------------------
    | System Settings
    |--------------------------------------------------------------------------
    */
    // Route::resource('system', SystemController::class)->only([
    //     'index', 'update'
    // ]);

        // System Settings
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/', [SystemController::class, 'index'])->name('index');
        Route::put('/update', [SystemController::class, 'update'])->name('update');
        Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
        Route::get('/backup', [SystemController::class, 'backupDatabase'])->name('backup');
        Route::post('/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        Route::post('/debug', [SystemController::class, 'debug'])->name('debug');
    });

    /*
    |--------------------------------------------------------------------------
    | Static / Template Pages (kept intentionally)
    |--------------------------------------------------------------------------
    */
    Route::view('/index', 'index')->name('index');
    Route::view('/invoice', 'invoice')->name('invoice');
    Route::view('/404', '404')->name('404');
    Route::view('/messages', 'messages')->name('messages');
    Route::view('/alerts', 'alerts')->name('alerts');
    Route::view('/blank', 'blank')->name('blank');
    Route::view('/calendar', 'calendar')->name('calendar');
    Route::view('/form-elements', 'form-elements')->name('form-elements');
    Route::view('/basic-tables', 'basic-tables')->name('basic-tables');
    Route::view('/avatars', 'avatars')->name('avatars');
    Route::view('/badge', 'badge')->name('badge');
    Route::view('/buttons', 'buttons')->name('buttons');
    Route::view('/images', 'images')->name('images');
    Route::view('/videos', 'videos')->name('videos');
    Route::view('/signin', 'signin')->name('signin');
    Route::view('/signup', 'signup')->name('signup');
    Route::view('/line-chart', 'line-chart')->name('line-chart');
    Route::view('/bar-chart', 'bar-chart')->name('bar-chart');
    Route::view('/dash', 'dash')->name('dash');
});

/*
|--------------------------------------------------------------------------
| Laravel Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
